<?php
/**
 * AiContextBuilder v3.0 - Comprehensive RAG Engine for AlfarezMart AI Chat
 *
 * Strategi "INTERNAL FIRST":
 * 1. Selalu sertakan analitik bisnis lengkap, finansial, stok, hutang
 * 2. Cari produk spesifik dari DB jika user menyebut nama produk/merk
 * 3. Ambil knowledge yang sudah dipelajari dari ai_knowledge
 * 4. Sertakan panduan fitur aplikasi agar AI bisa membantu navigasi
 * 5. AI DILARANG mencari dari internet jika data tersedia di konteks
 *
 * Tabel yang dicakup:
 * products, product_packagings, product_qty_prices, brands, categories,
 * units, stock, stock_movements, sale_transactions, sale_items,
 * purchases, purchase_items, suppliers, supplier_types, supplier_products,
 * sales_reps, customers, customer_types, customer_debts, customer_debt_payments,
 * shop_debts, shop_debt_payments, debt_sources, consignments, consignment_items,
 * finance_logs, finance_accounts, finance_categories, order_estimates,
 * order_estimate_items, app_settings, ai_knowledge, chat_history
 *
 * @version 3.0
 * @updated 2026-06-05
 * @see docs/ai-instructions.md untuk panduan pengembang
 */
class AiContextBuilder
{
    const VERSION      = '3.0';
    const SCHEMA_DATE  = '2026-06-05';

    /** @var \PDO */
    private $db;

    /** @var AiChatModel */
    private $knowledgeModel;

    public function __construct()
    {
        $this->db             = Database::getInstance()->getConnection();
        $this->knowledgeModel = new AiChatModel();
    }

    // ================================================================
    // ENTRY POINT UTAMA
    // ================================================================

    /**
     * Membangun system prompt lengkap untuk setiap request AI.
     */
    /**
     * Membangun system prompt LEAN untuk setiap request AI.
     *
     * Strategi v4.0 "SQL-First":
     * - Konteks minimal (~1500 token) agar tidak melebihi batas model gratis
     * - AI mengandalkan SQL query mandiri untuk ambil data dari database
     * - Hanya inject: tanggal, knowledge base hits, produk yang disebut user
     * - Feature guide hanya disertakan saat user bertanya soal cara pakai
     */
    public function buildSystemPrompt(string $userMessage = ''): string
    {
        $q        = mb_strtolower($userMessage);
        $keywords = $this->extractKeywords($userMessage);

        // --- KONTEKS MINIMAL (hemat token, total ~500-1500 token) ---
        $ctx = [];
        $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        $ctx[] = "Tanggal: " . date('Y-m-d') . " (" . $hari[(int)date('w')] . ")";

        // 1. Knowledge base (fakta terverifikasi user) — sangat kecil, sudah difilter
        if (!empty($keywords)) {
            try {
                $hits = $this->knowledgeModel->searchKnowledge($keywords, 3);
                if (!empty($hits)) {
                    $ctx[] = "\n## FAKTA TOKO (terverifikasi)";
                    foreach ($hits as $k) {
                        $ctx[] = "- {$k['topic']}: {$k['content']}";
                    }
                }
            } catch (Throwable $e) {}
        }

        // 2. Produk spesifik yang disebut user — hanya jika relevan
        if (!empty($keywords)) {
            try {
                $products = $this->searchProductsByKeyword($keywords);
                if (!empty($products)) {
                    $ctx[] = "\n## PRODUK DITEMUKAN";
                    $seen = [];
                    foreach (array_slice($products, 0, 5) as $p) {
                        $nama = $p['nama'] ?? '?';
                        if (isset($seen[$nama])) continue;
                        $seen[$nama] = true;
                        $parts = [$nama];
                        if (!empty($p['harga_jual_eceran'])) $parts[] = "Jual:Rp" . number_format((int)$p['harga_jual_eceran'], 0, ',', '.');
                        if (!empty($p['harga_beli'])) $parts[] = "Modal:Rp" . number_format((int)$p['harga_beli'], 0, ',', '.');
                        if (isset($p['stok'])) $parts[] = "Stok:{$p['stok']}";
                        if (!empty($p['supplier']) && $p['supplier'] !== '-') $parts[] = "Sup:{$p['supplier']}";
                        $ctx[] = "- " . implode(' | ', $parts);
                    }
                }
            } catch (Throwable $e) {}
        }

        // 3. Panduan fitur — hanya saat user bertanya cara pakai
        if ($this->contains($q, ['cara', 'fitur', 'bagaimana', 'gimana', 'panduan', 'tutorial', 'menu', 'tombol', 'setting', 'pengaturan'])) {
            $ctx[] = "\n" . $this->getFeatureGuide();
        }

        // --- SYSTEM PROMPT ULTRA-RINGKAS ---
        $prompt  = "Kamu AI Asisten toko AlfarezMart. Bahasa Indonesia, akurat, singkat.\n\n";
        $prompt .= "ATURAN:\n";
        $prompt .= "1. Jawab dari DATA di bawah jika tersedia. Dilarang menebak harga/angka.\n";
        $prompt .= "2. Jika data TIDAK ADA di bawah, WAJIB query database:\n";
        $prompt .= "   [SQL_QUERY] SELECT ... FROM ... LIMIT 50 [/SQL_QUERY]\n";
        $prompt .= "   HANYA tag itu saja, TANPA kalimat tambahan apapun.\n";
        $prompt .= "3. DILARANG bilang 'tidak tahu' / 'tidak memiliki data' sebelum mencoba SQL.\n";
        $prompt .= "4. JIKA SQL_RESULT KOSONG ([]), itu berarti data memang tidak ada. Langsung jawab ke user bahwa data tidak ditemukan. DILARANG membuat SQL_QUERY lagi.\n";
        $prompt .= "5. Output: Markdown rapi, angka penting di-**bold**.\n\n";

        if (!empty($ctx)) {
            $prompt .= implode("\n", $ctx) . "\n\n";
        }

        $prompt .= $this->getDatabaseSchema();

        return $prompt;
    }

