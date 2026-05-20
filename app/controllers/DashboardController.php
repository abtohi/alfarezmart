<?php
/**
 * DashboardController - Halaman utama dan help
 */
class DashboardController extends Controller
{
    public function index()
    {
        $productModel = new ProductModel();
        $stats = $productModel->getStats();

        $this->view('dashboard.index', [
            'title' => 'Dashboard',
            'activeNav' => 'home',
            'stats' => $stats,
        ]);
    }

    public function help()
    {
        $this->view('help.index', [
            'title' => 'Bantuan',
            'activeNav' => 'home',
        ]);
    }

    public function notFound()
    {
        http_response_code(404);
        $this->view('errors.404', [
            'title' => 'Halaman Tidak Ditemukan',
            'activeNav' => '',
        ]);
    }
}
