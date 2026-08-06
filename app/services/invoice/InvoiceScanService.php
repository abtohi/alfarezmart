<?php
/**
 * InvoiceScanService
 *
 * Orchestrates the full 11-stage AI invoice scanning pipeline.
 * Replaces the monolithic logic previously in ApiController::scanInvoiceAI.
 *
 * @package AlfarezMart\Services\Invoice
 */
class InvoiceScanService
{
    /** @var \PDO */
    private $db;
    /** @var SettingModel */
    private $settingModel;
    /** @var ProductModel */
    private $productModel;

    // Sub-services
    /** @var ImagePreprocessor */
    private $preprocessor;
    /** @var PromptBuilder */
    private $promptBuilder;
    /** @var LayoutAnalyzer */
    private $layoutAnalyzer;
    /** @var TableParser */
    private $tableParser;
    /** @var InvoiceValidator */
    private $validator;
    /** @var ProductMatcher */
    private $matcher;
    /** @var ConfidenceScorer */
    private $scorer;
    /** @var SelfCorrectionEngine */
    private $selfCorrection;
    /** @var TemplateLearner */
    private $templateLearner;

    public function __construct(\PDO $db)
    {
        $this->db           = $db;
        $this->settingModel = new SettingModel();
        $this->productModel = new ProductModel();

        // Initialize pipeline components
        $this->preprocessor    = new ImagePreprocessor();
        $this->promptBuilder   = new PromptBuilder($this->db, $this->settingModel);
        $this->layoutAnalyzer  = new LayoutAnalyzer();
        $this->tableParser     = new TableParser($this->layoutAnalyzer);
        $this->validator       = new InvoiceValidator();
        $this->matcher         = new ProductMatcher($this->layoutAnalyzer);
        $this->scorer          = new ConfidenceScorer();
        $this->selfCorrection  = new SelfCorrectionEngine();
        $this->templateLearner = new TemplateLearner($this->db);

        // Ensure database schema is up to date
        $this->ensureSupplierProductCodeColumn();
    }

    /**
     * Ensure the supplier_product_code column exists in supplier_products table.
     */
    private function ensureSupplierProductCodeColumn(): void
    {
        try {
            $this->db->query("SELECT supplier_product_code FROM supplier_products LIMIT 1");
        } catch (\PDOException $e) {
            // Column does not exist
            try {
                $this->db->exec("ALTER TABLE supplier_products ADD COLUMN supplier_product_code VARCHAR(100) DEFAULT NULL AFTER product_id");
                $this->db->exec("CREATE INDEX idx_sp_code ON supplier_products(supplier_product_code)");
            } catch (\PDOException $e2) {
                error_log('Failed to add supplier_product_code column: ' . $e2->getMessage());
            }
        }
    }

    // ----------------------------------------------------------------
    // PUBLIC API
    // ----------------------------------------------------------------

