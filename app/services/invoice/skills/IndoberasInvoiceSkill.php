<?php
require_once __DIR__ . '/InvoiceSkillInterface.php';

/**
 * IndoberasInvoiceSkill
 *
 * Dedicated AI scanning skill for CV. Indoberas (Distributor Beras, Tepung, Rokok, Susu, Sembako).
 *
 * Invoice Layout:
 * - Header: NO FAKTUR (Prefix IB5- / SB1-), SALES: TOKO, Kpd Yth: [SGA1995]/ALFAREZ MART(SRC)
 * - Table Columns:
 *   - NO             : Nomor urut baris (1, 2, 3, ...)
 *   - QUANTITY [ISI] : Jumlah kuantitas + Satuan (e.g., 2 SAK, 1 CRT, 1 CRT x20, 1 BAL, 6 SLO, 2 SLP)
 *   - N A M A  B A R A N G : Nama barang lengkap (e.g., B. 66 SUPER 10KG, TIGA SAPI SACHET PUTIH,
 *                            TEP.ROTI TULIP, TEH SURYA, SAOS IKAN, CAPPUCCINO [TK],
 *                            R.SURYA 12 [TK], R.SURYA 16 COKLAT [TK], R.SAGABOLD 20 [TK], R.BMW HITAM [TK],
 *                            KACANG HIJAU VENEZ BT, TEP.KANJI GUNUNG SALJU BT)
 *   - HARGA @        : Harga satuan per kemasan (e.g., 164.000, 135.000, 259.000, 95.500)
 *   - SUBTOTAL       : Total harga per baris = Qty * Harga @ (e.g., 328.000, 1.554.000)
 * - Footer: Grand Total, Cara Bayar, Sisa Kredit, Tonase, NO.REK: BCA 8235907999 AN: CV. INDOBERAS
 *
 * @package AlfarezMart\Services\Invoice\Skills
 */
class IndoberasInvoiceSkill implements InvoiceSkillInterface
{
    /**
     * Common Indoberas abbreviations found in invoice product names.
     */
    const ABBREVIATIONS = [
        'b.'         => 'beras',
        'b '         => 'beras ',
        'tep.roti'   => 'tepung roti',
        'tep.kanji'  => 'tepung kanji',
        'tep.terigu' => 'tepung terigu',
        'tep.beras'  => 'tepung beras',
        'tep.'       => 'tepung',
        'tep '       => 'tepung ',
        'r.'         => 'rokok',
        'r '         => 'rokok ',
        'sch'        => 'sachet',
        'sct'        => 'sachet',
        'crt'        => 'karton',
        'sak'        => 'sak',
        'bal'        => 'bal',
        'slo'        => 'slop',
        'slp'        => 'slop',
        'bks'        => 'bungkus',
        'btl'        => 'botol',
        'kc.'        => 'kacang',
        'kc '        => 'kacang ',
        'kcg'        => 'kacang',
        'minyak'     => 'minyak',
        'myk'        => 'minyak',
        'gr'         => 'gram',
        'kg'         => 'kg',
    ];

    /**
     * Known brands distributed by CV. Indoberas.
     */
    const KNOWN_BRANDS = [
        'surya', 'gudang garam', 'saga', 'sagabold', 'bmw', 'djarum', 'sampoerna',
        'tiga sapi', 'indomilk', 'frisian flag', 'tulip', 'gunung salju', 'rose brand',
        'segitiga biru', 'cakra kembar', 'kunci biru', 'sunco', 'bimoli', 'filma',
        'cappuccino', 'good day', 'torabika', 'luwak', 'kapal api', 'teh surya',
        'sariwangi', 'sosro', 'pohon pinang', 'venez', '66 super', 'dua lele',
    ];

    public function getSkillKey(): string
    {
        return 'indoberas';
    }

    public function getSupplierName(): string
    {
        return 'CV. Indoberas';
    }

    public function getDetectionSignatures(): array
    {
        return [
            'indoberas',
            'cv. indoberas',
            'cv indoberas',
            '8235907999',         // BCA Rekening CV. Indoberas
            'sga1995',            // Kode Pelanggan Alfarez Mart di Indoberas
            'ib5-',               // Prefix No Faktur Indoberas
            'sb1-',               // Prefix No Faktur Indoberas
            'quantity [isi]',
            'harga @',
            'tonase',
            'sisa kredit',
            'rantau prapat, labuhan batu',
        ];
    }

