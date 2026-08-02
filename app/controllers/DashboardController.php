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
        $stats['today_profit'] = $todaySaleStats['gross_profit'] ?? 0;

        // Calculate average markup today
        $todayItemsRev = (float)($todaySaleStats['items_revenue'] ?? 0);
        $todayProfit = (float)($todaySaleStats['gross_profit'] ?? 0);
        $todayCostBasis = max(0.01, $todayItemsRev - $todayProfit);
        $stats['today_avg_markup'] = ($todayProfit / $todayCostBasis) * 100;

        // Fetch finance summary for today
        $financeModel = new FinanceModel();
        $todayFinanceSummary = $financeModel->getDailySummary(date('Y-m-d'));
        $stats['finance_today'] = [
            'income' => $todayFinanceSummary['income'] ?? 0,
            'expense' => $todayFinanceSummary['expense'] ?? 0,
            'accumulative_net' => $todayFinanceSummary['accumulative_net'] ?? 0,
        ];

        // Fetch chart analytics data
        $db = Database::getInstance()->getConnection();
        $today = date('Y-m-d');
        $sevenDaysAgo = date('Y-m-d', strtotime('-6 days'));

        // 1. 7-day revenue & transaction series
        $stmt7Days = $db->prepare("
            SELECT DATE(created_at) AS date_str, COUNT(id) AS tx_count, COALESCE(SUM(total_amount), 0) AS revenue
            FROM sale_transactions
            WHERE DATE(created_at) BETWEEN :s AND :e
            GROUP BY DATE(created_at)
            ORDER BY date_str ASC
        ");
        $stmt7Days->execute([':s' => $sevenDaysAgo, ':e' => $today]);
        $raw7Days = $stmt7Days->fetchAll() ?: [];
        $mapped7Days = [];
        foreach ($raw7Days as $r) {
            $mapped7Days[$r['date_str']] = $r;
        }

        $weeklySeries = [];
        $dayNamesIndo = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $ts = strtotime($d);
            $lbl = $dayNamesIndo[date('w', $ts)] . ' ' . date('d/m', $ts);
            $weeklySeries[] = [
                'date' => $d,
                'label' => $lbl,
                'revenue' => (float)($mapped7Days[$d]['revenue'] ?? 0),
                'transactions' => (int)($mapped7Days[$d]['tx_count'] ?? 0),
            ];
        }

        // 2. Top 5 category sales (last 30 days)
        $thirtyDaysAgo = date('Y-m-d', strtotime('-29 days'));
        $stmtCat = $db->prepare("
            SELECT 
                COALESCE(c.name, 'Umum / Lainnya') AS category_name,
                SUM(si.quantity) AS total_qty,
                SUM(si.total_price) AS total_revenue
            FROM sale_items si
            JOIN sale_transactions st ON st.id = si.transaction_id
            LEFT JOIN products p ON p.id = si.product_id
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE DATE(st.created_at) BETWEEN :s AND :e
            GROUP BY category_name
            ORDER BY total_revenue DESC
            LIMIT 5
        ");
        $stmtCat->execute([':s' => $thirtyDaysAgo, ':e' => $today]);
        $topCategories = $stmtCat->fetchAll() ?: [];

        // 3. Top 5 products sold today
        $stmtTopToday = $db->prepare("
            SELECT 
                COALESCE(NULLIF(si.custom_name, ''), p.full_name, 'Produk') AS name,
                SUM(si.quantity) AS qty,
                SUM(si.total_price) AS revenue
            FROM sale_items si
            JOIN sale_transactions st ON st.id = si.transaction_id
            LEFT JOIN products p ON p.id = si.product_id
            WHERE DATE(st.created_at) = :today
            GROUP BY si.product_id, name
            ORDER BY qty DESC
            LIMIT 5
        ");
        $stmtTopToday->execute([':today' => $today]);
        $topProductsToday = $stmtTopToday->fetchAll() ?: [];

        // 4. PPOB Analytics Data for Dashboard
        $ppobStats = [
            'today_total' => 0,
            'today_success' => 0,
            'today_pending' => 0,
            'today_failed' => 0,
            'today_revenue' => 0,
            'today_profit' => 0,
            'today_success_rate' => 0,
            'today_avg_speed' => 0,
        ];
        $ppobTopToday = [];
        $ppobCat30Days = [];

        try {
            $stmtPpobToday = $db->prepare("
                SELECT 
                    COUNT(*) as total_count,
                    SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN LOWER(status) IN ('pending', 'processing') THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN LOWER(status) IN ('failed', 'gagal') THEN 1 ELSE 0 END) as failed_count,
                    COALESCE(SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN sell_price ELSE 0 END), 0) as revenue,
                    COALESCE(SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN profit ELSE 0 END), 0) as profit,
                    AVG(CASE WHEN LOWER(status) IN ('success', 'sukses', 'failed', 'gagal') 
                                 AND TIMESTAMPDIFF(SECOND, created_at, updated_at) >= 0 
                            THEN TIMESTAMPDIFF(SECOND, created_at, updated_at) 
                            ELSE NULL END) as avg_speed
                FROM digi_transactions
                WHERE DATE(created_at) = :today
            ");
            $stmtPpobToday->execute([':today' => $today]);
            $rowP = $stmtPpobToday->fetch(PDO::FETCH_ASSOC);
            if ($rowP) {
                $ppobStats['today_total'] = (int)($rowP['total_count'] ?? 0);
                $ppobStats['today_success'] = (int)($rowP['success_count'] ?? 0);
                $ppobStats['today_pending'] = (int)($rowP['pending_count'] ?? 0);
                $ppobStats['today_failed'] = (int)($rowP['failed_count'] ?? 0);
                $ppobStats['today_revenue'] = (float)($rowP['revenue'] ?? 0);
                $ppobStats['today_profit'] = (float)($rowP['profit'] ?? 0);
                $ppobStats['today_avg_speed'] = $rowP['avg_speed'] !== null ? round((float)$rowP['avg_speed'], 1) : 0;
                if ($ppobStats['today_total'] > 0) {
                    $ppobStats['today_success_rate'] = round(($ppobStats['today_success'] / $ppobStats['today_total']) * 100, 1);
                }
            }

            // Top 5 PPOB products sold today
            $stmtTopPpob = $db->prepare("
                SELECT 
                    product_name,
                    category,
                    COUNT(*) as qty,
                    COALESCE(SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN sell_price ELSE 0 END), 0) as revenue,
                    COALESCE(SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN profit ELSE 0 END), 0) as profit
                FROM digi_transactions
                WHERE DATE(created_at) = :today
                GROUP BY product_name, category
                ORDER BY qty DESC
                LIMIT 5
            ");
            $stmtTopPpob->execute([':today' => $today]);
            $ppobTopToday = $stmtTopPpob->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // PPOB category distribution (last 30 days)
            $stmtCatPpob = $db->prepare("
                SELECT 
                    COALESCE(NULLIF(category, ''), 'Lainnya') as category_name,
                    COUNT(*) as total_qty,
                    COALESCE(SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN sell_price ELSE 0 END), 0) as total_revenue
                FROM digi_transactions
                WHERE DATE(created_at) BETWEEN :s AND :e
                GROUP BY category_name
                ORDER BY total_qty DESC
                LIMIT 5
            ");
            $stmtCatPpob->execute([':s' => $thirtyDaysAgo, ':e' => $today]);
            $ppobCat30Days = $stmtCatPpob->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (\Throwable $e) {
            error_log("[DashboardController] PPOB query error: " . $e->getMessage());
        }

        $this->view('dashboard.index', [
            'title' => 'Dashboard',
            'activeNav' => 'home',
            'stats' => $stats,
            'weeklySeries' => $weeklySeries,
            'topCategories' => $topCategories,
            'topProductsToday' => $topProductsToday,
            'ppobStats' => $ppobStats,
            'ppobTopToday' => $ppobTopToday,
            'ppobCat30Days' => $ppobCat30Days,
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
