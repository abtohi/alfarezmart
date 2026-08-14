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
 * @package AlfarezMart\Services\Invoice\Skills
 */
class MdrInvoiceSkill implements InvoiceSkillInterface
{
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
        $lines[] = 'Kamu adalah AI spesialis analisis invoice PT Medan Distribusindo Raya (MDR / Wings Group).';
        $lines[] = 'Tugasmu adalah membaca gambar faktur/invoice MDR dengan tingkat presisi 100%.';
        $lines[] = '';
        $lines[] = '## STRUKTUR TABEL FAKTUR MDR:';
        $lines[] = 'Urutan kolom pada tabel faktur MDR adalah:';
        $lines[] = '1. QUANTITY (contoh: "10 BOX", "3 PCS", "1 BOX", "36 PCS")';
        $lines[] = '2. KODE BARANG (contoh: "20270", "20226", "20234", "20031", "1020087", "1220055", "81006")';
        $lines[] = '3. BATCH (biasanya kosong)';
        $lines[] = '4. NAMA BARANG (contoh: "ALE ALE LECI CUP 180ML", "SEDAAP MIE KOREAN SPICY CHKN BAG 87GR", "DAIA POWDER DET PUTIH SCT 23GR")';
        $lines[] = '5. ISI (Pcs) (abaikan untuk kalkulasi)';
        $lines[] = '6. HARGA (Rp.) (abaikan)';
        $lines[] = '7. PROMO DISCOUNT (abaikan)';
        $lines[] = '8. REGULAR DISCOUNT (abaikan)';
        $lines[] = '9. JUMLAH (Rp.) (KOLOM PALING KANAN — TOTAL PEMBELIAN AKHIR BARIS)';
        $lines[] = '';
        $lines[] = '## ATURAN EKSTRAKSI MDR:';
        $lines[] = '1. "supplier_code": Ambil angka KODE BARANG persis seperti di invoice.';
        $lines[] = '2. "qty": Ambil angka dari kolom QUANTITY (contoh "10 BOX" -> qty: 10, unit: "BOX").';
        $lines[] = '3. "total_price": Ambil nilai angka dari kolom JUMLAH (Rp.) di PALING KANAN (contoh: 194.000 -> 194000).';
        $lines[] = '   - Perhatikan format angka Indonesia: titik (.) adalah pemisah ribuan.';
        $lines[] = '4. "name": Ambil teks NAMA BARANG secara lengkap.';
        $lines[] = '5. "unit_price": Biarkan null (sistem backend akan menghitung total_price / qty).';
        $lines[] = '6. Abaikan baris pindahan "Pindahan dari halaman ...", "SUB TOTAL", atau catatan kaki.';
        $lines[] = '';

        if ($isCorrectionPass) {
            $lines[] = '## MODE KOREKSI: Periksa ulang baris yang dicurigai tidak lengkap.';
            $lines[] = '';
        }

        $lines[] = '## FORMAT OUTPUT JSON (HANYA JSON ARRAY VALID, TANPA TEKS LAIN):';
        $lines[] = '[';
        $lines[] = '  {';
        $lines[] = '    "supplier_code": "20270",';
        $lines[] = '    "name": "ALE ALE LECI CUP 180ML",';
        $lines[] = '    "qty": 10,';
        $lines[] = '    "unit": "BOX",';
        $lines[] = '    "total_price": 194000,';
        $lines[] = '    "unit_price": null';
        $lines[] = '  }';
        $lines[] = ']';

        return implode("\n", $lines);
    }

    public function getUserPromptHints(): string
    {
        return "Invoice ini terdeteksi sebagai faktur PT Medan Distribusindo Raya (MDR). Gunakan kolom KODE BARANG, QUANTITY, NAMA BARANG, dan JUMLAH paling kanan.";
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
            // Remove Rp, dots, spaces
            $cleaned = preg_replace('/[^0-9,]/', '', str_replace('.', '', $rawTotal));
            $cleaned = str_replace(',', '.', $cleaned);
            $totalPrice = (float)$cleaned;
        } else {
            $totalPrice = (float)$rawTotal;
        }

        // Calculate unit_price = Total / Qty
        $unitPrice = $qty > 0 ? round($totalPrice / $qty, 2) : $totalPrice;

        return [
            'name'                 => $name,
            'supplier_invoice_name'=> $name,
            'supplier_code'        => $code,
            'qty'                  => $qty,
            'unit'                 => $unit,
            'total_price'          => $totalPrice,
            'unit_price'           => $unitPrice,
            'skill_used'           => 'mdr'
        ];
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
