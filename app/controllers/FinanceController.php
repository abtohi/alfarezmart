<?php
/**
 * FinanceController - Controller untuk Laporan/Pencatatan Keuangan Harian
 */
class FinanceController extends Controller
{
    public function index()
    {
        $this->requireSuperadmin();

        $productModel = new ProductModel();
        $markupStats = $productModel->getMarkupStats();
        $combinedMarkup = (float)($markupStats['level1']['avg_combined'] ?? 27.82);

        $settingModel = new SettingModel();
        $storeName = $settingModel->get('store_name', 'AlfarezMart');
        $storeAddress = $settingModel->get('store_address', 'Jl. Sukatani Raya No. 42');
        $storePhone = $settingModel->get('store_phone', '0812-3456-7890');

        $logoBase64 = '';
        $logoPath = BASE_PATH . '/public/images/Icon.png';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $this->view('finance.index', [
            'title' => 'Keuangan Harian & Laporan Omzet',
            'activeNav' => 'home', // Keeps home menu active in bottom-nav
            'combinedMarkup' => $combinedMarkup,
            'markupStats' => $markupStats,
            'storeName' => $storeName,
            'storeAddress' => $storeAddress,
            'storePhone' => $storePhone,
            'logoBase64' => $logoBase64
        ]);
    }
}