    // ================================================================
    // PENGATURAN TOKO
    // ================================================================

    private function getStoreSettings(): array
    {
        try {
            $stmt = $this->db->query("SELECT setting_key, setting_value FROM app_settings");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $settings = [];
            foreach ($rows as $r) {
                $settings[$r['setting_key']] = $r['setting_value'];
            }
            return [
                'nama'              => $settings['store_name']              ?? 'AlfarezMart',
                'alamat'            => $settings['store_address']           ?? '-',
                'telepon'           => $settings['store_phone']             ?? '-',
                'margin_eceran'     => $settings['default_margin_retail']   ?? '0.15',
                'margin_grosir'     => $settings['default_margin_wholesale'] ?? '0.08',
                'ai_context_ver'    => self::VERSION,
            ];
        } catch (Throwable $e) {
            return ['nama' => 'AlfarezMart', 'ai_context_ver' => self::VERSION];
        }
    }

    // ================================================================
    // ANALITIK BISNIS KOMPREHENSIF
    // ================================================================

    /**
     * Omzet hari ini, minggu ini, bulan ini, bulan lalu, pertumbuhan, rata-rata harian.
     */
    private function getBusinessAnalytics(): array
    {
        try {
            $today       = date('Y-m-d');
            $startWeek   = date('Y-m-d', strtotime('monday this week'));
            $startMonth  = date('Y-m-01');
            $startLM     = date('Y-m-01', strtotime('-1 month'));
            $endLM       = date('Y-m-t',  strtotime('-1 month'));
            $daysElapsed = (int)date('j');

            $stmt = $this->db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN DATE(created_at) = :t1  THEN total_amount ELSE 0 END),0) AS omzet_hari,
                    COALESCE(SUM(CASE WHEN DATE(created_at) >= :w1 THEN total_amount ELSE 0 END),0) AS omzet_minggu,
                    COALESCE(SUM(CASE WHEN DATE(created_at) >= :m1 THEN total_amount ELSE 0 END),0) AS omzet_bulan,
                    COALESCE(SUM(CASE WHEN DATE(created_at) BETWEEN :lm1 AND :lm2 THEN total_amount ELSE 0 END),0) AS omzet_lalu,
                    COUNT(CASE WHEN DATE(created_at) = :t2  THEN 1 END) AS tx_hari,
                    COUNT(CASE WHEN DATE(created_at) >= :w2 THEN 1 END) AS tx_minggu,
                    COUNT(CASE WHEN DATE(created_at) >= :m2 THEN 1 END) AS tx_bulan
                FROM sale_transactions
            ");
            $stmt->execute([
                ':t1'=>$today,':t2'=>$today,
                ':w1'=>$startWeek,':w2'=>$startWeek,
                ':m1'=>$startMonth,':m2'=>$startMonth,
                ':lm1'=>$startLM,':lm2'=>$endLM,
            ]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);

            $growth  = ($r['omzet_lalu'] > 0)
                ? round((($r['omzet_bulan'] - $r['omzet_lalu']) / $r['omzet_lalu']) * 100, 1)
                : 0;
            $avgDaily = $daysElapsed > 0 ? (int)($r['omzet_bulan'] / $daysElapsed) : 0;

