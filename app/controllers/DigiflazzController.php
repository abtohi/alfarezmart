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
        $res = $this->digiService->getBalance();
        if ($res['success'] && isset($res['data']['deposit'])) {
            $this->syncPendingDeposits((float)$res['data']['deposit']);
        }
        header('Content-Type: application/json');
        echo json_encode($res);
        exit;
    }

    public function apiGetProducts(string $category) {
        AuthController::requireAuth();
        $brand = $_GET['brand'] ?? null;
        $type = $_GET['type'] ?? 'prepaid';
        
        $products = $this->digiModel->getProducts($category, $brand, $type);
        $rates = $this->digiModel->getSellerSuccessRates();
        $prodRates = $this->digiModel->getProductSuccessRates();
        
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
            } else {
                $p['product_success_rate'] = null;
                $p['product_trx_count'] = 0;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $products]);
        exit;
    }

    public function apiGetAllProducts() {
        AuthController::requireAuth();
        
        // Return all products for datatable
        $stmt = $this->digiModel->db->query("SELECT * FROM digi_products WHERE is_active = 1 AND buyer_product_status = 1 ORDER BY category ASC, brand ASC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $rates = $this->digiModel->getSellerSuccessRates();
        $prodRates = $this->digiModel->getProductSuccessRates();
        
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
            } else {
                $p['product_success_rate'] = null;
                $p['product_trx_count'] = 0;
            }
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

    public function apiSaveSettings() {
        AuthController::requireAuth();
        AuthController::requireLevel(['superadmin', 'admin']);

        $data = json_decode(file_get_contents('php://input'), true);
        $settingModel = new SettingModel();
        
        $currentMode = $settingModel->get('digiflazz_mode', 'development');
        $newMode = trim($data['mode'] ?? '');
        
        if ($newMode === 'production' && $currentMode !== 'production') {
            // Require password verification
            $password = $data['password'] ?? '';
            if (empty($password)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Password wajib diisi untuk beralih ke mode Production.']);
                exit;
            }
            
            $userModel = new UserModel();
            $user = $userModel->find($_SESSION['user_id']);
            if (!$userModel->verifyPassword($password, $user['password_hash'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Password tidak valid. Gagal beralih ke mode Production.']);
                exit;
            }
        }
        
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

            if (!empty($t['raw_response'])) {
                $raw = is_string($t['raw_response']) ? json_decode($t['raw_response'], true) : $t['raw_response'];
                if (is_array($raw)) {
                    if (isset($raw['buyer_last_saldo'])) {
                        $balAfter = (float)$raw['buyer_last_saldo'];
                    } else if (isset($raw['balance'])) {
                        $balAfter = (float)$raw['balance'];
                    }

                    if (empty($t['digiflazz_trx_id']) || $t['digiflazz_trx_id'] === $t['ref_id']) {
                        if (!empty($raw['tr_id'])) {
                            $t['digiflazz_trx_id'] = (string)$raw['tr_id'];
                        } else if (!empty($raw['trx_id']) && $raw['trx_id'] !== $t['ref_id']) {
                            $t['digiflazz_trx_id'] = (string)$raw['trx_id'];
                        } else {
                            $t['digiflazz_trx_id'] = null;
                        }
                    }
                }
            }

            if (!empty($t['digiflazz_trx_id']) && $t['digiflazz_trx_id'] === $t['ref_id']) {
                $t['digiflazz_trx_id'] = null;
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

            // Calculate transaction duration (speed) for successful transactions
            $durationSeconds = null;
            if (in_array(strtolower($t['status']), ['success', 'sukses']) && !empty($t['created_at']) && !empty($t['updated_at'])) {
                $created = strtotime($t['created_at']);
                $updated = strtotime($t['updated_at']);
                $diff = $updated - $created;
                if ($diff >= 0 && $diff <= 600) {
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
                $profit = (float)$trx['sell_price'] - (float)$trx['modal_price'];
                $totalProfit += $profit;
                $totalRevenue += (float)$trx['sell_price'];
                $dailyTrend[$trxDate]['revenue'] += (float)$trx['sell_price'];
                $dailyTrend[$trxDate]['profit'] += $profit;
            } else if ($isFailed) {
                $totalFailed++;
                $dailyTrend[$trxDate]['failed']++;
            } else {
                $totalPending++;
            }

            // Processing Speed in Seconds (Valid API callback window: 0 to 300 seconds / 5 mins max)
            // Diffs > 300s indicate batch migration updates or late manual status checks, not real-time API speed
            $processTime = null;
            if ($isSuccess) {
                $created = strtotime($trx['created_at']);
                $updated = strtotime($trx['updated_at']);
                $diff = $updated - $created;
                if ($diff >= 0 && $diff <= 300) {
                    $processTime = $diff;
                    $totalSpeedSum += $diff;
                    $totalSpeedCount++;
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
                    'process_time_count' => 0
                ];
            }

            $sellers[$realSeller]['total']++;
            if (!empty($handle) && empty($sellers[$realSeller]['handle'])) {
                $sellers[$realSeller]['handle'] = $handle;
            }

            if ($isSuccess) {
                $sellers[$realSeller]['success']++;
                $sellers[$realSeller]['revenue'] += (float)$trx['sell_price'];
                $sellers[$realSeller]['profit'] += ((float)$trx['sell_price'] - (float)$trx['modal_price']);
                $sellers[$realSeller]['modal_sum'] += (float)$trx['modal_price'];
            } else if ($isFailed) {
                $sellers[$realSeller]['failed']++;
            } else {
                $sellers[$realSeller]['pending']++;
            }

            if ($processTime !== null) {
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

        // Sort Top Sellers Leaderboard (by Score DESC, then Success Rate DESC, then Avg Speed ASC)
        $topSellers = $sellerResults;
        usort($topSellers, function($a, $b) {
            if ($b['score'] != $a['score']) return $b['score'] <=> $a['score'];
            if ($b['success_rate'] != $a['success_rate']) return $b['success_rate'] <=> $a['success_rate'];
            return $a['avg_process_time'] <=> $b['avg_process_time'];
        });
        $top5Sellers = array_slice($topSellers, 0, 5);

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
        $logFile = STORAGE_PATH . '/logs/webhook.log';
        if (file_exists($logFile)) {
            header('Content-Type: text/plain');
            echo file_get_contents($logFile);
        } else {
            echo "Belum ada log webhook yang diterima. File tidak ditemukan: " . $logFile;
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