    /**
     * Run the full invoice scanning pipeline.
     *
     * @param  string   $imageB64     Raw base64 image
     * @param  int|null $supplierId   Optional supplier ID context
     * @return array{
     *   success: bool,
     *   message: string,
     *   data: array,
     *   metadata: array
     * }
     */
    public function scan(string $imageB64, ?int $supplierId = null): array
    {
        $startTime = microtime(true);

        try {
            // ================================================================
            // STAGE 1: Image Preprocessing & Validation
            // ================================================================
            $imageInfo = $this->preprocessor->analyze($imageB64);
            if (!$imageInfo['valid']) {
                throw new \Exception($imageInfo['error'] ?? 'Gambar tidak valid');
            }

            // ================================================================
            // STAGE 2: Context Gathering (Products & Templates)
            // ================================================================
            $allProducts = $this->getAllProductsWithPackagings();
            $supplierProducts = [];
            $supplierName = 'Unknown Supplier';

            if ($supplierId && $supplierId > 0) {
                // Get supplier products for context
                $supplierProducts = $this->getSupplierProducts($supplierId);
                $stmt = $this->db->prepare("SELECT name FROM suppliers WHERE id = ?");
                $stmt->execute([$supplierId]);
                $supplierName = $stmt->fetchColumn() ?: 'Unknown Supplier';
            }

            // TEMPORARY: Bypass old templates to force the AI to read the new BSR/TGH rules
            // $template = $this->templateLearner->findTemplate($supplierId);
            $template = null;

            // ================================================================
            // STAGE 3: Prompt Building
            // ================================================================
            $prompts = $this->promptBuilder->build(
                $imageInfo,
                $supplierProducts,
                $template,
                false, // not correction pass
                []
            );

            // ================================================================
            // STAGE 4: First AI Call (OpenRouter)
            // ================================================================
            $aiResponse = $this->callOpenRouter($prompts['system'], $prompts['user'], $imageB64, $imageInfo['format']);
            if (empty($aiResponse)) {
                throw new \Exception('AI gagal memproses gambar atau mengembalikan respons kosong.');
            }

            // ================================================================
            // STAGE 5: Run Pipeline (Layout -> Parse -> Validate -> Match -> Score)
            // ================================================================
            $pipelineResult = $this->runExtractionPipeline($aiResponse, $allProducts, $supplierProducts);
            $items          = $pipelineResult['items'];
            $hasLowConf     = $pipelineResult['has_low_confidence'];
            $avgConf        = $pipelineResult['avg_confidence'];
            $colMap         = $pipelineResult['column_map'];

            // Identify items needing correction (low conf or validation failed)
            $correctionHints = [];
            foreach ($items as $itm) {
                if (($itm['needs_review'] ?? false) || ($itm['validation_failed'] ?? false)) {
                    $correctionHints[] = [
                        'name'   => $itm['name'] ?? '',
                        'issues' => $itm['issues'] ?? []
                    ];
                }
            }

            // ================================================================
            // STAGE 6: Self-Correction (if needed)
            // ================================================================
            if ($hasLowConf || !empty($correctionHints)) {
                $items = $this->selfCorrection->correct(
                    $items,
                    $hasLowConf,
                    $correctionHints,
                    $imageB64,
                    $this->getApiKey(),
                    $this->getModelName(),
                    function($hints) use ($imageInfo, $supplierProducts, $template) {
                        return $this->promptBuilder->build($imageInfo, $supplierProducts, $template, true, $hints);
                    },
                    function($sys, $usr, $img, $key, $mod) use ($imageInfo) {
                        return $this->callOpenRouter($sys, $usr, $img, $imageInfo['format'], $key, $mod);
                    },
                    function($rawAiResp) use ($allProducts, $supplierProducts) {
                        return $this->runExtractionPipeline($rawAiResp, $allProducts, $supplierProducts)['items'];
                    }
                );

                // Re-calculate average confidence after correction
                $totalConf = 0;
                foreach ($items as $itm) {
                    $totalConf += ($itm['confidence']['final'] ?? 0);
                }
                if (count($items) > 0) {
                    $avgConf = round($totalConf / count($items), 3);
                }
            }

            // ================================================================
            // STAGE 7: Template Learning
            // ================================================================
            if (count($items) > 0 && $avgConf >= TemplateLearner::MIN_CONFIDENCE_TO_SAVE) {
                $this->templateLearner->saveTemplate(
                    $supplierId,
                    $supplierName,
                    $colMap,
                    LayoutAnalyzer::HEADER_ALIASES,
                    $avgConf
                );
            }

            // ================================================================
            // STAGE 8: Format Final Output (Backward Compatibility)
            // ================================================================
            $finalItems = $this->formatForFrontend($items);

            $executionTime = round(microtime(true) - $startTime, 2);

            return [
                'success' => true,
                'message' => "Berhasil memproses " . count($finalItems) . " item (Avg Confidence: " . ($avgConf * 100) . "%)",
                'data'    => $finalItems,
                'metadata'=> [
                    'avg_confidence' => $avgConf,
                    'execution_time' => $executionTime,
                    'columns_detected'=> $colMap,
                ]
            ];

        } catch (\Throwable $e) {
            error_log('InvoiceScanService error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }
    }

    // ----------------------------------------------------------------
    // PRIVATE PIPELINE RUNNER
    // ----------------------------------------------------------------

    /**
     * Run the extraction pipeline on a raw AI JSON response.
     */
    private function runExtractionPipeline(array $rawAiResponse, array $allProducts, array $supplierProducts): array
    {
        $supplierProductIds = array_column($supplierProducts, 'id');

        // 1. Analyze layout and semantic columns
        $layoutResult = $this->layoutAnalyzer->analyze($rawAiResponse);
        
        // 2. Parse into clean table
        $parsedItems = $this->tableParser->parse($layoutResult['items']);
        
        // 3. Validate logical consistency
        $valResult = $this->validator->validate($parsedItems, $layoutResult['invoice_total']);
        $validatedItems = $valResult['items'];

        // 4. Product matching & Scoring
        $finalItems = [];
        foreach ($validatedItems as $item) {
            // Match against DB
            $matchResult = $this->matcher->match($item, $allProducts, $supplierProductIds);
            $mergedItem  = array_merge($item, $matchResult);

            // Score confidence
            $scoredItem  = $this->scorer->score($mergedItem);
            
            $finalItems[] = $scoredItem;
        }

        // 5. Aggregate scores
        $scoreResult = $this->scorer->scoreAll($finalItems);

        return [
            'items'              => $scoreResult['items'],
            'has_low_confidence' => $scoreResult['has_low_confidence'],
            'avg_confidence'     => $scoreResult['avg_confidence'],
            'column_map'         => $layoutResult['detected_columns'],
        ];
    }

    // ----------------------------------------------------------------
    // PRIVATE DATA FETCHERS
    // ----------------------------------------------------------------

    private function getAllProductsWithPackagings(): array
    {
        // Use a cached or lightweight fetch if possible
        $stmt = $this->db->query("
            SELECT p.id, p.full_name, p.code, p.supplier_invoice_name, p.short_label, 
                   p.variant, p.weight_value, p.weight_unit, b.name as brand_name,
                   (SELECT supplier_product_code FROM supplier_products sp WHERE sp.product_id = p.id LIMIT 1) as supplier_product_code
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.is_active = 1
        ");
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Attach packagings
        $this->productModel->attachPackagingsForProductList($products);
        return $products;
    }

    private function getSupplierProducts(int $supplierId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.full_name, p.code, p.supplier_invoice_name, p.short_label,
                   sp.supplier_product_code, sp.last_buy_price
            FROM supplier_products sp
            JOIN products p ON sp.product_id = p.id
            WHERE sp.supplier_id = ? AND p.is_active = 1
        ");
        $stmt->execute([$supplierId]);
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Attach packagings
        $this->productModel->attachPackagingsForProductList($products);
        return $products;
    }

    private function getApiKey(): string
    {
        $key = $this->settingModel->get('ai_api_key', '');
        return trim($key);
    }

    private function getModelName(): string
    {
        return $this->settingModel->get('ai_model', 'openrouter/auto');
    }

    // ----------------------------------------------------------------
    // API CALLER
    // ----------------------------------------------------------------

    private function callOpenRouter(
        string $systemPrompt,
        string $userPrompt,
        string $imageB64,
        string $imageFormat,
        ?string $apiKey = null,
        ?string $model = null
    ): ?array {
        $apiKey = $apiKey ?? $this->getApiKey();
        $model  = $model  ?? $this->getModelName();

        if (empty($apiKey)) {
            throw new \Exception('API Key AI Scanner belum diatur di Pengaturan Aplikasi.');
        }

        // Clean base64 data prefix if present
        if (strpos($imageB64, 'base64,') !== false) {
            $imageB64 = substr($imageB64, strpos($imageB64, 'base64,') + 7);
        }

        // Prevent timeout issues
        set_time_limit(120);

        // DIRECT GOOGLE GEMINI API (When API key starts with AIzaSy)
        if (str_starts_with($apiKey, 'AIzaSy')) {
            return $this->callDirectGeminiAPI($systemPrompt, $userPrompt, $imageB64, $imageFormat, $apiKey);
        }

        // Active 100% FREE vision models on OpenRouter
        $FREE_VISION_FALLBACKS = [
            'google/gemma-4-31b-it:free',
            'google/gemma-4-26b-a4b-it:free',
            'nvidia/nemotron-nano-12b-v2-vl:free',
            'meta-llama/llama-3.2-11b-vision-instruct:free',
            'qwen/qwen-2-vl-7b-instruct:free',
            'openrouter/free'
        ];

        $modelsToTry = [];
        if (!empty($model)) {
            $modelsToTry[] = $model;
        }
        foreach ($FREE_VISION_FALLBACKS as $fb) {
            if (!in_array($fb, $modelsToTry)) {
                $modelsToTry[] = $fb;
            }
        }

        $imageBlock = $this->preprocessor->buildImageUrlBlock($imageB64, $imageFormat);
        $lastError  = null;

        foreach ($modelsToTry as $attempt => $tryModel) {
            $payload = [
                'model'   => $tryModel,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => [
                        ['type' => 'text', 'text' => $userPrompt],
                        $imageBlock
                    ]]
                ],
                'temperature' => 0.1,
                'max_tokens'  => 8000,
            ];

            // Only set response_format for OpenAI/Gemini models that explicitly support it
            if (in_array($tryModel, ['openai/gpt-4o', 'openai/gpt-4o-mini', 'google/gemini-2.0-flash-001', 'google/gemini-2.5-flash'])) {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . BASE_URL,
                'X-Title: AlfarezMart'
            ]);

            $isFreeModel = str_contains($tryModel, ':free') || str_contains($tryModel, 'openrouter/free');
            $timeout = $isFreeModel ? 30 : 60;
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $err      = curl_error($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode === 402) {
                error_log("[AlfarezMart] Model {$tryModel} requires credit balance (402). Trying next fallback model.");
                $lastError = "Model {$tryModel} membutuhkan saldo kredit.";
                continue;
            }

            if ($err) {
                $lastError = "Koneksi ke AI ({$tryModel}) lambat atau timeout.";
                continue; // try next model
            }

            if ($httpCode === 429) {
                error_log("[AlfarezMart] Model {$tryModel} rate-limited (429). Trying next fallback model.");
                $lastError = "Model {$tryModel} sedang dibatasi kuota (rate limit).";
                continue;
            }

            if ($httpCode !== 200) {
                $errData = json_decode($response, true);
                $msg = $errData['error']['message'] ?? 'Unknown error';
                $lastError = "OpenRouter API Error ($httpCode): $msg";
                if ($httpCode >= 500 || in_array($httpCode, [400, 404])) continue; // retry next fallback
                break; // 401 client error, stop
            }

            // Success
            $resData = json_decode($response, true);
            $content = $resData['choices'][0]['message']['content'] ?? '';
            $jsonParsed = $this->parseAndRepairJson($content);

            if (is_array($jsonParsed) && !empty($jsonParsed)) {
                return $jsonParsed;
            }

            $lastError = "Model {$tryModel} mengembalikan format JSON tidak valid.";
            continue;
        }

        throw new \Exception("Gagal scan invoice dengan AI: Model gratisan OpenRouter sedang sibuk/habis kuota. Silakan coba lagi atau gunakan API Key Google Gemini gratis (AIzaSy...). Error detail: " . ($lastError ?? 'Unknown error'));
    }

