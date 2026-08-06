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

        $lines[] = 'Kamu adalah sistem OCR invoice yang sangat akurat untuk toko retail Indonesia.';
        $lines[] = 'Tugasmu: baca gambar invoice/faktur dan ekstrak SEMUA item menjadi JSON array.';
        $lines[] = '';

        $lines[] = '## ATURAN WAJIB:';
        $lines[] = '1. Output HANYA JSON array valid — tanpa markdown, tanpa penjelasan.';
        $lines[] = '2. EKSTRAK SELURUH BARIS ITEM dari baris pertama sampai terakhir. DILARANG skip item!';
        $lines[] = '3. Format Rupiah Indonesia: titik (.) = pemisah ribuan, koma (,) = desimal. "5.500" = Rp5.500.';
        $lines[] = '4. Jika nilai tidak terbaca jelas, gunakan null.';
        $lines[] = '5. Kemasan: CTN/DUS/KRT=Karton, PCS/BTL/BKS=satuan kecil, RENCENG/SLOP=level menengah.';
        $lines[] = '';

        $lines[] = '## STRATEGI BACA INVOICE:';
        $lines[] = '- INVOICE TERBALIK/ROTASI: Jika teks terbalik, baca dari arah yang benar. Jangan skip.';
        $lines[] = '- INVOICE TULIS TANGAN: Baca sebaik mungkin, gunakan konteks harga wajar.';
        $lines[] = '- INVOICE DOT-MATRIX: Perhatikan kolom Ctl (karton) dan Pcs terpisah. Qty = angka di kolom Ctl atau Pcs.';
        $lines[] = '- INVOICE TABEL PANJANG: Baca dari atas ke bawah SEMUA baris tanpa terkecuali.';
        $lines[] = '- KOLOM BSR/TGH/KCL: Jika ada, isi qty_bsr, qty_tgh, qty_kcl. Qty utama = total tertinggi yang relevan.';
        $lines[] = '';

        $lines[] = '## PENENTUAN HARGA:';
        $lines[] = '- total_price: angka di kolom PALING KANAN setiap baris item.';
        $lines[] = '- unit_price: hitung dari total_price / qty. Jika bisa dibaca langsung, isi langsung.';
        $lines[] = '- Jika total_price muncul di BARIS BAWAH item (posisi paling kanan), ambil angka tersebut.';
        $lines[] = '';

        if ($isCorrectionPass) {
            $lines[] = '## MODE KOREKSI';
            $lines[] = 'Ini scan ulang. Perhatikan khusus item di DAFTAR KOREKSI. Pastikan qty × unit_price ≈ total_price.';
            $lines[] = '';
        }

        $lines[] = '## FORMAT OUTPUT:';
        $lines[] = '[{"name":"Nama produk","supplier_code":"KODE","qty":2,"unit":"Karton","qty_bsr":0,"qty_tgh":2,"qty_kcl":0,"unit_price":8500,"total_price":17000,"discount":0,"brand":"Merk","variant":"Varian","size":"12x300ml","barcode":null,"notes":null}]';

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
            $parts[] = "=== INFO GAMBAR ===\n" . implode("; ", $imageHints['hints']);
        }

        // === BAGIAN 3: KONTEKS PRODUK SUPPLIER (max 50 items to save tokens) ===
        if (!empty($supplierProducts)) {
            $lines   = ["=== PRODUK SUPPLIER ==="];
            $lines[] = "Cocokkan nama/kode invoice dengan daftar ini:";
            $count   = 0;
            foreach ($supplierProducts as $p) {
                if ($count >= 50) { // Reduced from 80 to 50 to save tokens
                    $lines[] = "... +" . (count($supplierProducts) - $count) . " produk lainnya";
                    break;
                }
                // Compact format: CODE | Name | Packaging info
                $line = "- {$p['code']}|{$p['full_name']}";
                if (!empty($p['supplier_product_code'])) {
                    $line .= "|SC:{$p['supplier_product_code']}";
                }
                if (!empty($p['supplier_invoice_name'])) {
                    $line .= "|INV:{$p['supplier_invoice_name']}";
                }
                if (!empty($p['packagings'])) {
                    $pkgParts = [];
                    foreach ($p['packagings'] as $pkg) {
                        $price = number_format($pkg['buy_price'], 0, ',', '.');
                        $pkgParts[] = "L{$pkg['level']}:{$pkg['unit_name']}@{$price}";
                    }
                    $line .= "|" . implode(',', $pkgParts);
                }
                $lines[] = $line;
                $count++;
            }
            $parts[] = implode("\n", $lines);
        }

        // === BAGIAN 4: TEMPLATE INVOICE SEBELUMNYA ===
        if (!empty($template)) {
            $lines = ["=== TEMPLATE SEBELUMNYA ==="];
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
            $parts[] = implode("\n", $lines);
        }

        // === BAGIAN 5: DAFTAR KOREKSI (hanya pada correction pass) ===
        if ($isCorrectionPass && !empty($correctionHints)) {
            $lines   = ["=== KOREKSI DIPERLUKAN ==="];
            foreach ($correctionHints as $hint) {
                $line = "- \"{$hint['name']}\"";
                if (!empty($hint['issues'])) {
                    $line .= ": " . implode(', ', $hint['issues']);
                }
                $lines[] = $line;
            }
            $parts[] = implode("\n", $lines);
        }

        // === BAGIAN 6: PERINTAH AKHIR (sangat singkat) ===
        $parts[] = "Baca gambar invoice dan kembalikan JSON array lengkap. Output hanya JSON, tidak ada teks lain.";

        return implode("\n\n", $parts);
    }
}
