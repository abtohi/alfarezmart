<?php
/**
 * AiContextBuilder - RAG Engine for AlfarezMart AI Chat
 *
 * Strategi pencarian:
 * 1. Pencarian produk spesifik by keyword dari pertanyaan user (INTERNAL FIRST)
 * 2. Knowledge base (ai_knowledge) — koreksi dan fakta yang dipelajari dari user
 * 3. Data operasional: omzet, keuangan, stok, hutang, jadwal sales
 * 4. AI menggunakan data internal sebagai sumber utama
 */
class AiContextBuilder
{
    /** @var \PDO */
    private $db;

    /** @var AiChatModel */
    private $knowledgeModel;

    public function __construct()
    {
        $this->db             = Database::getInstance()->getConnection();
        $this->knowledgeModel = new AiChatModel();
    }

    /**
     * Membangun system prompt lengkap dengan semua konteks data toko
     */
    public function buildSystemPrompt(string $userMessage = ''): string
    {
        $today    = date('Y-m-d');
        $keywords = $this->extractKeywords($userMessage);

        // 1. Cari produk spesifik berdasarkan kata kunci (INTERNAL FIRST)
        $productData = $this->searchProductsByKeyword($keywords);

        // 2. Cari knowledge base (koreksi user, fakta dipelajari)
        $knowledgeData = $this->knowledgeModel->searchKnowledge($keywords, 5);

        // 3. Konteks operasional toko
        $context = [
            'toko'              => ['nama' => 'AlfarezMart', 'tanggal' => date('Y-m-d H:i:s'), 'timezone' => 'Asia/Jakarta'],
            'omzet'             => $this->getOmzetSummary(),
            'keuangan_harian'   => $this->getFinanceSummary($today),
            'stok_menipis'      => $this->getLowStockProducts(),
            'produk_terlaris'   => $this->getTopProducts(),
            'hutang_toko'       => $this->getShopDebtSummary(),
            'piutang_pelanggan' => $this->getCustomerDebtSummary(),
            'jadwal_sales'      => $this->getSalesReps(),
        ];

        // 4. Tambahkan produk spesifik jika ditemukan
        if (!empty($productData)) {
            $context['data_produk_dicari'] = $productData;
        }

        // 5. Tambahkan knowledge base jika ada
        if (!empty($knowledgeData)) {
            $context['pengetahuan_toko'] = array_map(fn($k) => [
                'topik'   => $k['topic'],
                'fakta'   => $k['content'],
                'sumber'  => $k['source'],
            ], $knowledgeData);
        }

        // 6. Konteks opsional berdasarkan kata kunci
        $q = mb_strtolower($userMessage);
        if ($this->contains($q, ['beli', 'supplier', 'modal', 'pembelian', 'restock'])) {
            $context['pembelian_terbaru'] = $this->getLatestPurchases();
        }
        if ($this->contains($q, ['konsinyasi', 'titipan', 'konsinye'])) {
            $context['konsinyasi'] = $this->getConsignments();
        }
        if ($this->contains($q, ['pelanggan', 'customer', 'pembeli', 'piutang yang sudah lunas'])) {
            $context['pelanggan_aktif'] = $this->getTopCustomers();
        }
        if ($this->contains($q, ['laporan', 'bulan', 'monthly', 'bulanan', 'rekap'])) {
            $context['keuangan_bulan_ini'] = $this->getMonthlyFinanceSummary();
        }

        $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE);

        // System prompt dengan instruksi explisit: utamakan data internal
        $prompt  = "Kamu adalah AI Asisten AlfarezMart dengan akses penuh ke database toko.\n";
        $prompt .= "ATURAN PENTING:\n";
        $prompt .= "1. SELALU utamakan data dari konteks ini. Jangan cari dari internet atau sumber luar.\n";
        $prompt .= "2. Jika 'data_produk_dicari' tersedia, gunakan sebagai jawaban utama untuk pertanyaan produk.\n";
        $prompt .= "3. Jika 'pengetahuan_toko' tersedia, gunakan sebagai fakta yang sudah diverifikasi.\n";
        $prompt .= "4. Jika data tidak ada di konteks, katakan: 'Data ini tidak tersedia di sistem AlfarezMart saat ini.'\n";
        $prompt .= "5. Format: Markdown. Tebalkan angka penting. Tabel/list jika data banyak.\n";
        $prompt .= "6. Bahasa: Indonesia. Ringkas, profesional, informatif.\n\n";
        $prompt .= "DATA_TOKO=" . $contextJson . "\n";

