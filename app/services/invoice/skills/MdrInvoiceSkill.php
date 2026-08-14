<?php
require_once __DIR__ . '/InvoiceSkillInterface.php';

/**
 * MdrInvoiceSkill
 *
 * Dedicated AI scanning skill for PT Medan Distribusindo Raya (MDR / Wings Group).
 *
 * MDR Invoice Structure:
 * - Columns: QUANTITY | KODE BARANG | BATCH | NAMA BARANG | ISI (Pcs) | HARGA (Rp.) | PROMO DISCOUNT | REGULAR DISCOUNT | JUMLAH (Rp.)
 * - Calculation: unit_price = JUMLAH / QUANTITY
 * - Packaging Level: Matched via price distance against product_packagings / purchase history.
 *
 * Smart Name Parsing:
 * - Extracts brand, variant, weight/volume from MDR-formatted product names
 * - Example: "DAIA POWDER DET PUTIH SCT 23GR" → brand=DAIA, variant=PUTIH, weight=23, weight_unit=GR
 * - Example: "ROYALE PREM SPRING BLOSSOM SCT 13ML 4+1" → brand=ROYALE, variant=SPRING BLOSSOM, weight=13, weight_unit=ML
 *
 * @package AlfarezMart\Services\Invoice\Skills
 */
class MdrInvoiceSkill implements InvoiceSkillInterface
{
    /**
     * Common MDR abbreviations found in invoice product names.
     * Used to expand names for better matching against database product names.
     */
    const MDR_ABBREVIATIONS = [
        'det'    => 'detergent',
        'prem'   => 'premium',
        'sct'    => 'sachet',
        'btl'    => 'botol',
        'bks'    => 'bungkus',
        'pwdr'   => 'powder',
        'liq'    => 'liquid',
        'cln'    => 'clean',
        'frsh'   => 'fresh',
        'chkn'   => 'chicken',
        'spcy'   => 'spicy',
        'orig'   => 'original',
        'spc'    => 'special',
        'reg'    => 'regular',
        'flr'    => 'floor',
        'clnr'   => 'cleaner',
        'wht'    => 'white',
        'grn'    => 'green',
        'blu'    => 'blue',
        'cln&fresh' => 'clean & fresh',
        'clean&fresh' => 'clean & fresh',
        'cleanfresh' => 'clean fresh',
    ];

    /**
     * Known Wings Group brand names to help identify brand boundary in invoice names.
     */
    const KNOWN_BRANDS = [
        'daia', 'so klin', 'soklin', 'wings', 'ale ale', 'ale-ale',
        'mie sedaap', 'sedaap', 'mama lemon', 'mama lime', 'mamalemon',
        'nuvo', 'giv', 'ciptadent', 'zinc', 'kodomo', 'emeron',
        'ekonomi', 'boom', 'gentle gen', 'gentlegen', 'softener',
        'royale', 'harpic', 'softly', 'wipol', 'super pell', 'superpell',
        'sweetener', 'top', 'shinzu', 'jas jus', 'floridina',
    ];

    public function getSkillKey(): string
    {
        return 'mdr';
    }

    public function getSupplierName(): string
    {
        return 'PT Medan Distribusindo Raya (MDR)';
    }

    public function getDetectionSignatures(): array
    {
        return [
            'medan distribusindo raya',
            'medan distribusindo',
            'distribusindo raya',
            'pt mdr',
            'mdr',
            '0228-01-001633-30-1', // Bank BRI Rekening MDR
            '022801001633301',
            'no. shipment',
            'isi (pcs)',
            'promo discount',
            'regular discount',
        ];
    }

