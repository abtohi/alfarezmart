<?php
/**
 * AiContextBuilder - RAG Engine for AlfarezMart AI Chat
 * Mengambil data dari semua tabel database dan menyusunnya menjadi konteks
 * untuk system prompt AI. Semua query dibungkus try-catch agar error SQL
 * tidak mempengaruhi respons AI.
 */
class AiContextBuilder
{
    /** @var \PDO */
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Membangun system prompt lengkap dengan context data toko
     */
    public function buildSystemPrompt(string $userMessage = ''): string
    {
        $today = date('Y-m-d');

        // Konteks inti yang selalu disertakan
        $context = [
            'toko'              => ['nama' => 'AlfarezMart', 'tanggal' => date('Y-m-d H:i:s'), 'timezone' => 'Asia/Jakarta'],
            'omzet'             => $this->getOmzetSummary(),
            'keuangan_harian'   => $this->getFinanceSummary($today),
            'stok_menipis'      => $this->getLowStockProducts(),
            'produk_terlaris'   => $this->getTopProducts(),
            'produk_tidak_laku' => $this->getInactiveProducts(),
            'hutang_toko'       => $this->getShopDebtSummary(),
            'piutang_pelanggan' => $this->getCustomerDebtSummary(),
            'jadwal_sales'      => $this->getSalesReps(),
        ];

        // Konteks opsional berdasarkan kata kunci pertanyaan
        $q = mb_strtolower($userMessage);

        if ($this->contains($q, ['beli', 'supplier', 'modal', 'pembelian', 'stok masuk'])) {
            $context['pembelian_terbaru'] = $this->getLatestPurchases();
        }
        if ($this->contains($q, ['konsinyasi', 'titipan', 'konsinye'])) {
            $context['konsinyasi'] = $this->getConsignments();
        }
        if ($this->contains($q, ['pelanggan', 'customer', 'pembeli'])) {
            $context['pelanggan_aktif'] = $this->getTopCustomers();
        }
        if ($this->contains($q, ['laporan', 'bulan', 'monthly', 'bulanan'])) {
            $context['keuangan_bulan_ini'] = $this->getMonthlyFinanceSummary();
        }

        $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE);

        $prompt  = "Kamu adalah AI Asisten toko AlfarezMart. Kamu memiliki akses penuh ke data real-time toko.\n";
        $prompt .= "Gunakan data di bawah untuk menjawab dengan akurat, ringkas, dan profesional dalam Bahasa Indonesia.\n";
        $prompt .= "Format: Markdown. Tebalkan angka penting. Gunakan tabel/list jika data banyak.\n";
        $prompt .= "Jika data tidak ada dalam konteks, katakan tidak tersedia.\n\n";
        $prompt .= "DATA_TOKO=" . $contextJson . "\n";

