<?php
require_once __DIR__ . '/InvoiceSkillInterface.php';

/**
 * AlamJayaInvoiceSkill
 *
 * Dedicated AI scanning skill for PT Alam Jaya Wirasentosa (Distributor Indofood, CBP, Noodle, Snack, Biscuit, Segitiga Biru, Dbest).
 *
 * Invoice Layout:
 * - Table Header:
 *   - No             : Nomor urut baris (1, 2, 3, ... 26+)
 *   - [Kode Produk]  : Kode Produk Supplier di kolom kedua (e.g. BNCM1, MGCF7, MGCG4, SBE1, GRS, IMGA, K, PABS, PBSS, PMAL, PMBL, PMKL, PMSL, PSAS, DSOB, CBEB19.5, CLGPR65, CLNOS65, CLSTB65, CTFRN65, CTWBEM65, CTWRS62, QBLS, QTBB60, QTBBQ20)
 *   - Barang         : Nama barang di faktur (e.g. NN Fun Play Bubble Puff 11gx96, GT Doublechoc 10,6x12x10 1K, Chitato Lite Nos, Segitiga Biru Ekonomi Pack 1kg, Indomie Goreng Rendang, Pop Mie Snek Ayam Bawang RL, Chitato BBQ 19.5gr Renceng, Chitato LiteGrillPrimeRibs65gr, Qtela Balado 60 Gr)
 *   - Jumlah/Carton  : Isi kemasan per karton (e.g. 96/CRT, 120/CRT, 48/CRT, 12/CRT, 40/Car, 24/Car, 60/Car, 30/Car)
 *   - Qty -> Crt     : Kuantitas karton/dus penuh (e.g. 10, 1, 5, 3, 2)
 *   - Qty -> Pcs     : Kuantitas satuan/eceran/pcs pecahan (e.g. 24, 48, 6, 15)
 *   - Harga Incl. PPN: Harga kotor per karton
 *   - Rp.            : Subtotal kotor
 *   - Discount       : Diskon Program / WBP
 *   - Total          : TOTAL HARGA AKHIR BARIS di kolom paling kanan (e.g. 39.960, 40.400, 44.623, 1.540.200, 114.175, 168.825, 299.850, 52.560, 131.400, 105.000)
 *
 * Special Multi-Invoice / Folded Sheets Feature:
 * - When 2 invoices are folded together into 1 image, reads all rows (e.g. 1-17 on top and 18-26 on bottom) continuously.
 *
 * @package AlfarezMart\Services\Invoice\Skills
 */
class AlamJayaInvoiceSkill implements InvoiceSkillInterface
{
    /**
     * Known abbreviations in PT Alam Jaya Wirasentosa invoices
     */
    const ABBREVIATIONS = [
        'nn'              => 'nyam nyam',
        'gt'              => 'good time',
        'doublecho'       => 'double choc',
        'doublechoc'      => 'double choc',
        'choc'            => 'chocolate',
        'cho'             => 'chocolate',
        'sbe1'            => 'segitiga biru ekonomi 1kg',
        'rl'              => 'renceng',
        'rcg'             => 'renceng',
        'snek'            => 'snek time',
        'lapeer'          => 'lapeer time',
        'lpr'             => 'lapeer time',
        'mos'             => 'rumput laut',
        'nos'             => 'rumput laut',
        'sat'             => 'salmon teriyaki',
        'frn'             => 'mi goreng',
        'grillprimeribs'  => 'grilled prime ribs',
        'gpr'             => 'grilled prime ribs',
        'wafy'            => 'wavy',
        'wavy'            => 'wavy',
        'bbq'             => 'barbeque',
        'soya'            => 'soya',
        'pack'            => 'pack',
        'crt'             => 'karton',
        'car'             => 'karton',
        'ctn'             => 'karton',
        'pcs'             => 'pcs',
        'gr'              => 'gram',
        'g'               => 'gram',
        'kg'              => 'kg',
    ];

