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
        $aiPrompt = $settingModel->get('ai_invoice_prompt', "Analyze the invoice image and extract all item purchases. For each item, identify and extract:\n1. \"name\": The exact raw product name printed on the invoice.\n2. \"qty\": The quantity purchased.\n3. \"price\": The unit price in absolute Indonesian Rupiah (convert abbreviations like 5.5, 5,5 or 12 to 5500 or 12000).\n4. \"brand\": The brand name (e.g. Cimory, Indomie, Aqua) if identifiable from the item name.\n5. \"product_type\": The type of product (e.g. UHT, Susu, Mie Instan, Sabun) if identifiable.\n6. \"variant\": The variant, flavor, color, or scent (e.g. Chocolate, Soto, Original, White) if identifiable.\n7. \"weight\": The numeric weight/volume value (e.g. 125, 250, 1.5).\n8. \"unit\": The unit of weight/volume (e.g. ml, g, kg, L).\n9. \"supplier_code\": The supplier's item code or reference code if printed next to the item name.\n\nEnsure to output a valid JSON object containing an \"items\" array, where each item is an object with these exact keys. If a key is not identifiable, set its value to null. Example output format:\n{\n  \"items\": [\n    {\n      \"name\": \"CIMORY UHT PORORO 125ML\",\n      \"qty\": 40,\n      \"price\": 5500,\n      \"brand\": \"Cimory\",\n      \"product_type\": \"UHT\",\n      \"variant\": \"Pororo\",\n      \"weight\": 125,\n      \"unit\": \"ml\",\n      \"supplier_code\": \"CMY-125\"\n    }\n  ]\n}");

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
