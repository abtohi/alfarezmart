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
        $this->view('ppob/index', ['title' => 'Produk Digital (PPOB)', 'mode' => $mode, 'requirePin' => $requirePin]);
    }

    public function settings() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);
        
        $settingModel = new SettingModel();
        $settings = [
            'username' => $settingModel->get('digiflazz_username', ''),
            'api_key' => $settingModel->get('digiflazz_api_key', ''),
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
        $this->view('ppob/documentation', ['title' => 'Dokumentasi PPOB']);
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
        $res = $this->digiService->getBalance();
        header('Content-Type: application/json');
        echo json_encode($res);
        exit;
    }

    public function apiGetProducts(string $category) {
        AuthController::requireAuth();
        $brand = $_GET['brand'] ?? null;
        $type = $_GET['type'] ?? 'prepaid';
        
        $products = $this->digiModel->getProducts($category, $brand, $type);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $products]);
        exit;
    }

    public function apiGetAllProducts() {
        AuthController::requireAuth();
        
        // Return all products for datatable
        $stmt = $this->digiModel->db->query("SELECT * FROM digi_products WHERE is_active = 1 AND buyer_product_status = 1 ORDER BY category ASC, brand ASC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $products]);
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
        
        $sku = $data['sku'] ?? '';
        $customerNo = $data['customer_no'] ?? '';
        
        if (empty($sku) || empty($customerNo)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'SKU and Customer No required']);
            exit;
        }

        $refId = 'INQ-' . date('YmdHis') . '-' . rand(1000, 9999);
        $res = $this->digiService->inquiryPostpaid($sku, $customerNo, $refId);

        $rc = $res['data']['rc'] ?? '';
        if ($res['success'] && $rc !== '00') {
            $res['success'] = false;
            $res['message'] = $res['data']['message'] ?? 'Tagihan tidak ditemukan atau gagal dicek.';
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
        
        $sku = $data['sku'] ?? '';
        $customerNo = $data['customer_no'] ?? '';
        $refIdPostpaid = $data['ref_id'] ?? null; // from inquiry
        
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

        // Insert pending transaction to DB
        $dbData = [
            'ref_id' => $refId,
            'buyer_sku_code' => $sku,
            'customer_no' => $customerNo,
            'customer_name' => $data['customer_name'] ?? null,
            'product_name' => $product['product_name'],
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
            $res = $this->digiService->payPostpaid($sku, $customerNo, $refId);
        } else {
            $res = $this->digiService->createTransaction($sku, $customerNo, $refId);
        }

        // Update DB with initial response
        if ($res['success'] && isset($res['data'])) {
            $status = strtolower($res['data']['status'] ?? 'pending');
            $message = $res['data']['message'] ?? '';
            $sn = $res['data']['sn'] ?? '';
            $trxId = $res['data']['trx_id'] ?? '';
            
            // Map digiflazz status to our enum
            if ($status === 'gagal') $status = 'failed';
            else if ($status === 'sukses') $status = 'success';
            
            $this->digiModel->updateTransactionStatus($refId, $status, $message, $sn, $trxId, $res['data']);
            
            // Modify response to include sell_price for frontend receipt
            $res['data']['sell_price'] = $dbData['sell_price'];
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
        ini_set('memory_limit', '512M');
        
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
        if ($successPrepaid || $successPostpaid) {
            echo json_encode(['success' => true, 'message' => 'Sinkronisasi berhasil']);
        } else {
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

    public function apiSaveSettings() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);

        $data = json_decode(file_get_contents('php://input'), true);
        $settingModel = new SettingModel();
        
        if (isset($data['username'])) $settingModel->set('digiflazz_username', trim($data['username']));
        if (isset($data['api_key'])) $settingModel->set('digiflazz_api_key', trim($data['api_key']));
        if (isset($data['webhook_secret'])) $settingModel->set('digiflazz_webhook_secret', trim($data['webhook_secret']));
        if (isset($data['mode'])) $settingModel->set('digiflazz_mode', trim($data['mode']));
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
            // Log to database
            $this->digiModel->createDepositLog([
                'amount' => $amount,
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
        
        // Log incoming webhook for debugging
        error_log("[Digiflazz Webhook] Received from IP {$clientIp}: " . $payload);

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
                    http_response_code(403);
                    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
                    exit;
                }
            }
        }

        $trx = $data['data'];
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

        http_response_code(200);
        echo json_encode(['status' => 'ok']);
        exit;
    }
}