    /**
     * Known brands distributed by PT Alam Jaya Wirasentosa
     */
    const KNOWN_BRANDS = [
        'indomie', 'pop mie', 'sarimi', 'supermi', 'chitato', 'chitato lite',
        'chitato wavy', 'qtela', 'cheetos', 'lays', 'doritos', 'good time',
        'nyam nyam', 'segitiga biru', 'cakra kembar', 'kunci biru', 'la fonte',
        'indomilk', 'cap enak', 'tiga sapi', 'dbest', 'd\'best', 'promina',
        'sun', 'bumbu racik', 'racik', 'sambal indofood', 'kecap indofood',
        'duo', 'chiki', 'jet-z', 'maxicorn',
    ];

    public function getSkillKey(): string
    {
        return 'alam_jaya';
    }

    public function getSupplierName(): string
    {
        return 'PT Alam Jaya Wirasentosa';
    }

    public function getDetectionSignatures(): array
    {
        return [
            'alam jaya',
            'alamjaya',
            'wirasentosa',
            'pt alam jaya',
            'pt. alam jaya',
            'pt alamjaya',
            'pt. alamjaya wirasentosa',
            'jumlah / carton',
            'jumlah/carton',
            'harga incl. ppn',
            'incl. ppn',
            'discount program',
            'discount wbp',
            'clnos65',
            'bncm1',
            'sbe1',
            'cbeb19.5',
            'clgpr65',
            'ctfrn65',
            'qbls',
            'qtbb60',
            'qtbbq20',
        ];
    }

