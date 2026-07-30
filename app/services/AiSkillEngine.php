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