    public function getSystemPrompt(bool $isCorrectionPass = false): string
    {
        $lines = [];
        $lines[] = 'Kamu adalah AI OCR khusus ekstraksi faktur/invoice CV. INDOBERAS (Distributor Beras, Tepung, Rokok, Susu, Sembako).';
        $lines[] = 'Tugasmu: Membaca seluruh baris produk pada tabel faktur ini dan mengekstraknya menjadi JSON array murni.';
        $lines[] = '';
        $lines[] = '## STRUKTUR TABEL FAKTUR CV. INDOBERAS:';
        $lines[] = '1. Kolom "QUANTITY [ISI]": Angka kuantitas dan satuan kemasan (misal "2 SAK", "1 CRT", "1 CRT x20", "1 BAL", "6 SLO", "2 SLP").';
        $lines[] = '   - "SAK" = Sak / Karung (Beras, Tepung, Kacang Hijau)';
        $lines[] = '   - "CRT" = Karton / Dus (Susu, Teh, Saos)';
        $lines[] = '   - "BAL" = Bal (Kopi, Cappuccino)';
        $lines[] = '   - "SLO" / "SLP" = Slop (Rokok)';
        $lines[] = '   - Jika ada "x20", "x24", "x40" merupakan keterangan jumlah isi dalam kemasan karton.';
        $lines[] = '2. Kolom "N A M A   B A R A N G": Nama barang lengkap persis seperti tertulis di faktur:';
        $lines[] = '   - "B. 66 SUPER 10KG" (B. = Beras)';
        $lines[] = '   - "TIGA SAPI SACHET PUTIH"';
        $lines[] = '   - "TEP.ROTI TULIP" / "TEP.KANJI GUNUNG SALJU BT" (TEP. = Tepung)';
        $lines[] = '   - "TEH SURYA"';
        $lines[] = '   - "CAPPUCCINO [TK]" ([TK] = Tanpa Kupon / Toko)';
        $lines[] = '   - "R.SURYA 12 [TK]" / "R.SURYA 16 COKLAT [TK]" / "R.SAGABOLD 20 [TK]" / "R.BMW HITAM [TK]" (R. = Rokok)';
        $lines[] = '   - "KACANG HIJAU VENEZ BT"';
        $lines[] = '3. Kolom "HARGA @": Harga satuan per kemasan (misal 164.000, 135.000, 171.000, 538.000, 66.000, 122.000, 259.000, 349.000, 245.000, 95.500, 630.000, 260.000).';
        $lines[] = '4. Kolom "SUBTOTAL" (Paling Kanan): Total harga per baris = Qty * Harga @.';
        $lines[] = '';
        $lines[] = '## ATURAN EKSTRAKSI:';
        $lines[] = '- Kembalikan HANYA JSON array yang valid tanpa teks pembuka/penutup atau penjelasan.';
        $lines[] = '- Ekstrak SEMUA baris produk dari nomor urut 1 sampai nomor urut terakhir.';
        $lines[] = '- Bersihkan tanda kurung/tag seperti "[TK]" atau "BT" ke nama produk yang rapi, atau biarkan tetap ada di `name`.';
        $lines[] = '- Format angka harga: hilangkan titik/koma pemisah ribuan (misal 164.000 -> 164000, 95.500 -> 95500, 1.554.000 -> 1554000).';
        $lines[] = '';
        $lines[] = '## CONTOH OUTPUT JSON (HANYA JSON ARRAY VALID):';
        $lines[] = '[';
        $lines[] = '  {';
        $lines[] = '    "supplier_code": "",';
        $lines[] = '    "name": "B. 66 SUPER 10KG",';
        $lines[] = '    "qty": 2,';
        $lines[] = '    "unit": "Sak",';
        $lines[] = '    "unit_price": 164000,';
        $lines[] = '    "total_price": 328000';
        $lines[] = '  },';
        $lines[] = '  {';
        $lines[] = '    "supplier_code": "",';
        $lines[] = '    "name": "TIGA SAPI SACHET PUTIH",';
        $lines[] = '    "qty": 1,';
        $lines[] = '    "unit": "Karton",';
        $lines[] = '    "unit_price": 135000,';
        $lines[] = '    "total_price": 135000';
        $lines[] = '  },';
        $lines[] = '  {';
        $lines[] = '    "supplier_code": "",';
        $lines[] = '    "name": "R.SURYA 12 [TK]",';
        $lines[] = '    "qty": 6,';
        $lines[] = '    "unit": "Slop",';
        $lines[] = '    "unit_price": 259000,';
        $lines[] = '    "total_price": 1554000';
        $lines[] = '  }';
        $lines[] = ']';

        return implode("\n", $lines);
    }

