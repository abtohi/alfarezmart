<?php
require_once __DIR__ . '/InvoiceSkillInterface.php';

/**
 * GeneralInvoiceSkill
 *
 * Default/Fallback AI scanning skill for generic supplier invoices.
 * Handles general formats (BSR/TGH/KCL, standard retail/wholesale invoices).
 *
 * @package AlfarezMart\Services\Invoice\Skills
 */
class GeneralInvoiceSkill implements InvoiceSkillInterface
{
    public function getSkillKey(): string
    {
        return 'general';
    }

    public function getSupplierName(): string
    {
        return 'General / Standar Supplier';
    }

    public function getDetectionSignatures(): array
    {
        return []; // Fallback skill matches everything
    }

    public function getSystemPrompt(bool $isCorrectionPass = false): string
    {
        $lines = [];
        $lines[] = 'Kamu adalah asisten OCR dan analisis invoice cerdas dan berkecepatan tinggi.';
        $lines[] = 'Tugasmu adalah membaca gambar invoice/faktur supplier dan mengekstrak semua baris item barang.';
        $lines[] = '';
        $lines[] = '## ATURAN EKSTRAKSI:';
        $lines[] = '1. Selalu kembalikan HANYA JSON array yang valid.';
        $lines[] = '2. Baca semua baris produk dalam tabel invoice.';
        $lines[] = '3. "supplier_code": Ambil kode produk/barcode yang tertulis di invoice jika ada.';
        $lines[] = '4. "name": Nama produk lengkap.';
        $lines[] = '5. "qty": Jumlah barang yang dibeli.';
        $lines[] = '6. "unit": Satuan barang (Karton, Box, Pcs, Renceng, Lusin, dll).';
        $lines[] = '7. "total_price": Total harga akhir baris (kolom paling kanan).';
        $lines[] = '8. "unit_price": Biarkan null (sistem backend akan menghitung otomatis total_price / qty).';
        $lines[] = '9. PENTING: Jika invoice memiliki kolom qty terpisah bernama BSR, TGH, KCL, isi "qty_bsr", "qty_tgh", "qty_kcl".';
        $lines[] = '';

        if ($isCorrectionPass) {
            $lines[] = '## MODE KOREKSI: Periksa ulang baris yang dicurigai tidak lengkap.';
            $lines[] = '';
        }

        $lines[] = '## FORMAT OUTPUT JSON (HANYA JSON ARRAY VALID, TANPA PENJELASAN):';
        $lines[] = '[';
        $lines[] = '  {';
        $lines[] = '    "supplier_code": "KODE123",';
        $lines[] = '    "name": "NAMA PRODUK LENGKAP",';
        $lines[] = '    "qty": 1,';
        $lines[] = '    "unit": "Karton",';
        $lines[] = '    "qty_bsr": 0,';
        $lines[] = '    "qty_tgh": 1,';
        $lines[] = '    "qty_kcl": 0,';
        $lines[] = '    "total_price": 100000,';
        $lines[] = '    "unit_price": null';
        $lines[] = '  }';
        $lines[] = ']';

        return implode("\n", $lines);
    }

    public function getUserPromptHints(): string
    {
        return "Baca faktur ini dengan teliti. Ambil kode barang, nama produk, qty, dan total harga di kolom paling kanan.";
    }

