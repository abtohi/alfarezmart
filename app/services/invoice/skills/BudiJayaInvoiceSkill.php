<?php
require_once __DIR__ . '/InvoiceSkillInterface.php';

/**
 * BudiJayaInvoiceSkill
 *
 * Dedicated AI scanning skill for CV. Budi Jaya (Distributor Mayora / Roma / Torabika).
 *
 * Invoice Layout:
 * - Header: CV. BUDI JAYA R.PRAPAT, No Faktur, Tgl Faktur, Salesman
 * - Table Columns:
 *   - Pcode        : Kode produk angka (e.g., 310837, 313715, 410068, 410471, 410566)
 *   - Nama Produk  : Nama produk lengkap + packing info (e.g., ROMA KELAPA 4BOX7PAK300G, SLAI OLAI SCH PINEAPPLE 12GBX10SCX32G)
 *   - BSR          : Qty Karton / Dus Besar
 *   - TGH          : Qty Box / Pak Tengah (e.g., 2, 4, 1)
 *   - KCL          : Qty Satuan / Kecil
 *   - * H Ctn      : Harga kotor per Karton
 *   - *Dis Req     : Diskon Request
 *   - *Dis Ext1/2  : Diskon Ekstra
 *   - *H Net Ctn   : Harga Bersih per Karton
 *   - *Neto Ket    : TOTAL HARGA AKHIR BARIS (e.g., 114,800, 32,000, 39,600, 64,000, 57,500)
 *
 * @package AlfarezMart\Services\Invoice\Skills
 */
class BudiJayaInvoiceSkill implements InvoiceSkillInterface
{
    /**
     * Common Mayora / Budi Jaya abbreviations found in invoice product names.
     */
    const ABBREVIATIONS = [
        'sch'     => 'sachet',
        'sct'     => 'sachet',
        'pak'     => 'pack',
        'pax'     => 'pack',
        'gbx'     => 'box',
        'box'     => 'box',
        'ctn'     => 'karton',
        'bks'     => 'bungkus',
        'btl'     => 'botol',
        'klg'     => 'kaleng',
        'klp'     => 'kelapa',
        'cok'     => 'cokelat',
        'choc'    => 'chocolate',
        'prm'     => 'promo',
        'peabtr'  => 'peanut butter',
        'bty'     => 'beauty',
        'bbq'     => 'barbeque',
        'splendid'=> 'splendid',
        'vnl'     => 'vanila',
        'vanila'  => 'vanila',
    ];

    /**
     * Known Mayora brands distributed by CV. Budi Jaya.
     */
    const KNOWN_BRANDS = [
        'roma', 'slai olai', 'slai-olai', 'bonita', 'apetito', 'better',
        'sari gandum', 'sarigandum', 'malkist', 'biskuit kelapa', 'choki choki',
        'kopiko', 'torabika', 'beng beng', 'beng-beng', 'astor', 'energen',
        'danisa', 'wafello', 'superstar', 'cal cheese', 'le minerale',
        'teh pucuk', 'pucuk harum', 'q guave', 'tora cafe',
    ];

    public function getSkillKey(): string
    {
        return 'budi_jaya';
    }

    public function getSupplierName(): string
    {
        return 'CV. Budi Jaya (Mayora Distributor)';
    }

    public function getDetectionSignatures(): array
    {
        return [
            'budi jaya',
            'cv. budi jaya',
            'cv budi jaya',
            'rantau prapat',
            'r.prapat',
            'jl olah raga',
            '4/4417',
            'h net ctn',
            'neto ket',
            'pcode',
            'roma kelapa',
            'slai olai',
            'sari gandum',
            'apetito',
        ];
    }

