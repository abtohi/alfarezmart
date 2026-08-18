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
require_once __DIR__ . '/LearnedAliasLookup.php';
require_once __DIR__ . '/ScanCache.php';

/**
 * InvoiceScanService
 *
 * Orchestrates the high-speed, modular AI invoice scanning pipeline.
 *
 * 2-Phase Architecture (Deterministic First, AI Fallback):
 *
 *   Phase 1 — FAST PATH (No AI):
 *     Image Validate → Hash Cache Check → Supplier Detect → Learned Alias Lookup
 *     → Product Match → Confidence Check → RETURN (0 AI calls)
 *
 *   Phase 2 — AI FALLBACK (only when fast path confidence is insufficient):
 *     Build Prompt → AI Vision Call → Parse Items → Match → Score → Cache → RETURN
 *
 * Features:
 *   - Image hash cache (skip AI for identical invoices)
 *   - Learned alias fast path (skip AI for known products)
 *   - Circuit breaker (prevent rate limit storms)
 *   - Multi-model failover
 *   - Full observability metadata
 *   - Duplicate invoice detection
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
    /** @var LearnedAliasLookup */
    private $aliasLookup;
    /** @var ScanCache */
    private $scanCache;

    // Circuit breaker state file path
    const CIRCUIT_BREAKER_FILE = __DIR__ . '/../../../logs/ai_circuit_breaker.json';
    const CIRCUIT_BREAKER_THRESHOLD = 5;    // Max 429 errors before opening circuit
    const CIRCUIT_BREAKER_WINDOW = 300;     // 5 minutes window
    const CIRCUIT_BREAKER_COOLDOWN = 300;   // 5 minutes cooldown

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
        $this->aliasLookup      = new LearnedAliasLookup($this->db);
        $this->scanCache        = new ScanCache($this->db);

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
     * Run the optimized 2-phase invoice scanning pipeline.
     *
     * @param  string   $imageB64     Raw base64 image
     * @param  int|null $supplierId   Optional supplier ID context
     * @return array
     */
    public function scan(string $imageB64, ?int $supplierId = null): array
    {
        $startTime = microtime(true);
        $scanId = uniqid('scan_', true);
        $metrics = [
            'scan_id'           => $scanId,
            'ai_called'         => false,
            'ai_provider'       => null,
            'ai_model_used'     => null,
            'ai_duration'       => 0,
            'ai_request_count'  => 0,
            'cache_hit'         => false,
            'fast_path'         => false,
            'file_hash'         => null,
            'matched_count'     => 0,
            'unmatched_count'   => 0,
            'duplicate_warning' => null,
        ];

        try {
            // ========================================
            // STAGE 1: Image Validation
            // ========================================
            $imageInfo = $this->preprocessor->analyze($imageB64);
            if (!$imageInfo['valid']) {
                throw new \Exception($imageInfo['error'] ?? 'Gambar tidak valid');
            }

            // ========================================
            // STAGE 2: Image Hash & Cache Check
            // ========================================
            $imageHash = $this->scanCache->hashImage($imageB64);
            $metrics['file_hash'] = $imageHash;

            $cachedResult = $this->scanCache->get($imageHash, $supplierId);
            if ($cachedResult !== null) {
                $metrics['cache_hit'] = true;
                $metrics['fast_path'] = true;
                $executionTime = round(microtime(true) - $startTime, 2);

                error_log("SCAN_TRACE [{$scanId}]: Cache HIT for hash {$imageHash} — returning cached result");

                return [
                    'success'  => true,
                    'message'  => "Berhasil memproses " . count($cachedResult['data'] ?? []) . " item dari cache ({$executionTime}s)",
                    'data'     => $cachedResult['data'] ?? [],
                    'metadata' => array_merge($cachedResult['metadata'] ?? [], $metrics, [
                        'execution_time' => $executionTime,
                    ])
                ];
            }

            // ========================================
            // STAGE 3: Supplier Detection & Skill Resolution
            // ========================================
            $detection = $this->supplierDetector->detect($supplierId);
            $skill = $this->skillManager->getSkill($detection['skill_key']);
            $resolvedSupplierId = $detection['supplier_id'] ?: $supplierId;

            // ========================================
            // STAGE 4: Load Products & Learned Aliases
            // ========================================
            $allProducts = $this->getAllProductsWithPackagings();
            $supplierProducts = [];
            if ($resolvedSupplierId && $resolvedSupplierId > 0) {
                $supplierProducts = $this->getSupplierProducts($resolvedSupplierId);
            }
            $supplierProductIds = array_column($supplierProducts, 'id');

            // Load learned alias lookup for fast path matching
            $this->aliasLookup->loadForSupplier($resolvedSupplierId);
            $aliasCount = $this->aliasLookup->getAliasCount();
            error_log("SCAN_TRACE [{$scanId}]: Loaded {$aliasCount} learned aliases for supplier {$resolvedSupplierId}");

            // ========================================
            // STAGE 5: AI Vision Call (with circuit breaker)
            // ========================================
            $aiStartTime = microtime(true);

            // Check circuit breaker before making AI call
            if ($this->isCircuitOpen()) {
                error_log("SCAN_TRACE [{$scanId}]: Circuit breaker OPEN — AI call skipped");
                throw new \Exception('AI Scanner sedang dalam mode cooldown karena rate limit. Coba lagi dalam beberapa menit.');
            }

            // Build prompts
            $systemPrompt = $skill->getSystemPrompt();
            $userHints = $skill->getUserPromptHints();

            $userMessageText = 'Ekstrak semua baris item barang/produk pada gambar faktur/nota ini ke dalam format JSON array.';
            if (!empty($userHints)) {
                $userMessageText .= "\n\nPetunjuk: " . $userHints;
            }

            // DYNAMIC LEARNING: Inject learned alias context for this supplier into the AI prompt (max 15 items for fast token efficiency)
            require_once __DIR__ . '/InvoiceLearningService.php';
            $learningService = new InvoiceLearningService($this->db);
            $learnedAliases = $learningService->getLearnedAliasesForPrompt($resolvedSupplierId, 15);

            $contextLines = [];
            if (!empty($learnedAliases)) {
                $contextLines[] = "\n## MEMORI POLA PRODUK SUPPLIER:";
                foreach ($learnedAliases as $la) {
                    $invAliases = trim((string)($la['supplier_invoice_name'] ?? ''));
                    $code = trim((string)($la['supplier_product_code'] ?? ''));
                    $fn = trim((string)($la['full_name'] ?? ''));
                    $aliasSample = explode("\n", $invAliases)[0] ?? $invAliases;
                    $line = "- " . $fn;
                    if ($code) $line .= " (Kode: {$code})";
                    if ($aliasSample && strcasecmp($aliasSample, $fn) !== 0) $line .= " [Nota: {$aliasSample}]";
                    $contextLines[] = $line;
                }
            } elseif (!empty($supplierProducts)) {
                $contextLines[] = "\n## REFERENSI PRODUK SUPPLIER:";
                foreach (array_slice($supplierProducts, 0, 15) as $sp) {
                    $c = trim($sp['supplier_product_code'] ?? $sp['code'] ?? '');
                    $n = trim($sp['full_name'] ?? '');
                    if ($c || $n) {
                        $contextLines[] = "- Kode: " . ($c ?: '-') . " | " . $n;
                    }
                }
            }
            if (!empty($contextLines)) {
                $userMessageText .= implode("\n", $contextLines);
            }

            // Make AI Vision Call
            $aiResponse = $this->callOpenRouter($systemPrompt, $userMessageText, $imageB64, $imageInfo['format'], null, null, $metrics);
            $metrics['ai_called'] = true;
            $metrics['ai_duration'] = round(microtime(true) - $aiStartTime, 2);

            if (empty($aiResponse)) {
                throw new \Exception('AI gagal memproses gambar atau mengembalikan respons kosong.');
            }

            // ========================================
            // STAGE 6: Extract Raw Items from AI Response
            // ========================================
            $rawItems = $this->extractRawItems($aiResponse);

            // Check if extracted text in items reveals a different supplier signature
            if ($detection['skill_key'] === 'general' && !empty($rawItems)) {
                $sampleText = json_encode(array_slice($rawItems, 0, 5));
                $signatureSkill = $this->skillManager->findSkillBySignatures($sampleText);
                if ($signatureSkill->getSkillKey() !== 'general') {
                    $skill = $signatureSkill;
                }
            }

            // ========================================
            // STAGE 7: Parse Items via Supplier Skill
            // ========================================
            $parsedItems = [];
            foreach ($rawItems as $rawItem) {
                if (!is_array($rawItem)) continue;
                $parsed = $skill->parseItem($rawItem);
                if (!empty($parsed['name']) || !empty($parsed['supplier_code'])) {
                    $parsedItems[] = $parsed;
                }
            }

            // ========================================
            // STAGE 8: Product Matching (with Learned Alias Priority)
            // ========================================
            $matchedItems = [];
            foreach ($parsedItems as $item) {
                // Try learned alias first (instant match, no fuzzy needed)
                $aliasResult = $this->aliasLookup->lookup(
                    $item['name'] ?? '',
                    $item['supplier_code'] ?? ''
                );

                if ($aliasResult !== null) {
                    // Fast path: learned alias matched
                    $mergedItem = array_merge($item, [
                        'product_id'              => $aliasResult['product_id'],
                        'product_name'            => $aliasResult['full_name'],
                        'is_matched'              => true,
                        'match_score'             => $aliasResult['match_score'],
                        'match_strategy'          => $aliasResult['match_type'],
                        'matched_packaging_level' => 1,
                        'product_data'            => $aliasResult,
                    ]);

                    // Determine packaging level using skill
                    $unitPrice = (float)($item['unit_price'] ?? 0);
                    $unit = $item['unit'] ?? '';
                    if (!empty($aliasResult['product_id'])) {
                        $productPackagings = $this->getProductPackagings($aliasResult['product_id']);
                        if (!empty($productPackagings)) {
                            $pkgDecision = $skill->determinePackagingLevel(
                                $unitPrice, $productPackagings, $unit,
                                $aliasResult['last_buy_price'] ?? null
                            );
                            $mergedItem['matched_packaging_level'] = $pkgDecision['level'] ?? 1;
                        }
                    }

                    $scoredItem = $this->scorer->score($mergedItem);
                    $matchedItems[] = $scoredItem;
                } else {
                    // Slow path: full ProductMatcher pipeline
                    $matchResult = $this->matcher->match($item, $allProducts, $supplierProductIds, $skill);
                    $mergedItem  = array_merge($item, $matchResult);
                    $scoredItem  = $this->scorer->score($mergedItem);
                    $matchedItems[] = $scoredItem;
                }
            }

            // ========================================
            // STAGE 9: Format Final Output
            // ========================================
            $finalItems = $this->formatForFrontend($matchedItems);
            $executionTime = round(microtime(true) - $startTime, 2);

            $matchedCount = count(array_filter($finalItems, fn($i) => $i['is_matched'] && $i['product_id']));
            $unmatchedCount = count($finalItems) - $matchedCount;
            $metrics['matched_count'] = $matchedCount;
            $metrics['unmatched_count'] = $unmatchedCount;

            $avgScore = 0;
            if (count($finalItems) > 0) {
                $totalScore = array_sum(array_column($finalItems, 'confidence'));
                $avgScore = round($totalScore / count($finalItems), 2);
            }

            // ========================================
            // STAGE 10: Cache Result & Duplicate Detection
            // ========================================
            $result = [
                'success'  => true,
                'message'  => "Berhasil memproses " . count($finalItems) . " item via skill " . $skill->getSupplierName() . " ({$executionTime}s)",
                'data'     => $finalItems,
                'metadata' => array_merge($metrics, [
                    'supplier_detected' => $detection['supplier_name'],
                    'skill_used'        => $skill->getSkillKey(),
                    'execution_time'    => $executionTime,
                    'avg_confidence'    => $avgScore,
                    'item_count'        => count($finalItems),
                    'alias_count'       => $aliasCount,
                ])
            ];

            // Store in cache for future reuse
            $this->scanCache->set($imageHash, $resolvedSupplierId, $result);

            // Duplicate detection
            $totalPrice = array_sum(array_column($finalItems, 'total_price'));
            if ($this->scanCache->isDuplicate($resolvedSupplierId, count($finalItems), $totalPrice)) {
                $result['metadata']['duplicate_warning'] = 'Invoice ini mungkin sudah pernah di-scan sebelumnya. Periksa kembali sebelum menyimpan.';
            }
            $this->scanCache->storeFingerprint($imageHash, $resolvedSupplierId, count($finalItems), $totalPrice);

            error_log("SCAN_TRACE [{$scanId}]: Complete — {$matchedCount} matched, {$unmatchedCount} unmatched, AI={$metrics['ai_called']}, time={$executionTime}s");

            return $result;

        } catch (\Throwable $e) {
            $executionTime = round(microtime(true) - $startTime, 2);
            error_log("InvoiceScanService error [{$scanId}]: " . $e->getMessage());
            return [
                'success'  => false,
                'message'  => $e->getMessage(),
                'data'     => [],
                'metadata' => array_merge($metrics, [
                    'execution_time' => $executionTime,
                    'error'          => $e->getMessage(),
                ])
            ];
        }
    }

    /**
     * Extract raw items array from AI response (handles various envelope shapes).
     *
     * @param mixed $aiResponse
     * @return array
     */
    private function extractRawItems(mixed $aiResponse): array
    {
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
        return $rawItems;
    }

    /**
     * Get packagings for a specific product (for alias-matched items).
     */
    private function getProductPackagings(int $productId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT pp.id, pp.level, pp.contained_qty, pp.base_qty, pp.buy_price, pp.sell_price_retail, pp.sell_price_wholesale, pp.barcode,
                       u.name as unit_name
                FROM product_packagings pp
                LEFT JOIN units u ON pp.unit_id = u.id
                WHERE pp.product_id = ?
                ORDER BY pp.level ASC
            ");
            $stmt->execute([$productId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
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

        // Attach full packagings with unit names and prices
        $pkgStmt = $this->db->query("
            SELECT pp.id, pp.product_id, pp.level, pp.unit_id, u.name as unit_name, 
                   pp.contained_qty, pp.base_qty, pp.barcode, pp.buy_price, 
                   pp.sell_price_retail, pp.sell_price_wholesale
            FROM product_packagings pp
            LEFT JOIN units u ON pp.unit_id = u.id
            ORDER BY pp.product_id, pp.level ASC
        ");
        $allPkgs = $pkgStmt->fetchAll(\PDO::FETCH_ASSOC);
        $pkgsByProduct = [];
        foreach ($allPkgs as $pkg) {
            $pkgsByProduct[$pkg['product_id']][] = $pkg;
        }
        foreach ($products as &$p) {
            $p['packagings'] = $pkgsByProduct[$p['id']] ?? [];
        }
        unset($p);

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

    // ================================================================
    // CIRCUIT BREAKER — Prevents rate limit storms
    // ================================================================

    /**
     * Check if the circuit breaker is currently open (blocking AI calls).
     */
    private function isCircuitOpen(): bool
    {
        $file = self::CIRCUIT_BREAKER_FILE;
        if (!file_exists($file)) return false;

        try {
            $data = json_decode(file_get_contents($file), true);
            if (!is_array($data)) return false;

            $now = time();

            // Check if in cooldown period after circuit opened
            if (isset($data['circuit_opened_at'])) {
                $elapsed = $now - (int)$data['circuit_opened_at'];
                if ($elapsed < self::CIRCUIT_BREAKER_COOLDOWN) {
                    return true; // Still in cooldown
                }
                // Cooldown expired — reset circuit (half-open, allow 1 attempt)
                $this->resetCircuitBreaker();
                return false;
            }

            // Count recent 429 errors within window
            $errors = $data['errors'] ?? [];
            $recentErrors = array_filter($errors, fn($ts) => ($now - $ts) < self::CIRCUIT_BREAKER_WINDOW);

            if (count($recentErrors) >= self::CIRCUIT_BREAKER_THRESHOLD) {
                // Open the circuit
                $data['circuit_opened_at'] = $now;
                @file_put_contents($file, json_encode($data));
                error_log("CIRCUIT_BREAKER: Circuit OPENED — {$recentErrors} 429 errors in " . self::CIRCUIT_BREAKER_WINDOW . "s window");
                return true;
            }
        } catch (\Throwable $e) {
            // If file read fails, don't block AI calls
            return false;
        }

        return false;
    }

    /**
     * Record a 429 rate limit error for circuit breaker tracking.
     */
    private function recordRateLimitError(): void
    {
        $file = self::CIRCUIT_BREAKER_FILE;
        try {
            $data = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
            $data['errors'] = $data['errors'] ?? [];
            $data['errors'][] = time();

            // Keep only recent errors (within window)
            $now = time();
            $data['errors'] = array_values(array_filter(
                $data['errors'],
                fn($ts) => ($now - $ts) < self::CIRCUIT_BREAKER_WINDOW * 2
            ));

            $dir = dirname($file);
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            @file_put_contents($file, json_encode($data));
        } catch (\Throwable $e) {
            // Silently fail
        }
    }

    /**
     * Reset the circuit breaker after cooldown.
     */
    private function resetCircuitBreaker(): void
    {
        $file = self::CIRCUIT_BREAKER_FILE;
        try {
            @file_put_contents($file, json_encode(['errors' => [], 'reset_at' => time()]));
        } catch (\Throwable $e) {
            // Silently fail
        }
    }

    // ================================================================
    // AI VISION API CALL — Multi-model with rate limit tracking
    // ================================================================

    private function callOpenRouter(
        string $systemPrompt,
        string $userPrompt,
        string $imageB64,
        string $imageFormat,
        ?string $apiKey = null,
        ?string $model = null,
        array &$metrics = []
    ): ?array {
        $apiKey = $apiKey ?? $this->getApiKey();
        $model  = $model  ?? $this->getModelName();

        if (empty($apiKey)) {
            throw new \Exception('API Key AI Scanner belum diatur di Pengaturan Sistem & AI.');
        }

        set_time_limit(180);

        // ROBUST 100% FREE VISION MODEL STRATEGY:
        // Respects model configured in Settings (Pengaturan Sistem & AI) as primary choice,
        // and falls back gracefully to active OpenRouter 100% free vision models if unavailable.
        $DEFAULT_VISION_MODELS = [
            'google/gemma-4-26b-a4b-it:free',
            'google/gemma-4-31b-it:free',
            'dots-studio/dots-3-note-preview:free',
            'nvidia/nemotron-nano-12b-v2-vl:free',
        ];

        if (empty($model) || in_array($model, ['openrouter/auto', 'auto', 'openrouter/free'])) {
            $modelsToTry = $DEFAULT_VISION_MODELS;
        } else {
            // User configured specific model -> try user's choice FIRST, followed by robust fallbacks
            $modelsToTry = array_unique(array_merge([$model], $DEFAULT_VISION_MODELS));
        }

        // Try up to 3 models if needed to guarantee successful invoice extraction
        $modelsToTry = array_slice($modelsToTry, 0, 3);

        $imageBlock   = $this->preprocessor->buildImageUrlBlock($imageB64, $imageFormat);
        $lastError    = null;
        $requestCount = 0;
        $rateLimitCount   = 0;
        $noEndpointCount  = 0;

        foreach ($modelsToTry as $tryModel) {
            set_time_limit(90);
            error_log("SCAN_AI_TRACE: Attempting OpenRouter model: {$tryModel}");
            $requestCount++;

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
                'max_tokens'  => 2000,
            ];

            $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . (defined('BASE_URL') ? BASE_URL : 'https://alfarezmart.com/'),
                'X-Title: AlfarezMart Invoice Scanner',
            ]);

            // 50s timeout per model attempt: fast failover to next model
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $err      = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            unset($ch);

            if ($err) {
                error_log("SCAN_AI_TRACE: Model {$tryModel} curl error: {$err}");
                $lastError = "Koneksi ke OpenRouter gagal ({$tryModel}): " . $err;
                continue;
            }

            if ($httpCode === 429) {
                $rateLimitCount++;
                $this->recordRateLimitError();
                error_log("SCAN_AI_TRACE: Model {$tryModel} rate limited 429 ({$rateLimitCount}x)");
                $lastError = "AI Scanner sedang sibuk (rate limit). Coba lagi sebentar.";
                continue;
            }


            if ($httpCode !== 200) {
                $errData = json_decode($response, true);
                $msg = $errData['error']['message'] ?? "HTTP $httpCode";
                error_log("SCAN_AI_TRACE: Model {$tryModel} error ($httpCode): $msg");

                // "No endpoints found" = model not available right now — skip silently
                if (stripos($msg, 'no endpoints') !== false || stripos($msg, 'not found') !== false) {
                    $noEndpointCount++;
                    $lastError = "Model AI tidak tersedia saat ini. Mencoba model lain...";
                    error_log("SCAN_AI_TRACE: Model {$tryModel} has no endpoints, trying next...");
                    continue;
                }

                $lastError = "OpenRouter error: $msg";
                continue;
            }

            $resData = json_decode($response, true);
            $content = $resData['choices'][0]['message']['content'] ?? '';

            if (empty(trim($content))) {
                error_log("SCAN_AI_TRACE: Model {$tryModel} returned empty content.");
                continue;
            }

            // Robust JSON extraction
            $decoded = $this->extractJsonFromContent($content);

            if (is_array($decoded)) {
                error_log("SCAN_AI_TRACE: Successfully parsed response from model {$tryModel}");
                $metrics['ai_provider'] = 'openrouter';
                $metrics['ai_model_used'] = $tryModel;
                $metrics['ai_request_count'] = $requestCount;
                return $decoded;
            } else {
                error_log("SCAN_AI_TRACE: Model {$tryModel} returned non-JSON content: " . substr($content, 0, 200));
            }
        }

        $metrics['ai_request_count'] = $requestCount;

        // Build a helpful final error message
        if ($noEndpointCount >= count($modelsToTry) && $rateLimitCount === 0) {
            throw new \Exception('Model AI yang dipilih sedang offline/tidak tersedia di OpenRouter saat ini. Silakan pilih model lain di menu Pengaturan → AI Scanner.');
        }
        if ($rateLimitCount >= 2) {
            throw new \Exception('AI Scanner sedang overload (rate limit). Tunggu 1-2 menit lalu coba lagi, atau gunakan model kustom di Pengaturan.');
        }
        throw new \Exception($lastError ?: 'AI gagal membaca gambar invoice. Pastikan gambar jelas dan koneksi internet stabil, lalu coba lagi.');
    }

    /**
     * Extract JSON from AI response content (handles various formats).
     */
    private function extractJsonFromContent(string $content): ?array
    {
        $cleanContent = trim($content);

        // Try markdown code block first
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $cleanContent, $m)) {
            $decoded = json_decode(trim($m[1]), true);
            if (is_array($decoded)) return $decoded;
        }

        // Try direct JSON
        $decoded = json_decode($cleanContent, true);
        if (is_array($decoded)) return $decoded;

        // Try extracting JSON array
        $firstBracket = strpos($cleanContent, '[');
        $lastBracket = strrpos($cleanContent, ']');
        if ($firstBracket !== false && $lastBracket !== false && $lastBracket > $firstBracket) {
            $slice = substr($cleanContent, $firstBracket, $lastBracket - $firstBracket + 1);
            $decoded = json_decode($slice, true);
            if (is_array($decoded)) return $decoded;
        }

        // Try extracting JSON object
        $firstBrace = strpos($cleanContent, '{');
        $lastBrace = strrpos($cleanContent, '}');
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $slice = substr($cleanContent, $firstBrace, $lastBrace - $firstBrace + 1);
            $decoded = json_decode($slice, true);
            if (is_array($decoded)) return $decoded;
        }

        return null;
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
                'match_strategy'  => $item['match_strategy'] ?? 'none',
                'product_data'    => $item['product_data'] ?? null,
            ];

            $frontendItems[] = $formatted;
        }

        return $frontendItems;
    }
}