    public function getUserPromptHints(): string
    {
        return "Invoice CV. INDOBERAS. " .
               "Kolom QUANTITY [ISI] memuat kuantitas dan satuan (SAK, CRT, BAL, SLO, SLP). " .
               "Kolom NAMA BARANG berisi nama produk (B. = Beras, TEP. = Tepung, R. = Rokok). " .
               "Kolom HARGA @ adalah harga satuan dan SUBTOTAL di sebelah kanan adalah total harga baris. " .
               "Ekstrak semua baris produk secara lengkap ke array JSON!";
    }

    public function parseItem(array $rawItem): array
    {
        $name = trim((string)($rawItem['name'] ?? $rawItem['nama_barang'] ?? $rawItem['nama_produk'] ?? ''));
        $supplierCode = trim((string)($rawItem['supplier_code'] ?? $rawItem['kode_barang'] ?? $rawItem['kode'] ?? ''));
        $rawUnit = trim((string)($rawItem['unit'] ?? $rawItem['satuan'] ?? ''));
        $rawQty = $rawItem['qty'] ?? $rawItem['quantity'] ?? $rawItem['jumlah'] ?? 1;

        // Parse quantity string if it contains unit (e.g. "2 SAK", "6 SLO", "1 CRT x20")
        $qty = 1.0;
        $multiplier = 1;
        if (is_numeric($rawQty)) {
            $qty = (float)$rawQty;
        } elseif (is_string($rawQty)) {
            if (preg_match('/^([\d\.,]+)\s*([A-Za-z]+)?(?:\s*x(\d+))?/i', trim($rawQty), $m)) {
                $qty = (float)str_replace(',', '.', str_replace('.', '', $m[1]));
                if (!empty($m[2]) && empty($rawUnit)) {
                    $rawUnit = $m[2];
                }
                if (!empty($m[3])) {
                    $multiplier = (int)$m[3];
                }
            }
        }
        if ($qty <= 0) $qty = 1.0;

        // Parse price values
        $totalPrice = $this->cleanPrice($rawItem['total_price'] ?? $rawItem['subtotal'] ?? $rawItem['jumlah'] ?? 0);
        $unitPrice = $this->cleanPrice($rawItem['unit_price'] ?? $rawItem['harga'] ?? $rawItem['harga_satuan'] ?? 0);

        if ($totalPrice > 0 && ($unitPrice <= 0 || abs(($unitPrice * $qty) - $totalPrice) > 500)) {
            $unitPrice = round($totalPrice / $qty, 2);
        } elseif ($unitPrice > 0 && $totalPrice <= 0) {
            $totalPrice = round($unitPrice * $qty, 2);
        }

        // Normalize unit standard
        $normalizedUnit = $this->normalizeUnit($rawUnit, $name);

        // Smart name parsing & expansion
        $parsedMeta = $this->extractProductMetadata($name);

        return [
            'name'                  => $name,
            'clean_name'            => $parsedMeta['clean_name'],
            'expanded_name'         => $parsedMeta['expanded_name'],
            'supplier_code'         => $supplierCode,
            'qty'                   => $qty,
            'unit'                  => $normalizedUnit,
            'unit_price'            => $unitPrice,
            'total_price'           => $totalPrice,
            'pack_multiplier'       => $multiplier,
            'brand'                 => $parsedMeta['brand'],
            'variant'               => $parsedMeta['variant'],
            'weight_value'          => $parsedMeta['weight_value'],
            'weight_unit'           => $parsedMeta['weight_unit'],
            'extracted_meta'        => $parsedMeta,
        ];
    }

