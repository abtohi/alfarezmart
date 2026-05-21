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
        $aiPrompt = $settingModel->get('ai_invoice_prompt', "Analyze the invoice/delivery order image and extract ALL item purchases with maximum precision.\n\nFor each item, extract these fields:\n1. \"name\": The EXACT raw product name/description as printed on the invoice (verbatim, do not alter).\n2. \"qty\": Quantity purchased (numeric only, no units).\n3. \"price\": Unit price in Indonesian Rupiah. Convert abbreviations: 5.5 or 5,5 → 5500, 12 → 12000. If value looks < 1000 but represents thousands, multiply by 1000.\n4. \"brand\": Brand/manufacturer name (e.g. Cimory, Indomie, Aqua, Garuda, Wings, ABC, Unilever, Sosro, Mayora).\n5. \"product_type\": Product category/type (e.g. UHT, Susu, Mie Instan, Sabun Mandi, Deterjen, Snack, Kopi, Teh, Rokok, Minuman Soda, Air Mineral).\n6. \"variant\": Specific flavor, taste, color, scent, or variety. Be VERY thorough — extract ALL descriptive words after brand/type. Common abbreviations: POR=Pororo, CHOC=Chocolate, STB=Strawberry, GRN=Green Tea, ORG=Original, VAN=Vanilla.\n7. \"weight\": Numeric weight or volume value ONLY (e.g. 125, 250, 1.5, 500, 1000). Do not include units here.\n8. \"unit\": Unit of measurement (ml, g, kg, L, pcs, box, pak, karton, sachet, pouch, btl, kaleng).\n9. \"size\": Package configuration or size descriptor. Extract: multi-pack formats (e.g. \"12x300ml\", \"6x1L\", \"1DZ\", \"1 LUSIN\"), garment/product sizes (\"XL\", \"S\", \"M\", \"L\", \"XXL\"), portion/count info (\"5pcs\", \"isi 10\", \"3in1\"), or any size/pack-count not already captured by weight+unit. Set to null if not applicable.\n10. \"supplier_code\": Supplier item code, SKU, barcode, or reference code printed near the product name — often in brackets [CODE], parentheses (CODE), or as alphanumeric prefix/suffix. Extract exactly as printed.\n\nCRITICAL RULES:\n- Extract variant THOROUGHLY. Invoices often abbreviate variant names.\n- For multi-pack items (e.g. \"AQUA 600ML 1DZ\"), set weight=600, unit=\"ml\", size=\"1DZ\".\n- Supplier product codes appear in brackets [CMY-125POR] or as standalone codes before/after the product name.\n- The \"supplier_code\" field is critical for accurate matching — extract even partial codes.\n- Return ONLY valid JSON with an \"items\" array. No extra text or markdown outside the JSON.\n\nExample 1 (Beverage with code):\nInvoice: \"CIMORY UHT PORORO 125ML [CMY-125POR] 40 @ 5.5\"\n→ {\"name\":\"CIMORY UHT PORORO 125ML\",\"qty\":40,\"price\":5500,\"brand\":\"Cimory\",\"product_type\":\"UHT\",\"variant\":\"Pororo\",\"weight\":125,\"unit\":\"ml\",\"size\":null,\"supplier_code\":\"CMY-125POR\"}\n\nExample 2 (Noodles with code):\nInvoice: \"INDOMIE GORENG RASA SOTO 85G [IND-GOR-ST] 60 @ 2.5\"\n→ {\"name\":\"INDOMIE GORENG RASA SOTO 85G\",\"qty\":60,\"price\":2500,\"brand\":\"Indomie\",\"product_type\":\"Mie Instan\",\"variant\":\"Goreng Soto\",\"weight\":85,\"unit\":\"g\",\"size\":null,\"supplier_code\":\"IND-GOR-ST\"}\n\nExample 3 (Multi-pack water):\nInvoice: \"AQUA BOTOL 600ML 1DZ 12 @ 35\"\n→ {\"name\":\"AQUA BOTOL 600ML 1DZ\",\"qty\":12,\"price\":35000,\"brand\":\"Aqua\",\"product_type\":\"Minuman\",\"variant\":\"Botol\",\"weight\":600,\"unit\":\"ml\",\"size\":\"1DZ\",\"supplier_code\":null}\n\nOutput format:\n{\n  \"items\": [ {...}, {...} ]\n}");

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
