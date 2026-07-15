<?php
/**
 * PpobCustomerController
 * Handles PPOB Customers management
 */
class PpobCustomerController extends Controller {
    private PpobCustomerModel $model;

    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../models/PpobCustomerModel.php';
        $this->model = new PpobCustomerModel();
    }

    public function index() {
        $customers = $this->model->getAll();
        
        $this->view('ppob_customers.index', [
            'title' => 'Pelanggan PPOB',
            'activeNav' => 'ppob_customers',
            'customers' => $customers,
            'csrfToken' => (new Security())->getCSRFToken()
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $security = new Security();
            if (!$security->validateCSRFToken($_POST['csrf_token'] ?? '')) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF Token']);
                return;
            }

            $data = [
                'type' => $_POST['type'] ?? '',
                'customer_name' => $_POST['customer_name'] ?? null,
                'customer_no' => $_POST['customer_no'] ?? '',
                'pln_name' => $_POST['pln_name'] ?? null,
                'pln_power' => $_POST['pln_power'] ?? null,
                'ewallet_accounts' => $_POST['ewallet_accounts'] ?? null
            ];

            if (empty($data['type']) || empty($data['customer_no'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Tipe dan Nomor Pelanggan wajib diisi']);
                return;
            }

            if ($this->model->create($data)) {
                $this->jsonResponse(['success' => true, 'message' => 'Pelanggan berhasil disimpan']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Gagal menyimpan pelanggan']);
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $security = new Security();
            if (!$security->validateCSRFToken($_POST['csrf_token'] ?? '')) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF Token']);
                return;
            }

            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                $this->jsonResponse(['success' => false, 'message' => 'ID Pelanggan tidak valid']);
                return;
            }

            $data = [
                'type' => $_POST['type'] ?? '',
                'customer_name' => $_POST['customer_name'] ?? null,
                'customer_no' => $_POST['customer_no'] ?? '',
                'pln_name' => $_POST['pln_name'] ?? null,
                'pln_power' => $_POST['pln_power'] ?? null,
                'ewallet_accounts' => $_POST['ewallet_accounts'] ?? null
            ];

            if ($this->model->update($id, $data)) {
                $this->jsonResponse(['success' => true, 'message' => 'Pelanggan berhasil diupdate']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Gagal update pelanggan']);
            }
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $security = new Security();
            if (!$security->validateCSRFToken($_POST['csrf_token'] ?? '')) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF Token']);
                return;
            }

            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                $this->jsonResponse(['success' => false, 'message' => 'ID tidak valid']);
                return;
            }

            if ($this->model->delete($id)) {
                $this->jsonResponse(['success' => true, 'message' => 'Pelanggan berhasil dihapus']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Gagal menghapus pelanggan']);
            }
        }
    }

    public function checkPln() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customerNo = $_POST['customer_no'] ?? '';
            if (empty($customerNo)) {
                $this->jsonResponse(['success' => false, 'message' => 'Nomor Meter wajib diisi']);
                return;
            }

            require_once __DIR__ . '/../services/DigiflazzService.php';
            $service = new DigiflazzService();
            $result = $service->inquiryPLN($customerNo);

            if ($result['success'] && isset($result['data']['name'])) {
                // Name and segment_power are usually returned by Digiflazz inquiry
                $name = $result['data']['name'];
                $power = $result['data']['segment_power'] ?? '';
                
                $this->jsonResponse([
                    'success' => true, 
                    'data' => [
                        'name' => $name,
                        'power' => $power
                    ]
                ]);
            } else {
                $message = $result['data']['message'] ?? 'Gagal cek nomor PLN';
                $this->jsonResponse(['success' => false, 'message' => $message]);
            }
        }
    }

    public function getByType() {
        $type = $_GET['type'] ?? '';
        $customers = $this->model->getByType($type);
        $this->jsonResponse(['success' => true, 'data' => $customers]);
    }

    private function jsonResponse(array $data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
