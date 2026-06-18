<?php
/**
 * SaleController - Penjualan (Retail & Grosir)
 */
class SaleController extends Controller
{
    public function index()
    {
        $model = new SaleModel();
        // Auto cleanup old transactions (> 30 days) to save storage
        $model->cleanupOldTransactions(30);

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $sales = $model->getList($page, 20);

        $this->view('sales.index', [
            'title' => 'Riwayat Penjualan',
            'activeNav' => 'pos',
            'sales' => $sales,
            'csrfToken' => (new Security())->getCSRFToken(),
        ]);
    }

    public function pos()
    {
        $settings = new SettingModel();
        require_once __DIR__ . '/../models/DebtModel.php';
        $debtModel = new DebtModel();
        $customerTypes = $debtModel->getCustomerTypes();

        $this->view('sales.pos', [
            'title' => 'Kasir',
            'activeNav' => 'pos',
            'storeSettings' => [
                'store_name' => $settings->get('store_name', 'AlfarezMart'),
                'store_address' => $settings->get('store_address', ''),
                'store_phone' => $settings->get('store_phone', ''),
                'thermal_printer_width' => (int) $settings->get('thermal_printer_width', 58),
                'receipt_header' => $settings->get('receipt_header', ''),
                'receipt_footer' => $settings->get('receipt_footer', ''),
                'store_logo' => $settings->get('store_logo', BASE_URL . 'public/images/Icon.png'),
            ],
            'customerTypes' => $customerTypes,
            'csrfToken' => (new Security())->getCSRFToken(),
        ]);
    }

    /**
     * @param string|int $id
     */
    public function show($id)
    {
        $model = new SaleModel();
        $sale = is_numeric($id) ? $model->getTransactionDetails((int)$id) : $model->findByInvoice($id);
        
        if (!$sale) {
            header("HTTP/1.0 404 Not Found");
            echo "Transaksi tidak ditemukan.";
            exit;
        }

        $settings = new SettingModel();
        $this->view('sales.detail', [
            'title' => 'Detail Transaksi ' . $sale['invoice_number'],
            'activeNav' => 'pos',
            'sale' => $sale,
            'storeSettings' => [
                'store_name' => $settings->get('store_name', 'AlfarezMart'),
                'store_address' => $settings->get('store_address', ''),
                'store_phone' => $settings->get('store_phone', ''),
                'thermal_printer_width' => (int) $settings->get('thermal_printer_width', 58),
                'receipt_header' => $settings->get('receipt_header', ''),
                'receipt_footer' => $settings->get('receipt_footer', ''),
                'store_logo' => $settings->get('store_logo', BASE_URL . 'public/images/Icon.png'),
            ]
        ]);
    }
}
