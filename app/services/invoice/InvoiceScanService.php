<?php
require_once __DIR__ . '/ImagePreprocessor.php';
require_once __DIR__ . '/SupplierDetector.php';
require_once __DIR__ . '/skills/SkillManager.php';
require_once __DIR__ . '/PromptBuilder.php';
require_once __DIR__ . '/LayoutAnalyzer.php';
require_once __DIR__ . '/TableParser.php';
require_once __DIR__ . '/InvoiceValidator.php';
require_once __DIR__ . '/ProductMatcher.php';
require_once __DIR__ . '/ConfidenceScorer.php';
require_once __DIR__ . '/SelfCorrectionEngine.php';
require_once __DIR__ . '/TemplateLearner.php';

/**
 * InvoiceScanService
 *
 * Orchestrates the high-speed, modular AI invoice scanning pipeline.
 * Utilizes SupplierDetector and SkillManager to adapt dynamically
 * to specific supplier formats (e.g., PT Medan Distribusindo Raya / MDR)
 * with graceful fallback to General invoice extraction.
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
    /** @var SupplierDetector */
    private $supplierDetector;
    /** @var SkillManager */
    private $skillManager;
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

    /**
     * @param \PDO $db
     */
    public function __construct(\PDO $db)
    {
        $this->db               = $db;
        $this->settingModel     = new SettingModel();
        $this->productModel     = new ProductModel();

        // Initialize pipeline components
        $this->preprocessor     = new ImagePreprocessor();
        $this->supplierDetector = new SupplierDetector($this->db);
        $this->skillManager     = new SkillManager();
        $this->promptBuilder    = new PromptBuilder($this->db, $this->settingModel);
        $this->layoutAnalyzer   = new LayoutAnalyzer();
        $this->tableParser      = new TableParser($this->layoutAnalyzer);
        $this->validator        = new InvoiceValidator();
        $this->matcher          = new ProductMatcher($this->layoutAnalyzer);
        $this->scorer           = new ConfidenceScorer();
        $this->selfCorrection   = new SelfCorrectionEngine();
        $this->templateLearner  = new TemplateLearner($this->db);

        $this->ensureSupplierProductCodeColumn();
    }

    private function ensureSupplierProductCodeColumn(): void
    {
        try {
            $this->db->query("SELECT supplier_product_code FROM supplier_products LIMIT 1");
        } catch (\PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE supplier_products ADD COLUMN supplier_product_code VARCHAR(100) DEFAULT NULL AFTER product_id");
                $this->db->exec("CREATE INDEX idx_sp_code ON supplier_products(supplier_product_code)");
            } catch (\PDOException $e2) {
                error_log('Failed to add supplier_product_code column: ' . $e2->getMessage());
            }
        }
    }

    /**
     * Run the full high-speed invoice scanning pipeline.
     *
     * @param  string   $imageB64     Raw base64 image
     * @param  int|null $supplierId   Optional supplier ID context
     * @return array
     */
    public function scan(string $imageB64, ?int $supplierId = null): array
    {
        $startTime = microtime(true);

        try {
            // STAGE 1: Image Preprocessing & Validation
            $imageInfo = $this->preprocessor->analyze($imageB64);
            if (!$imageInfo['valid']) {
                throw new \Exception($imageInfo['error'] ?? 'Gambar tidak valid');
            }

            // STAGE 2: Supplier Detection & Skill Resolution
            $detection = $this->supplierDetector->detect($supplierId);
            $skill = $this->skillManager->getSkill($detection['skill_key']);

            // STAGE 3: Context Gathering (Products & Supplier Data)
            $allProducts = $this->getAllProductsWithPackagings();
            $resolvedSupplierId = $detection['supplier_id'] ?: $supplierId;
            $supplierProducts = [];

            if ($resolvedSupplierId && $resolvedSupplierId > 0) {
                $supplierProducts = $this->getSupplierProducts($resolvedSupplierId);
            }

            // STAGE 4: Build System & User Prompts with Skill
            $systemPrompt = $skill->getSystemPrompt();
            $userHints = $skill->getUserPromptHints();

            $userPromptSetting = trim($this->settingModel->get('ai_invoice_prompt', ''));
            $userMessageText = $userPromptSetting ?: 'Baca gambar invoice ini dan ekstrak semua item barang ke dalam format JSON.';
            if (!empty($userHints)) {
                $userMessageText .= "\n\n" . $userHints;
            }

            // Add concise product list context for faster exact matching by AI if available
            if (!empty($supplierProducts)) {
                $contextLines = ["\n## REFERENSI PRODUK SUPPLIER INI (Gunakan kode/nama jika cocok):"];
                foreach (array_slice($supplierProducts, 0, 40) as $sp) {
                    $c = trim($sp['supplier_product_code'] ?? $sp['code'] ?? '');
                    $n = trim($sp['full_name'] ?? '');
                    if ($c || $n) {
                        $contextLines[] = "- Kode: " . ($c ?: '-') . " | " . $n;
                    }
                }
                $userMessageText .= implode("\n", $contextLines);
            }

            // STAGE 5: AI Vision API Call
            $aiResponse = $this->callOpenRouter($systemPrompt, $userMessageText, $imageB64, $imageInfo['format']);
            if (empty($aiResponse)) {
                throw new \Exception('AI gagal memproses gambar atau mengembalikan respons kosong.');
            }

            // If response is not an array of items, try to find the array key
            $rawItems = [];
            if (is_array($aiResponse)) {
                if (isset($aiResponse['items']) && is_array($aiResponse['items'])) {
                    $rawItems = $aiResponse['items'];
                } elseif (isset($aiResponse[0])) {
                    $rawItems = $aiResponse;
                } else {
                    foreach ($aiResponse as $val) {
                        if (is_array($val) && isset($val[0])) {
                            $rawItems = $val;
                            break;
                        }
                    }
                }
            }

            // Check if extracted text in items reveals a different supplier signature
            if ($detection['skill_key'] === 'general' && !empty($rawItems)) {
                $sampleText = json_encode(array_slice($rawItems, 0, 5));
                $signatureSkill = $this->skillManager->findSkillBySignatures($sampleText);
                if ($signatureSkill->getSkillKey() !== 'general') {
                    $skill = $signatureSkill;
                }
            }

            // STAGE 6: Parse Items via Supplier Skill
            $supplierProductIds = array_column($supplierProducts, 'id');
            $parsedItems = [];
            foreach ($rawItems as $rawItem) {
                if (!is_array($rawItem)) continue;
                $parsed = $skill->parseItem($rawItem);
                if (!empty($parsed['name']) || !empty($parsed['supplier_code'])) {
                    $parsedItems[] = $parsed;
                }
            }

            // STAGE 7: Product Matching with Price Distance & Packaging Selection
            $matchedItems = [];
            foreach ($parsedItems as $item) {
                $matchResult = $this->matcher->match($item, $allProducts, $supplierProductIds, $skill);
                $mergedItem  = array_merge($item, $matchResult);
                $scoredItem  = $this->scorer->score($mergedItem);
                $matchedItems[] = $scoredItem;
            }

            // STAGE 8: Format Final Output with Complete Product Data for Instant Frontend Injection
            $finalItems = $this->formatForFrontend($matchedItems);
            $executionTime = round(microtime(true) - $startTime, 2);

            $avgScore = 0;
            if (count($finalItems) > 0) {
                $totalScore = array_sum(array_column($finalItems, 'confidence'));
                $avgScore = round($totalScore / count($finalItems), 2);
            }

            return [
                'success'  => true,
                'message'  => "Berhasil memproses " . count($finalItems) . " item via skill " . $skill->getSupplierName() . " ({$executionTime}s)",
                'data'     => $finalItems,
                'metadata' => [
                    'supplier_detected' => $detection['supplier_name'],
                    'skill_used'        => $skill->getSkillKey(),
                    'execution_time'    => $executionTime,
                    'avg_confidence'    => $avgScore,
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

    private function getAllProductsWithPackagings(): array
    {
        $stmt = $this->db->query("
            SELECT p.id, p.full_name, p.code, p.invoice_name, p.supplier_invoice_name, p.short_label, 
                   p.variant, p.weight_value, p.weight_unit, b.name as brand_name,
                   (SELECT supplier_product_code FROM supplier_products sp WHERE sp.product_id = p.id LIMIT 1) as supplier_product_code,
                   (SELECT last_buy_price FROM supplier_products sp WHERE sp.product_id = p.id ORDER BY sp.id DESC LIMIT 1) as last_buy_price
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.is_active = 1
        ");
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->productModel->attachPackagingsForProductList($products);
        return $products;
    }

    private function getSupplierProducts(int $supplierId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.full_name, p.code, p.invoice_name, p.supplier_invoice_name, p.short_label,
                   p.variant, p.weight_value, p.weight_unit, b.name as brand_name,
                   sp.supplier_product_code, sp.last_buy_price
            FROM supplier_products sp
            JOIN products p ON sp.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE sp.supplier_id = ? AND p.is_active = 1
        ");
        $stmt->execute([$supplierId]);
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

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
        $model = trim($this->settingModel->get('ai_model', 'openrouter/auto'));
        return $model ?: 'openrouter/auto';
    }

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
            throw new \Exception('API Key AI Scanner belum diatur di Pengaturan Sistem & AI.');
        }

        set_time_limit(120);

        // List of proven fast, 100% free multimodal vision models on OpenRouter
        $FREE_VISION_MODELS = [
            'google/gemini-2.0-flash-lite-preview-02-05:free',
            'google/gemini-2.0-flash:free',
            'google/gemini-2.0-flash-exp:free',
            'qwen/qwen-2.5-vl-72b-instruct:free',
            'meta-llama/llama-3.2-11b-vision-instruct:free',
            'mistralai/pixtral-12b:free',
            'openrouter/free',
        ];

        // Determine list of models to try in order
        if (empty($model) || in_array($model, ['openrouter/auto', 'auto', 'openrouter/free'])) {
            // Default "Auto": Start with the fastest free vision model and cascade down if needed
            $modelsToTry = $FREE_VISION_MODELS;
        } else {
            // User selected a specific model from UI (presets or custom) -> try that model first!
            $modelsToTry = array_unique(array_merge([$model], $FREE_VISION_MODELS));
        }

        $imageBlock = $this->preprocessor->buildImageUrlBlock($imageB64, $imageFormat);
        $lastError  = null;

        foreach ($modelsToTry as $tryModel) {
            error_log("SCAN_AI_TRACE: Attempting OpenRouter model: {$tryModel}");

            $payload = [
                'model'    => $tryModel,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => [
                        ['type' => 'text', 'text' => $userPrompt],
                        $imageBlock
                    ]]
                ],
                'temperature' => 0.1,
                'max_tokens'  => 4000,
            ];

            // Response format json only for OpenAI / Gemini models that strictly support it
            if (strpos($tryModel, 'gpt-4o') !== false || strpos($tryModel, 'gemini-2.0-flash-001') !== false) {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . (defined('BASE_URL') ? BASE_URL : 'https://alfarezmart.com/'),
                'X-Title: AlfarezMart Invoice Scanner'
            ]);

            curl_setopt($ch, CURLOPT_TIMEOUT, 35);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $err      = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (PHP_VERSION_ID < 80500) {
                @curl_close($ch);
            }
            unset($ch);

            if ($err) {
                error_log("SCAN_AI_TRACE: Model {$tryModel} curl error: {$err}");
                $lastError = "Koneksi ke OpenRouter gagal ({$tryModel}): " . $err;
                continue;
            }

            if ($httpCode === 429) {
                error_log("SCAN_AI_TRACE: Model {$tryModel} hit rate limit 429, trying next model...");
                $lastError = "Model {$tryModel} terkena rate limit (429).";
                continue;
            }

            if ($httpCode !== 200) {
                $errData = json_decode($response, true);
                $msg = $errData['error']['message'] ?? "HTTP $httpCode";
                error_log("SCAN_AI_TRACE: Model {$tryModel} error ($httpCode): $msg");
                $lastError = "OpenRouter ({$tryModel}): $msg";
                if ($httpCode >= 500 || in_array($httpCode, [400, 402, 404, 503])) {
                    continue;
                }
                break;
            }

            $resData = json_decode($response, true);
            $content = $resData['choices'][0]['message']['content'] ?? '';

            if (empty(trim($content))) {
                error_log("SCAN_AI_TRACE: Model {$tryModel} returned empty content.");
                continue;
            }

            // Robust JSON extraction
            $decoded = null;
            $cleanContent = trim($content);
            if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $cleanContent, $m)) {
                $decoded = json_decode(trim($m[1]), true);
            }
            if (!is_array($decoded)) {
                $decoded = json_decode($cleanContent, true);
            }
            if (!is_array($decoded)) {
                $firstBracket = strpos($cleanContent, '[');
                $lastBracket = strrpos($cleanContent, ']');
                if ($firstBracket !== false && $lastBracket !== false && $lastBracket > $firstBracket) {
                    $slice = substr($cleanContent, $firstBracket, $lastBracket - $firstBracket + 1);
                    $decoded = json_decode($slice, true);
                }
            }
            if (!is_array($decoded)) {
                $firstBrace = strpos($cleanContent, '{');
                $lastBrace = strrpos($cleanContent, '}');
                if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
                    $slice = substr($cleanContent, $firstBrace, $lastBrace - $firstBrace + 1);
                    $decoded = json_decode($slice, true);
                }
            }

            if (is_array($decoded)) {
                error_log("SCAN_AI_TRACE: Successfully parsed response from model {$tryModel}");
                return $decoded;
            } else {
                error_log("SCAN_AI_TRACE: Model {$tryModel} returned non-JSON content: " . substr($content, 0, 200));
            }
        }

        throw new \Exception($lastError ?: 'AI gagal membaca gambar invoice setelah mencoba model yang tersedia. Pastikan gambar jelas dan coba kembali.');
    }

    private function formatForFrontend(array $items): array
    {
        $frontendItems = [];

        foreach ($items as $item) {
            $formatted = [
                'original_name'   => $item['name'] ?? '',
                'supplier_code'   => $item['supplier_code'] ?? '',
                'qty'             => $item['qty'] ?? 1,
                'unit_price'      => $item['unit_price'] ?? 0,
                'total_price'     => $item['total_price'] ?? 0,
                'unit'            => $item['unit'] ?? '',
                'is_matched'      => $item['is_matched'] ?? false,
                'product_id'      => $item['product_id'] ?? null,
                'product_name'    => $item['product_name'] ?? null,
                'match_score'     => $item['match_score'] ?? 0,
                'packaging_level' => $item['matched_packaging_level'] ?? 1,
                'confidence'      => $item['confidence']['final'] ?? ($item['match_score'] ?? 0),
                'product_data'    => $item['product_data'] ?? null,
            ];

            $frontendItems[] = $formatted;
        }

        return $frontendItems;
    }
}