    public function getSystemPrompt(bool $isCorrectionPass = false): string
    {
        $lines = [];
        $lines[] = 'Kamu adalah AI OCR dan data extractor presisi tinggi khusus faktur/invoice ' . $this->getSupplierName() . ' (Distributor Indofood, CBP, Snack, Biscuit, Segitiga Biru, Pop Mie, Chitato, Qtela, Good Time, Nyam Nyam).';
        $lines[] = 'Tugas utamamu: Ekstrak SELURUH baris produk pada tabel faktur secara lengkap 100% tanpa ada baris yang terlewat!';
        $lines[] = '';
        $lines[] = '## PENTING (DUA FAKTUR / DILIPAT / DIGABUNG DALAM SATU GAMBAR):';
        $lines[] = '- Jika gambar berisi dua lembar faktur yang dilipat atau disatukan (bagian atas dan bagian bawah), BACA SEMUA BARIS DARI ATAS HINGGA BAWAH secara berurutan (misal baris 1-17 di atas dan baris 18-26 di bawah).';
        $lines[] = '- JANGAN berhenti membaca pada garis lipatan/pembatas!';
        $lines[] = '';
        $lines[] = '## STRUKTUR KOLOM TABEL FAKTUR (Kiri ke Kanan):';
        $lines[] = '1. Kolom "No": Nomor urut baris (1, 2, 3, ...)';
        $lines[] = '2. Kolom "Kode Produk Supplier" (Kolom Kedua tepat setelah No): KODE INI SANGAT PENTING!';
        $lines[] = '   Contoh kode: BNCM1, MGCF7, MGCG4, SBE1, GRS, IMGA, K, PABS, PBSS, PMAL, PMBL, PMKL, PMSL, PSAS, DSOB, CBEB19.5, CLGPR65, CLNOS65, CLSTB65, CTFRN65, CTWBEM65, CTWRS62, QBLS, QTBB60, QTBBQ20.';
        $lines[] = '   WAJIB ekstrak kode ini persis ke field "supplier_code" di JSON.';
        $lines[] = '3. Kolom "Barang": Nama produk lengkap di faktur (misal: "NN Fun Play Bubble Puff 11gx96", "GT Doublechoc 10,6x12x10 1K", "Chitato Lite Nos", "Segitiga Biru Ekonomi Pack 1kg", "Indomie Goreng Rendang", "Pop Mie Snek Ayam Bawang RL").';
        $lines[] = '4. Kolom "Jumlah/Carton": Isi kemasan per karton (misal: "96/CRT", "120/CRT", "40/Car", "30/Car", "60/Car"). Ekstrak ke field "pack_info".';
        $lines[] = '5. Kolom "Qty" terbagi 2 sub-kolom:';
        $lines[] = '   - "Crt": Kuantitas pembelian karton penuh (misal: 10, 1, 5). Jika ada angka di Crt, isi "qty_crt" dan set unit = "CRT".';
        $lines[] = '   - "Pcs": Kuantitas pembelian eceran/satuan pcs (misal: 24, 48, 6, 15). Jika ada angka di Pcs, isi "qty_pcs" dan set unit = "PCS".';
        $lines[] = '6. Kolom "Harga Incl. PPN": Harga kotor per karton.';
        $lines[] = '7. Kolom "Rp.": Total kotor.';
        $lines[] = '8. Kolom "Discount": Diskon Program / WBP (jika ada).';
        $lines[] = '9. Kolom "Total" (Paling Kanan): TOTAL HARGA AKHIR BARIS yang dibayar toko (misal: 39.960 -> 39960, 1.540.200 -> 1540200, 114.175 -> 114175, 52.560 -> 52560, 131.400 -> 131400, 105.000 -> 105000). WAJIB masukkan nilai ini ke "total_price".';
        $lines[] = '';
        $lines[] = '## ATURAN KHUSUS BARIS POTONGAN / KREDIT MINUS:';
        $lines[] = '- Jika ada baris potongan tambahan bertanda minus (contoh baris 5: Segitiga Biru dengan Total -75.550), tandai dengan "is_discount_row": true dan "total_price": -75550.';
        $lines[] = '';
        $lines[] = '## CONTOH OUTPUT JSON (HANYA JSON ARRAY VALID TANPA PENJELASAN):';
        $lines[] = '[';
        $lines[] = '  {';
        $lines[] = '    "no": 1,';
        $lines[] = '    "supplier_code": "BNCM1",';
        $lines[] = '    "name": "NN Fun Play Bubble Puff 11gx96",';
        $lines[] = '    "pack_info": "96/CRT",';
        $lines[] = '    "qty_crt": 0,';
        $lines[] = '    "qty_pcs": 24,';
        $lines[] = '    "qty": 24,';
        $lines[] = '    "unit": "PCS",';
        $lines[] = '    "total_price": 39960';
        $lines[] = '  },';
        $lines[] = '  {';
        $lines[] = '    "no": 4,';
        $lines[] = '    "supplier_code": "SBE1",';
        $lines[] = '    "name": "Segitiga Biru Ekonomi Pack 1kg",';
        $lines[] = '    "pack_info": "12/CRT",';
        $lines[] = '    "qty_crt": 10,';
        $lines[] = '    "qty_pcs": 0,';
        $lines[] = '    "qty": 10,';
        $lines[] = '    "unit": "CRT",';
        $lines[] = '    "discount": 33000,';
        $lines[] = '    "total_price": 1540200';
        $lines[] = '  },';
        $lines[] = '  {';
        $lines[] = '    "no": 19,';
        $lines[] = '    "supplier_code": "CLNOS65",';
        $lines[] = '    "name": "Chitato Lite Nos",';
        $lines[] = '    "pack_info": "30/Car",';
        $lines[] = '    "qty_crt": 0,';
        $lines[] = '    "qty_pcs": 15,';
        $lines[] = '    "qty": 15,';
        $lines[] = '    "unit": "PCS",';
        $lines[] = '    "total_price": 131400';
        $lines[] = '  }';
        $lines[] = ']';

        return implode("\n", $lines);
    }

    public function getUserPromptHints(): string
    {
        return "Perhatikan kolom Kode Produk Supplier di kolom kedua (BNCM1, MGCF7, SBE1, GRS, IMGA, K, PABS, PBSS, PMAL, PMBL, PMKL, PMSL, PSAS, DSOB, CBEB19.5, CLGPR65, CLNOS65, CLSTB65, CTFRN65, CTWBEM65, CTWRS62, QBLS, QTBB60, QTBBQ20). Kolom Qty terbagi menjadi Crt (Karton) dan Pcs (Satuan). Ambil Total Harga dari kolom Total paling kanan.";
    }