    public function getSystemPrompt(bool $isCorrectionPass = false): string
    {
        $lines = [];
        $lines[] = 'Kamu adalah AI OCR & data extractor presisi tinggi spesialis faktur/invoice PT Medan Distribusindo Raya (MDR / Wings Group).';
        $lines[] = 'Tugas utamamu: Ekstrak SEMUA baris produk dari baris pertama sampai baris terakhir tabel secara lengkap 100% tanpa ada yang terlewat atau digabungkan!';
        $lines[] = '';
        $lines[] = '## STRUKTUR KOLOM FAKTUR MDR (Kiri ke Kanan):';
        $lines[] = '1. QUANTITY (misal: "10 BOX", "1 BOX", "360 PCS")';
        $lines[] = '2. KODE BARANG (misal: "609866", "609869", "60828", "1060495", "60490", "60870", "61023", "1463000", "1461238", "1480369")';
        $lines[] = '3. BATCH (abaikan / kosong)';
        $lines[] = '4. NAMA BARANG (misal: "DAIA POWDER DET PUTIH SCT 23GR", "DAIA POWDER DET CLEAN&FRESH SCT 45GR", "DAIA POWDER DET LILY JASMINE SCT 45GR", "DAIA POWDER DET ROMANTIC PINK SCT 47GR", "DAIA POWDER DET VIOLET PASSION SCT 47GR", "ROYALE PREM SPRING BLOSSOM SCT 13ML 4+1")';
        $lines[] = '5. ISI (Pcs) (jumlah isi kemasan)';
        $lines[] = '6. HARGA (Rp.) (harga gross per box)';
        $lines[] = '7. PROMO DISCOUNT (potongan promo)';
        $lines[] = '8. REGULAR DISCOUNT (potongan reguler)';
        $lines[] = '9. JUMLAH (Rp.) (KOLOM PALING KANAN — TOTAL PEMBELIAN AKHIR BARIS SETELAH DISKON)';
        $lines[] = '';
        $lines[] = '## ATURAN EKSTRAKSI SANGAT KRUSIAL:';
        $lines[] = '1. JANGAN PERNAH MENGGABUNGKAN / MELEWATKAN BARIS YANG HARGANYA SAMA!';
        $lines[] = '   - Pada faktur MDR sering terdapat 5-10 baris dengan harga yang sama (misalnya varian Daia 45g & 47g harga 55.800).';
        $lines[] = '   - SEMUA varian (Clean&Fresh, Fruity, Lily Jasmine, Romantic Pink, Violet Passion, Putih, Soft) ADALAH BARIS TERPISAH dan WAJIB MASUK SEMUANYA ke dalam JSON!';
        $lines[] = '2. BACA DARI ATAS KE BAWAH SECARA BERURUTAN HINGGA GARIS "End Of Document".';
        $lines[] = '3. "supplier_code": Ambil angka KODE BARANG persis seperti di kolom ke-2.';
        $lines[] = '4. "name": Ambil teks NAMA BARANG lengkap persis di faktur.';
        $lines[] = '5. "qty": Ambil angka dari kolom QUANTITY (contoh "10 BOX" -> 10, "1 BOX" -> 1).';
        $lines[] = '6. "unit": Ambil teks satuan ("BOX", "PCS", "CTN", "DUS").';
        $lines[] = '7. "total_price": Ambil nilai angka dari kolom JUMLAH (Rp.) di PALING KANAN tabel (misal "474.000" -> 474000, "55.800" -> 55800, "105.984" -> 105984).';
        $lines[] = '   - Abaikan titik pemisah ribuan.';
        $lines[] = '8. "unit_price": Biarkan null.';
        $lines[] = '9. ABAIKAN baris non-produk: "Pindahan dari halaman ...", "SUB TOTAL", catatan kaki, tanda tangan, nomor rekening.';
        $lines[] = '';

        if ($isCorrectionPass) {
            $lines[] = '## MODE KOREKSI: Periksa ulang baris yang dicurigai tidak lengkap.';
            $lines[] = '';
        }

        $lines[] = '## FORMAT OUTPUT JSON (HANYA JSON ARRAY VALID, TANPA PENJELASAN LAIN):';
        $lines[] = '[';
        $lines[] = '  {';
        $lines[] = '    "supplier_code": "609866",';
        $lines[] = '    "name": "DAIA POWDER DET PUTIH SCT 23GR",';
        $lines[] = '    "qty": 10,';
        $lines[] = '    "unit": "BOX",';
        $lines[] = '    "total_price": 474000,';
        $lines[] = '    "unit_price": null';
        $lines[] = '  },';
        $lines[] = '  {';
        $lines[] = '    "supplier_code": "60490",';
        $lines[] = '    "name": "DAIA POWDER DET LILY JASMINE SCT 45GR",';
        $lines[] = '    "qty": 1,';
        $lines[] = '    "unit": "BOX",';
        $lines[] = '    "total_price": 55800,';
        $lines[] = '    "unit_price": null';
        $lines[] = '  },';
        $lines[] = '  {';
        $lines[] = '    "supplier_code": "1463000",';
        $lines[] = '    "name": "ROYALE PREM SPRING BLOSSOM SCT 13ML 4+1",';
        $lines[] = '    "qty": 1,';
        $lines[] = '    "unit": "BOX",';
        $lines[] = '    "total_price": 105984,';
        $lines[] = '    "unit_price": null';
        $lines[] = '  }';
        $lines[] = ']';

        return implode("\n", $lines);
    }