    /**
     * Direct call to Google Gemini REST API (when key starts with AIzaSy)
     */
    private function callDirectGeminiAPI(
        string $systemPrompt,
        string $userPrompt,
        string $imageB64,
        string $imageFormat,
        string $apiKey
    ): array {
        $mimeType = match (strtolower($imageFormat)) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        $combinedPrompt = "System Instructions:\n" . $systemPrompt . "\n\nUser Prompt:\n" . $userPrompt;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $combinedPrompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data'      => $imageB64
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature'     => 0.1,
                'maxOutputTokens' => 8000,
                'responseMimeType'=> 'application/json'
            ]
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $err      = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($err) {
            throw new \Exception("Koneksi ke Gemini API gagal: " . $err);
        }

        if ($httpCode !== 200) {
            $errData = json_decode($response, true);
            $msg = $errData['error']['message'] ?? ('Gemini API Error (' . $httpCode . ')');
            throw new \Exception("Gemini API Error: " . $msg);
        }

        $resData = json_decode($response, true);
        $content = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '';

        $jsonParsed = $this->parseAndRepairJson($content);

        if (is_array($jsonParsed) && !empty($jsonParsed)) {
            return $jsonParsed;
        }

        throw new \Exception("Gemini API mengembalikan format JSON tidak valid.");
    }

    /**
     * Robust JSON parser and repair engine for AI outputs.
     * Extracts array of item objects even if output is partially formatted or truncated.
     */
    private function parseAndRepairJson(string $content): array
    {
        if (empty($content)) {
            return [];
        }

        // Clean markdown backticks
        $content = preg_replace('/```json\s*/i', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);

        // 1. Direct JSON decode
        $decoded = json_decode($content, true);
        if (is_array($decoded) && !empty($decoded)) {
            return $decoded;
        }

        // 2. Regex extract array [...]
        if (preg_match('/\[[\s\S]*\]/', $content, $matches)) {
            $arrayDecoded = json_decode($matches[0], true);
            if (is_array($arrayDecoded) && !empty($arrayDecoded)) {
                return $arrayDecoded;
            }
        }

        // 3. Repair unclosed JSON array (starts with [ but cut off)
        if (str_starts_with($content, '[') || preg_match('/^\s*\[/', $content)) {
            $lastBrace = strrpos($content, '}');
            if ($lastBrace !== false) {
                $repaired = substr($content, 0, $lastBrace + 1) . ']';
                $repairedDecoded = json_decode($repaired, true);
                if (is_array($repairedDecoded) && !empty($repairedDecoded)) {
                    return $repairedDecoded;
                }
            }
        }

        // 4. Regex extract ALL complete item objects {...} anywhere in $content
        if (preg_match_all('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $content, $allMatches)) {
            $extractedItems = [];
            foreach ($allMatches[0] as $jsonObjStr) {
                $itemObj = json_decode($jsonObjStr, true);
                if (is_array($itemObj) && (isset($itemObj['name']) || isset($itemObj['item']) || isset($itemObj['product_name']) || isset($itemObj['nama']))) {
                    $extractedItems[] = $itemObj;
                }
            }
            if (!empty($extractedItems)) {
                return $extractedItems;
            }
        }

        // 5. Fallback single object {...}
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $objDecoded = json_decode($matches[0], true);
            if (is_array($objDecoded) && !empty($objDecoded)) {
                return $objDecoded;
            }
        }

        return [];
    }

    // ----------------------------------------------------------------
    // OUTPUT FORMATTER
    // ----------------------------------------------------------------

    /**
     * Map the internal item structure to the format expected by the frontend.
     * Preserves backward compatibility.
     */
    private function formatForFrontend(array $items): array
    {
        $frontendItems = [];

        foreach ($items as $item) {
            // Frontend expects specific keys (e.g., original_name, is_matched, product_id, match_score)
            $formatted = [
                'original_name'   => $item['name'] ?? '',
                'qty'             => $item['qty'] ?? 1,
                'unit_price'      => $item['unit_price'] ?? 0,
                'total_price'     => $item['total_price'] ?? 0,
                'unit'            => $item['unit'] ?? '',
                'is_matched'      => $item['is_matched'] ?? false,
                'product_id'      => $item['product_id'] ?? null,
                'product_name'    => $item['product_name'] ?? null,
                'match_score'     => $item['match_score'] ?? 0,
                'packaging_level' => $item['matched_packaging_level'] ?? 1,
                'needs_review'    => $item['needs_review'] ?? false,
                'confidence'      => $item['confidence']['final'] ?? 0,
            ];

            // If there's issues or from correction, frontend might want to know
            if (!empty($item['issues'])) {
                $formatted['_issues'] = $item['issues'];
            }
            if (!empty($item['_from_correction'])) {
                $formatted['_from_correction'] = true;
            }

            $frontendItems[] = $formatted;
        }

        return $frontendItems;
    }
}
