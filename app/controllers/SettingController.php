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
            'activeNav' => 'home',
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
        $aiModel = $settingModel->get('ai_model', 'google/gemini-2.5-flash');
        $aiApiKey = $settingModel->get('ai_api_key', '');
        $defaultPrompt = "Kamu adalah AI asisten untuk AlfarezMart.\nTugas: Ekstrak data invoice supplier menjadi array JSON valid.\n\nINSTRUKSI WAJIB:\n1. OUTPUT HARUS JSON VALID (tanpa markdown ```json).\n2. DILARANG memberi penjelasan, cukup array JSON.\n3. Outputkan semua item dengan teliti dan 100% akurat. Partisi atau ringkas jika terlalu panjang agar tidak terpotong.\n4. Nilai uang hanya angka (tanpa titik/koma/Rp).\n\nEKSTRAKSI & IDENTIFIKASI CERDAS:\n1. Identifikasi detail produk: kode supplier, nama, ukuran (gram/ml), varian.\n2. Pahami singkatan: Analisis singkatan nama produk dan cari kecocokan (identik) dengan barang yang umum.\n3. Pengurangan berat (Shrinkflation): Jika gramasi turun sedikit (misal 30g jadi 27g), anggap sebagai barang yang sama/berkaitan.\n4. Qty: Angka jumlah barang.\n5. Satuan (Unit): WAJIB deteksi KARTON, RENCENG, PACK, BOX, SLOP, atau PCS.\n   - PENTING: Identifikasi satuan berdasarkan HARGA. Jika total harga dibagi qty bernilai besar (misal 20.000) dan tidak masuk akal untuk 1 PCS, maka itu adalah satuan besar (Karton/Pack/Box).\n   - Harga bisa fluktuatif (misal 19.000 atau 21.000). Gunakan logika aproksimasi.\n6. Harga Modal: Hitung otomatis (total harga / qty) jika perlu untuk memastikan akurasi.\n7. Total Harga: Total sebelum diskon.\n8. Diskon: Potongan harga baris.\n\nFORMAT JSON OUTPUT YANG WAJIB:\n[\n  {\n    \"supplier_product_code\": \"KODE123\",\n    \"name\": \"NAMA BARANG (Ukuran/Varian)\",\n    \"qty\": 3,\n    \"unit\": \"Pack\",\n    \"total_price\": 60000,\n    \"discount\": 0\n  }\n]";
        $aiPrompt = $settingModel->get('ai_invoice_prompt', $defaultPrompt);

        $this->view('settings.app', [
            'title' => 'Pengaturan Aplikasi',
            'activeNav' => 'home',
            'aiModel' => $aiModel,
            'aiApiKey' => $aiApiKey,
            'aiPrompt' => $aiPrompt
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
