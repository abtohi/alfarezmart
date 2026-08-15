# Arsitektur Lengkap & Kode Sumber Fitur AI Invoice Scanner (AlfarezMart)

Dokumen ini mendokumentasikan secara menyeluruh arsitektur, alur eksekusi (pipeline), komponen kode backend, sistem prompt, algoritma pencocokan produk (matching algorithm), alur frontend, serta analisis penyebab masalah saat ini (hasil selalu 0 item & proses sangat lama).

---

## DAFTAR ISI
1. [Ringkasan Masalah Saat Ini](#1-ringkasan-masalah-saat-ini)
2. [Arsitektur & Diagram Alur (Pipeline)](#2-arsitektur--diagram-alur-pipeline)
3. [Alur Komunikasi Frontend & Endpoint](#3-alur-komunikasi-frontend--endpoint)
4. [Kode Frontend: Pembacaan & Pengiriman Gambar](#4-kode-frontend-pembacaan--pengiriman-gambar)
5. [Kode Controller: `ApiController@scanInvoiceAI`](#5-kode-controller-apicontrollerscaninvoiceai)
6. [Kode Service Utama: `InvoiceScanService.php`](#6-kode-service-utama-invoicescanservicephp)
7. [Sistem Prompt & Ekstraksi AI Vision](#7-sistem-prompt--ekstraksi-ai-vision)
   - [7.1. General Invoice Skill (`GeneralInvoiceSkill.php`)](#71-general-invoice-skill-generalinvoiceskillphp)
   - [7.2. Supplier Khusus / MDR Skill (`MdrInvoiceSkill.php`)](#72-supplier-khusus--mdr-skill-mdrinvoiceskillphp)
   - [7.3. Dynamic Memory Injection (`InvoiceLearningService.php`)](#73-dynamic-memory-injection-invoicelearningservicephp)
8. [Pipeline Pencocokan Produk (`ProductMatcher.php`)](#8-pipeline-pencocokan-produk-productmatcherphp)
9. [Pre-processing Gambar (`ImagePreprocessor.php`)](#9-pre-processing-gambar-imagepreprocessorphp)
10. [Analisis Penyebab Kegagalan (Root Cause Analysis)](#10-analisis-penyebab-kegagalan-root-cause-analysis)
11. [Rekomendasi Arah Optimasi untuk Model Canggih](#11-rekomendasi-arah-optimasi-untuk-model-canggih)

---

## 1. RINGKASAN MASALAH SAAT INI

1. **Hasil Ekstraksi Sering 0 Item**:
   - Model Vision yang dipanggil mengembalikan respons kosong `[]` atau teks non-JSON yang gagal di-decode.
   - Atau model berhasil membaca teks nama barang nota, namun algoritma pencocokan database (`ProductMatcher.php`) gagal mencocokkan nama singkatan nota dengan produk database (skor di bawah `MATCH_THRESHOLD = 55`), sehingga di frontend dianggap tidak cocok dan tidak dimasukkan ke keranjang pembelian.
2. **Waktu Proses Sangat Lama (30 - 120 detik / Timeout)**:
   - Backend memanggil model-model gratis di OpenRouter (`google/gemma-4-31b-it:free`, `nvidia/nemotron-nano-12b-v2-vl:free`, `openrouter/free`).
   - Model-model gratis sering mengalami *cold-start*, antrean panjang (rate limit 429), dan timeout (40 detik x 3 percobaan = 120 detik).
   - Pengiriman context prompt yang terlalu panjang (daftar alias dan produk supplier) memperlambat waktu inferensi model vision.

---

## 2. ARSITEKTUR & DIAGRAM ALUR (PIPELINE)

```
[ User Ambil Foto / Upload Invoice ]
                 │
                 ▼
[ Frontend: Kompresi Gambar (1800px max, 0.85 JPEG) ]
                 │ (POST base64 via /api/ai/scan-invoice)
                 ▼
[ Backend: ApiController::scanInvoiceAI ]
                 │
                 ▼
[ Stage 1: ImagePreprocessor ] ───> Validasi base64 & format MIME
                 │
                 ▼
[ Stage 2: SupplierDetector ] ───> Deteksi template supplier (MDR / General)
                 │
                 ▼
[ Stage 3: PromptBuilder & Dynamic Context ] ───> Ambil memori alias nota dari InvoiceLearningService
                 │
                 ▼
[ Stage 4: Call OpenRouter API (Vision Model) ]
    ├─ Model 1: google/gemma-4-31b-it:free (Timeout: 40s)
    ├─ Fallback 2: google/gemma-4-26b-a4b-it:free
    └─ Fallback 3: nvidia/nemotron-nano-12b-v2-vl:free / openrouter/free
                 │ (Raw JSON Array of Extracted Items)
                 ▼
[ Stage 5: Skill Parsing (parseItem) ] ───> Normalisasi QTY (BSR/TGH/KCL), harga satuan, total harga
                 │
                 ▼
[ Stage 6: ProductMatcher ] ───> Pencocokan multi-strategi (Kode, Alias, Singkatan, Token, Kemasan)
                 │
                 ▼
[ Stage 7: ConfidenceScorer & Format Frontend ]
                 │ (JSON Response: success, data, metadata)
                 ▼
[ Frontend: purchases/create.php ] ───> Auto-inject item yang cocok ke Cart Pembelian
```

---

## 3. ALUR KOMUNIKASI FRONTEND & ENDPOINT

- **Endpoint**: `POST /api/ai/scan-invoice`
- **Controller**: `app/controllers/ApiController.php` -> method `scanInvoiceAI()`
- **Headers**: `Content-Type: application/json`, `X-CSRF-Token`
- **Request Payload**:
  ```json
  {
    "csrf_token": "...",
    "image_base64": "data:image/jpeg;base64,...",
    "supplier_id": 12
  }
  ```
- **Response Format**:
  ```json
  {
    "success": true,
    "message": "Berhasil memproses 15 item via skill PT Medan Distribusindo Raya (8.2s)",
    "data": [
      {
        "original_name": "GIV WHT BTY 4X110 BSR",
        "supplier_code": "100234",
        "qty": 2,
        "unit_price": 145000,
        "total_price": 290000,
        "unit": "Karton",
        "is_matched": true,
        "product_id": 45,
        "product_name": "Giv White Beauty Soap 110g",
        "match_score": 92.5,
        "packaging_level": 3,
        "confidence": 92.5,
        "product_data": { ... }
      }
    ],
    "metadata": {
      "supplier_detected": "PT Medan Distribusindo Raya",
      "skill_used": "mdr",
      "execution_time": 8.2,
      "avg_confidence": 88.4
    }
  }
  ```

---

## 4. KODE FRONTEND: PEMBACAAN & PENGIRIMAN GAMBAR

Lokasi File: `app/views/purchases/create.php` (dan `app/views/purchases/edit.php`)

```javascript
// 1. Kompresi Gambar Sebelum Dikirim (Mengurangi payload jaringan)
async function compressImageForAI(dataUrl, maxDimension = 1800, quality = 0.85) {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
            let w = img.width;
            let h = img.height;
            if (w > maxDimension || h > maxDimension) {
                if (w > h) {
                    h = Math.round((h * maxDimension) / w);
                    w = maxDimension;
                } else {
                    w = Math.round((w * maxDimension) / h);
                    h = maxDimension;
                }
            }
            const canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, w, h);
            resolve(canvas.toDataURL('image/jpeg', quality));
        };
        img.onerror = () => resolve(dataUrl);
        img.src = dataUrl;
    });
}

// 2. Fungsi Utama Eksekusi Scan AI
async function scanInvoiceWithAI() {
    if (!invoicePhotoBase64) {
        showToast('Pilih atau ambil foto invoice terlebih dahulu', 'error');
        return;
    }
    
    const btn = document.getElementById('btnScanAI');
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Memproses AI...';
        
        let imageToSend = invoicePhotoBase64;
        try {
            imageToSend = await compressImageForAI(invoicePhotoBase64, 1800, 0.85);
        } catch(ce) {
            console.warn('Image pre-compression bypassed:', ce);
        }

        const payload = {
            csrf_token: csrfVal,
            image_base64: imageToSend,
            supplier_id: currentSupplierId || null
        };
        
        const result = await api(`${BASE_URL}api/ai/scan-invoice`, {
            method: 'POST',
            timeout: 120000,
            body: JSON.stringify(payload)
        });

        if (!result) throw new Error('Tidak ada respons dari server.');
        if (result.error) throw new Error(result.error);
        
        if (result.success && result.data && result.data.length > 0) {
            showToast('AI berhasil memparsing ' + result.data.length + ' item', 'success');
            
            for (const item of result.data) {
                if (item.is_matched && item.product_id) {
                    try {
                        const productData = item.product_data || await api(`${BASE_URL}api/products/${item.product_id}`);
                        if (productData && productData.packagings && productData.packagings.length > 0) {
                            const targetLevel = item.packaging_level || 1;
                            let selectedPkg = productData.packagings.find(p => p.level == targetLevel);
                            if (!selectedPkg) {
                                let targetUnit = (item.unit || '').toLowerCase().trim();
                                if (targetUnit) {
                                    selectedPkg = productData.packagings.find(p => p.unit_name && p.unit_name.toLowerCase().includes(targetUnit));
                                }
                            }
                            if (!selectedPkg) selectedPkg = productData.packagings[0];

                            const scanUnitPrice = parseFloat(item.unit_price) || 0;
                            const scanQty = parseFloat(item.qty) || 1;
                            const scanTotal = parseFloat(item.total_price) || (scanQty * scanUnitPrice);

                            // Tambahkan atau update produk di keranjang pembelian
                            const existingIndex = purchaseItems.findIndex(i => i.product_id == productData.id && i.level == selectedPkg.level);
                            if (existingIndex > -1) {
                                const existing = purchaseItems[existingIndex];
                                existing.quantity = scanQty;
                                if (scanUnitPrice > 0) existing.buy_price = scanUnitPrice;
                                existing.total = scanTotal;
                                propagateFromMainInputs(existing);
                                syncSellPricesWhenBuyPriceChanges(existing);
                            } else {
                                addProductToCart(productData, selectedPkg.level);
                                const addedItem = purchaseItems[0];
                                if (addedItem && addedItem.product_id == productData.id) {
                                    addedItem.quantity  = scanQty;
                                    if (scanUnitPrice > 0) addedItem.buy_price = scanUnitPrice;
                                    addedItem.total     = scanTotal;
                                    propagateFromMainInputs(addedItem);
                                    syncSellPricesWhenBuyPriceChanges(addedItem);
                                }
                            }
                        }
                    } catch(e) {
                        console.error('Failed to add AI mapped item', e);
                    }
                } else {
                    showToast('Item "' + (item.original_name || item.supplier_code || item.name) + '" tidak dikenali di database, silakan input manual.', 'warning');
                }
            }
            renderCart();
            calculateTotal();
        } else {
            showToast('AI tidak menemukan item yang valid', 'warning');
        }
    } catch (err) {
        console.error('Error scanning invoice:', err);
        showToast(err.message || 'Gagal memindai invoice dengan AI', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
```

---

## 5. KODE CONTROLLER: `ApiController@scanInvoiceAI`

Lokasi File: `app/controllers/ApiController.php`

```php
    public function scanInvoiceAI()
    {
        $this->validateCSRF();
        set_time_limit(120); // Mencegah PHP timeout
        
        ob_start();
        try {
            $rawInput = file_get_contents('php://input');
            $rawJson = json_decode($rawInput, true);
            if (!is_array($rawJson)) {
                $rawJson = [];
            }
            $imageB64 = $rawJson['image_base64'] ?? '';
            if (empty($imageB64)) {
                throw new \Exception("Gambar invoice tidak ditemukan");
            }

            $supplierId = isset($rawJson['supplier_id']) ? (int)$rawJson['supplier_id'] : null;

            require_once __DIR__ . '/../services/invoice/ImagePreprocessor.php';
            require_once __DIR__ . '/../services/invoice/PromptBuilder.php';
            require_once __DIR__ . '/../services/invoice/LayoutAnalyzer.php';
            require_once __DIR__ . '/../services/invoice/TableParser.php';
            require_once __DIR__ . '/../services/invoice/InvoiceValidator.php';
            require_once __DIR__ . '/../services/invoice/ProductMatcher.php';
            require_once __DIR__ . '/../services/invoice/ConfidenceScorer.php';
            require_once __DIR__ . '/../services/invoice/SelfCorrectionEngine.php';
            require_once __DIR__ . '/../services/invoice/TemplateLearner.php';
            require_once __DIR__ . '/../services/invoice/InvoiceScanService.php';

            $service = new InvoiceScanService($this->db);
            $result = $service->scan($imageB64, $supplierId);

            ob_end_clean();

            if ($result['success']) {
                $this->json([
                    'success'  => true,
                    'message'  => $result['message'],
                    'data'     => $result['data'],
                    'metadata' => $result['metadata']
                ]);
            } else {
                $this->json(['error' => $result['message']], 500);
            }

        } catch (\Exception $e) {
            ob_end_clean();
            $this->json(['error' => $e->getMessage()], 500);
        } catch (\Error $err) {
            ob_end_clean();
            $this->json(['error' => 'Fatal internal error: ' . $err->getMessage()], 500);
        }
    }
```

---

## 6. KODE SERVICE UTAMA: `InvoiceScanService.php`

Lokasi File: `app/services/invoice/InvoiceScanService.php`

```php
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

class InvoiceScanService
{
    private $db;
    private $settingModel;
    private $productModel;

    private $preprocessor;
    private $supplierDetector;
    private $skillManager;
    private $promptBuilder;
    private $layoutAnalyzer;
    private $tableParser;
    private $validator;
    private $matcher;
    private $scorer;
    private $selfCorrection;
    private $templateLearner;

    public function __construct(\PDO $db)
    {
        $this->db               = $db;
        $this->settingModel     = new SettingModel();
        $this->productModel     = new ProductModel();

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
    }

    public function scan(string $imageB64, ?int $supplierId = null): array
    {
        $startTime = microtime(true);

        try {
            // 1. Analisis & Validasi Gambar Base64
            $imageInfo = $this->preprocessor->analyze($imageB64);
            if (!$imageInfo['valid']) {
                throw new \Exception($imageInfo['error'] ?? 'Gambar tidak valid');
            }

            // 2. Deteksi Supplier & Skill (MDR / Wings / General)
            $detection = $this->supplierDetector->detect($supplierId);
            $skill = $this->skillManager->getSkill($detection['skill_key']);

            // 3. Ambil Konteks Database Produk
            $allProducts = $this->getAllProductsWithPackagings();
            $resolvedSupplierId = $detection['supplier_id'] ?: $supplierId;
            $supplierProducts = [];
            if ($resolvedSupplierId && $resolvedSupplierId > 0) {
                $supplierProducts = $this->getSupplierProducts($resolvedSupplierId);
            }

            // 4. Bangun Prompt
            $systemPrompt = $skill->getSystemPrompt();
            $userHints = $skill->getUserPromptHints();

            $userPromptSetting = trim($this->settingModel->get('ai_invoice_prompt', ''));
            $userMessageText = $userPromptSetting ?: 'Baca gambar invoice ini dan ekstrak semua item barang ke dalam format JSON.';
            if (!empty($userHints)) {
                $userMessageText .= "\n\n" . $userHints;
            }

            // Injeksi Memori Alias yang Sudah Dipelajari
            require_once __DIR__ . '/InvoiceLearningService.php';
            $learningService = new InvoiceLearningService($this->db);
            $learnedAliases = $learningService->getLearnedAliasesForPrompt($resolvedSupplierId, 50);

            $contextLines = [];
            if (!empty($learnedAliases)) {
                $contextLines[] = "\n## MEMORI POLA PRODUK SUPPLIER (Alias nota yang sudah dipelajari):";
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
                $contextLines[] = "\n## REFERENSI PRODUK SUPPLIER INI (Gunakan kode/nama jika cocok):";
                foreach (array_slice($supplierProducts, 0, 40) as $sp) {
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

            // 5. Panggil API AI Vision (OpenRouter)
            $aiResponse = $this->callOpenRouter($systemPrompt, $userMessageText, $imageB64, $imageInfo['format']);
            if (empty($aiResponse)) {
                throw new \Exception('AI gagal memproses gambar atau mengembalikan respons kosong.');
            }

            // Parsing hasil JSON dari AI
            $rawItems = [];
            if (is_array($aiResponse)) {
                if (isset($aiResponse['items']) && is_array($aiResponse['items'])) {
                    $rawItems = $aiResponse['items'];
                } elseif (isset($aiResponse[0])) {
                    $rawItems = $aiResponse;
                }
            }

            // 6. Parse Item via Supplier Skill
            $supplierProductIds = array_column($supplierProducts, 'id');
            $parsedItems = [];
            foreach ($rawItems as $rawItem) {
                if (!is_array($rawItem)) continue;
                $parsed = $skill->parseItem($rawItem);
                if (!empty($parsed['name']) || !empty($parsed['supplier_code'])) {
                    $parsedItems[] = $parsed;
                }
            }

            // 7. Pencocokan Produk Database (Product Matching)
            $matchedItems = [];
            foreach ($parsedItems as $item) {
                $matchResult = $this->matcher->match($item, $allProducts, $supplierProductIds, $skill);
                $mergedItem  = array_merge($item, $matchResult);
                $scoredItem  = $this->scorer->score($mergedItem);
                $matchedItems[] = $scoredItem;
            }

            // 8. Format untuk Frontend
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

    private function callOpenRouter(
        string $systemPrompt,
        string $userPrompt,
        string $imageB64,
        string $imageFormat,
        ?string $apiKey = null,
        ?string $model = null
    ): ?array {
        $apiKey = $apiKey ?? trim($this->settingModel->get('ai_api_key', ''));
        $model  = $model  ?? trim($this->settingModel->get('ai_model', 'openrouter/auto'));

        if (empty($apiKey)) {
            throw new \Exception('API Key AI Scanner belum diatur di Pengaturan Sistem & AI.');
        }

        $FREE_VISION_MODELS = [
            'google/gemma-4-31b-it:free',
            'google/gemma-4-26b-a4b-it:free',
            'nvidia/nemotron-nano-12b-v2-vl:free',
            'openrouter/free',
        ];

        if (empty($model) || in_array($model, ['openrouter/auto', 'auto'])) {
            $modelsToTry = $FREE_VISION_MODELS;
        } else {
            $modelsToTry = array_unique(array_merge([$model], $FREE_VISION_MODELS));
        }

        $imageBlock = $this->preprocessor->buildImageUrlBlock($imageB64, $imageFormat);
        $lastError  = null;

        foreach ($modelsToTry as $tryModel) {
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 40);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $err      = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (PHP_VERSION_ID < 80500) { @curl_close($ch); }

            if ($err || $httpCode !== 200) {
                continue;
            }

            $resData = json_decode($response, true);
            $content = $resData['choices'][0]['message']['content'] ?? '';

            if (empty(trim($content))) continue;

            // Ekstraksi JSON dari respons
            $decoded = null;
            $cleanContent = trim($content);
            if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $cleanContent, $m)) {
                $decoded = json_decode(trim($m[1]), true);
            }
            if (!is_array($decoded)) {
                $decoded = json_decode($cleanContent, true);
            }
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        throw new \Exception('AI gagal membaca gambar invoice setelah mencoba model yang tersedia.');
    }
}
```

---

## 7. SISTEM PROMPT & EKSTRAKSI AI VISION

### 7.1. General Invoice Skill (`GeneralInvoiceSkill.php`)

```php
    public function getSystemPrompt(bool $isCorrectionPass = false): string
    {
        $lines = [];
        $lines[] = 'Kamu adalah asisten OCR dan analisis invoice cerdas dan berkecepatan tinggi.';
        $lines[] = 'Tugasmu adalah membaca gambar invoice/faktur supplier dan mengekstrak semua baris item barang.';
        $lines[] = '';
        $lines[] = '## ATURAN EKSTRAKSI:';
        $lines[] = '1. Selalu kembalikan HANYA JSON array yang valid.';
        $lines[] = '2. Baca semua baris produk dalam tabel invoice.';
        $lines[] = '3. "supplier_code": Ambil kode produk/barcode yang tertulis di invoice jika ada.';
        $lines[] = '4. "name": Nama produk lengkap.';
        $lines[] = '5. "qty": Jumlah barang yang dibeli.';
        $lines[] = '6. "unit": Satuan barang (Karton, Box, Pcs, Renceng, Lusin, dll).';
        $lines[] = '7. "total_price": Total harga akhir baris (kolom paling kanan).';
        $lines[] = '8. "unit_price": Biarkan null (sistem backend akan menghitung otomatis total_price / qty).';
        $lines[] = '9. PENTING: Jika invoice memiliki kolom qty terpisah bernama BSR, TGH, KCL, isi "qty_bsr", "qty_tgh", "qty_kcl".';
        $lines[] = '';
        $lines[] = '## FORMAT OUTPUT JSON (HANYA JSON ARRAY VALID, TANPA PENJELASAN):';
        $lines[] = '[';
        $lines[] = '  {';
        $lines[] = '    "supplier_code": "KODE123",';
        $lines[] = '    "name": "NAMA PRODUK LENGKAP",';
        $lines[] = '    "qty": 1,';
        $lines[] = '    "unit": "Karton",';
        $lines[] = '    "qty_bsr": 0,';
        $lines[] = '    "qty_tgh": 1,';
        $lines[] = '    "qty_kcl": 0,';
        $lines[] = '    "total_price": 100000,';
        $lines[] = '    "unit_price": null';
        $lines[] = '  }';
        $lines[] = ']';

        return implode("\n", $lines);
    }
```

### 7.2. Supplier Khusus / MDR Skill (`MdrInvoiceSkill.php`)

Karakteristik faktur distributor Wings / PT Medan Distribusindo Raya:
- Memiliki 3 kolom Qty terpisah: **BSR** (Karton/Dus), **TGH** (Lusin/Box), **KCL** (Pcs/Sachet).
- Nama produk sangat disingkat (Contoh: `FLR LIQ FLORAL REFILL 780`, `GIV WHT BTY 4X110 BSR`, `MIE SEDAAP GORENG 40X90`).
- Memiliki kode produk supplier angka (misal: `3001245`).
- Kolom Total Bayar / DPP Neto berada di sisi paling kanan.

---

## 8. PIPELINE PENCOCOKAN PRODUK (`ProductMatcher.php`)

Algoritma multi-strategi scoring yang digunakan untuk mencocokkan item nota dengan master data produk di database:

1. **Strategi 1: Exact Supplier Code Match (Skor: 200)**:
   - Mencocokkan `supplier_product_code` atau `product.code` persis dengan kode pada nota.
2. **Strategi 2: Exact Supplier Invoice Name Match (Skor: 95)**:
   - Mencocokkan dengan field `supplier_invoice_name` pada tabel `products` (termasuk multi-alias baris baru).
3. **Strategi 3: Exact Product Full Name Match (Skor: 90)**:
   - Mencocokkan string nama produk yang sudah dinormalisasi (lowercase, hapus tanda baca).
4. **Strategi 4: Abbreviation Expansion Match (Skor: 80)**:
   - Mengembangkan singkatan umum (`bsr` -> `besar`, `kcl` -> `kecil`, `ctn` -> `karton`, `flr` -> `floor`, `bks` -> `bungkus`, `btl` -> `botol`, dll).
5. **Strategi 5: Smart Token Overlap (Bobot Skor: 0 - 85)**:
   - Memecah kata-kata kunci, menghitung persentase kata yang cocok dengan mengabaikan kata kemasan (`skip_tokens`).
6. **Strategi 6: Fuzzy String Matching (`similar_text()`) (Skor: 0 - 75)**.
7. **Strategi 7: Brand, Variant, & Weight Boost (Tambahan Bonus: +15 hingga +45)**:
   - Jika brand + varian rasa + gramasi berat (`110g`, `780ml`) cocok bersamaan.
8. **Strategi 8: Price Distance Matching (Level Kemasan Satuan)**:
   - Mencocokkan harga satuan faktur dengan `buy_price` di tabel `product_packagings` (Level 1 Ecer, Level 2 Renceng/Lusin, Level 3 Dus/Karton).

---

## 9. PRE-PROCESSING GAMBAR (`ImagePreprocessor.php`)

```php
class ImagePreprocessor
{
    public function analyze(string $imageB64): array
    {
        $cleanB64 = $imageB64;
        $format = 'jpeg';

        if (preg_match('/^data:image\/(\w+);base64,/', $imageB64, $matches)) {
            $format = strtolower($matches[1]);
            $cleanB64 = substr($imageB64, strpos($imageB64, ',') + 1);
        }

        $decoded = base64_decode($cleanB64, true);
        if ($decoded === false || strlen($decoded) < 100) {
            return ['valid' => false, 'error' => 'Data gambar base64 tidak valid atau rusak'];
        }

        return [
            'valid'     => true,
            'format'    => $format,
            'clean_b64' => $cleanB64,
            'size_kb'   => round(strlen($decoded) / 1024, 1),
        ];
    }

    public function buildImageUrlBlock(string $imageB64, string $format = 'jpeg'): array
    {
        $mime = ($format === 'png') ? 'image/png' : 'image/jpeg';
        $url = (strpos($imageB64, 'data:') === 0) 
            ? $imageB64 
            : "data:{$mime};base64,{$imageB64}";

        return [
            'type'      => 'image_url',
            'image_url' => ['url' => $url]
        ];
    }
}
```

---

## 10. ANALISIS PENYEBAB KEGAGALAN (ROOT CAUSE ANALYSIS)

| No | Gejala / Masalah | Akar Masalah Teknis |
|---|---|---|
| 1 | **Proses Sangat Lambat (30s - 120s)** | Pemanggilan model gratis OpenRouter (`gemma-4-31b-it:free`, `nemotron-nano-12b-v2-vl:free`) sering mengalami antrean server, rate limit 429, dan cold-start. Setiap model dicoba berurutan dengan timeout 40 detik. |
| 2 | **Hasil Ekstraksi Selalu 0 Item** | 1) Model vision gratisan sering mengembalikan teks penjelasan atau tag markdown yang gagal di-decode JSON-nya.<br>2) AI berhasil mengekstrak teks nota, namun `ProductMatcher` menghasilkan skor di bawah ambang batas (`55`) karena nama nota sangat disingkat dan kode produk supplier belum tersimpan di database.<br>3) Frontend memfilter item yang `is_matched == false`, sehingga item yang tidak cocok tidak dimasukkan ke keranjang belanja. |
| 3 | **Prompt Terlalu Kompleks / Gemuk** | Penggabungan puluhan baris memori alias dan aturan QTY bertingkat ke dalam satu prompt vision membuat model kecil bingung dan kehilangan fokus pada tabel nota utama. |

---

## 11. REKOMENDASI ARAH OPTIMASI UNTUK MODEL CANGGIH

Untuk dianalisis lebih lanjut oleh model AI tingkat lanjut:

1. **Pemilihan Provider & Model Vision Terbaik**:
   - Evaluasi penggunaan direct API (misalnya Google Gemini 1.5 Flash / Pro via API key langsung, atau OpenRouter model vision premium yang stabil seperti `google/gemini-flash-1.5`, `anthropic/claude-3-5-sonnet`, atau `openai/gpt-4o-mini`).
   - Bandingkan latensi, akurasi OCR nota miring/buram, dan konsistensi output JSON.
2. **Optimalisasi System Prompt & Schema Ekstraksi**:
   - Pembuatan prompt yang ringkas, terstandarisasi, dengan instruksi pemisahan baris tabel, penanganan kolom QTY bertingkat (BSR/TGH/KCL atau QTY/SATUAN), diskon per baris, dan total harga.
   - Pemanfaatan *Structured Outputs / JSON Schema mode* agar AI dijamin selalu mengembalikan JSON murni tanpa teks pengantar.
3. **Peningkatan Logika Fuzzy & Semantic Product Matching**:
   - Bagaimana mencocokkan singkatan nota ekstrem (misal: `SGM EKS 1+ VN 900G` -> `SGM Eksplor 1 Plus Vanila 900 gr`) dengan akurasi tinggi.
   - Penanganan fallback UI: Menampilkan daftar item yang "Belum Cocok" di modal khusus agar user kasir bisa memilih produk padanannya dengan 1 klik, dan sistem otomatis merekam alias tersebut ke memori pembelajaran (`InvoiceLearningService`).
