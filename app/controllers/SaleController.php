<?php
/**
 * SaleController - Penjualan (Retail & Grosir)
 */
class SaleController extends Controller
{
    private function getStoreSettings(): array
    {
        $settings = new SettingModel();
        $storeLogo = $settings->get('store_logo', '');
        if (empty($storeLogo)) {
            $iconPath = PUBLIC_PATH . '/images/Icon.png';
            if (file_exists($iconPath)) {
                $storeLogo = 'data:image/png;base64,' . base64_encode(file_get_contents($iconPath));
            } else {
                $storeLogo = BASE_URL . 'public/images/Icon.png';
            }
        }

        return [
            'store_name' => $settings->get('store_name', 'Alfarez Mart'),
            'store_address' => $settings->get('store_address', ''),
            'store_phone' => $settings->get('store_phone', ''),
            'thermal_printer_width' => (int) $settings->get('thermal_printer_width', 58),
            'receipt_header' => $settings->get('receipt_header', ''),
            'receipt_footer' => $settings->get('receipt_footer', ''),
            'store_logo' => $storeLogo,
        ];
    }

    public function index()
    {
        $model = new SaleModel();
        // Catatan: Transaksi penjualan disimpan selamanya untuk analitik & rekap riwayat tahunan

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $sales = $model->getList($page, 20);

        $this->view('sales.index', [
            'title' => 'Riwayat Penjualan',
            'activeNav' => 'pos',
            'sales' => $sales,
            'storeSettings' => $this->getStoreSettings(),
            'csrfToken' => (new Security())->getCSRFToken(),
        ]);
    }

    public function pos()
    {
        require_once __DIR__ . '/../models/DebtModel.php';
        $debtModel = new DebtModel();
        $customerTypes = $debtModel->getCustomerTypes();

        $this->view('sales.pos', [
            'title' => 'Kasir',
            'activeNav' => 'pos',
            'storeSettings' => $this->getStoreSettings(),
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

        $this->view('sales.detail', [
            'title' => 'Detail Transaksi ' . $sale['invoice_number'],
            'activeNav' => 'pos',
            'sale' => $sale,
            'storeSettings' => $this->getStoreSettings(),
        ]);
    }
}