    public function determinePackagingLevel(
        float $unitPrice,
        array $packagings,
        string $extractedUnit = '',
        ?float $lastBuyPrice = null
    ): array {
        if (empty($packagings)) {
            return ['packaging' => null, 'level' => 1, 'strategy' => 'default_single'];
        }

        if (count($packagings) === 1) {
            return ['packaging' => $packagings[0], 'level' => (int)$packagings[0]['level'], 'strategy' => 'single_level'];
        }

        // Strategy 1: Last purchase price matching
        if ($lastBuyPrice !== null && $lastBuyPrice > 0 && $unitPrice > 0) {
            $ratio = $unitPrice / $lastBuyPrice;
            if ($ratio >= 0.70 && $ratio <= 1.40) {
                foreach ($packagings as $pkg) {
                    $pkgPrice = (float)($pkg['buy_price'] ?? 0);
                    if ($pkgPrice > 0 && abs($pkgPrice - $lastBuyPrice) / $lastBuyPrice <= 0.15) {
                        return ['packaging' => $pkg, 'level' => (int)$pkg['level'], 'strategy' => 'history_exact'];
                    }
                }
            }
        }

        // Strategy 2: Unit name exact/fuzzy matching
        if (!empty($extractedUnit)) {
            $unitLower = strtolower(trim($extractedUnit));
            foreach ($packagings as $pkg) {
                $pkgUnit = strtolower(trim($pkg['unit_name'] ?? ''));
                if ($pkgUnit === $unitLower) {
                    return ['packaging' => $pkg, 'level' => (int)$pkg['level'], 'strategy' => 'unit_exact'];
                }
            }

            // Map Indoberas units to common levels
            if (in_array($unitLower, ['slop', 'slo', 'slp', 'renceng', 'rcg', 'pak', 'pack', 'box', 'bal'])) {
                foreach ($packagings as $pkg) {
                    if ((int)$pkg['level'] === 2) {
                        return ['packaging' => $pkg, 'level' => 2, 'strategy' => 'unit_level_mapping'];
                    }
                }
            } elseif (in_array($unitLower, ['karton', 'crt', 'dus', 'case', 'ctn', 'sak', 'karung'])) {
                foreach ($packagings as $pkg) {
                    if ((int)$pkg['level'] === 3) {
                        return ['packaging' => $pkg, 'level' => 3, 'strategy' => 'unit_level_mapping'];
                    }
                }
            }
        }

        // Strategy 3: Price Distance Matching
        if ($unitPrice > 0) {
            $bestPkg = null;
            $bestDist = PHP_FLOAT_MAX;

            foreach ($packagings as $pkg) {
                $buyPrice = (float)($pkg['buy_price'] ?? 0);
                if ($buyPrice <= 0) continue;

                $dist = abs($buyPrice - $unitPrice) / max($buyPrice, $unitPrice);
                if ($dist < $bestDist) {
                    $bestDist = $dist;
                    $bestPkg = $pkg;
                }
            }

            if ($bestPkg !== null && $bestDist <= 0.40) {
                return ['packaging' => $bestPkg, 'level' => (int)$bestPkg['level'], 'strategy' => 'price_distance'];
            }
        }

        // Fallback: Level 1
        return ['packaging' => $packagings[0], 'level' => (int)$packagings[0]['level'], 'strategy' => 'fallback_base'];
    }

    /**
     * Clean numeric price strings (e.g. "164.000", "95.500", "1,554,000") to float.
     */
    private function cleanPrice($val): float
    {
        if (is_numeric($val)) {
            return (float)$val;
        }
        $str = trim((string)$val);
        $str = preg_replace('/[^\d\.,]/', '', $str);

        if (substr_count($str, '.') > 1) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (substr_count($str, ',') > 1) {
            $str = str_replace(',', '', $str);
        } elseif (strpos($str, '.') !== false && strpos($str, ',') !== false) {
            if (strrpos($str, ',') > strrpos($str, '.')) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        } elseif (strpos($str, '.') !== false) {
            $parts = explode('.', $str);
            if (strlen(end($parts)) === 3) {
                $str = str_replace('.', '', $str);
            }
        } elseif (strpos($str, ',') !== false) {
            $parts = explode(',', $str);
            if (strlen(end($parts)) === 3) {
                $str = str_replace(',', '', $str);
            } else {
                $str = str_replace(',', '.', $str);
            }
        }

        return is_numeric($str) ? (float)$str : 0.0;
    }

