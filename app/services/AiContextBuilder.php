<?php
/**
 * AiContextBuilder v5.0 - Smart & Token-Efficient RAG Engine
 *
 * Strategy: "SQL-First + Dynamic Introspection"
 * - System prompt ~800-1200 tokens (ultra-lean)
 * - Database schema auto-discovered via SHOW TABLES + DESCRIBE
 * - Knowledge base + learned facts injected only when relevant
 * - Product search only when user mentions specific products
 * - Feature guide only when user asks about tutorials
 * - AI queries database itself via [SQL_QUERY] tags
 *
 * @version 5.0
 * @updated 2026-07-30
 */
require_once __DIR__ . '/AiSkillEngine.php';

class AiContextBuilder
{
    const VERSION = '5.0';

    /** @var \PDO */
    private $db;

    /** @var AiChatModel */
    private $model;

    /** @var string|null Cached schema string */
    private static $cachedSchema = null;

    public function __construct()
    {
        $this->db    = Database::getInstance()->getConnection();
        $this->model = new AiChatModel();
    }

    // ================================================================
    // ENTRY POINT — Build System Prompt
    // ================================================================

    /**
     * Build an ultra-lean system prompt (~800-1200 tokens).
     * AI uses SQL queries to fetch data it needs.
     */
    public function buildSystemPrompt(string $userMessage = '', array $currentUser = []): string
    {
        $q        = mb_strtolower($userMessage);
        $keywords = $this->extractKeywords($userMessage);

        // --- 1. Core Identity & Strict Rules ---
        $prompt  = "Kamu adalah AI Asisten cerdas toko AlfarezMart. Nama kamu: AlfarezMart AI.\n";
        $prompt .= "ATURAN BAHASA & FORMAT: WAJIB 100% BAHASA INDONESIA. DILARANG KERAS MENGGUNAAN BAHASA INGGRIS ATAU MENGELUARKAN INTERNAL THOUGHT / REASONING PROCESS SEPERTI 'We need to query...', 'The user is asking...'. JIKA MEMBUTUHKAN DATA DATABASE, LANGSUNG TULIS TAG [SQL_QUERY]SELECT ...[/SQL_QUERY] TANPA KATA-KATA LAIN SEBELUM/SESUDAHNYA.\n\n";

        if (!empty($currentUser)) {
            $prompt .= "PENGGUNA AKTIF SEKARANG: ID=" . ($currentUser['id'] ?? '?') . ", Nama=\"" . ($currentUser['name'] ?? 'User') . "\", Level=" . ($currentUser['level'] ?? 'user') . "\n\n";
        }

        $prompt .= "ATURAN KETAT:\n";
        $prompt .= "1. Jawab dalam BAHASA INDONESIA yang ramah, akurat, dan profesional.\n";
        $prompt .= "2. PRIORITAS UTAMA: Gunakan DATA INTERNAL di bawah jika tersedia. DILARANG menebak angka/harga.\n";
        $prompt .= "3. Jika data TIDAK ADA di konteks, WAJIB query database dengan format:\n";
        $prompt .= "   [SQL_QUERY]SELECT ... FROM ... LIMIT 50[/SQL_QUERY]\n";
        $prompt .= "   HANYA tag itu saja. TANPA kalimat apapun sebelum/sesudah tag.\n";
        $prompt .= "4. DILARANG bilang 'tidak tahu' / 'tidak memiliki akses' SEBELUM mencoba SQL query.\n";
        $skillEngine = AiSkillEngine::getInstance();
        $prompt .= $skillEngine->getSkillInstructions() . "\n";

        $prompt .= "ATURAN SKILL PENCARIAN PRODUK & TYPO TOLERANCE:\n";
        $prompt .= "1. MULTI-COLUMN SEARCH: Nama produk di database tersimpan dalam beberapa kolom di tabel `products`: `full_name` (nama lengkap), `short_label` (label cetak), `invoice_name` (nama nota), `supplier_invoice_name` (nama di faktur supplier, misal 'R.SERGIO' untuk Sergio), `variant`, `code`.\n";
        $prompt .= "2. FLEXIBLE & FUZZY MATCHING: Saat mencari data produk, penjualan, atau riwayat pembelian produk tertentu (misal: 'rokok sergio', 'saset', 'mie'):\n";
        $prompt .= "   - SELALU gunakan klausa WHERE dengan OR/LIKE pada beberapa kolom sekaligus agar fleksibel terhadap typo dan variasi nama di faktur/nota.\n";
        $prompt .= "   - Contoh query riwayat pembelian (Purchases) untuk produk 'rokok sergio':\n";
        $prompt .= "     [SQL_QUERY]SELECT p.purchase_date, s.name AS supplier_name, pr.full_name, pr.supplier_invoice_name, pi.quantity, pi.buy_price, pi.total_price FROM purchases p JOIN purchase_items pi ON p.id = pi.purchase_id JOIN products pr ON pi.product_id = pr.id LEFT JOIN suppliers s ON p.supplier_id = s.id WHERE (pr.full_name LIKE '%sergio%' OR pr.short_label LIKE '%sergio%' OR pr.invoice_name LIKE '%sergio%' OR pr.supplier_invoice_name LIKE '%sergio%' OR pr.supplier_invoice_name LIKE '%R.SERGIO%' OR pr.full_name LIKE '%rokok%') ORDER BY p.purchase_date DESC LIMIT 50[/SQL_QUERY]\n";
        $prompt .= "3. PENANGANAN TYPO: Wajib deteksi typo umum user! Misal: 'saset' -> cari 'sachet' & 'saset', 'poci' -> cari 'pouch' & 'poci', 'coklat' -> cari 'cokelat' & 'coklat', 'sergio' -> cari 'sergio' & 'R.SERGIO'. Buat query SQL yang mencakup variasi kata asli dan variasi kata yang sudah dinormalisasi.\n\n";

        $prompt .= "ATURAN SKILL OUTPUT TABEL & GAMBAR:\n";
        $prompt .= "1. SKILL TABEL MARKDOWN: Saat user meminta daftar/list produk dengan kolom-kolom tertentu, SELALU sajikan dalam format TABEL MARKDOWN (`| Header1 | Header2 | ... |`). Tabel ini akan dirender sangat rapi dan dapat di-scroll horizontal secara halus pada layar hp/mobile.\n";
        $prompt .= "2. SKILL GAMBAR PRODUK: Tabel `products` memiliki kolom `photo` yang menyimpan lokasi foto produk (misal: `storage/uploads/products/prod_170_1782085048.webp`).\n";
        $prompt .= "   - Jika user meminta melihat gambar/foto produk atau daftar produk beserta fotonya, SELALU sertakan kolom `photo` dalam query SQL.\n";
        $prompt .= "   - Tampilkan foto produk menggunakan sintaks Markdown Gambar: `![Nama Produk](path_photo)` (contoh: `![ABC Kecap](storage/uploads/products/prod_170_1782085048.webp)`).\n";
        $prompt .= "   - Dalam tabel markdown, Anda dapat menaruh markdown gambar `![Nama](path_photo)` langsung di dalam sel tabel kolom Foto.\n";
        $prompt .= "   - Jika kolom `photo` kosong/null, tampilkan `-` atau `(Tidak ada foto)`.\n\n";

        $prompt .= "PETUNJUK PERTANYAAN PENJUALAN & PEMBELIAN:\n";
        $prompt .= "- Jika user bertanya 'belanja apa aja' / 'pembelian toko' / 'barang masuk': Query tabel `purchases` JOIN `purchase_items` ON purchases.id = purchase_items.purchase_id JOIN `products` ON purchase_items.product_id = products.id WHERE DATE(purchases.purchase_date) = CURDATE() (atau tanggal sesuai pertanyaan).\n";
        $prompt .= "- Jika user bertanya 'penjualan' / 'omzet' / 'transaksi kasir': Query `sale_transactions` JOIN `sale_items` ON sale_transactions.id = sale_items.transaction_id JOIN `products` ON sale_items.product_id = products.id WHERE DATE(sale_transactions.created_at) = CURDATE().\n\n";

        // --- 2. Current date/time ---
        $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        $prompt .= "Tanggal Hari Ini: " . date('Y-m-d') . " (" . $hari[(int)date('w')] . "), " . date('H:i') . " WIB\n\n";

        // --- 3. Learned Facts (cached answers from previous interactions) ---
        if (!empty($keywords)) {
            try {
                $facts = $this->model->searchFacts($keywords, 3);
                if (!empty($facts)) {
                    $prompt .= "## FAKTA YANG SUDAH DIPELAJARI\n";
                    foreach ($facts as $f) {
                        $val = mb_substr($f['fact_value'], 0, 200);
                        $prompt .= "- [{$f['category']}] {$f['fact_key']}: {$val}\n";
                    }
                    $prompt .= "\n";
                }
            } catch (Throwable $e) {}
        }

        // --- 4. Knowledge Base (user corrections) ---
        if (!empty($keywords)) {
            try {
                $hits = $this->model->searchKnowledge($keywords, 3);
                if (!empty($hits)) {
                    $prompt .= "## KOREKSI & PENGETAHUAN TERVERIFIKASI\n";
                    foreach ($hits as $k) {
                        $prompt .= "- {$k['topic']}: " . mb_substr($k['content'], 0, 150) . "\n";
                    }
                    $prompt .= "\n";
                }
            } catch (Throwable $e) {}
        }

        // --- 5. Product search (only if user mentions specific product/brand) ---
        if (!empty($keywords)) {
            try {
                $products = $this->searchProducts($keywords);
                if (!empty($products)) {
                    $prompt .= "## PRODUK DITEMUKAN DI KATALOG\n";
                    $seen = [];
                    foreach (array_slice($products, 0, 5) as $p) {
                        $nama = $p['nama'] ?? '?';
                        if (isset($seen[$nama])) continue;
                        $seen[$nama] = true;
                        $parts = [$nama];
                        if (!empty($p['harga_beli'])) $parts[] = "Modal:Rp" . number_format((int)$p['harga_beli'], 0, ',', '.');
                        if (!empty($p['harga_jual_eceran'])) $parts[] = "Jual:Rp" . number_format((int)$p['harga_jual_eceran'], 0, ',', '.');
                        if (isset($p['stok'])) $parts[] = "Stok:{$p['stok']}";
                        if (!empty($p['merk']) && $p['merk'] !== '-') $parts[] = "Merk:{$p['merk']}";
                        $prompt .= "- " . implode(' | ', $parts) . "\n";
                    }
                    $prompt .= "\n";
                }
            } catch (Throwable $e) {}
        }

        // --- 6. Feature Guide (only when user asks about tutorials) ---
        if ($this->contains($q, ['cara', 'fitur', 'bagaimana', 'gimana', 'panduan', 'tutorial', 'menu', 'tombol', 'setting', 'pengaturan', 'langkah', 'petunjuk'])) {
            $prompt .= $this->getFeatureGuide() . "\n";
        }

        // --- 6.5 Business Analytics Context (real-time KPI snapshot for business questions) ---
        if ($this->isBusinessAnalyticsQuestion($q)) {
            $prompt .= $this->getBusinessSnapshot();
        }

        // --- 7. Dynamic Database Schema (auto-introspected) ---
        $prompt .= $this->getDynamicSchema();

        return $prompt;
    }

