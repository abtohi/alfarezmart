<?php
/**
 * Digiflazz Controller
 * Handles HTTP requests for Digiflazz PPOB features
 */

class DigiflazzController extends Controller {
    private DigiflazzService $digiService;
    private DigiflazzModel $digiModel;

    public function __construct() {
        parent::__construct();
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/webhook') === false) {
            $this->requireService('ppob');
        }
        $this->digiService = new DigiflazzService();
        $this->digiModel = new DigiflazzModel();
    }

    /**
     * ==========================================
     * WEB VIEWS
     * ==========================================
     */

    public function index() {
        AuthController::requireAuth();
        $settingModel = new SettingModel();
        $mode = $settingModel->get('digiflazz_mode', 'development');
        $requirePin = !empty($settingModel->get('digiflazz_pin', ''));
        $csrfToken = (new Security())->getCSRFToken();
        $this->view('ppob/index', ['title' => 'Produk Digital (PPOB)', 'mode' => $mode, 'requirePin' => $requirePin, 'csrfToken' => $csrfToken]);
    }

    public function settings() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);
        
        $settingModel = new SettingModel();
        $settings = [
            'username' => $settingModel->get('digiflazz_username', ''),
            'api_key_dev' => $settingModel->get('digiflazz_api_key_dev', ''),
            'api_key_prod' => $settingModel->get('digiflazz_api_key_prod', ''),
            'webhook_secret' => $settingModel->get('digiflazz_webhook_secret', ''),
            'mode' => $settingModel->get('digiflazz_mode', 'development')
        ];

        $markupRules = $this->digiModel->getMarkupRules();

        $this->view('ppob/settings', ['title' => 'Pengaturan PPOB', 'settings' => $settings, 'markupRules' => $markupRules]);
    }

    public function priceList() {
        AuthController::requireAuth();
        $this->view('ppob/price_list', ['title' => 'Daftar Harga PPOB']);
    }

    public function documentation() {
        AuthController::requireAuth();
        $this->view('ppob/documentation', ['title' => 'Dokumentasi & API']);
    }

    public function summaryView() {
        AuthController::requireAuth();
        $this->view('ppob/summary', ['title' => 'Analytics PPOB']);
    }

    public function history() {
        AuthController::requireAuth();
        $stats = $this->digiModel->getTransactionStats();
        $this->view('ppob/history', ['title' => 'Laporan Transaksi Prabayar', 'stats' => $stats]);
    }

    /**
     * ==========================================
     * API ENDPOINTS
     * ==========================================
     */

    public function apiGetBalance() {
        AuthController::requireAuth();
        try {
            $res = $this->digiService->getBalance();
            if ($res['success'] && isset($res['data']['deposit'])) {
                try {
                    $this->syncPendingDeposits((float)$res['data']['deposit']);
                } catch (\Throwable $te) {}
            }
            header('Content-Type: application/json');
            echo json_encode($res);
        } catch (\Throwable $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiGetProducts(string $category) {
        AuthController::requireAuth();
        $brand = $_GET['brand'] ?? null;
        $type = $_GET['type'] ?? 'prepaid';
        
        $products = $this->digiModel->getProducts($category, $brand, $type);
        $rates = $this->digiModel->getSellerSuccessRates();
        $prodRates = $this->digiModel->getProductSuccessRates();
        $skuSellerRates = $this->digiModel->getSkuSellerSpeedRates();
        
        foreach ($products as &$p) {
            $seller = isset($p['seller_name']) ? trim($p['seller_name']) : null;
            if ($seller && isset($rates[$seller]) && $rates[$seller]['total'] > 0) {
                $p['success_rate'] = round(($rates[$seller]['success'] / $rates[$seller]['total']) * 100, 1);
                $p['seller_avg_speed'] = $rates[$seller]['avg_speed'];
                $p['seller_trx_count'] = (int)$rates[$seller]['success'];
            } else {
                $p['success_rate'] = null;
                $p['seller_avg_speed'] = null;
                $p['seller_trx_count'] = 0;
            }

            $sku = isset($p['buyer_sku_code']) ? trim($p['buyer_sku_code']) : null;
            if ($sku && isset($prodRates[$sku]) && $prodRates[$sku]['total'] > 0) {
                $p['product_success_rate'] = round(($prodRates[$sku]['success'] / $prodRates[$sku]['total']) * 100, 1);
                $p['product_trx_count'] = (int)$prodRates[$sku]['success'];
                $p['product_avg_speed'] = $prodRates[$sku]['avg_speed'];
            } else {
                $p['product_success_rate'] = null;
                $p['product_trx_count'] = 0;
                $p['product_avg_speed'] = null;
            }

            // Per-SKU+Seller speed (kecepatan produk ini spesifik pada seller ini)
            $skuSellerKey = $sku && $seller ? ($sku . '|' . $seller) : null;
            if ($skuSellerKey && isset($skuSellerRates[$skuSellerKey])) {
                $p['sku_seller_avg_speed'] = $skuSellerRates[$skuSellerKey]['avg_speed'];
                $p['sku_seller_trx_count'] = (int)$skuSellerRates[$skuSellerKey]['total'];
            } else {
                $p['sku_seller_avg_speed'] = null;
                $p['sku_seller_trx_count'] = 0;
            }
            $p['is_custom_price'] = isset($p['is_custom_price']) ? (int)$p['is_custom_price'] : 0;
        }
        
        $this->json(['success' => true, 'data' => $products]);
    }

    public function apiGetAllProducts() {
        AuthController::requireAuth();
        
        // Return all products for datatable
        $stmt = $this->digiModel->db->query("SELECT * FROM digi_products WHERE is_active = 1 AND buyer_product_status = 1 ORDER BY category ASC, brand ASC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $rates = $this->digiModel->getSellerSuccessRates();
        $prodRates = $this->digiModel->getProductSuccessRates();
        $skuSellerRates = $this->digiModel->getSkuSellerSpeedRates();
        
        foreach ($products as &$p) {
            $seller = isset($p['seller_name']) ? trim($p['seller_name']) : null;
            if ($seller && isset($rates[$seller]) && $rates[$seller]['total'] > 0) {
                $p['success_rate'] = round(($rates[$seller]['success'] / $rates[$seller]['total']) * 100, 1);
                $p['seller_avg_speed'] = $rates[$seller]['avg_speed'];
                $p['seller_trx_count'] = (int)$rates[$seller]['success'];
            } else {
                $p['success_rate'] = null;
                $p['seller_avg_speed'] = null;
                $p['seller_trx_count'] = 0;
            }

            $sku = isset($p['buyer_sku_code']) ? trim($p['buyer_sku_code']) : null;
            if ($sku && isset($prodRates[$sku]) && $prodRates[$sku]['total'] > 0) {
                $p['product_success_rate'] = round(($prodRates[$sku]['success'] / $prodRates[$sku]['total']) * 100, 1);
                $p['product_trx_count'] = (int)$prodRates[$sku]['success'];
                $p['product_avg_speed'] = $prodRates[$sku]['avg_speed'];
            } else {
                $p['product_success_rate'] = null;
                $p['product_trx_count'] = 0;
                $p['product_avg_speed'] = null;
            }

            // Per-SKU+Seller speed
            $skuSellerKey = $sku && $seller ? ($sku . '|' . $seller) : null;
            if ($skuSellerKey && isset($skuSellerRates[$skuSellerKey])) {
                $p['sku_seller_avg_speed'] = $skuSellerRates[$skuSellerKey]['avg_speed'];
                $p['sku_seller_trx_count'] = (int)$skuSellerRates[$skuSellerKey]['total'];
            } else {
                $p['sku_seller_avg_speed'] = null;
                $p['sku_seller_trx_count'] = 0;
            }
            $p['is_custom_price'] = isset($p['is_custom_price']) ? (int)$p['is_custom_price'] : 0;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $products]);
        exit;
    }

    public function apiSellerHistory() {
        AuthController::requireAuth();
        $seller = $_GET['seller'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        
        if (empty($seller)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Seller name is required']);
            exit;
        }

        $history = $this->digiModel->getSellerHistory($seller, $page);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $history]);
        exit;
    }

    public function apiGetTargetHistory() {
        AuthController::requireAuth();
        $number = isset($_GET['number']) ? trim(Security::sanitize($_GET['number'])) : '';
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);
        
        if (empty($cleanNumber) && empty($number)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'history' => []]);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        
        $variants = array_filter([$number, $cleanNumber]);
        if (strpos($cleanNumber, '08') === 0) {
            $variants[] = '62' . substr($cleanNumber, 1);
            $variants[] = substr($cleanNumber, 1);
        } elseif (strpos($cleanNumber, '628') === 0) {
            $variants[] = '0' . substr($cleanNumber, 2);
            $variants[] = substr($cleanNumber, 2);
        } elseif (strpos($cleanNumber, '8') === 0 && strlen($cleanNumber) >= 8) {
            $variants[] = '0' . $cleanNumber;
            $variants[] = '62' . $cleanNumber;
        }
        $variants = array_values(array_unique(array_filter($variants)));

        $inClause = implode(',', array_fill(0, count($variants), '?'));
        $sql = "
            SELECT t.id, t.ref_id, t.buyer_sku_code, t.customer_no, t.customer_name, 
                   t.product_name, t.category, t.brand, t.type, t.sell_price, t.modal_price, t.status, 
                   t.seller_name, t.created_at, t.user_id, u.name as user_name
            FROM digi_transactions t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE (
                t.customer_no IN ($inClause)
                OR REPLACE(REPLACE(REPLACE(t.customer_no, '-', ''), ' ', ''), '+', '') IN ($inClause)
        ";

        $params = array_merge($variants, $variants);
        if (!empty($cleanNumber) && strlen($cleanNumber) >= 6) {
            $lastDigits = '%' . substr($cleanNumber, -6);
            $sql .= " OR t.customer_no LIKE ? OR REPLACE(REPLACE(t.customer_no, '-', ''), ' ', '') LIKE ?";
            $params[] = $lastDigits;
            $params[] = $lastDigits;
        }
        $sql .= ") ORDER BY t.created_at DESC LIMIT 30";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'history' => []]);
            exit;
        }

        // Fetch products availability & current price from digi_products
        $skus = array_unique(array_column($rows, 'buyer_sku_code'));
        $productsMap = [];
        if (!empty($skus)) {
            $in  = str_repeat('?,', count($skus) - 1) . '?';
            $stmtProd = $db->prepare("
                SELECT buyer_sku_code, product_name, category, brand, type, 
                       seller_price, sell_price, buyer_product_status, 
                       seller_product_status, is_active, seller_name
                FROM digi_products 
                WHERE buyer_sku_code IN ($in)
            ");
            $stmtProd->execute(array_values($skus));
            $prods = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
            foreach ($prods as $p) {
                $productsMap[$p['buyer_sku_code']] = $p;
            }
        }

        // Fetch seller rates for sorting & badges
        $sellerRates = $this->digiModel->getSellerSuccessRates();

        $history = [];
        foreach ($rows as $r) {
            $sku = $r['buyer_sku_code'];
            $prodInfo = $productsMap[$sku] ?? null;

            $isAvailable = true;
            if ($prodInfo) {
                $isAvailable = ((int)($prodInfo['is_active'] ?? 1) === 1) &&
                               ((int)($prodInfo['buyer_product_status'] ?? 1) === 1);
            }

            $seller = !empty($r['seller_name']) ? $r['seller_name'] : (!empty($r['user_name']) ? $r['user_name'] : 'Kasir');
            $sellerData = $seller && isset($sellerRates[$seller]) ? $sellerRates[$seller] : null;
            $sellerSr = ($sellerData && $sellerData['total'] > 0) ? round(($sellerData['success'] / $sellerData['total']) * 100, 1) : 0;
            $sellerSpd = $sellerData && $sellerData['avg_speed'] !== null ? (float)$sellerData['avg_speed'] : 9999;

            $productPayload = $prodInfo ? [
                'buyer_sku_code' => $prodInfo['buyer_sku_code'],
                'product_name' => $prodInfo['product_name'],
                'category' => $prodInfo['category'],
                'brand' => $prodInfo['brand'],
                'type' => $prodInfo['type'],
                'seller_price' => (float)$prodInfo['seller_price'],
                'sell_price' => (float)($prodInfo['sell_price'] ?: $r['sell_price']),
                'seller_name' => $prodInfo['seller_name'] ?: $seller,
                'buyer_product_status' => (int)$prodInfo['buyer_product_status'],
                'seller_product_status' => (int)$prodInfo['seller_product_status'],
                'is_active' => (int)$prodInfo['is_active'],
                'seller_success_rate' => $sellerSr,
                'seller_avg_speed' => $sellerSpd < 9999 ? $sellerSpd : null
            ] : [
                'buyer_sku_code' => $r['buyer_sku_code'],
                'product_name' => $r['product_name'] ?: $r['buyer_sku_code'],
                'category' => $r['category'] ?: '',
                'brand' => $r['brand'] ?: '',
                'type' => $r['type'] ?: 'prepaid',
                'seller_price' => (float)($r['modal_price'] ?? $r['sell_price']),
                'sell_price' => (float)$r['sell_price'],
                'seller_name' => $seller,
                'buyer_product_status' => 1,
                'seller_product_status' => 1,
                'is_active' => 1,
                'seller_success_rate' => $sellerSr,
                'seller_avg_speed' => $sellerSpd < 9999 ? $sellerSpd : null
            ];

            $rawSt = strtolower(trim($r['status'] ?? ''));
            // Rank status: 1 = Success, 2 = Pending/Processing, 3 = Failed/Gagal
            $statusRank = 3;
            if ($rawSt === 'success' || $rawSt === 'sukses') {
                $statusRank = 1;
            } elseif ($rawSt === 'pending' || $rawSt === 'processing') {
                $statusRank = 2;
            }

            $history[] = [
                'id' => (int)$r['id'],
                'buyer_sku_code' => $r['buyer_sku_code'],
                'product_name' => $r['product_name'] ?: ($prodInfo['product_name'] ?? $r['buyer_sku_code']),
                'category' => $r['category'] ?: ($prodInfo['category'] ?? ''),
                'brand' => $r['brand'] ?: ($prodInfo['brand'] ?? ''),
                'type' => $r['type'] ?: ($prodInfo['type'] ?? 'prepaid'),
                'sell_price' => (float)$r['sell_price'],
                'status' => $r['status'],
                'status_rank' => $statusRank,
                'seller_name' => $seller,
                'seller_success_rate' => $sellerSr,
                'seller_avg_speed' => $sellerSpd < 9999 ? $sellerSpd : null,
                'created_at' => $r['created_at'],
                'is_available' => $isAvailable,
                'product' => $productPayload
            ];
        }

        // Sort history: 
        // 1. Status: Sukses terdepan (status_rank 1), Pending (2), Gagal paling belakang (3)
        // 2. Kecepatan tercepat (avg_speed terendah)
        // 3. Success Rate tertinggi (seller_success_rate tertinggi)
        // 4. Waktu transaksi terbaru (created_at DESC)
        usort($history, function($a, $b) {
            if ($a['status_rank'] !== $b['status_rank']) {
                return $a['status_rank'] - $b['status_rank'];
            }
            $spdA = $a['seller_avg_speed'] ?? 9999;
            $spdB = $b['seller_avg_speed'] ?? 9999;
            if ($spdA !== $spdB) {
                return ($spdA < $spdB) ? -1 : 1;
            }
            $srA = (float)($a['seller_success_rate'] ?? 0);
            $srB = (float)($b['seller_success_rate'] ?? 0);
            if ($srA !== $srB) {
                return ($srB > $srA) ? 1 : -1;
            }
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'history' => $history]);
        exit;
    }



    public function apiSearchProducts() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);
        $q = $_GET['q'] ?? '';
        
        if (strlen($q) < 2) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Query too short']);
            exit;
        }

        $stmt = $this->digiModel->db->prepare("
            SELECT buyer_sku_code, product_name, seller_price, sell_price, is_custom_price 
            FROM digi_products 
            WHERE product_name LIKE :q OR buyer_sku_code LIKE :q 
            ORDER BY product_name ASC LIMIT 50
        ");
        $stmt->execute(['q' => "%$q%"]);
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $products]);
        exit;
    }

    public function apiGetBrands(string $category) {
        AuthController::requireAuth();
        $brands = $this->digiModel->getBrands($category);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $brands]);
        exit;
    }

    public function apiInquiryPLN() {
        AuthController::requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        $customerNo = $data['customer_no'] ?? '';
        
        if (empty($customerNo)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Customer number required']);
            exit;
        }

        $res = $this->digiService->inquiryPLN($customerNo);
        
        $rc = $res['data']['rc'] ?? '';
        if ($res['success'] && $rc !== '00') {
            $res['success'] = false;
            $res['message'] = $res['data']['message'] ?? 'Gagal memverifikasi ID Pelanggan PLN.';
        }

        header('Content-Type: application/json');
        echo json_encode($res);
        exit;
    }

    public function apiInquiryPostpaid() {
        AuthController::requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sku = trim($data['sku'] ?? '');
        $customerNo = trim($data['customer_no'] ?? '');
        $amount = isset($data['amount']) && (int)$data['amount'] > 0 ? (int)$data['amount'] : null;
        $year = isset($data['year']) && (int)$data['year'] > 1900 ? (int)$data['year'] : null;
        
        if (empty($sku) || empty($customerNo)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'SKU dan Nomor Pelanggan / Tujuan wajib diisi.']);
            exit;
        }

        $refId = 'INQ-' . date('YmdHis') . '-' . rand(1000, 9999);
        $res = $this->digiService->inquiryPostpaid($sku, $customerNo, $refId, $amount, $year);

        $rc = $res['data']['rc'] ?? '';
        if ($res['success'] && $rc !== '00') {
            $res['success'] = false;
            $res['message'] = $res['data']['message'] ?? $res['message'] ?? 'Tagihan tidak ditemukan atau gagal dicek.';
        }

        if ($res['success'] && isset($res['data'])) {
            // Append ref_id so frontend can use it to pay
            $res['data']['ref_id'] = $refId;
        }

        header('Content-Type: application/json');
        echo json_encode($res);
        exit;
    }

    public function apiCreateTransaction() {
        AuthController::requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        
        $settingModel = new SettingModel();
        $pin = $settingModel->get('digiflazz_pin', '');
        if (!empty($pin)) {
            if (!isset($data['pin']) || $data['pin'] !== $pin) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'PIN PPOB salah atau tidak dimasukkan']);
                exit;
            }
        }
        
        $sku = trim($data['sku'] ?? '');
        $customerNo = trim($data['customer_no'] ?? '');
        $refIdPostpaid = $data['ref_id'] ?? null; // from inquiry
        $amount = isset($data['amount']) && (int)$data['amount'] > 0 ? (int)$data['amount'] : null;
        
        if (empty($sku) || empty($customerNo)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'SKU and Customer No required']);
            exit;
        }

        $product = $this->digiModel->getProductBySku($sku);
        if (!$product) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }

        // Generate unique ref_id for prepaid, or use postpaid ref_id
        $refId = $refIdPostpaid ? $refIdPostpaid : 'DIGI-' . date('YmdHis') . '-' . rand(1000, 9999);
        $currentUser = $_SESSION['user'] ?? ['id' => 1]; // Fallback ID 1
        $isPostpaid = (strtolower($product['type']) === 'postpaid' || strtolower($product['type']) === 'pascabayar');

        // Clean product name so masked name like (S*y*t*o) is not saved into product_name
        $cleanProductName = $data['product_name'] ?? $product['product_name'];
        $cleanProductName = trim(preg_replace('/\s*\([^)]*\*[^)]*\)/', '', $cleanProductName));

        // Insert pending transaction to DB
        $dbData = [
            'ref_id' => $refId,
            'buyer_sku_code' => $sku,
            'customer_no' => $customerNo,
            'customer_name' => $data['customer_name'] ?? null,
            'product_name' => $cleanProductName ?: $product['product_name'],
            'category' => $product['category'],
            'brand' => $product['brand'],
            'type' => $isPostpaid ? 'postpaid' : 'prepaid',
            'sell_price' => $data['sell_price'] ?? $product['sell_price'],
            'modal_price' => $product['seller_price'],
            'status' => 'pending',
            'user_id' => $currentUser['id'] ?? 1
        ];

        $this->digiModel->createTransaction($dbData);

        // Send request to Digiflazz
        if ($isPostpaid) {
            $res = $this->digiService->payPostpaid($sku, $customerNo, $refId, $amount);

            // Immediately log the postpaid response to webhook.log
            try {
                $logPayload = isset($res['data']) ? ['data' => $res['data']] : $res;
                $logPostpaid = date('Y-m-d H:i:s') . " - [PASCABAYAR DIRECT RESPONSE / CALLBACK]\n";
                $logPostpaid .= "Headers: {\"Content-Type\":\"application/json\",\"Event\":\"pay-pasca\",\"Source\":\"Digiflazz Postpaid Synchronous API\"}\n";
                $logPostpaid .= "Payload: " . json_encode($logPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
                $postpaidStatus = $res['data']['status'] ?? ($res['success'] ? 'sukses' : 'gagal');
                $logPostpaid .= "SUCCESS: Processed Postpaid $refId to " . strtolower($postpaidStatus) . "\n\n";
                @file_put_contents(STORAGE_PATH . '/logs/webhook.log', $logPostpaid, FILE_APPEND);
            } catch (\Throwable $e) {}
        } else {
            $res = $this->digiService->createTransaction($sku, $customerNo, $refId);
        }

        // Update DB with initial response
        if ($res['success'] && isset($res['data'])) {
            $status = strtolower($res['data']['status'] ?? 'pending');
            $message = $res['data']['message'] ?? '';
            $sn = $res['data']['sn'] ?? '';
            $trxId = $res['data']['trx_id'] ?? '';
            if (empty($trxId) && !empty($sn) && $sn !== '-') {
                $trxId = $sn;
            }
            
            // Map digiflazz status to our enum
            if ($status === 'gagal') $status = 'failed';
            else if ($status === 'sukses') $status = 'success';
            
            $this->digiModel->updateTransactionStatus($refId, $status, $message, $sn, $trxId, $res['data']);

            // Update customer_name in DB if Digiflazz returned full unmasked name
            $resCustName = $res['data']['customer_name'] ?? '';
            if (!empty($resCustName) && strpos($resCustName, '*') === false) {
                $dbData['customer_name'] = $resCustName;
                try {
                    $this->digiModel->db->prepare("UPDATE digi_transactions SET customer_name = ? WHERE ref_id = ?")->execute([$resCustName, $refId]);
                } catch (\Throwable $e) {}
            }
            
            // Modify response to include transaction details for frontend receipt & digital invoice
            $res['data']['sell_price'] = $dbData['sell_price'];
            $res['data']['customer_name'] = $dbData['customer_name'];
            $res['data']['product_name'] = $dbData['product_name'];
            $res['data']['customer_no'] = $dbData['customer_no'];
            $res['data']['ref_id'] = $dbData['ref_id'];
            $res['data']['trx_id'] = $trxId;
            $res['data']['digiflazz_trx_id'] = $trxId;
            $res['data']['created_at'] = date('d/m/Y H:i');
        } else {
            $this->digiModel->updateTransactionStatus($refId, 'failed', $res['message']);
        }

        header('Content-Type: application/json');
        echo json_encode($res);
        exit;
    }

    public function apiSyncPrices() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);
        
        // Prevent timeout during massive digiflazz catalog sync
        set_time_limit(0);
        ignore_user_abort(false);
        ini_set('memory_limit', '512M');
        
        // Ensure clean JSON output — flush any prior output buffer
        while (ob_get_level()) ob_end_clean();
        
        $body = json_decode(file_get_contents('php://input'), true);
        $requestedType = strtolower(trim($body['type'] ?? 'all'));

        $successPrepaid  = false;
        $successPostpaid = false;
        $errMsg = '';

        // Sync prepaid if requested
        if ($requestedType === 'all' || $requestedType === 'prepaid') {
            $resPrepaid = $this->digiService->getPriceList('prepaid');
            if ($resPrepaid['success'] && isset($resPrepaid['data']) && is_array($resPrepaid['data']) && !isset($resPrepaid['data']['rc'])) {
                $successPrepaid = $this->digiModel->syncPriceList($resPrepaid['data'], 'prepaid');
            } else {
                $errMsg = $resPrepaid['data']['message'] ?? $resPrepaid['message'] ?? 'Gagal sync prabayar';
            }
        }

        // Sync postpaid if requested
        if ($requestedType === 'all' || $requestedType === 'postpaid') {
            $resPostpaid = $this->digiService->getPriceList('pasca');
            if ($resPostpaid['success'] && isset($resPostpaid['data']) && is_array($resPostpaid['data']) && !isset($resPostpaid['data']['rc'])) {
                $successPostpaid = $this->digiModel->syncPriceList($resPostpaid['data'], 'postpaid');
            } else {
                $errMsg = $resPostpaid['data']['message'] ?? $resPostpaid['message'] ?? 'Gagal sync pascabayar';
            }
        }

        header('Content-Type: application/json');
        header('Cache-Control: no-cache');
        if ($successPrepaid || $successPostpaid) {
            echo json_encode(['success' => true, 'message' => 'Sinkronisasi berhasil']);
        } else {
            http_response_code(200); // always 200 so JS doesn't throw network error
            echo json_encode(['success' => false, 'message' => 'Gagal sinkronisasi: ' . ($errMsg ?: 'Rate limit / Unknown Error')]);
        }
        exit;
    }


    /**
     * Check current status of a pending transaction by calling Digiflazz API
     * (used when webhook hasn't fired yet)
     */
    public function apiCheckTransaction() {
        AuthController::requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        $refId = trim($data['ref_id'] ?? '');

        if (empty($refId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ref_id diperlukan']);
            exit;
        }

        // Lookup local transaction first
        $trx = $this->digiModel->getTransactionByRefId($refId);
        if (!$trx) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan']);
            exit;
        }

        // If already in final state, just return it
        if (in_array($trx['status'], ['success', 'failed', 'refunded'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $trx]);
            exit;
        }

        // Poll Digiflazz for current status (re-send transaction with same ref_id)
        // Digiflazz returns the current status when ref_id already exists
        $res = $this->digiService->checkTransaction($trx['buyer_sku_code'], $trx['customer_no'], $refId);

        if ($res['success'] && isset($res['data']['status'])) {
            $rawStatus = strtolower(trim($res['data']['status']));
            switch ($rawStatus) {
                case 'sukses': case 'success': $status = 'success'; break;
                case 'gagal': case 'failed': case 'fail': $status = 'failed'; break;
                default: $status = 'pending';
            }
            $sn  = $res['data']['sn'] ?? null;
            $msg = $res['data']['message'] ?? '';
            $trxId = $res['data']['trx_id'] ?? null;
            $this->digiModel->updateTransactionStatus($refId, $status, $msg, $sn, $trxId, $res['data']);
            $trx = $this->digiModel->getTransactionByRefId($refId);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $trx]);
        exit;
    }

    /**
     * Update selling price and profit for a specific transaction
     */
    public function apiUpdateTransactionPrice() {
        AuthController::requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        $refId = trim($data['ref_id'] ?? '');
        $sellPrice = floatval($data['sell_price'] ?? 0);
        $sku = trim($data['sku'] ?? '');

        if (empty($refId) && empty($sku)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ref_id atau sku diperlukan']);
            exit;
        }

        if (!empty($refId)) {
            $this->digiModel->updateTransactionSellPrice($refId, $sellPrice);
        }

        if (!empty($sku) && $sellPrice > 0) {
            $this->digiModel->updateCustomPrice($sku, $sellPrice);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Harga jual transaksi berhasil diperbarui',
            'sell_price' => $sellPrice
        ]);
        exit;
    }

    public function apiSaveSettings() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);

        $data = json_decode(file_get_contents('php://input'), true);
        $settingModel = new SettingModel();
        
        $newMode = trim($data['mode'] ?? '');
        
        if (isset($data['username'])) $settingModel->set('digiflazz_username', trim($data['username']));
        if (isset($data['api_key_dev'])) $settingModel->set('digiflazz_api_key_dev', trim($data['api_key_dev']));
        if (isset($data['api_key_prod'])) $settingModel->set('digiflazz_api_key_prod', trim($data['api_key_prod']));
        if (isset($data['webhook_secret'])) $settingModel->set('digiflazz_webhook_secret', trim($data['webhook_secret']));
        if ($newMode) $settingModel->set('digiflazz_mode', $newMode);
        if (isset($data['pin'])) $settingModel->set('digiflazz_pin', trim($data['pin']));

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Pengaturan berhasil disimpan']);
        exit;
    }

    public function apiCreateDeposit() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);

        $data = json_decode(file_get_contents('php://input'), true);
        $amount = floatval($data['amount'] ?? 0);
        $bank = trim($data['bank'] ?? '');
        $ownerName = trim($data['owner_name'] ?? '');

        if ($amount < 50000) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Minimal deposit adalah Rp 50.000']);
            exit;
        }
        if (empty($bank) || empty($ownerName)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Pilih bank dan isi nama pengirim']);
            exit;
        }

        $res = $this->digiService->createDeposit($amount, $bank, $ownerName);
        
        // Digiflazz returns rc="00" for success, and rc inside 'data'
        $rc = $res['data']['rc'] ?? '';
        if ($res['success'] && $rc === '00') {
            // Fetch current balance right before/after ticket creation to store as start_balance
            $balRes = $this->digiService->getBalance();
            if ($balRes['success'] && isset($balRes['data']['deposit'])) {
                $res['data']['start_balance'] = (float)$balRes['data']['deposit'];
            }

            $uniqueAmount = (float)($res['data']['amount'] ?? $amount);

            // Log to database
            $this->digiModel->createDepositLog([
                'amount' => $uniqueAmount,
                'bank' => $bank,
                'owner_name' => $ownerName,
                'status' => 'pending',
                'notes' => $res['data']['notes'] ?? '',
                'raw' => $res['data']
            ]);
        } else {
            // Override success flag to false so frontend knows it failed
            $res['success'] = false;
            $res['message'] = $res['data']['message'] ?? 'Gagal melakukan request tiket deposit ke server.';
        }

        header('Content-Type: application/json');
        echo json_encode($res);
        exit;
    }

    public function apiGetDepositHistory() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);
        
        $this->syncPendingDeposits();

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $deposits = $this->digiModel->getDeposits($limit);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $deposits]);
        exit;
    }

    /**
     * Auto sync pending deposit status using balance increase and API checks
     */
    private function syncPendingDeposits(?float $currentBalance = null) {
        try {
            $pendingDeposits = $this->digiModel->getPendingDeposits();
            if (empty($pendingDeposits)) {
                return;
            }

            if ($currentBalance === null) {
                $res = $this->digiService->getBalance();
                if ($res['success'] && isset($res['data']['deposit'])) {
                    $currentBalance = (float)$res['data']['deposit'];
                }
            }

            $settingModel = new SettingModel();
            $lastBalanceStr = $settingModel->get('digiflazz_last_known_balance', null);
            $lastBalance = $lastBalanceStr !== null ? (float)$lastBalanceStr : null;

            $now = time();

            foreach ($pendingDeposits as $dep) {
                $depId = $dep['id'];
                $createdAt = strtotime($dep['created_at']);
                $ageSeconds = $now - $createdAt;

                $uniqueAmount = (float)$dep['amount'];
                $startBalance = null;
                $raw = [];

                if (!empty($dep['raw_response'])) {
                    $raw = json_decode($dep['raw_response'], true) ?: [];
                    if (isset($raw['amount'])) {
                        $uniqueAmount = (float)$raw['amount'];
                    } elseif (isset($raw['deposit']['amount'])) {
                        $uniqueAmount = (float)$raw['deposit']['amount'];
                    }
                    if (isset($raw['start_balance'])) {
                        $startBalance = (float)$raw['start_balance'];
                    }
                }

                if (!empty($dep['notes'])) {
                    if (preg_match('/Rp\s*([0-9.,]+)/i', $dep['notes'], $m)) {
                        $parsed = (float)str_replace(['.', ','], '', $m[1]);
                        if ($parsed > 0) {
                            $uniqueAmount = $parsed;
                        }
                    }
                }

                $isSuccess = false;

                if ($currentBalance !== null) {
                    // 1. Check against start_balance saved in raw response
                    if ($startBalance !== null && $currentBalance >= ($startBalance + $uniqueAmount - 100)) {
                        $isSuccess = true;
                    }
                    // 2. Check against last_known_balance setting
                    elseif ($lastBalance !== null && $currentBalance > $lastBalance && ($currentBalance - $lastBalance) >= ($uniqueAmount - 100)) {
                        $isSuccess = true;
                    }
                    // 3. Check if current balance increased significantly relative to start balance
                    elseif ($startBalance !== null && $currentBalance > ($startBalance + 1000)) {
                        $isSuccess = true;
                    }
                }

                if ($isSuccess) {
                    $statusNote = $dep['notes'];
                    if (strpos($statusNote, 'Saldo terdeteksi') === false) {
                        $statusNote .= ' (Saldo terdeteksi masuk)';
                    }
                    $this->digiModel->updateDepositStatusById($depId, 'success', $statusNote, $raw);
                    continue;
                }

                // If ticket is older than 24 hours, mark as failed / expired
                if ($ageSeconds > 86400) {
                    $statusNote = $dep['notes'];
                    if (strpos($statusNote, 'kadaluarsa') === false) {
                        $statusNote .= ' (Tiket deposit kadaluarsa)';
                    }
                    $this->digiModel->updateDepositStatusById($depId, 'failed', $statusNote, $raw);
                }
            }

            if ($currentBalance !== null) {
                $settingModel->set('digiflazz_last_known_balance', (string)$currentBalance);
            }
        } catch (\Throwable $e) {
            error_log("[DigiflazzController] syncPendingDeposits error: " . $e->getMessage());
        }
    }

    public function apiGetTransaction(string $refId) {
        AuthController::requireAuth();
        $trx = $this->digiModel->getTransactionByRefId($refId);
        if (!$trx) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan']);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $trx]);
        exit;
    }

    public function apiSaveMarkupRules() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);

        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['rules']) && is_array($data['rules'])) {
            foreach ($data['rules'] as $rule) {
                $this->digiModel->saveMarkupRule(
                    $rule['category'] ?? '', 
                    $rule['markup_type'] ?? 'fixed', 
                    floatval($rule['markup_value'] ?? 0)
                );
            }
            $this->digiModel->applyAllMarkups();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Markup rules saved and applied']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }
    public function apiSaveCustomPricesBulk() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);

        $data = json_decode(file_get_contents('php://input'), true);
        $prices = $data['prices'] ?? [];
        
        $count = 0;
        foreach ($prices as $sku => $sellPrice) {
            if ($sku && floatval($sellPrice) >= 0) {
                $this->digiModel->updateCustomPrice($sku, floatval($sellPrice));
                $count++;
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => "Berhasil sinkronisasi $count harga kustom"]);
        exit;
    }

    public function apiSaveCustomPrice() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);

        $data = json_decode(file_get_contents('php://input'), true);
        $sku = trim($data['sku'] ?? '');
        $sellPrice = floatval($data['sell_price'] ?? 0);

        if ($sku && $sellPrice >= 0) {
            $this->digiModel->updateCustomPrice($sku, $sellPrice);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Custom price saved']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }

    public function apiResetCustomPrice() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);

        $data = json_decode(file_get_contents('php://input'), true);
        $sku = trim($data['sku'] ?? '');

        if ($sku) {
            $this->digiModel->resetCustomPrice($sku);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Custom price reset to auto markup']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }

    public function apiGetTransactions() {
        AuthController::requireAuth();
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $status_filter = $_GET['status'] ?? 'all';
        
        $transactions = $this->digiModel->getTransactions($limit, $offset);

        // Calculate and attach balance_before, balance_after, and clean digiflazz_trx_id
        foreach ($transactions as &$t) {
            $balAfter = null;
            $balBefore = null;

            // Clean product name if it has masked name appended e.g. "OTO Kredit Mobil/Motor (S*y*t*o)"
            if (!empty($t['product_name'])) {
                $t['product_name'] = trim(preg_replace('/\s*\([^)]*\*[^)]*\)/', '', $t['product_name']));
            }

            if (!empty($t['raw_response'])) {
                $raw = is_string($t['raw_response']) ? json_decode($t['raw_response'], true) : $t['raw_response'];
                if (is_array($raw)) {
                    if (isset($raw['buyer_last_saldo'])) {
                        $balAfter = (float)$raw['buyer_last_saldo'];
                    } else if (isset($raw['balance'])) {
                        $balAfter = (float)$raw['balance'];
                    }

                    // If DB customer_name is masked with asterisk, take unmasked name from raw response
                    if (!empty($raw['customer_name']) && (empty($t['customer_name']) || strpos($t['customer_name'], '*') !== false)) {
                        $t['customer_name'] = $raw['customer_name'];
                    }

                    if (empty($t['digiflazz_trx_id']) || $t['digiflazz_trx_id'] === $t['ref_id']) {
                        if (!empty($raw['tr_id'])) {
                            $t['digiflazz_trx_id'] = (string)$raw['tr_id'];
                        } else if (!empty($raw['trx_id']) && $raw['trx_id'] !== $t['ref_id']) {
                            $t['digiflazz_trx_id'] = (string)$raw['trx_id'];
                        } else if (!empty($t['sn']) && $t['sn'] !== '-') {
                            $t['digiflazz_trx_id'] = (string)$t['sn'];
                        } else {
                            $t['digiflazz_trx_id'] = null;
                        }
                    }
                }
            }

            if (!empty($t['digiflazz_trx_id']) && $t['digiflazz_trx_id'] === $t['ref_id']) {
                $t['digiflazz_trx_id'] = (!empty($t['sn']) && $t['sn'] !== '-') ? (string)$t['sn'] : null;
            }

            if ($balAfter !== null) {
                $modal = (float)($t['modal_price'] ?? 0);
                if (in_array(strtolower($t['status']), ['success', 'sukses'])) {
                    $balBefore = $balAfter + $modal;
                } else {
                    $balBefore = $balAfter;
                }
            }

            $t['balance_after'] = $balAfter;
            $t['balance_before'] = $balBefore;

            // Calculate transaction duration (speed) for completed transactions (success & failed)
            $durationSeconds = null;
            if (in_array(strtolower($t['status']), ['success', 'sukses', 'failed', 'gagal']) && !empty($t['created_at']) && !empty($t['updated_at'])) {
                $created = strtotime($t['created_at']);
                $updated = strtotime($t['updated_at']);
                $diff = $updated - $created;
                if ($diff >= 0) {
                    $durationSeconds = $diff;
                }
            }
            $t['duration_seconds'] = $durationSeconds;
        }
        
        if ($status_filter !== 'all') {
            $transactions = array_filter($transactions, function($t) use ($status_filter) {
                if ($status_filter === 'pending') {
                    return in_array($t['status'], ['pending', 'processing']);
                }
                return $t['status'] === $status_filter;
            });
            $transactions = array_values($transactions);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $transactions]);
        exit;
    }

    public function apiGetSummary() {
        AuthController::requireAuth();
        $range = $_GET['range'] ?? 'this_month';
        $category = $_GET['category'] ?? 'all';

        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');

        if ($range === 'today') {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        } else if ($range === 'yesterday') {
            $startDate = date('Y-m-d', strtotime('-1 day'));
            $endDate = date('Y-m-d', strtotime('-1 day'));
        } else if ($range === '7days') {
            $startDate = date('Y-m-d', strtotime('-7 days'));
            $endDate = date('Y-m-d');
        } else if ($range === 'this_month') {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        } else if ($range === 'all') {
            $startDate = '2000-01-01';
            $endDate = date('Y-m-d');
        }

        $transactions = $this->digiModel->getAnalyticsData($startDate, $endDate);

        // Process analytics data in PHP
        $categories = [];
        $sellers = [];
        $dailyTrend = [];
        $totalTrx = 0;
        $totalSuccess = 0;
        $totalFailed = 0;
        $totalPending = 0;
        $totalRevenue = 0;
        $totalProfit = 0;
        $totalSpeedSum = 0;
        $totalSpeedCount = 0;

        foreach ($transactions as $trx) {
            if ($category !== 'all' && strtolower($trx['category']) !== strtolower($category)) {
                continue;
            }

            $totalTrx++;
            $trxDate = date('Y-m-d', strtotime($trx['created_at']));
            if (!isset($dailyTrend[$trxDate])) {
                $dailyTrend[$trxDate] = [
                    'date' => $trxDate,
                    'label' => date('d M', strtotime($trxDate)),
                    'total' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'revenue' => 0,
                    'profit' => 0
                ];
            }
            $dailyTrend[$trxDate]['total']++;

            // Category count
            $catName = !empty($trx['category']) ? ucfirst($trx['category']) : 'Lainnya';
            if (!isset($categories[$catName])) {
                $categories[$catName] = 0;
            }
            $categories[$catName]++;

            // Status check
            $isSuccess = ($trx['status'] === 'success');
            $isFailed = ($trx['status'] === 'failed');
            $isPending = ($trx['status'] === 'pending');

            if ($isSuccess) {
                $totalSuccess++;
                $dailyTrend[$trxDate]['success']++;
                $sellPrice = (float)$trx['sell_price'];
                $modalPrice = (float)$trx['modal_price'];
                $profit = $sellPrice > 0 ? ($sellPrice - $modalPrice) : 0;
                $totalProfit += $profit;
                $totalRevenue += $sellPrice;
                $dailyTrend[$trxDate]['revenue'] += $sellPrice;
                $dailyTrend[$trxDate]['profit'] += $profit;
            } else if ($isFailed) {
                $totalFailed++;
                $dailyTrend[$trxDate]['failed']++;
            } else {
                $totalPending++;
            }

            // Processing Speed in Seconds (For both success and failed transactions)
            $processTime = null;
            if ($isSuccess || $isFailed) {
                $created = strtotime($trx['created_at']);
                $updated = strtotime($trx['updated_at']);
                $diff = $updated - $created;
                if ($diff >= 0) {
                    $processTime = $diff;
                    if ($diff <= 900) {
                        $totalSpeedSum += $diff;
                        $totalSpeedCount++;
                    }
                }
            }

            // Extract Real Seller Name & Handle
            $handle = '';
            if (!empty($trx['raw_response'])) {
                $raw = json_decode($trx['raw_response'], true);
                if (!empty($raw['tele'])) {
                    $handle = (strpos($raw['tele'], '@') === 0 ? '' : '@') . $raw['tele'];
                } else if (!empty($raw['wa'])) {
                    $handle = $raw['wa'];
                }
            }

            $realSeller = trim($trx['trx_seller_name'] ?: ($trx['prod_seller_name'] ?: ''));
            if (empty($realSeller)) {
                if (!empty($handle)) {
                    $realSeller = $handle;
                } else {
                    $realSeller = 'Digiflazz Supplier';
                }
            }

            if (!isset($sellers[$realSeller])) {
                $sellers[$realSeller] = [
                    'name' => $realSeller,
                    'handle' => $handle,
                    'total' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'pending' => 0,
                    'revenue' => 0,
                    'profit' => 0,
                    'modal_sum' => 0,
                    'process_time_sum' => 0,
                    'process_time_count' => 0,
                    'transactions' => []
                ];
            }

            $sellers[$realSeller]['total']++;
            if (!empty($handle) && empty($sellers[$realSeller]['handle'])) {
                $sellers[$realSeller]['handle'] = $handle;
            }

            $tSell = (float)($trx['sell_price'] ?? 0);
            $tModal = (float)($trx['modal_price'] ?? 0);
            $tProfit = $tSell > 0 ? ($tSell - $tModal) : 0;

            // Save transaction record to seller's transaction history
            $sellers[$realSeller]['transactions'][] = [
                'ref_id' => $trx['ref_id'],
                'digiflazz_trx_id' => !empty($trx['digiflazz_trx_id']) && $trx['digiflazz_trx_id'] !== $trx['ref_id'] ? $trx['digiflazz_trx_id'] : null,
                'product_name' => $trx['product_name'] ?? '',
                'customer_no' => $trx['customer_no'] ?? '',
                'customer_name' => $trx['customer_name'] ?? '',
                'category' => $trx['category'] ?? '',
                'modal_price' => $tModal,
                'sell_price' => $tSell,
                'profit' => $tProfit,
                'status' => strtolower($trx['status']),
                'created_at' => $trx['created_at'],
                'updated_at' => $trx['updated_at'],
                'duration_seconds' => $processTime
            ];

            if ($isSuccess) {
                $sellers[$realSeller]['success']++;
                $sellers[$realSeller]['revenue'] += $tSell;
                $sellers[$realSeller]['profit'] += $tProfit;
                $sellers[$realSeller]['modal_sum'] += $tModal;
            } else if ($isFailed) {
                $sellers[$realSeller]['failed']++;
            } else {
                $sellers[$realSeller]['pending']++;
            }

            if ($processTime !== null && $processTime <= 900) {
                $sellers[$realSeller]['process_time_sum'] += $processTime;
                $sellers[$realSeller]['process_time_count']++;
            }
        }

        // Finalize Sellers Data
        $sellerResults = [];
        foreach ($sellers as $s) {
            $s['success_rate'] = $s['total'] > 0 ? round(($s['success'] / $s['total']) * 100, 1) : 0;
            $s['failed_rate'] = $s['total'] > 0 ? round(($s['failed'] / $s['total']) * 100, 1) : 0;
            $s['avg_process_time'] = $s['process_time_count'] > 0 ? round($s['process_time_sum'] / $s['process_time_count'], 1) : 0;
            $s['avg_profit_per_trx'] = $s['success'] > 0 ? round($s['profit'] / $s['success'], 0) : 0;

            // Calculate Performance Score (0 - 100 pts) for Leaderboard
            // SR Weight: 50%, Speed Weight: 35%, Price/Margin Weight: 15%
            $srScore = $s['success_rate'];
            $speedScore = max(0, 100 - ($s['avg_process_time'] / 2));
            $marginPct = ($s['modal_sum'] > 0) ? ($s['profit'] / $s['modal_sum']) * 100 : 0;
            $marginScore = min(100, max(0, $marginPct * 10));

            $s['score'] = round(($srScore * 0.50) + ($speedScore * 0.35) + ($marginScore * 0.15), 1);
            $sellerResults[] = $s;
        }

        // Filter out sellers with 0 success rate, 0 successful transactions, or <= 0 profit for Top Seller Leaderboard
        $eligibleTopSellers = array_filter($sellerResults, function($s) {
            return $s['success'] > 0 && $s['success_rate'] > 0 && $s['profit'] > 0;
        });

        // Sort Top Sellers Leaderboard (by Profit DESC, then Success Rate DESC, then Score DESC, then Avg Speed ASC)
        usort($eligibleTopSellers, function($a, $b) {
            if ($b['profit'] != $a['profit']) return $b['profit'] <=> $a['profit'];
            if ($b['success_rate'] != $a['success_rate']) return $b['success_rate'] <=> $a['success_rate'];
            if ($b['score'] != $a['score']) return $b['score'] <=> $a['score'];
            return $a['avg_process_time'] <=> $b['avg_process_time'];
        });
        $top5Sellers = array_values($eligibleTopSellers);
        $top5Sellers = array_slice($top5Sellers, 0, 5);

        // Sort default seller table by Total Trx DESC
        usort($sellerResults, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // Format Daily Trend sorted by date ASC
        ksort($dailyTrend);
        $dailyTrendArray = array_values($dailyTrend);

        $globalSuccessRate = $totalTrx > 0 ? round(($totalSuccess / $totalTrx) * 100, 1) : 0;
        $globalAvgSpeed = $totalSpeedCount > 0 ? round($totalSpeedSum / $totalSpeedCount, 1) : 0;

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => [
                'metrics' => [
                    'total' => $totalTrx,
                    'success_count' => $totalSuccess,
                    'failed_count' => $totalFailed,
                    'pending_count' => $totalPending,
                    'success_rate' => $globalSuccessRate,
                    'revenue' => $totalRevenue,
                    'profit' => $totalProfit,
                    'avg_speed' => $globalAvgSpeed
                ],
                'categories' => $categories,
                'sellers' => $sellerResults,
                'top_sellers' => $top5Sellers,
                'daily_trend' => $dailyTrendArray
            ]
        ]);
        exit;
    }

    /**
     * ==========================================
     * WEBHOOK ENDPOINT
     * ==========================================
     */
    public function webhookTest() {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Endpoint Webhook Aktif. Silakan gunakan method POST untuk menerima callback dari Digiflazz.'
        ]);
        exit;
    }

    public function webhookLog() {
        AuthController::requireAuth(); // Ensure only logged-in users can view logs
        header('Content-Type: text/plain; charset=utf-8');

        $logDir = STORAGE_PATH . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/webhook.log';
        $logContent = file_exists($logFile) ? (file_get_contents($logFile) ?: '') : '';

        // Auto-sync postpaid responses from database into webhook.log if not already present
        try {
            $stmt = $this->digiModel->db->query("SELECT id, ref_id, buyer_sku_code, customer_no, customer_name, product_name, type, status, sn, digiflazz_trx_id, created_at, raw_response FROM digi_transactions WHERE type = 'postpaid' OR category = 'multifinance' ORDER BY id ASC");
            if ($stmt) {
                $postpaidTrxs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $newEntries = '';
                foreach ($postpaidTrxs as $pt) {
                    $ref = $pt['ref_id'];
                    if (!empty($ref) && strpos($logContent, $ref) === false) {
                        $raw = $pt['raw_response'];
                        $rawObj = null;
                        if (!empty($raw)) {
                            $rawObj = is_string($raw) ? json_decode($raw, true) : $raw;
                        }
                        if (!$rawObj) {
                            $rawObj = [
                                'data' => [
                                    'ref_id' => $pt['ref_id'],
                                    'customer_no' => $pt['customer_no'],
                                    'customer_name' => $pt['customer_name'],
                                    'buyer_sku_code' => $pt['buyer_sku_code'],
                                    'status' => $pt['status'],
                                    'sn' => $pt['sn'] ?: $pt['digiflazz_trx_id'],
                                    'trx_id' => $pt['digiflazz_trx_id']
                                ]
                            ];
                        } elseif (!isset($rawObj['data'])) {
                            $rawObj = ['data' => $rawObj];
                        }

                        $dateStr = $pt['created_at'] ?: date('Y-m-d H:i:s');
                        $newEntries .= "$dateStr - [PASCABAYAR DIRECT RESPONSE / CALLBACK]\n";
                        $newEntries .= "Headers: {\"Content-Type\":\"application/json\",\"Event\":\"pay-pasca\",\"Source\":\"Digiflazz Postpaid Synchronous API\"}\n";
                        $newEntries .= "Payload: " . json_encode($rawObj, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
                        $newEntries .= "SUCCESS: Processed Postpaid $ref to " . strtolower($pt['status']) . "\n\n";
                    }
                }

                if (!empty($newEntries)) {
                    @file_put_contents($logFile, $newEntries, FILE_APPEND);
                    $logContent .= $newEntries;
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore DB errors when reading log
        }

        if (!empty(trim($logContent))) {
            echo $logContent;
        } else {
            echo "Belum ada log webhook atau transaksi pascabayar yang terekam.\nLog file: " . $logFile;
        }
        exit;
    }

    public function requestLog() {
        AuthController::requireAuth(); // Ensure only logged-in users can view logs
        $logFile = STORAGE_PATH . '/logs/digiflazz_debug.log';
        if (file_exists($logFile)) {
            header('Content-Type: text/plain');
            echo file_get_contents($logFile);
        } else {
            echo "Belum ada log request Digiflazz yang terekam. File tidak ditemukan: " . $logFile;
        }
        exit;
    }

    public function webhook() {
        // --- IP WHITELIST CHECK REMOVED ---
        // Digiflazz uses multiple IPs now, and signature verification is sufficient.
        $clientIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        if (strpos($clientIp, ',') !== false) {
            $clientIps = explode(',', $clientIp);
            $clientIp = trim($clientIps[0]);
        }

        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);
        
        $headers = getallheaders();
        $logData = date('Y-m-d H:i:s') . " - IP: $clientIp\n";
        $logData .= "Headers: " . json_encode($headers) . "\n";
        $logData .= "Payload: " . $payload . "\n";
        
        // Log incoming webhook for debugging
        error_log("[Digiflazz Webhook] Received from IP {$clientIp}: " . $payload);
        @file_put_contents(STORAGE_PATH . '/logs/webhook.log', $logData, FILE_APPEND);

        // Handle Digiflazz Ping Event
        if (isset($data['hook_id']) && !isset($data['data'])) {
            error_log("[Digiflazz Webhook] Ping received for hook_id: " . $data['hook_id']);
            http_response_code(200);
            echo json_encode(['status' => 'ok', 'message' => 'Ping received']);
            exit;
        }

        if (!$data || !isset($data['data'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
            exit;
        }

        // Fetch Webhook Secret from DB
        $settingModel = new SettingModel();
        $secret = $settingModel->get('digiflazz_webhook_secret', '');

        // Verify Signature if Secret is configured
        if (!empty($secret)) {
            $headerSignature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
            if (!empty($headerSignature)) {
                $expectedSignature = 'sha1=' . hash_hmac('sha1', $payload, $secret);
                if (!hash_equals($expectedSignature, $headerSignature)) {
                    error_log("[Digiflazz Webhook] Signature mismatch. Expected: $expectedSignature, Got: $headerSignature");
                    @file_put_contents(STORAGE_PATH . '/logs/webhook.log', "ERROR: Signature mismatch. Expected: $expectedSignature, Got: $headerSignature\n\n", FILE_APPEND);
                    http_response_code(403);
                    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
                    exit;
                }
            }
        }

        $trx = $data['data'];
        
        // Handle Deposit Webhook
        if ((isset($data['action']) && $data['action'] === 'deposit') || isset($trx['amount'])) {
            $rawStatus = strtolower(trim($trx['status'] ?? ''));
            $status = 'pending';
            if ($rawStatus === 'sukses' || $rawStatus === 'success') $status = 'success';
            elseif ($rawStatus === 'gagal' || $rawStatus === 'failed') $status = 'failed';
            
            $amount = $trx['amount'] ?? 0;
            $notes = $trx['notes'] ?? '';
            
            error_log("[Digiflazz Webhook] Deposit update received: amount=$amount status=$status");
            
            if ($amount > 0) {
                $this->digiModel->updateDepositStatus($amount, $status, $notes, $trx);
                @file_put_contents(STORAGE_PATH . '/logs/webhook.log', "SUCCESS: Updated Deposit $amount to $status\n\n", FILE_APPEND);
            }
            
            http_response_code(200);
            echo json_encode(['status' => 'ok']);
            exit;
        }

        $refId    = $trx['ref_id']    ?? '';
        $trxId    = $trx['trx_id']    ?? null;
        $rawStatus = strtolower(trim($trx['status'] ?? ''));
        $message  = $trx['message']   ?? '';
        $sn       = $trx['sn']        ?? '';
        
        // Normalize Digiflazz status strings → our internal enum
        // Digiflazz may send: 'Sukses', 'sukses', 'Gagal', 'gagal', 'Pending', 'pending', 'processing'
        switch ($rawStatus) {
            case 'sukses':
            case 'success':
                $status = 'success';
                break;
            case 'gagal':
            case 'failed':
            case 'fail':
                $status = 'failed';
                break;
            case 'pending':
            case 'processing':
            default:
                $status = 'pending';
                break;
        }

        error_log("[Digiflazz Webhook] ref_id=$refId status=$status message=$message sn=$sn");

        if (empty($refId)) {
            error_log("[Digiflazz Webhook] Missing ref_id in payload");
            http_response_code(200);
            echo json_encode(['status' => 'ok', 'message' => 'No ref_id, skipped']);
            exit;
        }

        // Update transaction in DB
        $this->digiModel->updateTransactionStatus($refId, $status, $message, $sn, $trxId, $trx);

        @file_put_contents(STORAGE_PATH . '/logs/webhook.log', "SUCCESS: Updated $refId to $status\n\n", FILE_APPEND);

        http_response_code(200);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    // Proxy for E-Wallet Check
    public function apiCekEwallet() {
        header('Content-Type: application/json');
        AuthController::requireAuth();

        $data = json_decode(file_get_contents('php://input'), true);
        $bank = $data['account_bank'] ?? '';
        $number = $data['account_number'] ?? '';

        if (empty($bank) || empty($number)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
            return;
        }

        $url = 'https://netovas.com/api/cekrek/v1/account-inquiry';
        $postData = http_build_query([
            'account_bank' => $bank,
            'account_number' => $number
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (!$response) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'API Cek Nama sedang gangguan atau tidak dapat diakses']);
            return;
        }

        // Handle dead API that returns 404 or unexpected HTML/JSON without success field
        $decoded = json_decode($response, true);
        if ($httpCode >= 400 && (!is_array($decoded) || !isset($decoded['success']))) {
             http_response_code(200); // Return 200 so UI can parse message
             echo json_encode(['success' => false, 'message' => 'Layanan Cek Nama e-Wallet sedang offline dari provider pusat.']);
             return;
        }

        http_response_code($httpCode);
        echo $response;
    }
}
