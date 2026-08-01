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
            $modelName = $this->getModelName();
            $isFreeTierModel = str_contains($modelName, ':free') || str_contains($modelName, 'openrouter/free');
            if (($hasLowConf || !empty($correctionHints)) && !$isFreeTierModel) {
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
        $model = $this->settingModel->get('ai_model', 'openrouter/auto');
        return $model ?: 'openrouter/auto';
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

        // Prevent timeout issues
        set_time_limit(120);

        // Free vision model fallback chain — verified ACTIVE as of 2026-08-01 via OpenRouter API
        // All models below confirmed free (pricing=0) and support image input_modalities
        $FREE_VISION_FALLBACKS = [
            'google/gemma-4-27b-it:free',               // Google Gemma 4 27B - most capable free vision
            'google/gemma-4-26b-a4b-it:free',           // Google Gemma 4 26B MoE variant
            'google/gemma-4-31b-it:free',               // Google Gemma 4 31B
            'nvidia/nemotron-nano-12b-v2-vl:free',      // NVIDIA Nemotron 12B Vision-Language
            'nvidia/nemotron-3-nano-omni-30b-a3b-reasoning:free', // NVIDIA Omni 30B
        ];


        $modelsToTry = [$model];
        // Only add free fallbacks if primary model is not already one of them
        if (!in_array($model, $FREE_VISION_FALLBACKS)) {
            foreach ($FREE_VISION_FALLBACKS as $fb) {
                $modelsToTry[] = $fb;
            }
        } else {
            // Primary IS a free model — add remaining free models as fallbacks
            foreach ($FREE_VISION_FALLBACKS as $fb) {
                if ($fb !== $model) $modelsToTry[] = $fb;
            }
        }

        $imageBlock = $this->preprocessor->buildImageUrlBlock($imageB64, $imageFormat);
        $lastError  = null;

        foreach ($modelsToTry as $attempt => $tryModel) {
            // Combine system prompt and user prompt into user content text for maximum compatibility across all OpenRouter vision models
            $combinedText = "System Context: " . $systemPrompt . "\n\nUser Task: " . $userPrompt;

            $payload = [
                'model'   => $tryModel,
                'messages' => [
                    ['role' => 'user', 'content' => [
                        ['type' => 'text', 'text' => $combinedText],
                        $imageBlock
                    ]]
                ],
                'temperature' => 0.1,
                'max_tokens'  => 2500,
            ];

            // Only add response_format for specific paid models (e.g. OpenAI gpt-4o), do NOT send it for free/auto models as many free vision models reject json_object with HTTP 400!
            if (in_array($tryModel, ['openai/gpt-4o', 'openai/gpt-4o-mini', 'google/gemini-2.0-flash-001'])) {
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

            // Free tier timeout (55s); auto/paid models get 110s
            $isFreeModel = str_contains($tryModel, ':free') || str_contains($tryModel, 'openrouter/free');
            $timeout = $isFreeModel ? 55 : 110;
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $err      = curl_error($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($err) {
                $lastError = "Koneksi ke OpenRouter gagal ({$tryModel}): " . $err;
                error_log("[AlfarezMart] Model {$tryModel} curl error: $err");
                continue; // try next model
            }

            if ($httpCode === 429) {
                $nextModel = $modelsToTry[$attempt + 1] ?? null;
                error_log("[AlfarezMart] Model {$tryModel} rate-limited (429). " . ($nextModel ? "Trying: $nextModel" : "No more fallbacks."));
                $lastError = "Model {$tryModel} sedang dibatasi (rate limit). Mencoba model gratis...";
                continue;
            }

            if ($httpCode !== 200) {
                $errData = json_decode($response, true);
                $msg = $errData['error']['message'] ?? ($errData['message'] ?? 'Unknown error');
                $lastError = "OpenRouter API Error ($httpCode): $msg";
                error_log("[AlfarezMart] Model {$tryModel} failed with HTTP $httpCode: $msg");

                // Fallback to free models if credit error (402), bad request (400), rate limit (429), not found (404), or server error (5xx)
                if ($httpCode >= 500 || in_array($httpCode, [400, 402, 404, 429])) {
                    continue; // try next fallback model
                }

                break; // 4xx client error (e.g. 401 Unauthorized API key), stop
            }

            // HTTP 200 Success — Parse AI response
            $resData = json_decode($response, true);
            $content = $resData['choices'][0]['message']['content'] ?? '';

            if (empty($content)) {
                error_log("[AlfarezMart] Model {$tryModel} returned empty content in choices.");
                $lastError = "Model {$tryModel} mengembalikan respons kosong.";
                continue;
            }

            // Strip markdown codeblock backticks if present
            $content = preg_replace('/```json\s*/i', '', $content);
            $content = preg_replace('/```\s*/', '', $content);
            $content = trim($content);

            // 1. Direct JSON parse
            $jsonParsed = json_decode($content, true);

            // 2. Regex JSON extraction between '{' and '}' if direct parse fails
            if (!is_array($jsonParsed)) {
                if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
                    $jsonParsed = json_decode($matches[0], true);
                }
            }

            if (is_array($jsonParsed) && !empty($jsonParsed)) {
                return $jsonParsed;
            }

            error_log("[AlfarezMart] Model {$tryModel} output could not be parsed as JSON: " . substr($content, 0, 150));
            $lastError = "Model {$tryModel} mengembalikan format JSON yang tidak valid.";
            continue;
        }

        throw new \Exception($lastError ?? 'AI gagal memproses gambar setelah mencoba semua model.');
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