            return [
                'omzet_hari_ini'      => (int)$r['omzet_hari'],
                'omzet_minggu_ini'    => (int)$r['omzet_minggu'],
                'omzet_bulan_ini'     => (int)$r['omzet_bulan'],
                'omzet_bulan_lalu'    => (int)$r['omzet_lalu'],
                'pertumbuhan_pct'     => $growth,
                'rata_rata_harian'    => $avgDaily,
                'tx_hari_ini'         => (int)$r['tx_hari'],
                'tx_minggu_ini'       => (int)$r['tx_minggu'],
                'tx_bulan_ini'        => (int)$r['tx_bulan'],
            ];
        } catch (Throwable $e) {
            return ['error' => 'Analytics tidak tersedia'];
        }
    }

    /**
     * Laba bersih dari kolom profit di sale_items.
     */
    private function getProfitSummary(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN DATE(st.created_at) = :t1 THEN si.profit ELSE 0 END),0) AS laba_hari,
                    COALESCE(SUM(CASE WHEN DATE(st.created_at) >= :m1 THEN si.profit ELSE 0 END),0) AS laba_bulan,
                    COALESCE(SUM(CASE WHEN DATE(st.created_at) >= :lm1 AND DATE(st.created_at) <= :lm2 THEN si.profit ELSE 0 END),0) AS laba_bulan_lalu
                FROM sale_items si
                JOIN sale_transactions st ON si.transaction_id = st.id
            ");
            $stmt->execute([
                ':t1' => date('Y-m-d'),
                ':m1' => date('Y-m-01'),
                ':lm1' => date('Y-m-01', strtotime('-1 month')),
                ':lm2' => date('Y-m-t',  strtotime('-1 month')),
            ]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'laba_hari_ini'    => (int)$r['laba_hari'],
                'laba_bulan_ini'   => (int)$r['laba_bulan'],
                'laba_bulan_lalu'  => (int)$r['laba_bulan_lalu'],
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data laba tidak tersedia'];
        }
    }

    // ================================================================
    // KEUANGAN
    // ================================================================

    /**
     * Snapshot keuangan hari ini: pemasukan, pengeluaran, saldo per akun.
     * KOLOM: category = 'Pemasukan' / 'Pengeluaran' (BUKAN 'type')
     */
    private function getFinancialSnapshot(string $date): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN category='Pemasukan'   THEN amount ELSE 0 END),0) AS pemasukan,
                    COALESCE(SUM(CASE WHEN category='Pengeluaran' THEN amount ELSE 0 END),0) AS pengeluaran
                FROM finance_logs WHERE log_date = :date
            ");
            $stmt->execute([':date' => $date]);
            $today = $stmt->fetch(PDO::FETCH_ASSOC);

            // Saldo akumulatif per akun (balance_type)
            $stmt2 = $this->db->prepare("
                SELECT balance_type,
                    COALESCE(SUM(CASE WHEN category='Pemasukan'   THEN amount ELSE 0 END),0) -
                    COALESCE(SUM(CASE WHEN category='Pengeluaran' THEN amount ELSE 0 END),0) AS saldo
                FROM finance_logs WHERE log_date <= :date
                GROUP BY balance_type
            ");
            $stmt2->execute([':date' => $date]);
            $saldo = [];
            foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $r) {
                if ($r['balance_type']) $saldo[$r['balance_type']] = (int)$r['saldo'];
            }

            return [
                'pemasukan_hari_ini'   => (int)($today['pemasukan']   ?? 0),
                'pengeluaran_hari_ini' => (int)($today['pengeluaran'] ?? 0),
                'net_hari_ini'         => (int)(($today['pemasukan'] ?? 0) - ($today['pengeluaran'] ?? 0)),
                'saldo_per_akun'       => $saldo,
            ];
        } catch (Throwable $e) {
            return ['error' => 'Snapshot keuangan tidak tersedia'];
        }
    }

    private function getMonthlyFinanceSummary(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN category='Pemasukan'   THEN amount ELSE 0 END),0) AS total_masuk,
                    COALESCE(SUM(CASE WHEN category='Pengeluaran' THEN amount ELSE 0 END),0) AS total_keluar
                FROM finance_logs WHERE log_date >= :s
            ");
            $stmt->execute([':s' => date('Y-m-01')]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'total_pemasukan'   => (int)$r['total_masuk'],
                'total_pengeluaran' => (int)$r['total_keluar'],
                'net'               => (int)(($r['total_masuk'] ?? 0) - ($r['total_keluar'] ?? 0)),
            ];
        } catch (Throwable $e) {
            return ['error' => 'Laporan bulanan tidak tersedia'];
        }
    }

    /** 10 log keuangan terbaru */
    private function getRecentFinanceLogs(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT log_date, category, amount, balance_type, detail, description
                FROM finance_logs ORDER BY log_date DESC, id DESC LIMIT 10
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Saldo berjalan semua akun keuangan */
    private function getFinanceAccountsBalance(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT fl.balance_type AS akun,
                    COALESCE(SUM(CASE WHEN fl.category='Pemasukan'   THEN fl.amount ELSE 0 END),0) -
                    COALESCE(SUM(CASE WHEN fl.category='Pengeluaran' THEN fl.amount ELSE 0 END),0) AS saldo_berjalan
                FROM finance_logs fl
                GROUP BY fl.balance_type
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Breakdown penjualan per metode pembayaran hari ini */
    private function getPaymentBreakdown(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT payment_method, COUNT(*) AS jumlah, COALESCE(SUM(total_amount),0) AS total
                FROM sale_transactions
                WHERE DATE(created_at) >= :start
                GROUP BY payment_method ORDER BY total DESC
            ");
            $stmt->execute([':start' => date('Y-m-01')]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    // ================================================================
    // STOK & INVENTORI
    // ================================================================

    /** Gambaran umum kesehatan stok seluruh produk aktif */
    private function getStockOverview(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT
                    COUNT(DISTINCT p.id)                                                               AS total_produk_aktif,
                    SUM(CASE WHEN s.current_qty_base = 0 THEN 1 ELSE 0 END)                          AS stok_habis,
                    SUM(CASE WHEN s.current_qty_base > 0 AND s.current_qty_base <= p.min_stock AND p.min_stock > 0 THEN 1 ELSE 0 END) AS stok_kritis,
                    SUM(CASE WHEN s.current_qty_base > GREATEST(p.min_stock,0) AND s.current_qty_base <= 5 THEN 1 ELSE 0 END) AS stok_rendah,
                    SUM(CASE WHEN s.current_qty_base > 5 THEN 1 ELSE 0 END)                           AS stok_aman
                FROM products p
                LEFT JOIN stock s ON p.id = s.product_id
                WHERE p.is_active = 1 AND p.code != 'CUSTOM'
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return ['error' => 'Stock overview tidak tersedia'];
        }
    }

    /** Produk dengan stok <= 5 (prioritas restock) */
    private function getLowStockProducts(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT p.full_name, b.name AS merk, s.current_qty_base AS stok, p.min_stock AS min
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                JOIN stock s ON p.id = s.product_id
                WHERE s.current_qty_base <= 5 AND p.is_active = 1 AND p.code != 'CUSTOM'
                ORDER BY s.current_qty_base ASC LIMIT 15
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Produk dengan tanggal expiry < 30 hari ke depan */
    private function getExpiryAlerts(): array
    {
        try {
            $limit = date('Y-m-d', strtotime('+30 days'));
            $stmt  = $this->db->prepare("
                SELECT p.full_name, s.nearest_expiry, s.current_qty_base AS stok
                FROM products p
                JOIN stock s ON p.id = s.product_id
                WHERE s.nearest_expiry IS NOT NULL
                AND s.nearest_expiry <= :limit
                AND s.nearest_expiry >= CURDATE()
                ORDER BY s.nearest_expiry ASC LIMIT 10
            ");
            $stmt->execute([':limit' => $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Ringkasan pergerakan stok 7 hari terakhir */
    private function getStockMovementsSummary(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT movement_type, COUNT(*) AS jumlah, SUM(ABS(quantity)) AS total_qty
                FROM stock_movements
                WHERE created_at >= :start
                GROUP BY movement_type
            ");
            $stmt->execute([':start' => date('Y-m-d', strtotime('-7 days'))]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    // ================================================================
    // PRODUK & KATALOG
    // ================================================================

    /**
     * Cari produk dari pesan user — INTERNAL FIRST.
     * Mengembalikan info lengkap: harga beli, harga jual, margin, stok, supplier.
     */
    private function searchProductsByKeyword(array $keywords): array
    {
        if (empty($keywords)) return [];
        try {
            $conditions = [];
            $params     = [];
            foreach ($keywords as $i => $kw) {
                $k             = ':pkw' . $i;
                $conditions[]  = "(p.full_name LIKE {$k} OR b.name LIKE {$k} OR cat.name LIKE {$k} OR p.invoice_name LIKE {$k})";
                $params[$k]    = '%' . $kw . '%';
            }
            $where = implode(' OR ', $conditions);

            $stmt = $this->db->prepare("
                SELECT
                    p.full_name                          AS nama,
                    p.code                               AS kode,
                    b.name                               AS merk,
                    cat.name                             AS kategori,
                    pp.level                             AS level_kemasan,
                    u.name                               AS satuan,
                    pp.buy_price                         AS harga_beli,
                    pp.sell_price_retail                 AS harga_jual_eceran,
                    pp.sell_price_wholesale              AS harga_jual_grosir,
                    ROUND(pp.margin_retail  * 100, 1)   AS margin_eceran_pct,
                    ROUND(pp.margin_wholesale*100, 1)   AS margin_grosir_pct,
                    stk.current_qty_base                 AS stok,
                    stk.nearest_expiry                   AS exp_terdekat,
                    COALESCE(s.name, '-')                AS supplier
                FROM products p
                LEFT JOIN brands b           ON p.brand_id    = b.id
                LEFT JOIN categories cat     ON p.category_id = cat.id
                LEFT JOIN product_packagings pp ON p.id       = pp.product_id
                LEFT JOIN units u            ON pp.unit_id    = u.id
                LEFT JOIN stock stk          ON p.id          = stk.product_id
                LEFT JOIN supplier_products sp ON p.id        = sp.product_id
                LEFT JOIN suppliers s        ON sp.supplier_id = s.id
                WHERE p.is_active = 1 AND p.code != 'CUSTOM' AND ({$where})
                ORDER BY p.full_name ASC, pp.level ASC
                LIMIT 20
            ");
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Ringkasan katalog: jumlah produk per kategori & range harga */
    private function getCatalogSummary(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT
                    COALESCE(cat.name,'Tanpa Kategori') AS kategori,
                    COUNT(DISTINCT p.id) AS jumlah_produk,
                    MIN(pp.buy_price)    AS harga_beli_min,
                    MAX(pp.buy_price)    AS harga_beli_max,
                    MIN(pp.sell_price_retail) AS harga_jual_min,
                    MAX(pp.sell_price_retail) AS harga_jual_max
                FROM products p
                LEFT JOIN categories cat     ON p.category_id = cat.id
                LEFT JOIN product_packagings pp ON p.id = pp.product_id AND pp.level = 1
                WHERE p.is_active = 1 AND p.code != 'CUSTOM'
                GROUP BY cat.id, cat.name
                ORDER BY jumlah_produk DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /** 5 produk terlaris bulan ini */
    private function getTopProducts(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.full_name AS nama, b.name AS merk,
                       SUM(si.quantity) AS qty_terjual,
                       SUM(si.total_price) AS omzet,
                       SUM(si.profit) AS laba
                FROM sale_items si
                JOIN sale_transactions st ON si.transaction_id = st.id
                JOIN products p ON si.product_id = p.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE st.created_at >= :s AND p.code != 'CUSTOM'
                GROUP BY p.id, p.full_name, b.name
                ORDER BY qty_terjual DESC LIMIT 5
            ");
            $stmt->execute([':s' => date('Y-m-01')]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Produk stok = 0 dan tidak ada pembelian 30 hari (dead stock) */
    private function getInactiveProducts(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.full_name AS nama, s.current_qty_base AS stok,
                       s.last_restock_date AS terakhir_restock
                FROM products p
                JOIN stock s ON p.id = s.product_id
                WHERE s.current_qty_base = 0 AND p.is_active = 1 AND p.code != 'CUSTOM'
                AND p.id NOT IN (
                    SELECT pi.product_id FROM purchase_items pi
                    JOIN purchases pu ON pi.purchase_id = pu.id
                    WHERE pu.purchase_date >= :d
                )
                LIMIT 10
            ");
            $stmt->execute([':d' => date('Y-m-d', strtotime('-30 days'))]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Semua merk/brand yang ada di katalog */
    private function getBrandsSummary(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT b.name AS merk, COUNT(p.id) AS jumlah_produk
                FROM brands b
                LEFT JOIN products p ON p.brand_id = b.id AND p.is_active = 1
                GROUP BY b.id, b.name
                ORDER BY jumlah_produk DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    // ================================================================
    // HUTANG & PIUTANG
    // ================================================================

    /** Hutang toko ke supplier (shop_debts) */
    private function getShopDebtSummary(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS jumlah, COALESCE(SUM(remaining_amount),0) AS total_sisa
                FROM shop_debts WHERE status='belum_lunas'
            ");
            $stmt->execute();
            $s = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt2 = $this->db->prepare("
                SELECT sd.debt_date, sd.due_date, sd.amount, sd.remaining_amount,
                       COALESCE(sup.name, sd.supplier_name_fallback,'?') AS supplier,
                       COALESCE(ds.name,'-') AS sumber_hutang
                FROM shop_debts sd
                LEFT JOIN suppliers sup ON sd.supplier_id = sup.id
                LEFT JOIN debt_sources ds ON sd.debt_source_id = ds.id
                WHERE sd.status='belum_lunas'
                ORDER BY sd.remaining_amount DESC LIMIT 8
            ");
            $stmt2->execute();

            return [
                'jumlah_belum_lunas'     => (int)$s['jumlah'],
                'total_sisa'             => (int)$s['total_sisa'],
                'detail'                 => $stmt2->fetchAll(PDO::FETCH_ASSOC),
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
                SELECT COUNT(*) AS jumlah, COALESCE(SUM(remaining_amount),0) AS total_sisa
                FROM customer_debts WHERE status='belum_lunas'
            ");
            $stmt->execute();
            $s = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt2 = $this->db->prepare("
                SELECT cd.debt_date, cd.due_date, cd.amount, cd.remaining_amount, cd.notes,
                       COALESCE(c.name, cd.customer_name_fallback,'?') AS pelanggan
                FROM customer_debts cd
                LEFT JOIN customers c ON cd.customer_id = c.id
                WHERE cd.status='belum_lunas'
                ORDER BY cd.remaining_amount DESC LIMIT 8
            ");
            $stmt2->execute();

            return [
                'jumlah_belum_lunas'  => (int)$s['jumlah'],
                'total_sisa'          => (int)$s['total_sisa'],
                'detail'              => $stmt2->fetchAll(PDO::FETCH_ASSOC),
            ];
        } catch (Throwable $e) {
            return ['error' => 'Data piutang tidak tersedia'];
        }
    }

    // ================================================================
    // SUPPLIER & SALES
    // ================================================================

    /** Ringkasan supplier aktif dengan total belanja & produk */
    private function getSuppliersSummary(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT sup.name AS supplier, st.name AS tipe,
                       COUNT(DISTINCT sp.product_id) AS jumlah_produk,
                       MAX(pu.purchase_date) AS terakhir_beli,
                       COALESCE(SUM(pu.grand_total),0) AS total_belanja_semua
                FROM suppliers sup
                LEFT JOIN supplier_types st ON sup.type_id = st.id
                LEFT JOIN supplier_products sp ON sp.supplier_id = sup.id
                LEFT JOIN purchases pu ON pu.supplier_id = sup.id
                WHERE sup.is_active = 1
                GROUP BY sup.id, sup.name, st.name
                ORDER BY total_belanja_semua DESC
                LIMIT 15
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Semua sales rep aktif & jadwal kunjungan */
    private function getSalesReps(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT sr.name AS sales, sup.name AS supplier,
                       sr.visit_day AS hari_kunjungan,
                       sr.delivery_day AS hari_pengiriman,
                       sr.visit_period AS periode,
                       sr.sales_type AS tipe,
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

    // ================================================================
    // PEMBELIAN
    // ================================================================

    /** 10 pembelian terbaru dengan supplier & status bayar */
    private function getLatestPurchases(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT pu.purchase_code, sup.name AS supplier, sr.name AS sales,
                       pu.purchase_date, pu.grand_total, pu.payment_status, pu.total_items
                FROM purchases pu
                LEFT JOIN suppliers sup ON pu.supplier_id = sup.id
                LEFT JOIN sales_reps sr ON pu.sales_rep_id = sr.id
                ORDER BY pu.purchase_date DESC LIMIT 10
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    // ================================================================
    // KONSINYASI
    // ================================================================

    /** Konsinyasi belum lunas */
    private function getConsignments(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT sup.name AS supplier, c.consignment_date,
                       c.total_cost, c.total_sold, c.total_returned,
                       c.payment_status, c.next_check_date, c.notes
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

    // ================================================================
    // PELANGGAN
    // ================================================================

    /** Top pelanggan berdasarkan total belanja bulan ini */
    private function getTopCustomers(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COALESCE(c.name,'Umum') AS pelanggan, ct.name AS tipe,
                       COUNT(st.id) AS jumlah_tx, SUM(st.total_amount) AS total
                FROM sale_transactions st
                LEFT JOIN customers c ON st.customer_id = c.id
                LEFT JOIN customer_types ct ON c.type_id = ct.id
                WHERE st.created_at >= :s
                GROUP BY st.customer_id
                ORDER BY total DESC LIMIT 8
            ");
            $stmt->execute([':s' => date('Y-m-01')]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    // ================================================================
    // ESTIMASI ORDER
    // ================================================================

    /** Estimasi order terbaru */
    private function getOrderEstimates(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT oe.title, sup.name AS supplier, oe.total_amount,
                       oe.created_at,
                       (SELECT COUNT(*) FROM order_estimate_items WHERE estimate_id = oe.id) AS jumlah_item
                FROM order_estimates oe
                LEFT JOIN suppliers sup ON oe.supplier_id = sup.id
                ORDER BY oe.created_at DESC LIMIT 5
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    // ================================================================
    // FEATURE GUIDE (static text — panduan fitur aplikasi)
    // ================================================================

    /**
     * Panduan singkat semua fitur aplikasi AlfarezMart.
     * Digunakan AI untuk membantu user yang bingung cara penggunaan.
     */
    private function getFeatureGuide(): string
    {
        return "=== PANDUAN_FITUR_ALFAREZMART ===\n" .
"1. DASHBOARD: Ringkasan real-time omzet, transaksi, stok kritis, hutang. " .
"Akses fitur via menu bawah (mobile) atau sidebar.\n" .
"2. PRODUK (/products): Kelola katalog produk.\n" .
"   - Tambah: klik tombol '+' atau 'Tambah Produk'\n" .
"   - Edit: klik nama produk → Edit\n" .
"   - Kemasan: 1 produk bisa banyak level (Pcs/Box/Karton). " .
"Atur harga beli & jual per level kemasan\n" .
"   - Harga: ada 2 tier (Eceran & Grosir). Bisa tambah harga per qty (diskon bertingkat)\n" .
"   - Barcode: isi di form kemasan, bisa scan atau generate\n" .
"3. KASIR/POS (/sales/pos): Proses transaksi penjualan.\n" .
"   - Cari produk: ketik nama atau scan barcode\n" .
"   - Pilih mode: Eceran atau Grosir\n" .
"   - Konfirmasi & pilih metode bayar (Cash/Transfer/QRIS)\n" .
"   - Struk: otomatis tampil, bisa cetak via printer thermal 58mm\n" .
"4. PENJUALAN (/sales): Riwayat semua transaksi. Filter by tanggal. " .
"Klik transaksi untuk lihat detail/invoice. Bisa hapus/edit\n" .
"5. PEMBELIAN (/purchases): Catat pembelian dari supplier.\n" .
"   - Tambah: pilih supplier, cari produk, isi qty & harga beli\n" .
"   - Stok otomatis bertambah saat pembelian disimpan\n" .
"   - Upload foto faktur sebagai bukti\n" .
"6. KEUANGAN (/finance): Pencatatan keuangan harian.\n" .
"   - Catat pemasukan/pengeluaran per akun (Uang Laci, Saldo Utama, dll)\n" .
"   - Lihat saldo berjalan & riwayat per akun\n" .
"   - Akun keuangan & kategori bisa dikelola di menu Finance\n" .
"7. HUTANG (/debts):\n" .
"   - Piutang Pelanggan: pelanggan berhutang ke toko. Catat cicilan.\n" .
"   - Hutang Toko: toko berhutang ke supplier. Catat cicilan.\n" .
"   - Filter: belum lunas / lunas. Bisa cari by nama\n" .
"8. SUPPLIER (/suppliers): Kelola pemasok.\n" .
"   - Tambah supplier & tipe (Distributor, Grosir, dll)\n" .
"   - Tambah sales rep: nama, telepon, jadwal kunjungan & pengiriman\n" .
"9. LAPORAN (/reports/product-history): Riwayat lengkap per produk " .
"(pembelian, penjualan, stok). Bisa export.\n" .
"10. SCANNER (/scanner): Scan barcode untuk cek harga & stok produk real-time.\n" .
"11. HITUNG ORDERAN (/hitung-orderan): Buat estimasi belanja ke supplier. " .
"Pilih produk, isi qty & harga, simpan sebagai draft order.\n" .
"12. PENGATURAN:\n" .
"    - Master Data: kelola kategori, satuan, merk, tipe supplier/pelanggan\n" .
"    - Struk: nama toko, alamat, format struk thermal\n" .
"    - Aplikasi: margin default, AI Chat (API key, model)\n" .
"13. AI CHAT (/chat): Tanya apa saja tentang toko — data dijawab real-time.\n" .
"    - Klik ✏️ pada pesan AI untuk memberi koreksi (AI akan belajar)\n" .
"    - Koreksi disimpan otomatis & digunakan di pertanyaan berikutnya\n" .
"=== AKHIR PANDUAN ===\n";
    }

    // ================================================================
    // HELPERS
    // ================================================================

    /**
     * Ekstrak kata kunci bermakna dari pesan user (tanpa stop words Indonesia).
     */
    private function extractKeywords(string $message): array
    {
        $stopWords = [
            'apa','yang','dan','di','ke','dari','untuk','ini','itu','adalah','dengan',
            'pada','atau','juga','saja','ada','bisa','kamu','saya','toko','produk',
            'berapa','tolong','coba','gimana','bagaimana','apakah','kenapa','mengapa',
            'kapan','siapa','dimana','kalau','jika','bila','sudah','belum','akan',
            'sedang','telah','sebuah','setiap','semua','mana','nya','lah','pun','kah',
            'kami','kita','mereka','dia','ia','harga','modal','jual','beli','stok',
            'info','informasi','data','tolong','cek','lihat','tampilkan','kasih',
            'tahu','tentang','mengenai','soal','hal','lebih','atau','lagi',
        ];
        $clean    = preg_replace('/[^\w\s]/u', ' ', mb_strtolower($message));
        $words    = preg_split('/\s+/', trim($clean));
        $keywords = [];
        foreach ($words as $word) {
            if (mb_strlen($word) >= 3 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }
        return array_unique(array_slice($keywords, 0, 7));
    }

    private function contains(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (mb_strpos($haystack, $n) !== false) return true;
        }
        return false;
    }

    // ================================================================
    // SCHEMAS FOR TEXT-TO-SQL (AGENTIC LOOP)
    // ================================================================

    /**
     * Membaca struktur database statis yang dirancang khusus untuk dimengerti AI.
     */
    private function getDatabaseSchema(): string
    {
        return "
=== SKEMA DATABASE PENTING ===
Gunakan skema ini saat membuat SQL_QUERY.

- `products`: id, full_name, code, category_id, brand_id, min_stock, is_active
- `product_packagings`: id, product_id, level (1=Terkecil), unit_id, base_qty, buy_price, sell_price_retail, sell_price_wholesale
- `stock`: product_id, current_qty_base (stok real-time), nearest_expiry
- `sale_transactions`: id, created_at, total_amount, payment_method, customer_id, cashier_id
- `sale_items`: id, transaction_id, product_id, quantity, unit_price, total_price, profit
- `purchases`: id, supplier_id, purchase_date, grand_total, payment_status
- `purchase_items`: id, purchase_id, product_id, quantity, buy_price, subtotal
- `finance_logs`: id, log_date, category ('Pemasukan'/'Pengeluaran'), amount, balance_type (misal 'Saldo Utama', 'Saldo Rokok'), detail
- `shop_debts`: id, supplier_id, remaining_amount, status ('lunas'/'belum_lunas')
- `customer_debts`: id, customer_id, remaining_amount, status ('lunas'/'belum_lunas')
- `suppliers`: id, name, type_id
- `sales_reps`: id, supplier_id, name, phone, status

TIPS RELASI (JOIN):
- Stok produk: `products` JOIN `stock` ON products.id = stock.product_id
- Transaksi jual: `sale_transactions` JOIN `sale_items` ON sale_transactions.id = sale_items.transaction_id
- Laba penjualan: Hitung dengan `SUM(profit)` di `sale_items`
- Keuangan: Filter berdasarkan `category` dan `balance_type` di tabel `finance_logs`
";
    }

    /**
     * Mengambil katalog produk lengkap (nama, modal, jual, stok) dalam format yang sangat padat
     * agar AI tahu semua produk namun tidak boros token.
     */
    private function getFullCatalogCompressed(): string
    {
        try {
            $stmt = $this->db->query("
                SELECT p.full_name, p.code,
                       COALESCE((SELECT buy_price FROM product_packagings WHERE product_id = p.id ORDER BY level ASC LIMIT 1), 0) AS modal,
                       COALESCE((SELECT sell_price_retail FROM product_packagings WHERE product_id = p.id ORDER BY level ASC LIMIT 1), 0) AS jual,
                       COALESCE(s.current_qty_base, 0) AS stok
                FROM products p
                LEFT JOIN stock s ON p.id = s.product_id
                WHERE p.is_active = 1 AND p.code != 'CUSTOM'
                ORDER BY p.full_name ASC
            ");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($products)) return "Kosong";

            // Format padat: "Nama(Kode)|Modal|Jual|Stok;"
            $compressed = "";
            foreach ($products as $p) {
                $compressed .= "{$p['full_name']}({$p['code']})|M:{$p['modal']}|J:{$p['jual']}|S:{$p['stok']}; ";
            }
            return trim($compressed);
        } catch (Throwable $e) {
            return "Error memuat katalog";
        }
    }
}
