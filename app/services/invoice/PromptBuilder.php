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

        $lines[] = 'Kamu adalah sistem OCR dan analisis invoice yang sangat akurat.';
        $lines[] = 'Tugasmu adalah membaca gambar invoice/faktur supplier dan mengekstrak data menjadi JSON yang valid.';
        $lines[] = '';

        $lines[] = '## ATURAN WAJIB:';
        $lines[] = '1. Selalu kembalikan JSON array yang valid — tidak ada markdown, tidak ada penjelasan di luar JSON.';
        $lines[] = '2. Baca SEMUA baris item dalam tabel invoice. Jangan lewatkan satu pun.';
        $lines[] = '3. Jika ada nilai yang tidak terbaca, gunakan null (bukan 0 atau string kosong).';
        $lines[] = '4. Pahami konteks: qty × unit_price HARUS menghasilkan total_price yang masuk akal.';
        $lines[] = '5. Harga dalam format Rupiah Indonesia. Jika tertulis "5.500" artinya Rp 5.500, bukan Rp 5,5.';
        $lines[] = '6. Perhatikan tanda desimal: titik (.) adalah pemisah ribuan, koma (,) adalah desimal.';
        $lines[] = '7. Kemasan/satuan: kenali istilah seperti CTN=Karton, DUS=Karton, PCS/BTL/BKS=satuan kecil, dll.';
        $lines[] = '8. PENTING (KOLOM QTY BSR/TGH/KCL): Jika invoice memiliki kolom qty terpisah bernama BSR, TGH, dan KCL, isi nilai angka dari masing-masing kolom tersebut ke dalam field "qty_bsr", "qty_tgh", dan "qty_kcl" di JSON.';
        $lines[] = '   - Jika tidak ada kolom tersebut, biarkan null atau 0.';
        $lines[] = '9. PENTING (TOTAL HARGA): Angka Total Harga (total_price) PASTI berada di KOLOM PALING KANAN tabel.';
        $lines[] = '   - PERHATIAN: Terkadang angka Total Harga dicetak PADA BARIS BARU TEPAT DI BAWAH baris produk, di posisi paling kanan (contoh: baris pertama 153.000 di tengah, lalu di bawahnya ada 17.000 di ujung kanan. Maka total_price = 17000).';
        $lines[] = '   - JANGAN mengambil harga per karton yang ada di tengah-tengah kolom. Selalu cari angka paling kanan.';
        $lines[] = '10. PENTING (HARGA SATUAN): Biarkan "unit_price" = null. Sistem kami akan menghitung otomatis dari (total_price / qty). JANGAN isi unit_price.';
        $lines[] = '';

        if ($isCorrectionPass) {
            $lines[] = '## MODE: KOREKSI ULANG';
            $lines[] = 'Ini adalah scan ulang karena hasil sebelumnya memiliki inkonsistensi.';
            $lines[] = 'Perhatikan khusus item yang disebutkan dalam DAFTAR KOREKSI di bawah.';
            $lines[] = 'Pastikan qty × unit_price = total_price untuk setiap baris.';
            $lines[] = '';
        }

        $lines[] = '## FORMAT OUTPUT JSON:';
        $lines[] = 'Kembalikan HANYA JSON array berikut (tidak ada teks lain):';
        $lines[] = '[';
        $lines[] = '  {';
        $lines[] = '    "name": "Nama produk/barang di invoice",';
        $lines[] = '    "supplier_invoice_name": "Nama persis di invoice (jika beda dari name)",';
        $lines[] = '    "supplier_code": "Kode barang supplier jika ada",';
        $lines[] = '    "qty": 2,';
        $lines[] = '    "unit": "Karton",';
        $lines[] = '    "qty_bsr": 0,';
        $lines[] = '    "qty_tgh": 2,';
        $lines[] = '    "qty_kcl": 0,';
        $lines[] = '    "unit_price": null,';
        $lines[] = '    "total_price": 17000,';
        $lines[] = '    "discount": 0,';
        $lines[] = '    "brand": "Nama merk jika tertulis",';
        $lines[] = '    "variant": "Varian produk jika ada (misal: Merah, 500ml)",';
        $lines[] = '    "weight": null,';
        $lines[] = '    "weight_unit": null,';
        $lines[] = '    "size": "12x300ml",';
        $lines[] = '    "barcode": null,';
        $lines[] = '    "notes": "Catatan tambahan jika relevan"';
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

        // === BAGIAN 1: INSTRUKSI USER (tidak diubah) ===
        $parts[] = "=== INSTRUKSI UTAMA ===\n" . $userPrompt;

        // === BAGIAN 2: HINT KUALITAS GAMBAR ===
        if (!empty($imageHints['hints'])) {
            $parts[] = "=== INFO KUALITAS GAMBAR ===\n" . implode("\n", $imageHints['hints']);
        }

        // === BAGIAN 3: KONTEKS PRODUK SUPPLIER ===
        if (!empty($supplierProducts)) {
            $lines   = ["=== DAFTAR PRODUK SUPPLIER INI ==="];
            $lines[] = "Gunakan daftar ini untuk mencocokkan nama/kode barang di invoice:";
            $count   = 0;
            foreach ($supplierProducts as $p) {
                if ($count >= 80) { // Limit to avoid token overflow
                    $lines[] = "... dan " . (count($supplierProducts) - $count) . " produk lainnya";
                    break;
                }
                $line = "- [{$p['code']}] {$p['full_name']}";
                if (!empty($p['supplier_product_code'])) {
                    $line .= " (Kode Supplier: {$p['supplier_product_code']})";
                }
                if (!empty($p['supplier_invoice_name'])) {
                    $line .= " [Nama Invoice: {$p['supplier_invoice_name']}]";
                }
                if (!empty($p['packagings'])) {
                    $pkgParts = [];
                    foreach ($p['packagings'] as $pkg) {
                        $pkgParts[] = "{$pkg['unit_name']}@Rp" . number_format($pkg['buy_price'], 0, ',', '.');
                    }
                    $line .= " — Kemasan: " . implode(', ', $pkgParts);
                }
                $lines[] = $line;
                $count++;
            }
            $parts[] = implode("\n", $lines);
        }

        // === BAGIAN 4: TEMPLATE INVOICE SEBELUMNYA ===
        if (!empty($template)) {
            $lines   = ["=== TEMPLATE INVOICE SEBELUMNYA (DARI SUPPLIER YANG SAMA) ==="];
            $lines[] = "Invoice supplier ini sebelumnya memiliki format kolom berikut:";
            if (!empty($template['column_map'])) {
                $colMap = is_string($template['column_map'])
                    ? json_decode($template['column_map'], true)
                    : $template['column_map'];
                if (is_array($colMap)) {
                    foreach ($colMap as $semantic => $aliases) {
                        $lines[] = "- {$semantic}: " . (is_array($aliases) ? implode(', ', $aliases) : $aliases);
                    }
                }
            }
            if (!empty($template['scan_count'])) {
                $lines[] = "(Template dari {$template['scan_count']} scan sebelumnya)";
            }
            $parts[] = implode("\n", $lines);
        }

        // === BAGIAN 5: DAFTAR KOREKSI (hanya pada correction pass) ===
        if ($isCorrectionPass && !empty($correctionHints)) {
            $lines   = ["=== DAFTAR ITEM YANG PERLU DIKOREKSI ==="];
            $lines[] = "Fokus pada item-item ini karena hasil scan pertama tidak konsisten:";
            foreach ($correctionHints as $hint) {
                $line = "- \"{$hint['name']}\"";
                if (!empty($hint['issues'])) {
                    $line .= ": " . implode(', ', $hint['issues']);
                }
                $lines[] = $line;
            }
            $parts[] = implode("\n", $lines);
        }

        // === BAGIAN 6: PERINTAH AKHIR & PENEKANAN ===
        $parts[] = "=== PERINTAH KHUSUS BSR/TGH/KCL & HARGA ===\n" .
                   "1. QTY: Jika ada kolom BSR, TGH, atau KCL, masukkan angka qty-nya ke `qty_bsr`, `qty_tgh`, atau `qty_kcl`.\n" .
                   "2. TOTAL HARGA: Cari angka di posisi PALING KANAN. Jika ada angka menyempil di baris bawahnya tapi posisinya di paling kanan (ujung), ITULAH `total_price` yang benar (contoh: 17.000 atau 20.500).\n" .
                   "3. UNIT PRICE: SELALU kembalikan `null` untuk `unit_price`. Jangan diisi angka apapun.\n\n" .
                   "=== PERINTAH ===\nBaca gambar invoice di atas dan kembalikan JSON array sesuai format. Tidak ada teks selain JSON.";

        return implode("\n\n", $parts);
    }
}
