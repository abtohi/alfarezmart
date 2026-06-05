<?php
/**
 * AiContextBuilder - RAG Engine for AlfarezMart AI Chat
 * Mengambil data dari database dan menyusunnya menjadi konteks JSON untuk system prompt
 */
class AiContextBuilder
{
    /** @var \PDO */
    private $db;
    /** @var FinanceModel */
    private $financeModel;
    /** @var ProductModel */
    private $productModel;
    /** @var PurchaseModel */
    private $purchaseModel;
    /** @var SaleModel */
    private $saleModel;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->financeModel = new FinanceModel();
        $this->productModel = new ProductModel();
        $this->purchaseModel = new PurchaseModel();
        $this->saleModel = new SaleModel();
    }

    /**
     * Membangun system prompt lengkap dengan context data toko
     */
    public function buildSystemPrompt($userMessage = '')
    {
        $context = [
            'store_info' => [
                'name' => 'AlfarezMart',
                'current_date' => date('Y-m-d H:i:s'),
                'timezone' => 'Asia/Jakarta'
            ],
            'finance_summary' => $this->getFinanceSummary(),
            'stock_alerts' => $this->getLowStockProducts(),
            'top_products' => $this->getTopProducts(),
            'inactive_products' => $this->getInactiveProducts()
        ];

        // Opsional: jika pertanyaan menyinggung 'sales' atau 'jadwal', tambahkan konteks jadwal
        if (stripos($userMessage, 'sales') !== false || stripos($userMessage, 'jadwal') !== false || stripos($userMessage, 'kunjungan') !== false) {
            $context['sales_schedule'] = $this->getSalesSchedule();
        }

        // Opsional: jika pertanyaan menyinggung 'modal', 'beli', 'supplier', tambahkan info pembelian terbaru
        if (stripos($userMessage, 'modal') !== false || stripos($userMessage, 'beli') !== false || stripos($userMessage, 'supplier') !== false || stripos($userMessage, 'harga') !== false) {
            $context['latest_purchases'] = $this->getLatestPurchases();
        }

        $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $prompt = "Kamu adalah AI Asisten pintar untuk toko AlfarezMart.\n";
        $prompt .= "Kamu memiliki akses ke snapshot data toko secara real-time yang diberikan di bawah ini.\n";
        $prompt .= "Gunakan data tersebut untuk menjawab pertanyaan pengguna dengan akurat, ringkas, informatif, dan profesional.\n";
        $prompt .= "Berikan analisis atau insight bisnis jika relevan (misal: margin profit, saran stok).\n";
        $prompt .= "Format jawabanmu menggunakan Markdown. Gunakan tebal (bold) untuk angka penting, dan list bullet/tabel jika datanya banyak.\n";
        $prompt .= "Jawab dalam Bahasa Indonesia. Jika data yang diminta tidak ada di snapshot, katakan bahwa data tersebut tidak tersedia di konteks saat ini.\n\n";
        $prompt .= "=== DATA TOKO SAAT INI ===\n";
        $prompt .= $contextJson . "\n";
        $prompt .= "=========================\n";

        return $prompt;
    }

    private function getFinanceSummary(): array
    {
        try {
            $today = date('Y-m-d');
            $firstDayOfMonth = date('Y-m-01');

            $stmt1 = $this->db->prepare("SELECT COALESCE(SUM(total_amount), 0) as omzet FROM sale_transactions WHERE DATE(created_at) = :today");
            $stmt1->execute([':today' => $today]);
            $omzetToday = $stmt1->fetchColumn();

            $stmt2 = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as expense FROM finance_logs WHERE type = 'expense' AND DATE(log_date) >= :start_date");
            $stmt2->execute([':start_date' => $firstDayOfMonth]);
            $expenseMonth = $stmt2->fetchColumn();

            $stmt3 = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as income FROM finance_logs WHERE type = 'income' AND DATE(log_date) >= :start_date");
            $stmt3->execute([':start_date' => $firstDayOfMonth]);
            $incomeMonth = $stmt3->fetchColumn();

            return [
                'omzet_hari_ini'          => (float)$omzetToday,
                'pengeluaran_bulan_ini'   => (float)$expenseMonth,
                'pemasukan_lain_bulan_ini'=> (float)$incomeMonth,
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data keuangan tidak tersedia'];
        }
    }

    private function getLowStockProducts(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT p.full_name, s.current_qty_base 
                FROM products p 
                JOIN stock s ON p.id = s.product_id 
                WHERE s.current_qty_base <= 5 AND p.is_active = 1
                ORDER BY s.current_qty_base ASC
                LIMIT 10
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function getTopProducts(): array
    {
        try {
            $firstDayOfMonth = date('Y-m-01');
            $stmt = $this->db->prepare("
                SELECT p.full_name, SUM(si.quantity) as total_sold
                FROM sale_items si
                JOIN sale_transactions s ON si.transaction_id = s.id
                JOIN products p ON si.product_id = p.id
                WHERE s.created_at >= :start_date
                GROUP BY p.id
                ORDER BY total_sold DESC
                LIMIT 5
            ");
            $stmt->execute([':start_date' => $firstDayOfMonth]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function getSalesSchedule()
    {
        // Fitur jadwal sales belum tersedia di DB saat ini
        return [];
    }

    private function getLatestPurchases(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT pu.invoice_number, sup.name as supplier, pu.purchase_date, pu.grand_total, pu.status
                FROM purchases pu
                JOIN suppliers sup ON pu.supplier_id = sup.id
                ORDER BY pu.purchase_date DESC
                LIMIT 10
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function getInactiveProducts(): array
    {
        try {
            $lastMonth = date('Y-m-d', strtotime('-30 days'));
            $stmt = $this->db->prepare("
                SELECT p.full_name
                FROM products p
                JOIN stock s ON p.id = s.product_id
                WHERE s.current_qty_base = 0 
                AND p.is_active = 1
                AND p.id NOT IN (
                    SELECT pi.product_id 
                    FROM purchase_items pi
                    JOIN purchases pu ON pi.purchase_id = pu.id
                    WHERE pu.purchase_date >= :last_month
                )
                LIMIT 10
            ");
            $stmt->execute([':last_month' => $lastMonth]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }
}