        return $prompt;
    }

    // ============================================================
    // KEUANGAN
    // ============================================================

    /** Omzet hari ini dan bulan ini dari transaksi penjualan */
    private function getOmzetSummary(): array
    {
        try {
            $today = date('Y-m-d');
            $firstOfMonth = date('Y-m-01');

            $stmt = $this->db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN DATE(created_at) = :today THEN total_amount ELSE 0 END), 0) AS omzet_hari_ini,
                    COALESCE(SUM(CASE WHEN DATE(created_at) >= :first_month THEN total_amount ELSE 0 END), 0) AS omzet_bulan_ini,
                    COUNT(CASE WHEN DATE(created_at) = :today2 THEN 1 END) AS transaksi_hari_ini,
                    COUNT(CASE WHEN DATE(created_at) >= :first_month2 THEN 1 END) AS transaksi_bulan_ini
                FROM sale_transactions
            ");
            $stmt->execute([
                ':today'        => $today,
                ':first_month'  => $firstOfMonth,
                ':today2'       => $today,
                ':first_month2' => $firstOfMonth,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'omzet_hari_ini'      => (float)($row['omzet_hari_ini'] ?? 0),
                'omzet_bulan_ini'     => (float)($row['omzet_bulan_ini'] ?? 0),
                'transaksi_hari_ini'  => (int)($row['transaksi_hari_ini'] ?? 0),
                'transaksi_bulan_ini' => (int)($row['transaksi_bulan_ini'] ?? 0),
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data omzet tidak tersedia'];
        }
    }

    /**
     * Pemasukan & pengeluaran harian dari finance_logs.
     * PENTING: kolom adalah `category` dengan nilai 'Pemasukan' / 'Pengeluaran'
     */
    private function getFinanceSummary(string $date): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN category = 'Pemasukan'  THEN amount ELSE 0 END), 0) AS pemasukan_hari_ini,
                    COALESCE(SUM(CASE WHEN category = 'Pengeluaran' THEN amount ELSE 0 END), 0) AS pengeluaran_hari_ini
                FROM finance_logs
                WHERE log_date = :date
            ");
            $stmt->execute([':date' => $date]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Saldo per akun (balance_type)
            $stmt2 = $this->db->prepare("
                SELECT balance_type,
                    COALESCE(SUM(CASE WHEN category = 'Pemasukan'  THEN amount ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN category = 'Pengeluaran' THEN amount ELSE 0 END), 0) AS saldo_berjalan
                FROM finance_logs
                WHERE log_date <= :date
                GROUP BY balance_type
            ");
            $stmt2->execute([':date' => $date]);
            $saldoRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $saldo = [];
            foreach ($saldoRows as $r) {
                if ($r['balance_type']) {
                    $saldo[$r['balance_type']] = (float)$r['saldo_berjalan'];
                }
            }

            return [
                'pemasukan_hari_ini'  => (float)($row['pemasukan_hari_ini'] ?? 0),
                'pengeluaran_hari_ini' => (float)($row['pengeluaran_hari_ini'] ?? 0),
                'saldo_per_akun'       => $saldo,
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data keuangan harian tidak tersedia: ' . $e->getMessage()];
        }
    }

    /** Ringkasan keuangan bulan ini */
    private function getMonthlyFinanceSummary(): array
    {
        try {
            $firstOfMonth = date('Y-m-01');
            $stmt = $this->db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN category = 'Pemasukan'  THEN amount ELSE 0 END), 0) AS total_pemasukan,
                    COALESCE(SUM(CASE WHEN category = 'Pengeluaran' THEN amount ELSE 0 END), 0) AS total_pengeluaran
                FROM finance_logs
                WHERE log_date >= :start
            ");
            $stmt->execute([':start' => $firstOfMonth]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'total_pemasukan'   => (float)($row['total_pemasukan'] ?? 0),
                'total_pengeluaran' => (float)($row['total_pengeluaran'] ?? 0),
                'net'               => (float)(($row['total_pemasukan'] ?? 0) - ($row['total_pengeluaran'] ?? 0)),
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data keuangan bulanan tidak tersedia'];
        }
    }

    // ============================================================
    // HUTANG
    // ============================================================

    /** Hutang toko ke supplier (shop_debts) */
    private function getShopDebtSummary(): array
    {
        try {
            // Total hutang belum lunas
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*) AS jumlah_hutang,
                    COALESCE(SUM(remaining_amount), 0) AS total_sisa_hutang,
                    COALESCE(SUM(amount), 0) AS total_hutang_awal
                FROM shop_debts
                WHERE status = 'belum_lunas'
            ");
            $stmt->execute();
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);

            // Detail 5 hutang terbesar yang belum lunas
            $stmt2 = $this->db->prepare("
                SELECT sd.debt_date, sd.due_date, sd.amount, sd.remaining_amount,
                       COALESCE(s.name, sd.supplier_name_fallback, 'Tidak diketahui') AS supplier
                FROM shop_debts sd
                LEFT JOIN suppliers s ON sd.supplier_id = s.id
                WHERE sd.status = 'belum_lunas'
                ORDER BY sd.remaining_amount DESC
                LIMIT 5
            ");
            $stmt2->execute();
            $detail = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            return [
                'jumlah_belum_lunas'  => (int)($summary['jumlah_hutang'] ?? 0),
                'total_sisa_hutang'   => (float)($summary['total_sisa_hutang'] ?? 0),
                'detail_hutang_terbesar' => $detail,
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data hutang toko tidak tersedia'];
        }
    }

    /** Piutang pelanggan (customer_debts) */
    private function getCustomerDebtSummary(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*) AS jumlah_piutang,
                    COALESCE(SUM(remaining_amount), 0) AS total_sisa_piutang,
                    COALESCE(SUM(amount), 0) AS total_piutang_awal
                FROM customer_debts
                WHERE status = 'belum_lunas'
            ");
            $stmt->execute();
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);

            // Detail 5 piutang terbesar
            $stmt2 = $this->db->prepare("
                SELECT cd.debt_date, cd.due_date, cd.amount, cd.remaining_amount,
                       COALESCE(c.name, cd.customer_name_fallback, 'Tidak diketahui') AS pelanggan
                FROM customer_debts cd
                LEFT JOIN customers c ON cd.customer_id = c.id
                WHERE cd.status = 'belum_lunas'
                ORDER BY cd.remaining_amount DESC
                LIMIT 5
            ");
            $stmt2->execute();
            $detail = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            return [
                'jumlah_belum_lunas'     => (int)($summary['jumlah_piutang'] ?? 0),
                'total_sisa_piutang'     => (float)($summary['total_sisa_piutang'] ?? 0),
                'detail_piutang_terbesar' => $detail,
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data piutang pelanggan tidak tersedia'];
        }
    }

    // ============================================================
    // STOK & PRODUK
    // ============================================================

    /** Produk dengan stok menipis (<= 5 unit base) */
    private function getLowStockProducts(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT p.full_name, s.current_qty_base AS stok, p.min_stock AS stok_minimum
                FROM products p
                JOIN stock s ON p.id = s.product_id
                WHERE s.current_qty_base <= 5 AND p.is_active = 1
                ORDER BY s.current_qty_base ASC
                LIMIT 15
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /** 5 produk terlaris bulan ini berdasarkan qty terjual */
    private function getTopProducts(): array
    {
        try {
            $firstOfMonth = date('Y-m-01');
            $stmt = $this->db->prepare("
                SELECT p.full_name, SUM(si.quantity) AS qty_terjual,
                       SUM(si.total_price) AS omzet_produk
                FROM sale_items si
                JOIN sale_transactions st ON si.transaction_id = st.id
                JOIN products p ON si.product_id = p.id
                WHERE st.created_at >= :start
                GROUP BY p.id, p.full_name
                ORDER BY qty_terjual DESC
                LIMIT 5
            ");
            $stmt->execute([':start' => $firstOfMonth]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Produk stok 0 dan tidak ada restock dalam 30 hari */
    private function getInactiveProducts(): array
    {
        try {
            $lastMonth = date('Y-m-d', strtotime('-30 days'));
            $stmt = $this->db->prepare("
                SELECT p.full_name, s.current_qty_base AS stok
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

    // ============================================================
    // PEMBELIAN
    // ============================================================

    /** 10 pembelian terbaru */
    private function getLatestPurchases(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT pu.purchase_code, sup.name AS supplier,
                       pu.purchase_date, pu.grand_total, pu.payment_status
                FROM purchases pu
                LEFT JOIN suppliers sup ON pu.supplier_id = sup.id
                ORDER BY pu.purchase_date DESC
                LIMIT 10
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    // ============================================================
    // JADWAL SALES
    // ============================================================

    /** Daftar sales representative beserta jadwal kunjungan */
    private function getSalesReps(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT sr.name AS nama_sales, sup.name AS supplier,
                       sr.visit_day AS hari_kunjungan,
                       sr.delivery_day AS hari_pengiriman,
                       sr.visit_period AS periode_kunjungan,
                       sr.sales_type AS tipe_sales,
                       sr.phone AS telepon,
                       sr.status
                FROM sales_reps sr
                JOIN suppliers sup ON sr.supplier_id = sup.id
                WHERE sr.status = 'Aktif'
                ORDER BY sup.name ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    // ============================================================
    // KONSINYASI
    // ============================================================

    /** Konsinyasi aktif yang belum selesai pembayaran */
    private function getConsignments(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.consignment_date, sup.name AS supplier,
                       c.total_cost, c.total_sold, c.payment_status,
                       c.next_check_date
                FROM consignments c
                JOIN suppliers sup ON c.supplier_id = sup.id
                WHERE c.payment_status != 'Lunas'
                ORDER BY c.consignment_date DESC
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    // ============================================================
    // PELANGGAN
    // ============================================================

    /** Top pelanggan berdasarkan total belanja bulan ini */
    private function getTopCustomers(): array
    {
        try {
            $firstOfMonth = date('Y-m-01');
            $stmt = $this->db->prepare("
                SELECT COALESCE(c.name, 'Umum') AS pelanggan,
                       COUNT(st.id) AS jumlah_transaksi,
                       SUM(st.total_amount) AS total_belanja
                FROM sale_transactions st
                LEFT JOIN customers c ON st.customer_id = c.id
                WHERE st.created_at >= :start
                GROUP BY st.customer_id
                ORDER BY total_belanja DESC
                LIMIT 5
            ");
            $stmt->execute([':start' => $firstOfMonth]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /** Cek apakah string haystack mengandung salah satu keyword */
    private function contains(string $haystack, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (mb_strpos($haystack, $kw) !== false) {
                return true;
            }
        }
        return false;
    }
}