        return $prompt;
    }

    // ============================================================
    // PENCARIAN PRODUK SPESIFIK (INTERNAL FIRST)
    // ============================================================

    /**
     * Cari produk berdasarkan kata kunci dari pesan user.
     * Mengembalikan data lengkap: nama, merk, kategori, harga beli, harga jual,
     * margin, satuan, stok, supplier.
     */
    private function searchProductsByKeyword(array $keywords): array
    {
        if (empty($keywords)) return [];

        try {
            // Buat kondisi WHERE: full_name LIKE :kw0 OR b.name LIKE :kw0 OR ...
            $conditions = [];
            $params     = [];
            foreach ($keywords as $i => $kw) {
                $k             = ':pkw' . $i;
                $conditions[]  = "(p.full_name LIKE {$k} OR b.name LIKE {$k} OR cat.name LIKE {$k})";
                $params[$k]    = '%' . $kw . '%';
            }
            $where = implode(' OR ', $conditions);

            $sql = "
                SELECT
                    p.full_name                          AS nama_produk,
                    b.name                               AS merk,
                    cat.name                             AS kategori,
                    pp.level                             AS level_kemasan,
                    u.name                               AS satuan,
                    pp.buy_price                         AS harga_beli,
                    pp.sell_price_retail                 AS harga_jual_eceran,
                    pp.sell_price_wholesale              AS harga_jual_grosir,
                    ROUND(pp.margin_retail * 100, 1)     AS margin_eceran_pct,
                    ROUND(pp.margin_wholesale * 100, 1)  AS margin_grosir_pct,
                    stk.current_qty_base                 AS stok,
                    COALESCE(s.name, sp2.name, '-')      AS supplier
                FROM products p
                LEFT JOIN brands b           ON p.brand_id    = b.id
                LEFT JOIN categories cat     ON p.category_id = cat.id
                LEFT JOIN product_packagings pp ON p.id       = pp.product_id
                LEFT JOIN units u            ON pp.unit_id    = u.id
                LEFT JOIN stock stk          ON p.id          = stk.product_id
                LEFT JOIN supplier_products sp ON p.id        = sp.product_id
                LEFT JOIN suppliers s        ON sp.supplier_id = s.id
                LEFT JOIN purchases pu       ON pu.supplier_id = s.id
                LEFT JOIN suppliers sp2      ON pu.supplier_id = sp2.id
                WHERE p.is_active = 1 AND ({$where})
                ORDER BY p.full_name ASC, pp.level ASC
                LIMIT 20
            ";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Ekstrak kata kunci bermakna dari pesan user.
     * Hilangkan stop words bahasa Indonesia.
     */
    private function extractKeywords(string $message): array
    {
        $stopWords = [
            'apa','yang','dan','di','ke','dari','untuk','ini','itu','adalah',
            'dengan','pada','atau','juga','saja','ada','bisa','kamu','saya',
            'toko','produk','berapa','tolong','coba','gimana','bagaimana',
            'apakah','kenapa','mengapa','kapan','siapa','dimana','kalau',
            'jika','bila','sudah','belum','akan','sedang','telah','sebuah',
            'setiap','semua','mana','nya','lah','pun','kah','yang','kami',
            'kita','mereka','dia','ia','harga','modal','jual','beli','stok',
            'info','informasi','data','tolong','cek','lihat','tampilkan',
            'kasih','tahu','tentang','mengenai','soal','hal','lebih','atau',
        ];

        // Normalisasi & pecah per kata
        $clean = preg_replace('/[^\w\s]/u', ' ', mb_strtolower($message));
        $words = preg_split('/\s+/', trim($clean));

        $keywords = [];
        foreach ($words as $word) {
            if (mb_strlen($word) >= 3 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }

        return array_unique(array_slice($keywords, 0, 6));
    }

    // ============================================================
    // KEUANGAN
    // ============================================================

    private function getOmzetSummary(): array
    {
        try {
            $today        = date('Y-m-d');
            $firstOfMonth = date('Y-m-01');
            $stmt = $this->db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN DATE(created_at) = :today  THEN total_amount ELSE 0 END), 0) AS omzet_hari_ini,
                    COALESCE(SUM(CASE WHEN DATE(created_at) >= :first  THEN total_amount ELSE 0 END), 0) AS omzet_bulan_ini,
                    COUNT(CASE WHEN DATE(created_at) = :today2 THEN 1 END)  AS transaksi_hari_ini,
                    COUNT(CASE WHEN DATE(created_at) >= :first2 THEN 1 END) AS transaksi_bulan_ini
                FROM sale_transactions
            ");
            $stmt->execute([':today' => $today, ':first' => $firstOfMonth, ':today2' => $today, ':first2' => $firstOfMonth]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'omzet_hari_ini'      => (float)($row['omzet_hari_ini']      ?? 0),
                'omzet_bulan_ini'     => (float)($row['omzet_bulan_ini']      ?? 0),
                'transaksi_hari_ini'  => (int)  ($row['transaksi_hari_ini']   ?? 0),
                'transaksi_bulan_ini' => (int)  ($row['transaksi_bulan_ini']  ?? 0),
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data omzet tidak tersedia'];
        }
    }

    /**
     * Pemasukan & pengeluaran harian dari finance_logs.
     * Kolom: `category` = 'Pemasukan' / 'Pengeluaran' (BUKAN 'type').
     */
    private function getFinanceSummary(string $date): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN category = 'Pemasukan'   THEN amount ELSE 0 END), 0) AS pemasukan_hari_ini,
                    COALESCE(SUM(CASE WHEN category = 'Pengeluaran' THEN amount ELSE 0 END), 0) AS pengeluaran_hari_ini
                FROM finance_logs
                WHERE log_date = :date
            ");
            $stmt->execute([':date' => $date]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Saldo akumulatif per akun
            $stmt2 = $this->db->prepare("
                SELECT balance_type,
                    COALESCE(SUM(CASE WHEN category='Pemasukan'   THEN amount ELSE 0 END),0) -
                    COALESCE(SUM(CASE WHEN category='Pengeluaran' THEN amount ELSE 0 END),0) AS saldo
                FROM finance_logs
                WHERE log_date <= :date
                GROUP BY balance_type
            ");
            $stmt2->execute([':date' => $date]);
            $saldo = [];
            foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $r) {
                if ($r['balance_type']) $saldo[$r['balance_type']] = (float)$r['saldo'];
            }

            return [
                'pemasukan_hari_ini'   => (float)($row['pemasukan_hari_ini']   ?? 0),
                'pengeluaran_hari_ini' => (float)($row['pengeluaran_hari_ini'] ?? 0),
                'saldo_per_akun'       => $saldo,
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data keuangan tidak tersedia: ' . $e->getMessage()];
        }
    }

    private function getMonthlyFinanceSummary(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN category='Pemasukan'   THEN amount ELSE 0 END),0) AS total_pemasukan,
                    COALESCE(SUM(CASE WHEN category='Pengeluaran' THEN amount ELSE 0 END),0) AS total_pengeluaran
                FROM finance_logs
                WHERE log_date >= :start
            ");
            $stmt->execute([':start' => date('Y-m-01')]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'total_pemasukan'   => (float)($row['total_pemasukan']   ?? 0),
                'total_pengeluaran' => (float)($row['total_pengeluaran'] ?? 0),
                'net'               => (float)(($row['total_pemasukan'] ?? 0) - ($row['total_pengeluaran'] ?? 0)),
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data keuangan bulanan tidak tersedia'];
        }
    }

    // ============================================================
    // HUTANG & PIUTANG
    // ============================================================

    private function getShopDebtSummary(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS jumlah, COALESCE(SUM(remaining_amount),0) AS total_sisa
                FROM shop_debts WHERE status = 'belum_lunas'
            ");
            $stmt->execute();
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt2 = $this->db->prepare("
                SELECT sd.debt_date, sd.due_date, sd.amount, sd.remaining_amount,
                       COALESCE(s.name, sd.supplier_name_fallback, 'Tidak diketahui') AS supplier
                FROM shop_debts sd
                LEFT JOIN suppliers s ON sd.supplier_id = s.id
                WHERE sd.status = 'belum_lunas'
                ORDER BY sd.remaining_amount DESC LIMIT 5
            ");
            $stmt2->execute();

            return [
                'jumlah_belum_lunas'     => (int)  ($summary['jumlah']     ?? 0),
                'total_sisa_hutang'      => (float) ($summary['total_sisa'] ?? 0),
                'detail_hutang_terbesar' => $stmt2->fetchAll(PDO::FETCH_ASSOC),
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data hutang toko tidak tersedia'];
        }
    }

    private function getCustomerDebtSummary(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS jumlah, COALESCE(SUM(remaining_amount),0) AS total_sisa
                FROM customer_debts WHERE status = 'belum_lunas'
            ");
            $stmt->execute();
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt2 = $this->db->prepare("
                SELECT cd.debt_date, cd.due_date, cd.amount, cd.remaining_amount,
                       COALESCE(c.name, cd.customer_name_fallback, 'Tidak diketahui') AS pelanggan
                FROM customer_debts cd
                LEFT JOIN customers c ON cd.customer_id = c.id
                WHERE cd.status = 'belum_lunas'
                ORDER BY cd.remaining_amount DESC LIMIT 5
            ");
            $stmt2->execute();

            return [
                'jumlah_belum_lunas'      => (int)   ($summary['jumlah']     ?? 0),
                'total_sisa_piutang'      => (float)  ($summary['total_sisa'] ?? 0),
                'detail_piutang_terbesar' => $stmt2->fetchAll(PDO::FETCH_ASSOC),
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data piutang pelanggan tidak tersedia'];
        }
    }

    // ============================================================
    // STOK & PRODUK
    // ============================================================

    private function getLowStockProducts(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT p.full_name, s.current_qty_base AS stok, p.min_stock AS stok_minimum
                FROM products p
                JOIN stock s ON p.id = s.product_id
                WHERE s.current_qty_base <= 5 AND p.is_active = 1
                ORDER BY s.current_qty_base ASC LIMIT 15
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function getTopProducts(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.full_name, SUM(si.quantity) AS qty_terjual, SUM(si.total_price) AS omzet_produk
                FROM sale_items si
                JOIN sale_transactions st ON si.transaction_id = st.id
                JOIN products p ON si.product_id = p.id
                WHERE st.created_at >= :start
                GROUP BY p.id, p.full_name
                ORDER BY qty_terjual DESC LIMIT 5
            ");
            $stmt->execute([':start' => date('Y-m-01')]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function getInactiveProducts(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.full_name, s.current_qty_base AS stok
                FROM products p
                JOIN stock s ON p.id = s.product_id
                WHERE s.current_qty_base = 0 AND p.is_active = 1
                AND p.id NOT IN (
                    SELECT pi.product_id FROM purchase_items pi
                    JOIN purchases pu ON pi.purchase_id = pu.id
                    WHERE pu.purchase_date >= :last_month
                )
                LIMIT 10
            ");
            $stmt->execute([':last_month' => date('Y-m-d', strtotime('-30 days'))]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    // ============================================================
    // PEMBELIAN, SALES, KONSINYASI, PELANGGAN
    // ============================================================

    private function getLatestPurchases(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT pu.purchase_code, sup.name AS supplier,
                       pu.purchase_date, pu.grand_total, pu.payment_status
                FROM purchases pu
                LEFT JOIN suppliers sup ON pu.supplier_id = sup.id
                ORDER BY pu.purchase_date DESC LIMIT 10
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function getSalesReps(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT sr.name AS nama_sales, sup.name AS supplier,
                       sr.visit_day AS hari_kunjungan, sr.delivery_day AS hari_pengiriman,
                       sr.visit_period AS periode, sr.phone AS telepon, sr.status
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

    private function getConsignments(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.consignment_date, sup.name AS supplier,
                       c.total_cost, c.total_sold, c.payment_status, c.next_check_date
                FROM consignments c
                JOIN suppliers sup ON c.supplier_id = sup.id
                WHERE c.payment_status != 'Lunas'
                ORDER BY c.consignment_date DESC LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function getTopCustomers(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COALESCE(c.name, 'Umum') AS pelanggan,
                       COUNT(st.id) AS transaksi, SUM(st.total_amount) AS total_belanja
                FROM sale_transactions st
                LEFT JOIN customers c ON st.customer_id = c.id
                WHERE st.created_at >= :start
                GROUP BY st.customer_id
                ORDER BY total_belanja DESC LIMIT 5
            ");
            $stmt->execute([':start' => date('Y-m-01')]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function contains(string $haystack, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (mb_strpos($haystack, $kw) !== false) return true;
        }
        return false;
    }
}