    public function getSystemPrompt(bool $isCorrectionPass = false): string
    {
        $lines = [];
        $lines[] = 'Kamu adalah AI OCR khusus ekstraksi faktur/invoice CV. BUDI JAYA (Distributor Mayora / Roma / Slai Olai / Torabika).';
        $lines[] = 'Tugasmu adalah membaca seluruh baris produk pada tabel faktur ini dan mengekstraknya menjadi JSON array murni.';
        $lines[] = '';
        $lines[] = '## STRUKTUR TABEL FAKTUR CV. BUDI JAYA:';
        $lines[] = '1. Kolom "Pcode": Kode angka produk (misal: "310837", "313715", "410068", "410471", "410566", "410648", "410665", "410761", "410898", "410973", "410974", "411037", "411043", "410972").';
        $lines[] = '2. Kolom "Nama Produk": Nama barang lengkap (misal: "ROMA KELAPA 4BOX7PAK300G", "SLAI OLAI SCH PINEAPPLE 12GBX10SCX32G", "ROMA BONITA CHOCOLATE BOX 6BOX12SCX30G", "APETITO BBQ 8BOX10BKSX15G", "BETTER CARAMEL SCH PRM 12DBX(10+1)SCX27G").';
        $lines[] = '3. Kolom QTY Bertingkat:';
        $lines[] = '   - "BSR": Jumlah Dus/Karton Besar (biasanya 0 jika beli eceran box)';
        $lines[] = '   - "TGH": Jumlah Box/Pak Tengah (misal: 2, 4, 1)';
        $lines[] = '   - "KCL": Jumlah Pcs/Sachet Kecil (biasanya 0)';
        $lines[] = '   - Total Qty dihitung dari nilai kolom yang > 0 (misal jika TGH = 2, maka qty = 2, unit = "Box" atau "Pak").';
        $lines[] = '4. Kolom "*Neto Ket" (Paling Kanan): Total harga bayar akhir baris setelah diskon (misal: 114,800 -> 114800, 32,000 -> 32000, 39,600 -> 39600, 64,000 -> 64000, 57,500 -> 57500, 119,000 -> 119000).';
        $lines[] = '';
        $lines[] = '## ATURAN EKSTRAKSI:';
        $lines[] = '- Kembalikan HANYA JSON array yang valid tanpa teks penjelasan.';
        $lines[] = '- Baca SEMUA baris produk dari atas sampai baris paling bawah.';
        $lines[] = '- Pastikan nilai "supplier_code" diisi dari kolom "Pcode".';
        $lines[] = '- Pastikan nilai "total_price" diambil dari kolom "*Neto Ket" (kolom paling kanan).';
        $lines[] = '';
        $lines[] = '## CONTOH OUTPUT JSON (HANYA JSON ARRAY VALID):';
        $lines[] = '[';
        $lines[] = '  {';
        $lines[] = '    "supplier_code": "310837",';
        $lines[] = '    "name": "ROMA KELAPA 4BOX7PAK300G",';
        $lines[] = '    "qty": 2,';
        $lines[] = '    "qty_bsr": 0,';
        $lines[] = '    "qty_tgh": 2,';
        $lines[] = '    "qty_kcl": 0,';
        $lines[] = '    "unit": "Box",';
        $lines[] = '    "total_price": 114800,';
        $lines[] = '    "unit_price": 57400';
        $lines[] = '  },';
        $lines[] = '  {';
        $lines[] = '    "supplier_code": "313715",';
        $lines[] = '    "name": "SLAI OLAI SCH PINEAPPLE 12GBX10SCX32G",';
        $lines[] = '    "qty": 2,';
        $lines[] = '    "qty_bsr": 0,';
        $lines[] = '    "qty_tgh": 2,';
        $lines[] = '    "qty_kcl": 0,';
        $lines[] = '    "unit": "Box",';
        $lines[] = '    "total_price": 32000,';
        $lines[] = '    "unit_price": 16000';
        $lines[] = '  }';
        $lines[] = ']';

        return implode("\n", $lines);
    }

    public function getUserPromptHints(): string
    {
        return "Baca faktur CV. BUDI JAYA ini. Kolom 'Pcode' adalah kode barang, kolom 'Nama Produk' adalah nama barang, kolom 'TGH' adalah jumlah box/pak, dan kolom '*Neto Ket' di sebelah kanan adalah total harga.";
    }

    public function parseItem(array $rawItem): array
    {
        $code = trim($rawItem['supplier_code'] ?? $rawItem['pcode'] ?? $rawItem['code'] ?? '');
        $name = trim($rawItem['name'] ?? $rawItem['product_name'] ?? '');

        $rawQty = $rawItem['qty'] ?? 1;
        $qty = is_numeric($rawQty) ? max(1, (float)$rawQty) : 1;

        $qtyBsr = (float)($rawItem['qty_bsr'] ?? 0);
        $qtyTgh = (float)($rawItem['qty_tgh'] ?? 0);
        $qtyKcl = (float)($rawItem['qty_kcl'] ?? 0);

        if ($qtyBsr > 0) {
            $qty = $qtyBsr;
            $unit = 'Karton';
        } elseif ($qtyTgh > 0) {
            $qty = $qtyTgh;
            $unit = 'Box';
        } elseif ($qtyKcl > 0) {
            $qty = $qtyKcl;
            $unit = 'Pcs';
        } else {
            $unit = trim($rawItem['unit'] ?? 'Box');
        }

        // Total Price from *Neto Ket
        $rawTotal = $rawItem['total_price'] ?? $rawItem['neto_ket'] ?? $rawItem['total'] ?? 0;
        if (is_string($rawTotal)) {
            $cleaned = preg_replace('/[^0-9,.]/', '', $rawTotal);
            // Replace thousand dots if needed
            if (strpos($cleaned, ',') !== false && strpos($cleaned, '.') !== false) {
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            } elseif (strpos($cleaned, '.') !== false && substr_count($cleaned, '.') > 1) {
                $cleaned = str_replace('.', '', $cleaned);
            } elseif (strpos($cleaned, ',') !== false) {
                $cleaned = str_replace(',', '', $cleaned);
            }
            $totalPrice = (float)$cleaned;
        } else {
            $totalPrice = (float)$rawTotal;
        }

        $unitPrice = $qty > 0 ? round($totalPrice / $qty, 2) : $totalPrice;

        // Expanded name for matching
        $expandedName = $this->expandAbbreviations($name);
        $parsedMeta = $this->extractMetaFromName($name);

        return [
            'name'                 => $name,
            'supplier_invoice_name'=> $name,
            'expanded_name'        => $expandedName,
            'supplier_code'        => $code,
            'qty'                  => $qty,
            'qty_bsr'              => $qtyBsr,
            'qty_tgh'              => $qtyTgh,
            'qty_kcl'              => $qtyKcl,
            'unit'                 => $unit,
            'total_price'          => $totalPrice,
            'unit_price'           => $unitPrice,
            'brand'                => $parsedMeta['brand'],
            'variant'              => $parsedMeta['variant'],
            'weight'               => $parsedMeta['weight'],
            'weight_unit'          => $parsedMeta['weight_unit'],
            'skill_used'           => 'budi_jaya'
        ];
    }

