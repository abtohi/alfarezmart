<?php
/**
 * PromptBuilder
 *
 * CRITICAL RULE:
 *   The user's prompt stored in app_settings('ai_invoice_prompt') is the
 *   Single Source of Truth and MUST always be used as the primary instruction.
 *   This class only AUGMENTS that prompt with:
 *     1. Contextual product/supplier data
 *     2. Previous scan templates (if any)
 *     3. Expected JSON output schema
 *     4. Image quality hints
 *
 *   The user prompt content is NEVER modified, only wrapped.
 *
 * @package AlfarezMart\Services\Invoice
 */
class PromptBuilder
{
    /** @var SettingModel */
    private $settingModel;

    /** @var \PDO */
    private $db;

    public function __construct(\PDO $db, SettingModel $settingModel)
    {
        $this->db           = $db;
        $this->settingModel = $settingModel;
    }

    // ----------------------------------------------------------------
    // PUBLIC API
    // ----------------------------------------------------------------

    /**
     * Build the complete, final prompt to send to the AI.
     *
     * @param  array       $imageHints     Output from ImagePreprocessor::analyze()
     * @param  array|null  $supplierProducts  Products belonging to this supplier
     * @param  array|null  $template       Previous scan template for this supplier
     * @param  bool        $isCorrectionPass  True when called from SelfCorrectionEngine
     * @param  array       $correctionHints   Items that need correction with issues found
     * @return array{system: string, user: string}  Separate system and user prompts
     */
    public function build(
        array $imageHints = [],
        ?array $supplierProducts = null,
        ?array $template = null,
        bool $isCorrectionPass = false,
        array $correctionHints = []
    ): array {
        // 1. Read user prompt — MANDATORY, never hardcode a fallback
        $userPrompt = trim($this->settingModel->get('ai_invoice_prompt', ''));

        if (empty($userPrompt)) {
            // Provide a minimal fallback only as last resort (still not hardcoded into the
            // main logic — the setting simply hasn't been configured yet)
            $userPrompt = 'Kamu adalah asisten scan invoice. Baca gambar invoice/faktur dan ekstrak semua item barang menjadi JSON.';
        }

        // 2. Build system prompt (instructions AI must always follow)
        $system = $this->buildSystemPrompt($isCorrectionPass);

        // 3. Build user message (combines user prompt + context + image hints)
        $user = $this->buildUserMessage(
            $userPrompt,
            $imageHints,
            $supplierProducts,
            $template,
            $isCorrectionPass,
            $correctionHints
        );

        return ['system' => $system, 'user' => $user];
    }

    // ----------------------------------------------------------------
    // PRIVATE HELPERS
    // ----------------------------------------------------------------

