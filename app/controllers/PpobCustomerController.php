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
            $this->validateCSRF();

            $data = [
                'type' => $this->input('type', ''),
                'customer_name' => $this->input('customer_name'),
                'customer_no' => $this->input('customer_no', ''),
                'pln_name' => $this->input('pln_name'),
                'pln_power' => $this->input('pln_power'),
                'ewallet_accounts' => $this->input('ewallet_accounts')
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
            $this->validateCSRF();

            $id = $this->input('id', '');
            if (empty($id)) {
                $this->jsonResponse(['success' => false, 'message' => 'ID Pelanggan tidak valid']);
                return;
            }

            $data = [
                'type' => $this->input('type', ''),
                'customer_name' => $this->input('customer_name'),
                'customer_no' => $this->input('customer_no', ''),
                'pln_name' => $this->input('pln_name'),
                'pln_power' => $this->input('pln_power'),
                'ewallet_accounts' => $this->input('ewallet_accounts')
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
            $this->validateCSRF();

            $id = $this->input('id', '');
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
            $customerNo = $this->input('customer_no', '');
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