    private function expandAbbreviations(string $name): string
    {
        $tokens = preg_split('/\s+/', strtolower($name));
        $expanded = [];
        foreach ($tokens as $t) {
            $expanded[] = self::ABBREVIATIONS[$t] ?? $t;
        }
        return implode(' ', $expanded);
    }

    private function extractMetaFromName(string $name): array
    {
        $nameLower = strtolower($name);
        $brand = '';
        foreach (self::KNOWN_BRANDS as $b) {
            if (strpos($nameLower, $b) !== false) {
                $brand = $b;
                break;
            }
        }

        $weight = null;
        $weightUnit = '';
        if (preg_match('/(\d+(?:\.\d+)?)\s*(gr|g|kg|ml|l|oz|sct|sc|pak|bks)\b/i', $name, $m)) {
            $weight = (float)$m[1];
            $weightUnit = strtoupper($m[2]);
        }

        $variant = '';
        $cleanName = preg_replace('/\b\d+[xX]\d+\w*\b/', '', $nameLower);
        if (preg_match('/\b(cokelat|chocolate|cok|kelapa|klp|pineaple|pineapple|pandan|bbq|barbeque|pizza|durian|caramel|vanila|vanilla|keju|cheese|gandum)\b/i', $cleanName, $vm)) {
            $variant = $vm[1];
        }

        return [
            'brand'       => $brand,
            'variant'     => $variant,
            'weight'      => $weight,
            'weight_unit' => $weightUnit
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

        // 1. Match based on price distance
        if ($unitPrice > 0) {
            $bestPkg = null;
            $minDiff = PHP_FLOAT_MAX;
            $bestLevel = 1;

            foreach ($packagings as $pkg) {
                $pkgBuyPrice = (float)($pkg['buy_price'] ?? 0);
                if ($pkgBuyPrice > 0) {
                    $diff = abs($pkgBuyPrice - $unitPrice);
                    if ($diff < $minDiff && ($diff / $pkgBuyPrice) <= 0.40) {
                        $minDiff = $diff;
                        $bestPkg = $pkg;
                        $bestLevel = (int)$pkg['level'];
                    }
                }
            }

            if ($bestPkg) {
                return [
                    'packaging' => $bestPkg,
                    'level'     => $bestLevel,
                    'strategy'  => 'price_distance_match'
                ];
            }
        }

        // 2. Match based on extracted unit name (e.g., Box, Karton, Pcs)
        if (!empty($extractedUnit)) {
            $uLower = strtolower($extractedUnit);
            foreach ($packagings as $pkg) {
                $pUnit = strtolower($pkg['unit_name'] ?? '');
                if ($pUnit === $uLower || strpos($pUnit, $uLower) !== false || strpos($uLower, $pUnit) !== false) {
                    return [
                        'packaging' => $pkg,
                        'level'     => (int)$pkg['level'],
                        'strategy'  => 'unit_name_match'
                    ];
                }
            }
        }

        // 3. Fallback: If unit is Box/Tgh, default to level 2 or 1
        $defaultLevel = 1;
        if (count($packagings) > 1 && (strtolower($extractedUnit) === 'box' || strtolower($extractedUnit) === 'pak')) {
            $defaultLevel = 2;
        }

        return [
            'packaging' => $packagings[$defaultLevel - 1] ?? $packagings[0],
            'level'     => $defaultLevel,
            'strategy'  => 'budi_jaya_default'
        ];
    }
}
