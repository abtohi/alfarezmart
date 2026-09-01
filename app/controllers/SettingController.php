<?php
/**
 * SettingController - Settings & Database Setup
 */
class SettingController extends Controller
{
    public function index()
    {
        $this->view('settings.index', [
            'title' => 'Pengaturan',
            'activeNav' => 'settings',
        ]);
    }

    public function masterData()
    {
        $brandModel = new BrandModel();
        $categoryModel = new CategoryModel();
        $unitModel = new UnitModel();

        $this->view('settings.master_data', [
            'title' => 'Master Data',
            'activeNav' => 'home',
            'brands' => $brandModel->all('name', 'ASC'),
            'categories' => $categoryModel->all('name', 'ASC'),
            'units' => $unitModel->all('name', 'ASC'),
        ]);
    }

    public function receiptSettings()
    {
        $this->view('settings.receipt', [
            'title' => 'Pengaturan Struk',
            'activeNav' => 'home',
        ]);
    }

    public function appSettings()
    {
        $settingModel = new SettingModel();
        
        // Pass setting values to view
        $aiModel = $settingModel->get('ai_model', 'openrouter/auto');
        $aiApiKey = $settingModel->get('ai_api_key', '');
        $defaultPrompt = "Kamu adalah AI asisten untuk AlfarezMart.\nTugas: Ekstrak data invoice/faktur supplier menjadi array JSON valid.\n\nINSTRUKSI WAJIB:\n1. OUTPUT HARUS JSON VALID (tanpa markdown ```json).\n2. DILARANG memberi penjelasan, cukup array JSON.\n3. Outputkan semua item dengan teliti dan 100% akurat. Partisi jika terlalu panjang.\n4. Nilai uang hanya angka (tanpa titik/koma/Rp).\n\nALGORITMA PENCARIAN & IDENTIFIKASI CERDAS:\n1. KODE BARANG SUPPLIER: Cari kode barang supplier (biasanya di paling kiri, kolom Item/ID), TRIM spasi, dan masukkan ke `supplier_product_code`. Jika ada, kode ini HARUS EXACT MATCH. Jika berbeda, anggap produk berbeda.\n2. NAMA BARANG SUPPLIER: Lakukan exact match dulu. Jika tidak ada, gunakan fuzzy matching. Cari berdasarkan kombinasi kata paling mirip, pertimbangkan singkatan, ukuran, berat, dan varian.\n3. NAMA PRODUK & LABEL: AI harus cerdas mirip search engine! Kenali penulisan tidak baku, singkatan (Hydrococo vs Hydro Coco), varian, dan ukuran.\n- Toleransi berat: Jika di invoice berat 70g/69g tapi di database 72g (karena penyusutan/shrinkflation pabrik), BISA dianggap sama asalkan toleransi sekitar 10-20%. Jangan mereferensikan 72g ke 50g!\n- Analisis Kemasan Bertingkat: Jika 1 dus = 10 renceng, cari nama dus dulu, jika ketemu cari renceng berdasarkan nama dus tersebut.\n4. PEMBELAJARAN SATUAN DARI HARGA: AI HARUS mempelajari satuan kemasan dari harga dan qty!\n- Misal di DB: 1 pcs = Rp800, 1 renceng (10 pcs) = Rp8.000, 1 karton (15 renceng) = Rp120.000.\n- Jika di invoice QTY = 4 dan TOTAL = Rp32.000, maka Rp32.000 / 4 = Rp8.000. Karena Rp8.000 = harga renceng, tetapkan `unit` sebagai \"Renceng\" (meskipun satuan tidak tertulis di invoice).\n- Fluktuasi harga: Misal QTY = 4, TOTAL = Rp35.000 -> Harga per unit = Rp8.750. Angka Rp8.750 lebih mendekati harga renceng (Rp8.000) dibanding pcs (Rp800) atau karton (Rp120.000). AI harus tetap menetapkan unit sebagai Renceng!\n\nFORMAT JSON OUTPUT YANG WAJIB:\n[\n  {\n    \"supplier_product_code\": \"KODE123\",\n    \"name\": \"NAMA BARANG LENGKAP\",\n    \"qty\": 4,\n    \"unit\": \"Renceng\",\n    \"unit_price\": 8750,\n    \"total_price\": 35000,\n    \"discount\": 0\n  }\n]";
        $aiPrompt = $settingModel->get('ai_invoice_prompt', $defaultPrompt);

        // Auto-update prompt in DB if it's using the old version
        if (strpos($aiPrompt, 'ALGORITMA PENCARIAN & IDENTIFIKASI CERDAS') === false) {
            $settingModel->set('ai_invoice_prompt', $defaultPrompt);
            $aiPrompt = $defaultPrompt;
        }

        $storeLat = $settingModel->get('store_latitude', '');
        $storeLng = $settingModel->get('store_longitude', '');
        $storeRadius = $settingModel->get('store_radius_meters', '25');

        $this->view('settings.app', [
            'title' => 'Pengaturan Aplikasi',
            'activeNav' => 'home',
            'aiModel' => $aiModel,
            'aiApiKey' => $aiApiKey,
            'aiPrompt' => $aiPrompt,
            'storeLat' => $storeLat,
            'storeLng' => $storeLng,
            'storeRadius' => $storeRadius
        ]);
    }

    public function errorLogs()
    {
        $this->view('settings.error_logs', [
            'title'     => 'Error Log Catcher',
            'activeNav' => 'error_logs',
        ]);
    }

    public function backupData()
    {
        $this->view('settings.backup', [
            'title'     => 'Backup Data Harian',
            'activeNav' => 'backup',
        ]);
    }

    public function setupDatabase()
    {
        if (defined('APP_ENV') && APP_ENV !== 'development') {
            $this->json(['error' => 'Not allowed in production'], 403);
            return;
        }

        require_once BASE_PATH . '/database/setup.php';
        $messages = setupDatabase();

        $this->view('settings.setup', [
            'title' => 'Database Setup',
            'activeNav' => 'home',
            'messages' => $messages,
        ]);
    }
}
