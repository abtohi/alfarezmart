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
        $prompt .= "ATURAN BAHASA: WAJIB 100% BAHASA INDONESIA. DILARANG MENGGUNAKAN BAHASA INGGRIS. DILARANG MENAMPILKAN THOUGHT/REASONING PROCESS INTERNAL.\n\n";

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
        $prompt .= "5. Jika [SQL_RESULT] kosong ([]), beritahu user data tidak ditemukan. JANGAN buat SQL lagi.\n";
        $prompt .= "6. Output: Markdown rapi. Angka penting di-**bold**. Gunakan tabel jika data tabular.\n";
        $prompt .= "7. SCOPE: Hanya menjawab seputar data & fitur toko AlfarezMart. Tolak pertanyaan di luar scope dengan sopan.\n";
        $prompt .= "8. Untuk pertanyaan harga, SELALU tampilkan harga beli (modal), harga jual eceran, dan harga jual grosir jika tersedia.\n\n";

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
    // PRODUCT SEARCH
    // ================================================================

    private function searchProducts(array $keywords): array
    {
        if (empty($keywords)) return [];
        try {
            $conditions = [];
            $params     = [];
            foreach ($keywords as $i => $kw) {
                $k             = ':pkw' . $i;
                $conditions[]  = "(p.full_name LIKE {$k} OR b.name LIKE {$k} OR p.invoice_name LIKE {$k})";
                $params[$k]    = '%' . $kw . '%';
            }
            $where = implode(' OR ', $conditions);

            $stmt = $this->db->prepare("
                SELECT
                    p.full_name AS nama, b.name AS merk,
                    pp.buy_price AS harga_beli,
                    pp.sell_price_retail AS harga_jual_eceran,
                    pp.sell_price_wholesale AS harga_jual_grosir,
                    stk.current_qty_base AS stok
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN product_packagings pp ON p.id = pp.product_id AND pp.level = 1
                LEFT JOIN stock stk ON p.id = stk.product_id
                WHERE p.is_active = 1 AND p.code != 'CUSTOM' AND ({$where})
                ORDER BY p.full_name ASC
                LIMIT 8
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
     * Extract meaningful keywords from user message.
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
            'lebih','lagi','dong','deh','sih','kok','ya','kan','lho','mau',
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

    /**
     * Determine the category of a user question for fact caching.
     */
    public function categorizeQuestion(string $message): string
    {
        $q = mb_strtolower($message);
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
}
