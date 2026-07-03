<?php
/**
 * Digiflazz Controller
 * Handles HTTP requests for Digiflazz PPOB features
 */
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../services/DigiflazzService.php';
require_once __DIR__ . '/../models/DigiflazzModel.php';
require_once __DIR__ . '/../models/UserModel.php';

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
        $this->view('ppob/settings', ['title' => 'Pengaturan PPOB']);
    }

    public function history() {
        AuthController::requireAuth();
        $this->view('ppob/history', ['title' => 'Riwayat PPOB']);
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
        echo json_encode(['success' => false, 'message' => 'Failed to fetch from API']);
        exit;
    }

    public function apiGetTransactions() {
        AuthController::requireAuth();
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        
        $transactions = $this->digiModel->getTransactions($limit, $offset);
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

        $trx = $data['data'];
        $refId = $trx['ref_id'] ?? '';
        $status = strtolower($trx['status'] ?? '');
        $message = $trx['message'] ?? '';
        $sn = $trx['sn'] ?? '';
        
        // Map status
        if ($status === 'gagal') $status = 'failed';
        else if ($status === 'sukses') $status = 'success';

        // Validasi webhook secret atau signature jika ada di header
        $expectedSecret = $_ENV['DIGIFLAZZ_WEBHOOK_SECRET'] ?? '';
        $headerSecret = $_SERVER['HTTP_X_DIGIFLAZZ_EVENT'] ?? $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? ''; // Example check, adjust based on actual Digiflazz docs if they send a specific header
        
        // Update transaction status
        $this->digiModel->updateTransactionStatus($refId, $status, $message, $sn, null, $trx);

        http_response_code(200);
        echo "OK";
        exit;
    }
}