    public function getUserPromptHints(): string
    {
        return "Invoice PT Medan Distribusindo Raya (MDR / Wings Group). " .
               "BACA BARIS PER BARIS DARI PALING ATAS SAMPAI GARIS 'End Of Document'. " .
               "PENTING: Jangan skip baris yang harganya sama (misal Daia 55.800 ada 5 varian berbeda: Clean&Fresh, Fruity, Lily Jasmine, Romantic Pink, Violet Passion). Semua varian WAJIB masuk ke array JSON!";
    }

    public function parseItem(array $rawItem): array
    {
        $name = trim($rawItem['name'] ?? $rawItem['product_name'] ?? '');
        $code = trim($rawItem['supplier_code'] ?? $rawItem['code'] ?? '');
        
        // Clean numeric qty
        $rawQty = $rawItem['qty'] ?? 1;
        $qty = is_numeric($rawQty) ? max(1, (float)$rawQty) : 1;
        
        $unit = strtoupper(trim($rawItem['unit'] ?? ''));
        
        // Clean total_price
        $rawTotal = $rawItem['total_price'] ?? $rawItem['total'] ?? 0;
        if (is_string($rawTotal)) {
            $cleaned = preg_replace('/[^0-9,]/', '', str_replace('.', '', $rawTotal));
            $cleaned = str_replace(',', '.', $cleaned);
            $totalPrice = (float)$cleaned;
        } else {
            $totalPrice = (float)$rawTotal;
        }

        // Calculate unit_price = Total / Qty
        $unitPrice = $qty > 0 ? round($totalPrice / $qty, 2) : $totalPrice;

        // === SMART NAME PARSING ===
        // Extract brand, variant, weight/volume from MDR invoice name
        $parsed = $this->parseMdrProductName($name);

        return [
            'name'                  => $name,
            'supplier_invoice_name' => $name,
            'expanded_name'         => $parsed['expanded_name'],
            'supplier_code'         => $code,
            'qty'                   => $qty,
            'unit'                  => $unit,
            'total_price'           => $totalPrice,
            'unit_price'            => $unitPrice,
            'brand'                 => $parsed['brand'],
            'variant'               => $parsed['variant'],
            'weight'                => $parsed['weight'],
            'weight_unit'           => $parsed['weight_unit'],
            'packaging_type'        => $parsed['packaging_type'],
            'skill_used'            => 'mdr'
        ];
    }