    public function parseItem(array $rawItem): array
    {
        $supplierCode = trim((string)($rawItem['supplier_code'] ?? $rawItem['kode_barang'] ?? $rawItem['kode'] ?? ''));
        $name = trim((string)($rawItem['name'] ?? $rawItem['barang'] ?? $rawItem['nama_barang'] ?? ''));
        $packInfo = trim((string)($rawItem['pack_info'] ?? $rawItem['jumlah_carton'] ?? ''));

        // Handle quantity columns (Crt vs Pcs)
        $qtyCrt = isset($rawItem['qty_crt']) ? (float)$rawItem['qty_crt'] : (isset($rawItem['crt']) ? (float)$rawItem['crt'] : 0);
        $qtyPcs = isset($rawItem['qty_pcs']) ? (float)$rawItem['qty_pcs'] : (isset($rawItem['pcs']) ? (float)$rawItem['pcs'] : 0);
        $genericQty = isset($rawItem['qty']) ? (float)$rawItem['qty'] : 1;
        $unit = strtoupper(trim((string)($rawItem['unit'] ?? $rawItem['satuan'] ?? '')));

        if ($qtyCrt > 0 && $qtyPcs <= 0) {
            $qty = $qtyCrt;
            $unit = 'CRT';
            $levelHint = 3; // Karton/Dus
        } elseif ($qtyPcs > 0 && $qtyCrt <= 0) {
            $qty = $qtyPcs;
            $unit = 'PCS';
            $levelHint = 1; // Satuan kecil / eceran
        } elseif ($qtyCrt > 0 && $qtyPcs > 0) {
            // Mixed carton + pieces
            $qty = $qtyCrt;
            $unit = 'CRT';
            $levelHint = 3;
        } else {
            $qty = $genericQty > 0 ? $genericQty : 1;
            $levelHint = (in_array($unit, ['CRT', 'CAR', 'CTN', 'DUS', 'KARTON', 'BOX'])) ? 3 : 1;
        }

        // Handle total price (Column: Total paling kanan)
        $totalPrice = 0;
        if (isset($rawItem['total_price'])) {
            $totalPrice = (float)$rawItem['total_price'];
        } elseif (isset($rawItem['total'])) {
            $totalPrice = (float)$rawItem['total'];
        } elseif (isset($rawItem['subtotal'])) {
            $totalPrice = (float)$rawItem['subtotal'];
        } elseif (isset($rawItem['rp'])) {
            $totalPrice = (float)$rawItem['rp'];
        }

        // Derived unit price (total_price / qty)
        $unitPrice = ($qty > 0 && $totalPrice > 0) ? round($totalPrice / $qty, 2) : 0;

        // Expanded name for matching
        $expandedName = $this->expandName($name);

        return [
            'name'                 => $name,
            'supplier_invoice_name'=> $name,
            'expanded_name'        => $expandedName,
            'supplier_code'        => $supplierCode,
            'pack_info'            => $packInfo,
            'qty'                  => $qty,
            'qty_crt'              => $qtyCrt,
            'qty_pcs'              => $qtyPcs,
            'unit'                 => $unit,
            'packaging_level_hint' => $levelHint,
            'unit_price'           => $unitPrice,
            'total_price'          => $totalPrice,
            'discount'             => (float)($rawItem['discount'] ?? 0),
            'brand'                => $this->extractBrand($name),
            'is_discount_row'      => (bool)($rawItem['is_discount_row'] ?? ($totalPrice < 0)),
            '_raw'                 => $rawItem,
        ];
    }