    public function parseItem(array $rawItem): array
    {
        $name = trim($rawItem['name'] ?? $rawItem['product_name'] ?? $rawItem['nama_barang'] ?? $rawItem['nama_produk'] ?? $rawItem['deskripsi'] ?? '');
        $code = trim($rawItem['supplier_product_code'] ?? $rawItem['supplier_code'] ?? $rawItem['code'] ?? $rawItem['kode_barang'] ?? $rawItem['item_code'] ?? '');
        
        $rawQty = $rawItem['qty'] ?? $rawItem['jumlah'] ?? $rawItem['quantity'] ?? 1;
        $qty = is_numeric($rawQty) ? max(1, (float)$rawQty) : 1;
        
        // Handle BSR/TGH/KCL
        $qtyBsr = (float)($rawItem['qty_bsr'] ?? $rawItem['bsr'] ?? 0);
        $qtyTgh = (float)($rawItem['qty_tgh'] ?? $rawItem['tgh'] ?? 0);
        $qtyKcl = (float)($rawItem['qty_kcl'] ?? $rawItem['kcl'] ?? 0);
        
        $unit = trim($rawItem['unit'] ?? $rawItem['satuan'] ?? '');

        // Prioritize BSR / TGH / KCL if provided
        if ($qtyBsr > 0) {
            $qty = $qtyBsr;
            if (empty($unit) || in_array(strtolower($unit), ['pcs', 'unit', 'satuan'])) $unit = 'Karton';
        } elseif ($qtyTgh > 0) {
            $qty = $qtyTgh;
            if (empty($unit) || in_array(strtolower($unit), ['pcs', 'unit', 'satuan'])) $unit = 'Pack';
        } elseif ($qtyKcl > 0) {
            $qty = $qtyKcl;
            if (empty($unit)) $unit = 'PCS';
        }
        
        $rawTotal = $rawItem['total_price'] ?? $rawItem['total'] ?? $rawItem['neto_ket'] ?? $rawItem['neto'] ?? $rawItem['subtotal'] ?? 0;
        if (is_string($rawTotal)) {
            $cleaned = preg_replace('/[^0-9,]/', '', str_replace('.', '', $rawTotal));
            $cleaned = str_replace(',', '.', $cleaned);
            $totalPrice = (float)$cleaned;
        } else {
            $totalPrice = (float)$rawTotal;
        }

        $rawUnitPrice = $rawItem['unit_price'] ?? $rawItem['harga_satuan'] ?? null;
        if ($rawUnitPrice !== null && is_numeric($rawUnitPrice) && (float)$rawUnitPrice > 0 && $totalPrice == 0) {
            $unitPrice = (float)$rawUnitPrice;
            $totalPrice = round($unitPrice * $qty, 2);
        } else {
            $unitPrice = $qty > 0 ? round($totalPrice / $qty, 2) : $totalPrice;
        }

        return [
            'name'                 => $name,
            'supplier_invoice_name'=> $name,
            'supplier_code'        => $code,
            'supplier_product_code'=> $code,
            'qty'                  => $qty,
            'qty_bsr'              => $qtyBsr,
            'qty_tgh'              => $qtyTgh,
            'qty_kcl'              => $qtyKcl,
            'unit'                 => $unit,
            'total_price'          => $totalPrice,
            'unit_price'           => $unitPrice,
            'brand'                => trim($rawItem['brand'] ?? $rawItem['merk'] ?? ''),
            'variant'              => trim($rawItem['variant'] ?? $rawItem['rasa'] ?? ''),
            'weight'               => $rawItem['weight'] ?? $rawItem['gramasi'] ?? $rawItem['size'] ?? null,
            'weight_unit'          => trim($rawItem['weight_unit'] ?? ''),
            'barcode'              => trim($rawItem['barcode'] ?? ''),
            'discount'             => (float)($rawItem['discount'] ?? $rawItem['diskon'] ?? 0),
            'skill_used'           => 'general'
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

        // 1. Price distance match
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

            if ($bestPkg !== null) {
                return [
                    'packaging' => $bestPkg,
                    'level'     => $bestLevel,
                    'strategy'  => 'price_distance'
                ];
            }
        }

        // 2. Unit name match
        if (!empty($extractedUnit)) {
            $unitLower = strtolower($extractedUnit);
            foreach ($packagings as $pkg) {
                $pkgUnitName = strtolower(trim($pkg['unit_name'] ?? ''));
                if (strpos($pkgUnitName, $unitLower) !== false || strpos($unitLower, $pkgUnitName) !== false) {
                    return [
                        'packaging' => $pkg,
                        'level'     => (int)$pkg['level'],
                        'strategy'  => 'unit_name_match'
                    ];
                }
            }
        }

        // 3. Fallback
        return [
            'packaging' => $packagings[0],
            'level'     => (int)($packagings[0]['level'] ?? 1),
            'strategy'  => 'fallback_level'
        ];
    }
}
