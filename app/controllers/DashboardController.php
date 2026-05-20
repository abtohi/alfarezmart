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

        // Fetch sales stats for today
        $saleModel = new SaleModel();
        $todaySaleStats = $saleModel->getDailyStats(date('Y-m-d'));
        $stats['today_revenue'] = $todaySaleStats['revenue'] ?? 0;
        $stats['today_transactions'] = $todaySaleStats['transactions'] ?? 0;

        // Fetch finance summary for today
        $financeModel = new FinanceModel();
        $todayFinanceSummary = $financeModel->getDailySummary(date('Y-m-d'));
        $stats['finance_today'] = [
            'income' => $todayFinanceSummary['income'] ?? 0,
            'expense' => $todayFinanceSummary['expense'] ?? 0,
        ];

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