    /**
     * Parse MDR invoice product name to extract brand, variant, weight, packaging type.
     *
     * Examples:
     *   "DAIA POWDER DET PUTIH SCT 23GR"          → brand=DAIA, variant=PUTIH, weight=23, unit=g, pkg=sachet
     *   "ROYALE PREM SPRING BLOSSOM SCT 13ML 4+1"  → brand=ROYALE, variant=SPRING BLOSSOM, weight=13, unit=ml, pkg=sachet
     *   "ALE ALE LECI CUP 180ML"                   → brand=ALE ALE, variant=LECI, weight=180, unit=ml, pkg=cup
     *   "SEDAAP MIE KOREAN SPICY CHKN BAG 87GR"    → brand=SEDAAP, variant=KOREAN SPICY CHICKEN, weight=87, unit=g, pkg=bag
     *   "DAIA POWDER DET CLEAN&FRESH SCT 45GR"     → brand=DAIA, variant=CLEAN & FRESH, weight=45, unit=g, pkg=sachet
     */
    private function parseMdrProductName(string $name): array
    {
        $result = [
            'brand'          => '',
            'variant'        => '',
            'weight'         => null,
            'weight_unit'    => '',
            'packaging_type' => '',
            'expanded_name'  => '',
        ];

        if (empty($name)) return $result;

        $nameLower = mb_strtolower(trim($name));

        // 1. Extract weight/volume (e.g., "23GR", "180ML", "500G", "1L", "13ML")
        if (preg_match('/(\d+(?:\.\d+)?)\s*(gr|g|ml|l|kg|oz|cc)\b/i', $name, $wMatch)) {
            $result['weight'] = (float)$wMatch[1];
            $rawUnit = strtolower($wMatch[2]);
            // Normalize weight unit
            $unitMap = ['gr' => 'g', 'g' => 'g', 'ml' => 'ml', 'l' => 'l', 'kg' => 'kg', 'oz' => 'oz', 'cc' => 'ml'];
            $result['weight_unit'] = $unitMap[$rawUnit] ?? $rawUnit;
        }

        // 2. Extract packaging type (SCT, CUP, BTL, BAG, BOX, etc.)
        $pkgPatterns = [
            'sct' => 'sachet', 'sachet' => 'sachet',
            'cup' => 'cup', 'btl' => 'botol', 'bottle' => 'botol',
            'bag' => 'bag', 'bks' => 'bungkus', 'pouch' => 'pouch',
            'can' => 'kaleng', 'tin' => 'kaleng',
            'tube' => 'tube', 'jar' => 'jar',
            'refill' => 'refill', 'rfl' => 'refill',
        ];
        foreach ($pkgPatterns as $abbr => $pkgName) {
            if (preg_match('/\b' . preg_quote($abbr, '/') . '\b/i', $name)) {
                $result['packaging_type'] = $pkgName;
                break;
            }
        }

        // 3. Detect brand from known Wings brands
        $detectedBrand = '';
        foreach (self::KNOWN_BRANDS as $brand) {
            if (stripos($nameLower, $brand) === 0 || stripos($nameLower, $brand) !== false) {
                if (strlen($brand) > strlen($detectedBrand)) {
                    $detectedBrand = $brand;
                }
            }
        }
        $result['brand'] = $detectedBrand;

        // 4. Extract variant = everything between brand+category keywords and packaging/weight
        // Strip known non-variant tokens
        $stripTokens = [
            'powder', 'det', 'detergent', 'liquid', 'cream', 'bar', 'soap',
            'mie', 'mi', 'instant', 'noodle', 'premium', 'prem',
            'sct', 'sachet', 'cup', 'btl', 'botol', 'bag', 'bks', 'bungkus',
            'pouch', 'can', 'tin', 'tube', 'jar', 'refill', 'rfl',
            'box', 'pcs', 'pack', 'prg',
        ];

        // Remove brand, category keywords, packaging, weight/size, and "4+1" patterns
        $variantStr = $nameLower;
        if (!empty($detectedBrand)) {
            $variantStr = preg_replace('/\b' . preg_quote($detectedBrand, '/') . '\b/i', '', $variantStr);
        }
        foreach ($stripTokens as $tok) {
            $variantStr = preg_replace('/\b' . preg_quote($tok, '/') . '\b/i', '', $variantStr);
        }
        // Remove weight/volume pattern
        $variantStr = preg_replace('/\d+(?:\.\d+)?\s*(gr|g|ml|l|kg|oz|cc)\b/i', '', $variantStr);
        // Remove "4+1", "3+1" patterns
        $variantStr = preg_replace('/\d\+\d/', '', $variantStr);
        // Remove remaining digits at boundaries
        $variantStr = preg_replace('/\b\d+\b/', '', $variantStr);
        // Clean special chars and normalize
        $variantStr = preg_replace('/[&+]/', ' ', $variantStr);
        $variantStr = preg_replace('/\s+/', ' ', trim($variantStr));

        $result['variant'] = ucwords(trim($variantStr));

        // 5. Build expanded name with abbreviations expanded
        $words = preg_split('/\s+/', mb_strtolower(trim($name)));
        $expandedWords = [];
        foreach ($words as $word) {
            $clean = preg_replace('/[^a-z0-9&]/', '', $word);
            $expandedWords[] = self::MDR_ABBREVIATIONS[$clean] ?? $word;
        }
        $result['expanded_name'] = implode(' ', $expandedWords);

        return $result;
    }