    /**
     * Normalize unit string to standard human readable form.
     */
    private function normalizeUnit(string $rawUnit, string $productName): string
    {
        $u = strtolower(trim($rawUnit));
        if ($u === 'sak' || strpos($u, 'sak') !== false) return 'Sak';
        if ($u === 'crt' || $u === 'karton' || $u === 'dus' || $u === 'ctn') return 'Karton';
        if ($u === 'bal' || $u === 'ball') return 'Bal';
        if ($u === 'slo' || $u === 'slp' || $u === 'slop') return 'Slop';
        if ($u === 'rcg' || $u === 'renceng') return 'Renceng';
        if ($u === 'box' || $u === 'pak' || $u === 'pack') return 'Box';
        if ($u === 'bks' || $u === 'bungkus') return 'Bungkus';
        if ($u === 'pcs' || $u === 'biji' || $u === 'satuan') return 'Pcs';

        // Infer unit from product name prefix if unit is empty
        $pLower = strtolower($productName);
        if (strpos($pLower, 'r.') === 0 || strpos($pLower, 'rokok') !== false) return 'Slop';
        if (strpos($pLower, 'b.') === 0 || strpos($pLower, 'beras') !== false) return 'Sak';

        return !empty($rawUnit) ? ucfirst(strtolower($rawUnit)) : 'Pcs';
    }

    /**
     * Smart product name expansion and metadata extraction.
     */
    private function extractProductMetadata(string $name): array
    {
        $raw = $name;

        // Clean out noise tags: [TK], [tk], BT, etc.
        $clean = preg_replace('/\[.*?\]/', '', $raw);
        $clean = preg_replace('/\bBT\b/i', '', $clean);
        $clean = preg_replace('/\s+/', ' ', trim($clean));

        // Normalize specific prefixes
        $normalizedPrefix = $clean;
        $prefixMap = [
            '/^B\.\s*/i'         => 'Beras ',
            '/^R\.\s*/i'         => 'Rokok ',
            '/^TEP\.ROTI\s*/i'   => 'Tepung Roti ',
            '/^TEP\.KANJI\s*/i'  => 'Tepung Kanji ',
            '/^TEP\.TERIGU\s*/i' => 'Tepung Terigu ',
            '/^TEP\.BERAS\s*/i'  => 'Tepung Beras ',
            '/^TEP\.\s*/i'       => 'Tepung ',
            '/^KC\.\s*/i'        => 'Kacang ',
            '/^KCG\.\s*/i'       => 'Kacang ',
        ];
        foreach ($prefixMap as $pattern => $replacement) {
            $normalizedPrefix = preg_replace($pattern, $replacement, $normalizedPrefix);
        }

        // Expand internal abbreviations
        $expanded = $normalizedPrefix;
        foreach (self::ABBREVIATIONS as $abbr => $full) {
            if (strpos($abbr, '.') !== false) continue; // Already handled in prefix map
            $pattern = '/\b' . preg_quote($abbr, '/') . '\b/i';
            $expanded = preg_replace($pattern, $full, $expanded);
        }
        $expanded = preg_replace('/\s+/', ' ', trim($expanded));

        // Extract weight / volume (e.g. 10KG, 500GR, 250ML)
        $weightVal = null;
        $weightUnit = null;
        if (preg_match('/(\d+(?:[\.,]\d+)?)\s*(kg|gram|gr|g|ml|l|liter)\b/i', $clean, $wm)) {
            $weightVal = (float)str_replace(',', '.', $wm[1]);
            $weightUnit = strtoupper($wm[2]);
            if ($weightUnit === 'G') $weightUnit = 'GR';
        }

        // Detect brand
        $detectedBrand = null;
        $expandedLower = strtolower($expanded);
        foreach (self::KNOWN_BRANDS as $b) {
            if (strpos($expandedLower, $b) !== false) {
                $detectedBrand = ucwords($b);
                break;
            }
        }

        return [
            'clean_name'    => $clean,
            'expanded_name' => $expanded,
            'brand'         => $detectedBrand,
            'variant'       => null,
            'weight_value'  => $weightVal,
            'weight_unit'   => $weightUnit,
        ];
    }

}
