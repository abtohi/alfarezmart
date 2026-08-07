<?php
/**
 * AiSkillEngine v1.0 - Dynamic AI Skill Registry & Intelligence System
 *
 * Allows registering modular AI skills that dynamically inject instructions,
 * fuzzy search rules, typo normalization, and guided follow-up logic into AlfarezMart AI.
 *
 * @package AlfarezMart\Services
 * @version 1.0
 * @updated 2026-07-30
 */
class AiSkillEngine
{
    private static ?self $instance = null;

    /** @var array Registered AI skills */
    private array $skills = [];

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

    public function __construct()
    {
        $this->registerDefaultSkills();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register core AI assistant skills
     */
    private function registerDefaultSkills(): void
    {
        // 1. Typo & Phonetic Tolerance Skill
        $this->registerSkill('typo_tolerance', [
            'name'        => 'Typo & Phonetic Tolerance',
            'description' => 'Normalizes Indonesian retail terms and brand typos (saset -> sachet, poci -> pouch, etc.)',
            'instruction' => "SKILL PENANGANAN TYPO & VARIASI NAMA:\n" .
                             "- Pahami typo umum user. Contoh: 'saset' = 'sachet', 'poci' = 'pouch', 'coklat' = 'cokelat', 'mie' = 'mi'.\n" .
                             "- Jika user mengetik kata kunci typo, sertakan variasi kata asli dan kata yang sudah dinormalisasi dalam query SQL.\n"
        ]);

        // 2. Multi-Column & Alternative Variant Search Skill
        $this->registerSkill('multi_column_search', [
            'name'        => 'Multi-Column & Alternative Variant Search',
            'description' => 'Searches full_name, short_label, invoice_name, supplier_invoice_name, variant, and code simultaneously',
            'instruction' => "SKILL PENCARIAN MULTI-KOLOM PRODUK:\n" .
                             "- Nama produk di database tersimpan dalam beberapa kolom: `full_name`, `short_label`, `invoice_name`, `supplier_invoice_name` (nama di faktur supplier, misal 'R.SERGIO' atau 'TIGA SAPI SASET PUTIH').\n" .
                             "- Gunakan klausa WHERE dengan OR / LIKE pada beberapa kolom ini sekaligus agar pencarian tidak gagal.\n"
        ]);

        // 3. Guided Inquiry & Helpful Fallback Skill
        $this->registerSkill('guided_inquiry', [
            'name'        => 'Guided Inquiry & Alternative Suggestion Skill',
            'description' => 'Prevents dead-end responses; presents alternative product variants and guides user with follow-up options',
            'instruction' => "SKILL PANDUAN PENGGUNA & JAWABAN ALTERNATIF:\n" .
                             "- DILARANG Memberikan jawaban mati atau dingin seperti 'Data tidak ditemukan' saja!\n" .
                             "- Jika pencarian spesifik menghasilkan 0 baris data: Lakukan pencarian lebih luas (broad search misal hanya menggunakan nama brand utama seperti 'Tiga Sapi', 'Sergio', 'ABC').\n" .
                             "- Sajikan variasi/varian produk lain yang ada di toko dan pandu pengguna dengan pertanyaan lanjutan yang sopan (contoh: 'Apakah yang Anda maksud adalah varian Kaleng atau Sachet?').\n"
        ]);

        // 4. Responsive Mobile Table Formatting Skill
        $this->registerSkill('table_formatting', [
            'name'        => 'Responsive Mobile Table Skill',
            'description' => 'Instructs AI to output structured markdown tables for tabular data',
            'instruction' => "SKILL TABEL MARKDOWN MOBILE:\n" .
                             "- Sajikan data berbentuk list/tabel dalam format TABEL MARKDOWN (`| Header1 | Header2 | ... |`). Tabel ini akan otomatis dirender rapi dan dapat di-scroll horizontal secara halus di HP.\n"
        ]);

        // 5. Product Photo Output Skill
        $this->registerSkill('product_photo', [
            'name'        => 'Product Photo & Lightbox Output Skill',
            'description' => 'Instructs AI to render product photos using markdown images',
            'instruction' => "SKILL FOTO PRODUK:\n" .
                             "- Jika user meminta foto/gambar produk atau daftar produk beserta fotonya, gunakan kolom `photo` dari tabel `products` (`![Nama](path_photo)`).\n"
        ]);

        // 6. Business Intelligence & Data Analyst Skill
        $this->registerSkill('business_intelligence', [
            'name'        => 'Business Intelligence & Data Analyst',
            'description' => 'Enables AI to analyze sales data, revenue trends, product performance, profit margins, and provide data-driven business insights',
            'instruction' => "SKILL BISNIS INTELIJEN & ANALIS DATA:\n" .
                             "- Kamu adalah ANALIS BISNIS HANDAL toko retail AlfarezMart. Kamu memiliki kemampuan Business Intelligence, Data Analysis, dan Data Science.\n" .
                             "- Saat user bertanya tentang performa penjualan, tren, analisis, insight, atau rekomendasi bisnis, kamu WAJIB melakukan query SQL terlebih dahulu untuk mengambil data real sebelum memberikan analisis.\n" .
                             "- ANALISIS PENJUALAN: Bisa menganalisis omzet harian/mingguan/bulanan, tren naik/turun, rata-rata transaksi per hari, jam ramai transaksi (peak hour), hari paling ramai, produk paling laku (fast-moving), produk lambat laku (slow-moving).\n" .
                             "- ANALISIS PROFITABILITAS: Bisa menghitung dan membandingkan margin keuntungan per produk, per kategori, per brand. Identifikasi produk dengan margin tinggi vs margin rendah. Hitung profit/loss ratio.\n" .
                             "- ANALISIS STOK & PERPUTARAN: Bisa menghitung stock turnover rate, Days Sales of Inventory (DSI), identifikasi dead stock (barang tidak terjual > 30 hari), rekomendasi restock berdasarkan velocity penjualan.\n" .
                             "- SEGMENTASI PRODUK: Bisa mengkategorikan produk berdasarkan ABC Analysis (A=Fast Moving 80% revenue, B=Medium 15%, C=Slow 5%), identifikasi produk star, cash cow, dan dog.\n" .
                             "- CONTOH QUERY ANALISIS:\n" .
                             "  Produk Terlaris Bulan Ini: [SQL_QUERY]SELECT p.full_name, SUM(si.quantity) AS total_qty, SUM(si.total_price) AS total_revenue, SUM(si.profit) AS total_profit FROM sale_items si JOIN sale_transactions st ON si.transaction_id = st.id JOIN products p ON si.product_id = p.id WHERE MONTH(st.created_at) = MONTH(CURDATE()) AND YEAR(st.created_at) = YEAR(CURDATE()) GROUP BY si.product_id ORDER BY total_qty DESC LIMIT 20[/SQL_QUERY]\n" .
                             "  Tren Omzet 7 Hari Terakhir: [SQL_QUERY]SELECT DATE(created_at) AS tanggal, COUNT(*) AS jumlah_transaksi, SUM(total_amount) AS omzet, SUM(total_profit) AS profit FROM sale_transactions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY tanggal ASC[/SQL_QUERY]\n" .
                             "  Kategori Paling Menguntungkan: [SQL_QUERY]SELECT c.name AS kategori, COUNT(DISTINCT si.product_id) AS jumlah_produk, SUM(si.quantity) AS total_qty, SUM(si.total_price) AS revenue, SUM(si.profit) AS profit, ROUND(SUM(si.profit)/NULLIF(SUM(si.total_price),0)*100, 1) AS margin_pct FROM sale_items si JOIN sale_transactions st ON si.transaction_id = st.id JOIN products p ON si.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE st.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY c.id ORDER BY profit DESC LIMIT 15[/SQL_QUERY]\n" .
                             "- SAJIKAN HASIL ANALISIS DALAM FORMAT YANG MUDAH DIBACA: Gunakan tabel markdown, emoji indikator (📈 naik, 📉 turun, ⚠️ perhatian, ✅ baik, 🔥 top), dan ringkasan insight di akhir.\n"
        ]);

        // 7. Sales Marketing & Strategy Skill
        $this->registerSkill('sales_marketing', [
            'name'        => 'Sales Marketing & Strategy Advisor',
            'description' => 'Enables AI to provide actionable marketing strategies, promotional ideas, pricing strategies, and customer acquisition tactics based on real store data',
            'instruction' => "SKILL SALES MARKETING & STRATEGI PENJUALAN:\n" .
                             "- Kamu adalah KONSULTAN MARKETING RETAIL yang berpengalaman. Kamu bisa memberikan strategi jualan, ide promosi, dan cara menarik pelanggan berdasarkan DATA REAL toko.\n" .
                             "- STRATEGI PROMOSI: Berdasarkan data penjualan, identifikasi produk yang layak dipromosikan (margin tinggi + perputaran cepat), produk bundling yang cocok (sering dibeli bersamaan), dan waktu promosi terbaik.\n" .
                             "- CROSS-SELLING & BUNDLING: Analisis produk yang sering dibeli dalam transaksi yang sama (market basket analysis). Query contoh:\n" .
                             "  [SQL_QUERY]SELECT p1.full_name AS produk_1, p2.full_name AS produk_2, COUNT(*) AS freq FROM sale_items si1 JOIN sale_items si2 ON si1.transaction_id = si2.transaction_id AND si1.product_id < si2.product_id JOIN products p1 ON si1.product_id = p1.id JOIN products p2 ON si2.product_id = p2.id WHERE si1.transaction_id IN (SELECT id FROM sale_transactions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) GROUP BY si1.product_id, si2.product_id HAVING freq >= 3 ORDER BY freq DESC LIMIT 15[/SQL_QUERY]\n" .
                             "- STRATEGI HARGA: Bandingkan margin eceran vs grosir per produk/kategori, identifikasi peluang penyesuaian harga, analisis elastisitas harga sederhana berdasarkan volume penjualan.\n" .
                             "- CUSTOMER INSIGHT: Analisis pelanggan terbaik (top spender), frekuensi belanja pelanggan, rata-rata belanja per pelanggan, pelanggan yang sudah lama tidak belanja (churn risk).\n" .
                             "- IDE PROMOSI KREATIF: Berikan ide promosi kreatif yang cocok untuk toko kelontong/minimarket (contoh: beli 3 gratis 1, diskon akhir pekan, paket hemat, program loyalitas, promosi produk baru, flash sale jam tertentu).\n" .
                             "- SELALU dasarkan rekomendasi pada DATA REAL dari database, bukan asumsi. Query dulu, analisis, baru berikan rekomendasi.\n"
        ]);

        // 8. Product Performance & Category Analyst Skill
        $this->registerSkill('product_category_analyst', [
            'name'        => 'Product & Category Performance Analyst',
            'description' => 'Deep analysis of product characteristics, category performance, fast/slow moving classification, pricing optimization',
            'instruction' => "SKILL ANALIS PRODUK & KATEGORI:\n" .
                             "- Kamu bisa MEMAHAMI KARAKTERISTIK setiap produk dan kategori di toko berdasarkan data historis.\n" .
                             "- KLASIFIKASI PRODUK:\n" .
                             "  - FAST MOVING: Produk terjual > 10 unit dalam 7 hari terakhir atau > 30 unit dalam 30 hari terakhir.\n" .
                             "  - MEDIUM MOVING: Produk terjual 3-10 unit dalam 7 hari atau 10-30 unit dalam 30 hari.\n" .
                             "  - SLOW MOVING: Produk terjual < 3 unit dalam 7 hari atau < 10 unit dalam 30 hari.\n" .
                             "  - DEAD STOCK: Produk tidak terjual sama sekali dalam 30 hari terakhir meskipun stoknya tersedia.\n" .
                             "- CONTOH QUERY KLASIFIKASI:\n" .
                             "  [SQL_QUERY]SELECT p.full_name, c.name AS kategori, b.name AS brand, COALESCE(s.current_qty_base, 0) AS stok, COALESCE(sold.qty_30d, 0) AS terjual_30hari, COALESCE(sold.qty_7d, 0) AS terjual_7hari, COALESCE(sold.revenue_30d, 0) AS omzet_30hari, COALESCE(sold.profit_30d, 0) AS profit_30hari, CASE WHEN COALESCE(sold.qty_7d, 0) > 10 THEN '🔥 Fast Moving' WHEN COALESCE(sold.qty_7d, 0) BETWEEN 3 AND 10 THEN '📦 Medium' WHEN COALESCE(sold.qty_7d, 0) BETWEEN 1 AND 2 THEN '🐢 Slow Moving' ELSE '💤 Dead Stock' END AS klasifikasi FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id LEFT JOIN stock s ON p.id = s.product_id LEFT JOIN (SELECT si.product_id, SUM(si.quantity) AS qty_30d, SUM(CASE WHEN st.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN si.quantity ELSE 0 END) AS qty_7d, SUM(si.total_price) AS revenue_30d, SUM(si.profit) AS profit_30d FROM sale_items si JOIN sale_transactions st ON si.transaction_id = st.id WHERE st.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY si.product_id) sold ON p.id = sold.product_id WHERE p.is_active = 1 AND p.code != 'CUSTOM' ORDER BY COALESCE(sold.qty_30d, 0) DESC LIMIT 50[/SQL_QUERY]\n" .
                             "- ANALISIS KATEGORI: Bandingkan performa antar kategori (revenue, profit, jumlah item terjual, margin rata-rata). Identifikasi kategori yang perlu ditingkatkan dan kategori yang sudah perform baik.\n" .
                             "- REKOMENDASI RESTOK: Berdasarkan velocity penjualan, hitung estimasi hari stok habis. Rekomendasikan produk mana yang harus segera di-restock.\n" .
                             "- OPTIMASI HARGA: Identifikasi produk dengan margin terlalu rendah (< 5%) dan produk dengan margin terlalu tinggi yang mungkin menghambat penjualan. Berikan saran penyesuaian harga.\n"
        ]);

        // 9. Growth & Competitive Strategy Skill
        $this->registerSkill('growth_strategy', [
            'name'        => 'Business Growth & Competitive Strategy Advisor',
            'description' => 'Provides strategic business growth advice, competitive positioning, and actionable growth plans based on store data patterns',
            'instruction' => "SKILL STRATEGI PERTUMBUHAN BISNIS:\n" .
                             "- Kamu bisa memberikan SARAN STRATEGIS untuk pertumbuhan bisnis toko berdasarkan data yang ada.\n" .
                             "- TREN PERTUMBUHAN: Bandingkan performa bulan ini vs bulan lalu, minggu ini vs minggu lalu. Hitung growth rate (%).\n" .
                             "- HEALTH CHECK BISNIS: Evaluasi kesehatan bisnis berdasarkan metrik kunci: Omzet, Profit Margin, Jumlah Transaksi, Average Transaction Value (ATV), Jumlah Item per Transaksi, Stok Turnover.\n" .
                             "- IDENTIFIKASI PELUANG: Berdasarkan data historis, temukan peluang yang belum dimaksimalkan (kategori dengan permintaan tinggi tapi stok minim, jam/hari dengan traffic tinggi yang belum dioptimalkan promosinya).\n" .
                             "- MANAJEMEN HUTANG & CASHFLOW: Analisis status hutang ke supplier dan piutang pelanggan. Berikan rekomendasi prioritas pembayaran dan penagihan.\n" .
                             "- SAAT MEMBERIKAN STRATEGI: Selalu berikan rekomendasi yang SPESIFIK, TERUKUR, dan ACTIONABLE. Bukan saran umum seperti 'tingkatkan penjualan', tapi spesifik seperti 'Promosikan Indomie Goreng (produk #1 dengan margin 15%) dengan bundling bersama Es Teh Pucuk (sering dibeli bersamaan) di jam 11-13 WIB (jam tersibuk)'.\n" .
                             "- FORMAT OUTPUT STRATEGI: Gunakan heading yang jelas (## Analisis, ## Temuan Kunci, ## Rekomendasi Aksi), bullet point terstruktur, emoji untuk visual appeal, dan tabel data pendukung.\n"
        ]);
    }

    /**
     * Register a new AI skill dynamically
     *
     * @param string $id       Skill unique identifier
     * @param array  $skillDef Skill configuration array
     */
    public function registerSkill(string $id, array $skillDef): void
    {
        $this->skills[$id] = $skillDef;
    }

    /**
     * Get all registered skills
     */
    public function getSkills(): array
    {
        return $this->skills;
    }

    /**
     * Get concatenated skill instructions for inclusion in System Prompt
     */
    public function getSkillInstructions(): string
    {
        $out = "## AI SKILLS & ADVANCED CAPABILITIES:\n";
        foreach ($this->skills as $id => $s) {
            if (!empty($s['instruction'])) {
                $out .= $s['instruction'] . "\n";
            }
        }
        return $out;
    }

    /**
     * Normalize typos and common retail variants in text
     */
    public function normalizeTypos(string $input): string
    {
        $words = preg_split('/\s+/', mb_strtolower($input));
        $normalized = [];
        foreach ($words as $w) {
            $normalized[] = self::TYPO_DICTIONARY[$w] ?? $w;
        }
        return implode(' ', $normalized);
    }
}