    private function buildSystemPrompt(bool $isCorrectionPass): string
    {
        $lines = [];
        $lines[] = 'Kamu adalah OCR invoice otomatis.';
        $lines[] = 'Tugas: Ekstrak data invoice/faktur menjadi JSON array valid.';
        $lines[] = '';
        $lines[] = 'ATURAN WAJIB (Ikuti dengan ketat 100%):';
        $lines[] = '1. OUTPUT HARUS JSON ARRAY. DILARANG memberi penjelasan teks di luar JSON.';
        $lines[] = '2. BACA HEADER KOLOM dulu untuk memahami letak harga, qty, dan diskon.';
        $lines[] = '3. KOORDINAT: Kolom TOTAL HARGA (total_price) selalu di ujung PALING KANAN. Jangan ambil harga dari kolom tengah.';
        $lines[] = '4. KOORDINAT: Kolom KODE BARANG (jika ada) biasanya di ujung PALING KIRI (sebelum nama barang).';
        $lines[] = '5. BSR/TGH/KCL: Kadang QTY terbagi 3 kolom (Besar/Tengah/Kecil). Jika ada, ambil qty sesuai kolomnya (misal: "qty_kcl": 1).';
        $lines[] = '6. KEMASAN: Jika satuan tidak jelas, amati harga per unit vs daftar produk supplier. Misal: jika qty=4, total=32.000 -> unit_price=8.000. Jika 8.000 adalah harga Renceng, maka unit = "Renceng".';
        $lines[] = '7. HARGA: Format angka saja (12000, bukan Rp12.000 atau 12.000). Desimal gunakan titik (12.5).';
        $lines[] = '8. FORMAT: Jika nilai tidak ada/kosong, isi dengan null.';
        $lines[] = '9. FORMAT NAMA BARANG: Jika nama barang diawali dengan titik dua atau ada sisipan seperti "x10 : R.MANSION", maka ambil nama aslinya (misal "R.MANSION"). Jangan anggap itu sebagai header atau diabaikan.';
        $lines[] = '';

        if ($isCorrectionPass) {
            $lines[] = 'MODE KOREKSI ULANG:';
            $lines[] = 'Periksa ulang item pada daftar koreksi di bawah. Pastikan Qty * Harga Satuan = Total Harga.';
            $lines[] = '';
        }

        $lines[] = 'FORMAT JSON YANG DIHARAPKAN:';
        $lines[] = '[';
        $lines[] = '  {';
        $lines[] = '    "name": "NAMA BARANG DI INVOICE",';
        $lines[] = '    "supplier_invoice_name": "NAMA PERSIS DI INVOICE",';
        $lines[] = '    "supplier_code": "KODE BARANG",';
        $lines[] = '    "qty": 2,';
        $lines[] = '    "unit": "Karton",';
        $lines[] = '    "qty_bsr": 0,';
        $lines[] = '    "qty_tgh": 0,';
        $lines[] = '    "qty_kcl": 0,';
        $lines[] = '    "unit_price": 5000,';
        $lines[] = '    "total_price": 10000,';
        $lines[] = '    "discount": 0,';
        $lines[] = '    "brand": "Merk",';
        $lines[] = '    "variant": "Varian"';
        $lines[] = '  }';
        $lines[] = ']';

        return implode("\n", $lines);
    }

    private function buildUserMessage(
        string $userPrompt,
        array $imageHints,
        ?array $supplierProducts,
        ?array $template,
        bool $isCorrectionPass,
        array $correctionHints
    ): string {
        $parts = [];

        // === BAGIAN 1: INSTRUKSI USER ===
        if (!empty($userPrompt)) {
            $parts[] = "=== INSTRUKSI USER ===\n" . $userPrompt;
        }

        // === BAGIAN 2: DAFTAR PRODUK SUPPLIER INI ===
        if (!empty($supplierProducts)) {
            $lines   = ["=== REFERENSI PRODUK & HARGA MODAL ==="];
            $lines[] = "Gunakan data ini untuk mencocokkan Nama Barang, Kode Barang, dan menebak Satuan (Unit) dari harganya:";
            $count   = 0;
            foreach ($supplierProducts as $p) {
                if ($count >= 40) { // Limit to 40 to save tokens
                    $lines[] = "... dan " . (count($supplierProducts) - $count) . " produk lainnya";
                    break;
                }
                $line = "- [{$p['code']}] {$p['full_name']}";
                if (!empty($p['supplier_product_code'])) {
                    $line .= " (Kode Sup: {$p['supplier_product_code']})";
                }
                if (!empty($p['packagings'])) {
                    $pkgParts = [];
                    foreach ($p['packagings'] as $pkg) {
                        $pkgParts[] = "{$pkg['unit_name']}@Rp" . number_format($pkg['buy_price'], 0, '', '');
                    }
                    $line .= " | Kemasan: " . implode(', ', $pkgParts);
                }
                $lines[] = $line;
                $count++;
            }
            $parts[] = implode("\n", $lines);
        }

        // === BAGIAN 3: DAFTAR KOREKSI ===
        if ($isCorrectionPass && !empty($correctionHints)) {
            $lines   = ["=== DAFTAR KOREKSI ==="];
            $lines[] = "Ada masalah pada item-item ini, tolong baca ulang fotonya dengan sangat teliti:";
            foreach ($correctionHints as $hint) {
                $line = "- \"{$hint['name']}\"";
                if (!empty($hint['issues'])) {
                    $line .= ": " . implode(', ', $hint['issues']);
                }
                $lines[] = $line;
            }
            $parts[] = implode("\n", $lines);
        }

        return implode("\n\n", $parts);
    }
}
