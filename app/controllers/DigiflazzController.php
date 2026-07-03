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
        // Return view (lazy-loaded SPA style)
        $this->view('ppob/index', ['title' => 'Produk Digital']);
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

        $this->view('ppob/settings', ['title' => 'Pengaturan PPOB', 'settings' => $settings]);
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
        $products = $this->digiModel->getProducts($category, $brand);
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
        header('Content-Type: application/json');
        echo json_encode($res);
        exit;
    }

    public function apiCreateTransaction() {
        AuthController::requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sku = $data['sku'] ?? '';
        $customerNo = $data['customer_no'] ?? '';
        
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

        // Generate unique ref_id: DIGI-TIMESTAMP-RANDOM
        $refId = 'DIGI-' . date('YmdHis') . '-' . rand(1000, 9999);
        $currentUser = $_SESSION['user'] ?? ['id' => 1]; // Fallback ID 1

        // Insert pending transaction to DB
        $dbData = [
            'ref_id' => $refId,
            'buyer_sku_code' => $sku,
            'customer_no' => $customerNo,
            'customer_name' => $data['customer_name'] ?? null,
            'product_name' => $product['product_name'],
            'category' => $product['category'],
            'brand' => $product['brand'],
            'type' => $product['type'],
            'sell_price' => $product['sell_price'],
            'modal_price' => $product['seller_price'],
            'status' => 'pending',
            'user_id' => $currentUser['id'] ?? 1
        ];

        $this->digiModel->createTransaction($dbData);

        // Send request to Digiflazz
        $res = $this->digiService->createTransaction($sku, $customerNo, $refId);

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
            $res['data']['sell_price'] = $product['sell_price'];
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
        
        $res = $this->digiService->getPriceList('prepaid');
        
        if ($res['success'] && isset($res['data'])) {
            $success = $this->digiModel->syncPriceList($res['data'], 'prepaid');
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'message' => $success ? 'Sync success' : 'Sync failed']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to fetch from API: ' . ($res['message'] ?? 'Unknown Error')]);
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

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Pengaturan berhasil disimpan']);
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
    public function webhook() {
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);
        
        // Log incoming webhook for debugging
        error_log("Digiflazz Webhook Received: " . $payload);

        if (!$data || !isset($data['data'])) {
            http_response_code(400);
            echo "Invalid payload";
            exit;
        }

        // Fetch Webhook Secret from DB
        $settingModel = new SettingModel();
        $secret = $settingModel->get('digiflazz_webhook_secret', '');

        // Verify Signature if Secret is configured
        if (!empty($secret)) {
            $headerSignature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
            $expectedSignature = 'sha1=' . hash_hmac('sha1', $payload, $secret);
            
            if (!hash_equals($expectedSignature, $headerSignature)) {
                error_log("Webhook verification failed. Expected: $expectedSignature, Got: $headerSignature");
                http_response_code(403);
                echo "Invalid signature";
                exit;
            }
        }

        $trx = $data['data'];
        $refId = $trx['ref_id'] ?? '';
        $status = strtolower($trx['status'] ?? '');
        $message = $trx['message'] ?? '';
        $sn = $trx['sn'] ?? '';
        
        // Map status
        if ($status === 'gagal') $status = 'failed';
        else if ($status === 'sukses') $status = 'success';

        // Update transaction status
        $this->digiModel->updateTransactionStatus($refId, $status, $message, $sn, null, $trx);

        http_response_code(200);
        echo "OK";
        exit;
    }
}
