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