    public function determinePackagingLevel(
        float $unitPrice,
        array $packagings,
        string $extractedUnit = '',
        ?float $lastBuyPrice = null
    ): array {
        if (empty($packagings)) {
            return [
                'packaging' => null,
                'level'     => 1,
                'strategy'  => 'default_level_1'
            ];
        }

        // 1. PRICE DISTANCE STRATEGY (Primary for MDR)
        // Compare calculated unit_price against buy_price of each packaging level
        if ($unitPrice > 0) {
            $bestPkg = null;
            $minDiff = PHP_FLOAT_MAX;
            $bestLevel = 1;

            foreach ($packagings as $pkg) {
                $pkgBuyPrice = (float)($pkg['buy_price'] ?? 0);
                $level = (int)($pkg['level'] ?? 1);

                if ($pkgBuyPrice > 0) {
                    $diff = abs($pkgBuyPrice - $unitPrice);
                    $relativeDiff = $diff / $pkgBuyPrice;

                    // If relative difference is within 45%, evaluate as candidate
                    if ($diff < $minDiff && $relativeDiff <= 0.45) {
                        $minDiff = $diff;
                        $bestPkg = $pkg;
                        $bestLevel = $level;
                    }
                }
            }

            // Also check if lastBuyPrice matches closely
            if ($lastBuyPrice !== null && $lastBuyPrice > 0) {
                $diffLast = abs($lastBuyPrice - $unitPrice);
                if ($diffLast / $lastBuyPrice <= 0.3) {
                    // Find packaging matching lastBuyPrice
                    foreach ($packagings as $pkg) {
                        if (abs((float)$pkg['buy_price'] - $lastBuyPrice) < 1.0) {
                            $bestPkg = $pkg;
                            $bestLevel = (int)$pkg['level'];
                            break;
                        }
                    }
                }
            }

            if ($bestPkg !== null) {
                return [
                    'packaging' => $bestPkg,
                    'level'     => $bestLevel,
                    'strategy'  => 'price_distance'
                ];
            }
        }

        // 2. UNIT NAME MATCHING STRATEGY (Fallback)
        if (!empty($extractedUnit)) {
            $unitLower = strtolower($extractedUnit);
            
            // Map common MDR unit terms
            $unitAliases = [
                'box' => ['karton', 'box', 'dus', 'ctn', 'pack'],
                'ctn' => ['karton', 'box', 'dus', 'ctn'],
                'dus' => ['karton', 'box', 'dus', 'ctn'],
                'pcs' => ['pcs', 'biji', 'satuan', 'buah', 'botol', 'btl', 'sachet', 'sct', 'bks'],
                'sct' => ['sachet', 'sct', 'pcs', 'bungkus', 'bks'],
                'btl' => ['botol', 'btl', 'pcs']
            ];

            $searchAliases = $unitAliases[$unitLower] ?? [$unitLower];

            foreach ($packagings as $pkg) {
                $pkgUnitName = strtolower(trim($pkg['unit_name'] ?? ''));
                foreach ($searchAliases as $alias) {
                    if (strpos($pkgUnitName, $alias) !== false) {
                        return [
                            'packaging' => $pkg,
                            'level'     => (int)$pkg['level'],
                            'strategy'  => 'unit_name_match'
                        ];
                    }
                }
            }
        }

        // 3. DEFAULT HIGHEST OR LEVEL 1
        $fallback = $packagings[0];
        return [
            'packaging' => $fallback,
            'level'     => (int)($fallback['level'] ?? 1),
            'strategy'  => 'fallback_level'
        ];
    }
}