    /**
     * Expand common Alam Jaya / Indofood abbreviations to full product names.
     */
    public function expandName(string $name): string
    {
        $result = $name;

        // Replace specific product prefixes
        if (preg_match('/^NN\s+/i', $result)) {
            $result = preg_replace('/^NN\s+/i', 'Nyam Nyam ', $result);
        }
        if (preg_match('/^GT\s+/i', $result)) {
            $result = preg_replace('/^GT\s+/i', 'Good Time ', $result);
        }

        // Token replacements
        $words = preg_split('/(\s+)/', $result, -1, PREG_SPLIT_DELIM_CAPTURE);
        $expandedWords = [];
        foreach ($words as $word) {
            $wLower = strtolower(trim($word));
            if (isset(self::ABBREVIATIONS[$wLower])) {
                $expandedWords[] = self::ABBREVIATIONS[$wLower];
            } else {
                $expandedWords[] = $word;
            }
        }
        $result = implode('', $expandedWords);

        // Specific pattern replacements
        $result = str_ireplace('Chitato Lite Nos', 'Chitato Lite Rumput Laut', $result);
        $result = str_ireplace('Chitato Lite Mos', 'Chitato Lite Rumput Laut', $result);
        $result = str_ireplace('Chitato Lite SAT', 'Chitato Lite Salmon Teriyaki', $result);
        $result = str_ireplace('Chitato Wafy FRN', 'Chitato Mi Goreng', $result);
        $result = str_ireplace('Chitato Wavy FRN', 'Chitato Mi Goreng', $result);
        $result = str_ireplace('Chitato Wavy Beef Mala', 'Chitato Beef Mala', $result);
        $result = str_ireplace('Segitiga Biru Ekonomi', 'Segitiga Biru Tepung Terigu Kemasan', $result);
        $result = str_ireplace('D\'BEST BOTOL SOYA', 'Dbest Soya Botol', $result);

        return trim($result);
    }

    public function extractBrand(string $name): string
    {
        $nameLower = strtolower($name);
        foreach (self::KNOWN_BRANDS as $brand) {
            if (strpos($nameLower, $brand) !== false) {
                return ucwords($brand);
            }
        }
        return '';
    }

    public function determinePackagingLevel(
        float $unitPrice,
        array $packagings,
        string $extractedUnit = '',
        ?float $lastBuyPrice = null
    ): array {
        if (empty($packagings)) {
            return ['packaging' => null, 'level' => 1, 'strategy' => 'empty_fallback'];
        }

        $unitUpper = strtoupper(trim($extractedUnit));

        // 1. Explicit Carton / Dus Unit Hint
        if (in_array($unitUpper, ['CRT', 'CAR', 'CTN', 'DUS', 'KARTON', 'BOX'])) {
            // Find highest level packaging (level 3 or max base_qty)
            usort($packagings, fn($a, $b) => ($b['base_qty'] ?? $b['level']) <=> ($a['base_qty'] ?? $a['level']));
            $cartonPkg = $packagings[0];
            return [
                'packaging' => $cartonPkg,
                'level'     => (int)($cartonPkg['level'] ?? 3),
                'strategy'  => 'explicit_carton_unit'
            ];
        }

        // 2. Explicit PCS / Satuan Eceran Unit Hint
        if (in_array($unitUpper, ['PCS', 'BKS', 'BTL', 'SCH', 'SACHET', 'LEMBAR', 'BUTIR'])) {
            // Find smallest level packaging (level 1 or smallest base_qty)
            usort($packagings, fn($a, $b) => ($a['base_qty'] ?? $a['level']) <=> ($b['base_qty'] ?? $b['level']));
            $pcsPkg = $packagings[0];
            return [
                'packaging' => $pcsPkg,
                'level'     => (int)($pcsPkg['level'] ?? 1),
                'strategy'  => 'explicit_pcs_unit'
            ];
        }

        // 3. Price Distance Matching against packagings
        if ($unitPrice > 0) {
            $bestPkg = null;
            $minDiff = PHP_FLOAT_MAX;
            foreach ($packagings as $pkg) {
                $pkgBuy = (float)($pkg['buy_price'] ?? 0);
                if ($pkgBuy > 0) {
                    $diff = abs($pkgBuy - $unitPrice);
                    if ($diff < $minDiff) {
                        $minDiff = $diff;
                        $bestPkg = $pkg;
                    }
                }
            }

            if ($bestPkg !== null && ($minDiff / max($unitPrice, 1)) < 0.35) {
                return [
                    'packaging' => $bestPkg,
                    'level'     => (int)($bestPkg['level'] ?? 1),
                    'strategy'  => 'price_distance'
                ];
            }
        }

        // Default to Level 1
        return ['packaging' => $packagings[0] ?? null, 'level' => 1, 'strategy' => 'default_level_1'];
    }
}