    // ================================================================
    // DYNAMIC DATABASE SCHEMA (Auto-Introspection)
    // ================================================================

    /**
     * Auto-discover database schema using SHOW TABLES + DESCRIBE.
     * Cached per-request to avoid repeated introspection.
     */
    private function getDynamicSchema(): string
    {
        if (self::$cachedSchema !== null) {
            return self::$cachedSchema;
        }

        try {
            $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            $schema = "\n## SKEMA DATABASE (auto-detected)\n";
            $schema .= "Gunakan skema ini untuk membuat SQL_QUERY yang akurat.\n\n";

            if ($driver === 'mysql') {
                // Get all tables
                $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

                // Skip system/cache tables to save tokens
                $skipTables = ['sessions', 'cache', 'migrations', 'password_resets', 'failed_jobs'];

                foreach ($tables as $table) {
                    if (in_array($table, $skipTables)) continue;

                    $cols = $this->db->query("DESCRIBE `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
                    $colNames = [];
                    foreach ($cols as $col) {
                        $name = $col['Field'];
                        $type = $col['Type'];
                        // Simplify type for token efficiency
                        if (strpos($type, 'int') !== false) $type = 'INT';
                        elseif (strpos($type, 'varchar') !== false) $type = 'VARCHAR';
                        elseif (strpos($type, 'text') !== false) $type = 'TEXT';
                        elseif (strpos($type, 'decimal') !== false || strpos($type, 'double') !== false || strpos($type, 'float') !== false) $type = 'DECIMAL';
                        elseif (strpos($type, 'date') !== false || strpos($type, 'time') !== false) $type = 'DATETIME';
                        elseif (strpos($type, 'enum') !== false) $type = $col['Type']; // Keep enum values
                        elseif (strpos($type, 'tinyint') !== false) $type = 'BOOL';

                        $pk = ($col['Key'] === 'PRI') ? '*' : '';
                        $colNames[] = "{$pk}{$name}({$type})";
                    }
                    $schema .= "- `{$table}`: " . implode(', ', $colNames) . "\n";
                }
            } else {
                // SQLite
                $tables = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")
                                   ->fetchAll(PDO::FETCH_COLUMN);
                foreach ($tables as $table) {
                    $cols = $this->db->query("PRAGMA table_info(`{$table}`)")->fetchAll(PDO::FETCH_ASSOC);
                    $colNames = [];
                    foreach ($cols as $col) {
                        $pk = $col['pk'] ? '*' : '';
                        $colNames[] = "{$pk}{$col['name']}({$col['type']})";
                    }
                    $schema .= "- `{$table}`: " . implode(', ', $colNames) . "\n";
                }
            }

            // Add relationship hints (static, token-efficient)
            $schema .= "\n## RELASI PENTING\n";
            $schema .= "- Stok: products JOIN stock ON products.id = stock.product_id\n";
            $schema .= "- Harga: products JOIN product_packagings ON products.id = product_packagings.product_id (level 1=terkecil)\n";
            $schema .= "- Penjualan: sale_transactions JOIN sale_items ON sale_transactions.id = sale_items.transaction_id\n";
            $schema .= "- Pembelian: purchases JOIN purchase_items ON purchases.id = purchase_items.purchase_id\n";
            $schema .= "- Laba: SUM(profit) dari sale_items, JOIN sale_transactions untuk filter tanggal\n";
            $schema .= "- Keuangan: finance_logs.category = 'Pemasukan' atau 'Pengeluaran'. balance_type = nama akun\n";
            $schema .= "- Hutang toko: shop_debts (status='belum_lunas'). Piutang: customer_debts\n";
            $schema .= "- Supplier: suppliers JOIN supplier_products ON suppliers.id = supplier_products.supplier_id\n";
            $schema .= "- Brand: products JOIN brands ON products.brand_id = brands.id\n";
            $schema .= "- Kategori: products JOIN categories ON products.category_id = categories.id\n";

            self::$cachedSchema = $schema;
            return $schema;

        } catch (Throwable $e) {
            // Fallback to static schema
            return $this->getStaticSchema();
        }
    }

    /**
     * Static fallback schema (used when introspection fails).
     */
    private function getStaticSchema(): string
    {
        return "
## SKEMA DATABASE (static fallback)
- `products`: *id, full_name, code, photo, category_id, brand_id, min_stock, is_active
- `product_packagings`: *id, product_id, level(1=Terkecil), unit_id, base_qty, buy_price, sell_price_retail, sell_price_wholesale, margin_retail, margin_wholesale
- `stock`: product_id, current_qty_base, nearest_expiry, last_restock_date
- `sale_transactions`: *id, created_at, total_amount, payment_method, customer_id, cashier_id
- `sale_items`: *id, transaction_id, product_id, quantity, unit_price, total_price, profit
- `purchases`: *id, supplier_id, purchase_date, grand_total, payment_status, total_items
- `purchase_items`: *id, purchase_id, product_id, quantity, buy_price, subtotal
- `finance_logs`: *id, log_date, category('Pemasukan'/'Pengeluaran'), amount, balance_type, detail, description
- `shop_debts`: *id, supplier_id, remaining_amount, status('lunas'/'belum_lunas')
- `customer_debts`: *id, customer_id, remaining_amount, status('lunas'/'belum_lunas')
- `suppliers`: *id, name, type_id, is_active
- `sales_reps`: *id, supplier_id, name, phone, visit_day, delivery_day, status
- `customers`: *id, name, phone, type_id
- `brands`: *id, name
- `categories`: *id, name
- `units`: *id, name
";
    }

    // ================================================================
    // PRODUCT SEARCH (Multi-Column & Typo-Tolerant)
    // ================================================================

    /**
     * Dictionary for typo normalization and common retail terms
     */
    private const TYPO_DICTIONARY = [
        'saset'    => 'sachet',
        'sacset'   => 'sachet',
        'sacet'    => 'sachet',
        'sach'     => 'sachet',
        'btg'      => 'batang',
        'bt'       => 'botol',
        'btl'      => 'botol',
        'pck'      => 'pack',
        'pckg'     => 'pack',
        'pcs'      => 'pcs',
        'coklat'   => 'cokelat',
        'sampo'    => 'shampoo',
        'sampoo'   => 'shampoo',
        'shampo'   => 'shampoo',
        'poci'     => 'pouch',
        'puch'     => 'pouch',
        'poucs'    => 'pouch',
        'ekstra'   => 'extra',
        'eksa'     => 'extra',
        'dus'      => 'karton',
        'ctn'      => 'karton',
        'bal'      => 'bal',
        'renteng'  => 'renceng',
        'rcg'      => 'renceng',
        'krupuk'   => 'kerupuk',
        'indomie'  => 'indomie',
        'mie'      => 'mi',
    ];

    private function searchProducts(array $keywords): array
    {
        if (empty($keywords)) return [];
        try {
            $conditions = [];
            $params     = [];
            foreach ($keywords as $i => $kw) {
                $k            = ':pkw' . $i;
                // Search multi-column: full_name, short_label, invoice_name, supplier_invoice_name, code, variant, brand, category
                $conditions[] = "(p.full_name LIKE {$k} OR p.short_label LIKE {$k} OR p.invoice_name LIKE {$k} OR p.supplier_invoice_name LIKE {$k} OR p.code LIKE {$k} OR p.variant LIKE {$k} OR b.name LIKE {$k} OR c.name LIKE {$k})";
                $params[$k]   = '%' . $kw . '%';
            }
            $where = implode(' OR ', $conditions);

            $stmt = $this->db->prepare("
                SELECT
                    p.full_name AS nama,
                    p.short_label AS label,
                    p.supplier_invoice_name AS nama_invoice_supplier,
                    b.name AS merk,
                    p.photo AS foto,
                    pp.buy_price AS harga_beli,
                    pp.sell_price_retail AS harga_jual_eceran,
                    pp.sell_price_wholesale AS harga_jual_grosir,
                    stk.current_qty_base AS stok
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN product_packagings pp ON p.id = pp.product_id AND pp.level = 1
                LEFT JOIN stock stk ON p.id = stk.product_id
                WHERE p.is_active = 1 AND p.code != 'CUSTOM' AND ({$where})
                ORDER BY p.full_name ASC
                LIMIT 10
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

    // ================================================================
    // FEATURE GUIDE (Static — App Documentation)
    // ================================================================

    private function getFeatureGuide(): string
    {
        return "## PANDUAN FITUR ALFAREZMART
1. DASHBOARD (/): Ringkasan omzet, transaksi, stok kritis, hutang. Menu via sidebar/bottom nav.
2. PRODUK (/products): Kelola katalog. Tambah/Edit/Hapus produk. Kemasan multi-level (Pcs/Box/Karton). 2 tier harga (Eceran & Grosir). Barcode support.
3. KASIR/POS (/sales/pos): Transaksi penjualan. Cari produk/scan barcode. Mode Eceran/Grosir. Bayar Cash/Transfer/QRIS. Cetak struk thermal 58mm.
4. PENJUALAN (/sales): Riwayat transaksi. Filter tanggal. Detail/invoice. Hapus/edit.
5. PEMBELIAN (/purchases): Catat pembelian dari supplier. Stok auto bertambah. Upload foto faktur.
6. KEUANGAN (/finance): Catat pemasukan/pengeluaran per akun. Saldo berjalan & riwayat.
7. HUTANG (/debts): Piutang pelanggan & hutang toko ke supplier. Catat cicilan. Filter lunas/belum.
8. SUPPLIER (/suppliers): Kelola pemasok. Tambah sales rep + jadwal kunjungan/pengiriman.
9. LAPORAN (/reports/product-history): Riwayat per produk (beli/jual/stok). Export.
10. CEK HARGA (/scanner): Scan barcode cek harga & stok real-time.
11. HITUNG ORDERAN (/hitung-orderan): Estimasi belanja ke supplier. Draft order.
12. PPOB (/ppob): Produk digital (pulsa, e-wallet, game, token listrik). Digiflazz API.
13. PENGATURAN: Master data (kategori, satuan, merk). Struk. AI Chat (API key, model).
14. AI CHAT (/chat): Tanya apapun tentang toko. Klik ✏️ untuk koreksi (AI belajar).";
    }

    // ================================================================
    // HELPERS
    // ================================================================

    /**
     * Extract meaningful keywords and normalize typos/synonyms.
     */
    private function extractKeywords(string $message): array
    {
        $stopWords = [
            'apa','yang','dan','di','ke','dari','untuk','ini','itu','adalah','dengan',
            'pada','atau','juga','saja','ada','bisa','kamu','saya','toko','produk',
            'berapa','tolong','coba','gimana','bagaimana','apakah','kenapa','mengapa',
            'kapan','siapa','dimana','kalau','jika','bila','sudah','belum','akan',
            'sedang','telah','sebuah','setiap','semua','mana','nya','lah','pun','kah',
            'kami','kita','mereka','dia','ia','info','informasi','data','tolong',
            'cek','lihat','tampilkan','kasih','tahu','tentang','mengenai','soal','hal',
            'lebih','lagi','dong','deh','sih','kok','ya','kan','lho','mau','darimana',
            'darimana saja','mana saja','riwayat','pembelian','penjualan','belanja'
        ];
        $clean    = preg_replace('/[^\w\s]/u', ' ', mb_strtolower($message));
        $words    = preg_split('/\s+/', trim($clean));
        $keywords = [];

        foreach ($words as $word) {
            if (mb_strlen($word) >= 2 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
                if (isset(self::TYPO_DICTIONARY[$word])) {
                    $keywords[] = self::TYPO_DICTIONARY[$word];
                }
            }
        }
        return array_unique(array_slice($keywords, 0, 10));
    }

    private function contains(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (mb_strpos($haystack, $n) !== false) return true;
        }
        return false;
    }

    /**
     * Determine the category of a user question for fact caching.
     */
    public function categorizeQuestion(string $message): string
    {
        $q = mb_strtolower($message);
        if ($this->isBusinessAnalyticsQuestion($q)) {
            return 'business';
        }
        if ($this->contains($q, ['omzet', 'revenue', 'pendapatan', 'keuangan', 'pemasukan', 'pengeluaran', 'laba', 'profit', 'rugi', 'saldo', 'kas'])) {
            return 'finance';
        }
        if ($this->contains($q, ['stok', 'stock', 'habis', 'kosong', 'restock', 'persediaan'])) {
            return 'stock';
        }
        if ($this->contains($q, ['harga', 'modal', 'jual', 'margin', 'diskon', 'promo'])) {
            return 'product';
        }
        if ($this->contains($q, ['jual', 'terjual', 'laku', 'penjualan', 'transaksi', 'kasir'])) {
            return 'sales';
        }
        if ($this->contains($q, ['cara', 'fitur', 'tutorial', 'panduan', 'gimana', 'bagaimana', 'langkah'])) {
            return 'tutorial';
        }
        return 'general';
    }

    // ================================================================
    // BUSINESS ANALYTICS HELPERS
    // ================================================================

    /**
     * Detect if the user is asking a business analytics / strategy question.
     */
    private function isBusinessAnalyticsQuestion(string $q): bool
    {
        return $this->contains($q, [
            'analisis', 'analisa', 'tren', 'trend', 'insight', 'strategi',
            'marketing', 'promosi', 'promo', 'bundling', 'cross sell',
            'fast moving', 'slow moving', 'dead stock',
            'rekomendasi', 'saran', 'tingkatkan', 'naikkan',
            'pelanggan', 'customer', 'tarik pelanggan',
            'produk terlaris', 'paling laku', 'kurang laku',
            'performa', 'performance', 'growth', 'pertumbuhan',
            'margin', 'profitabilitas', 'profitable',
            'kategori terbaik', 'brand terbaik',
            'bantu jualan', 'bantu jual', 'cara jualan',
            'health check', 'evaluasi bisnis', 'kondisi bisnis',
            'optimasi', 'optimalisasi', 'peluang',
            'bundling', 'paket', 'diskon', 'flash sale',
            'abc analysis', 'pareto', 'segmentasi',
            'turnover', 'perputaran', 'velocity',
        ]);
    }

    /**
     * Inject a real-time business KPI snapshot so the AI has baseline data
     * to reason about without needing a first SQL pass.
     */
    private function getBusinessSnapshot(): string
    {
        try {
            $snapshot = "\n## SNAPSHOT BISNIS REAL-TIME (Data Pendukung Analisis)\n";

            // --- Today's KPIs ---
            $today = $this->db->query("
                SELECT
                    COUNT(*) AS trx_count,
                    COALESCE(SUM(total_amount), 0) AS omzet,
                    COALESCE(SUM(total_profit), 0) AS profit,
                    COALESCE(ROUND(AVG(total_amount), 0), 0) AS avg_trx
                FROM sale_transactions
                WHERE DATE(created_at) = CURDATE()
            ")->fetch(PDO::FETCH_ASSOC);

            if ($today) {
                $snapshot .= "### Hari Ini\n";
                $snapshot .= "- Transaksi: {$today['trx_count']} struk | Omzet: Rp" . number_format((float)$today['omzet'], 0, ',', '.') . " | Profit: Rp" . number_format((float)$today['profit'], 0, ',', '.') . " | Rata-rata/struk: Rp" . number_format((float)$today['avg_trx'], 0, ',', '.') . "\n";
            }

            // --- This month vs last month ---
            $monthly = $this->db->query("
                SELECT
                    SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN total_amount ELSE 0 END) AS omzet_bulan_ini,
                    SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN total_profit ELSE 0 END) AS profit_bulan_ini,
                    COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 END) AS trx_bulan_ini,
                    SUM(CASE WHEN MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN total_amount ELSE 0 END) AS omzet_bulan_lalu,
                    SUM(CASE WHEN MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN total_profit ELSE 0 END) AS profit_bulan_lalu,
                    COUNT(CASE WHEN MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN 1 END) AS trx_bulan_lalu
                FROM sale_transactions
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
            ")->fetch(PDO::FETCH_ASSOC);

            if ($monthly) {
                $omzetIni  = (float)($monthly['omzet_bulan_ini'] ?? 0);
                $omzetLalu = (float)($monthly['omzet_bulan_lalu'] ?? 0);
                $growthPct = $omzetLalu > 0 ? round(($omzetIni - $omzetLalu) / $omzetLalu * 100, 1) : 0;
                $growthEmoji = $growthPct >= 0 ? '📈' : '📉';

                $snapshot .= "### Perbandingan Bulanan\n";
                $snapshot .= "- Bulan Ini: Omzet Rp" . number_format($omzetIni, 0, ',', '.') . " | Profit Rp" . number_format((float)($monthly['profit_bulan_ini'] ?? 0), 0, ',', '.') . " | {$monthly['trx_bulan_ini']} transaksi\n";
                $snapshot .= "- Bulan Lalu: Omzet Rp" . number_format($omzetLalu, 0, ',', '.') . " | Profit Rp" . number_format((float)($monthly['profit_bulan_lalu'] ?? 0), 0, ',', '.') . " | {$monthly['trx_bulan_lalu']} transaksi\n";
                $snapshot .= "- Growth Rate: {$growthEmoji} {$growthPct}%\n";
            }

            // --- Top 5 products this month ---
            $topProducts = $this->db->query("
                SELECT p.full_name, SUM(si.quantity) AS qty, SUM(si.total_price) AS revenue, SUM(si.profit) AS profit
                FROM sale_items si
                JOIN sale_transactions st ON si.transaction_id = st.id
                JOIN products p ON si.product_id = p.id
                WHERE MONTH(st.created_at) = MONTH(CURDATE()) AND YEAR(st.created_at) = YEAR(CURDATE())
                GROUP BY si.product_id ORDER BY qty DESC LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($topProducts)) {
                $snapshot .= "### Top 5 Produk Terlaris Bulan Ini\n";
                foreach ($topProducts as $i => $tp) {
                    $rank = $i + 1;
                    $snapshot .= "- #{$rank} {$tp['full_name']}: {$tp['qty']} terjual, Omzet Rp" . number_format((float)$tp['revenue'], 0, ',', '.') . ", Profit Rp" . number_format((float)$tp['profit'], 0, ',', '.') . "\n";
                }
            }

            // --- Stock alerts ---
            $lowStock = $this->db->query("
                SELECT COUNT(*) AS cnt FROM products p
                JOIN stock s ON p.id = s.product_id
                WHERE p.is_active = 1 AND s.current_qty_base <= p.min_stock AND s.current_qty_base > 0
            ")->fetch(PDO::FETCH_ASSOC);
            $outOfStock = $this->db->query("
                SELECT COUNT(*) AS cnt FROM products p
                JOIN stock s ON p.id = s.product_id
                WHERE p.is_active = 1 AND s.current_qty_base <= 0
            ")->fetch(PDO::FETCH_ASSOC);

            $snapshot .= "### Kondisi Stok\n";
            $snapshot .= "- Stok Rendah (perlu restock): " . ($lowStock['cnt'] ?? 0) . " produk\n";
            $snapshot .= "- Stok Habis: " . ($outOfStock['cnt'] ?? 0) . " produk\n";

            // --- Total active products & categories ---
            $catStats = $this->db->query("
                SELECT COUNT(DISTINCT p.id) AS total_produk, COUNT(DISTINCT p.category_id) AS total_kategori, COUNT(DISTINCT p.brand_id) AS total_brand
                FROM products p WHERE p.is_active = 1
            ")->fetch(PDO::FETCH_ASSOC);
            if ($catStats) {
                $snapshot .= "### Katalog\n";
                $snapshot .= "- Produk Aktif: {$catStats['total_produk']} | Kategori: {$catStats['total_kategori']} | Brand: {$catStats['total_brand']}\n";
            }

            $snapshot .= "\nGunakan data snapshot di atas sebagai baseline. Untuk analisis lebih dalam, lakukan [SQL_QUERY] tambahan sesuai kebutuhan.\n\n";
            return $snapshot;

        } catch (Throwable $e) {
            return '';
        }
    }
}
