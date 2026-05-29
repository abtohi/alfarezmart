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

    /**
     * Summary & statistik bulanan untuk superadmin
     * (omzet, belanja, profit, markup rata-rata, top produk)
     */
    public function summary()
    {
        $this->requireSuperadmin();

        $month = isset($_GET['month']) ? preg_replace('/[^0-9\-]/', '', $_GET['month']) : date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));

        $db = Database::getInstance()->getConnection();

        // Omzet & profit bulanan + jumlah transaksi
        $stmt = $db->prepare("
            SELECT
                COUNT(DISTINCT st.id) AS tx_count,
                COALESCE(SUM(st.total_amount), 0) AS revenue,
                COALESCE(SUM(si.profit), 0) AS gross_profit,
                COALESCE(SUM(si.total_price), 0) AS items_revenue
            FROM sale_transactions st
            LEFT JOIN sale_items si ON si.transaction_id = st.id
            WHERE DATE(st.created_at) BETWEEN :s AND :e
        ");
        $stmt->execute([':s' => $start, ':e' => $end]);
        $salesAgg = $stmt->fetch() ?: [];

        // Belanja (purchases) bulanan
        $stmt = $db->prepare("
            SELECT COUNT(id) AS p_count, COALESCE(SUM(grand_total), 0) AS p_total
            FROM purchases
            WHERE purchase_date BETWEEN :s AND :e
        ");
        $stmt->execute([':s' => $start, ':e' => $end]);
        $purchaseAgg = $stmt->fetch() ?: [];

        // Markup rata-rata: profit / cost basis dari items terjual
        $itemsRevenue = (float)($salesAgg['items_revenue'] ?? 0);
        $grossProfit = (float)($salesAgg['gross_profit'] ?? 0);
        $costBasis = max(0.01, $itemsRevenue - $grossProfit);
        $avgMarkup = ($grossProfit / $costBasis) * 100;

        // Daily revenue series (chart)
        $stmt = $db->prepare("
            SELECT DATE(created_at) AS d, COALESCE(SUM(total_amount), 0) AS rev
            FROM sale_transactions
            WHERE DATE(created_at) BETWEEN :s AND :e
            GROUP BY DATE(created_at)
            ORDER BY d ASC
        ");
        $stmt->execute([':s' => $start, ':e' => $end]);
        $dailySeries = $stmt->fetchAll() ?: [];

        // Top 10 produk laris (bulan ini)
        $stmt = $db->prepare("
            SELECT
                COALESCE(NULLIF(si.custom_name, ''), p.full_name, 'Produk') AS name,
                SUM(si.quantity) AS qty_sold,
                SUM(si.total_price) AS revenue,
                SUM(si.profit) AS profit
            FROM sale_items si
            JOIN sale_transactions st ON st.id = si.transaction_id
            LEFT JOIN products p ON p.id = si.product_id
            WHERE DATE(st.created_at) BETWEEN :s AND :e
            GROUP BY si.product_id, name
            ORDER BY qty_sold DESC
            LIMIT 10
        ");
        $stmt->execute([':s' => $start, ':e' => $end]);
        $topProducts = $stmt->fetchAll() ?: [];

        // Hutang outstanding (snapshot)
        $debtOut = ['customer' => 0, 'shop' => 0];
        try {
            $row = $db->query("SELECT COALESCE(SUM(remaining_amount),0) v FROM customer_debts WHERE status != 'lunas'")->fetch();
            $debtOut['customer'] = (float)($row['v'] ?? 0);
        } catch (\Throwable $e) {}
        try {
            $row = $db->query("SELECT COALESCE(SUM(remaining_amount),0) v FROM shop_debts WHERE status != 'lunas'")->fetch();
            $debtOut['shop'] = (float)($row['v'] ?? 0);
        } catch (\Throwable $e) {}

        $this->view('dashboard.summary', [
            'title' => 'Summary & Statistik',
            'activeNav' => 'home',
            'month' => $month,
            'salesAgg' => $salesAgg,
            'purchaseAgg' => $purchaseAgg,
            'avgMarkup' => $avgMarkup,
            'dailySeries' => $dailySeries,
            'topProducts' => $topProducts,
            'debtOut' => $debtOut,
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
