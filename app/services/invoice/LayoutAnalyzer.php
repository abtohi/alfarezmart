<?php
/**
 * LayoutAnalyzer
 *
 * Analyzes the raw AI response to understand the invoice structure:
 *   - Identifies semantic columns (qty, price, total, name, unit, code)
 *   - Handles various Indonesian and international header names
 *   - Normalizes extracted items
 *
 * @package AlfarezMart\Services\Invoice
 */
class LayoutAnalyzer
{
    /**
     * Semantic header aliases.
     *
     * Key   = canonical field name
     * Value = list of header aliases (case-insensitive, partial match allowed)
     */
    const HEADER_ALIASES = [
        'name' => [
            'nama', 'keterangan', 'description', 'item', 'produk', 'barang',
            'uraian', 'deskripsi', 'nama barang', 'nama produk', 'goods',
            'commodity', 'artikel', 'items', 'ket',
        ],
        'qty' => [
            'qty', 'quantity', 'jumlah', 'kuantitas', 'jml', 'volume',
            'banyak', 'pcs', 'kuantiti', 'q', 'jumlah barang',
        ],
        'unit' => [
            'satuan', 'unit', 'kemasan', 'uom', 'pak', 'sat', 'unt',
            'unit of measure', 'packaging',
        ],
        'unit_price' => [
            'harga', 'price', 'harga netto', 'net price', 'unit price',
            'h.satuan', 'hrg', 'harga satuan', 'harga unit', 'h/satuan',
            'harga/unit', 'h.net', 'harga net', 'netto', 'nett price',
            'harga pokok', 'h.beli', 'harga beli',
        ],
        'total' => [
            'total', 'amount', 'net amount', 'subtotal', 'extended price',
            'jumlah harga', 'total harga', 'total price', 'nilai', 'total nilai',
            'total amount', 'grand total', 'jml harga', 'j.harga',
        ],
        'discount' => [
            'diskon', 'discount', 'disc', 'pot', 'potongan', 'dis', 'rab',
            'rabat', 'diskon %', 'disc%',
        ],
        'code' => [
            'kode', 'code', 'no', 'no.', 'sku', 'item code', 'kode barang',
            'kode produk', 'barcode', 'id', 'artikel',
        ],
    ];

    /**
     * Packaging level semantic hints.
     * Used to infer packaging level from unit text.
     */
    const PACKAGING_LEVEL_HINTS = [
        3 => ['bsr', 'big', 'karton', 'carton', 'ctn', 'case', 'master', 'dus',
              'outer', 'box', 'kardus', 'krt', 'kotak', 'dos'],
        2 => ['tgh', 'inner', 'pack', 'pak', 'renteng', 'slop', 'mid', 'bundle',
              'wrap', 'set', 'lusin', 'dozen', 'dz'],
        1 => ['kcl', 'pcs', 'ecer', 'satuan', 'biji', 'buah', 'unit', 'btl',
              'botol', 'bks', 'bungkus', 'sachet', 'scht', 'lembar', 'lbr',
              'kaleng', 'klg', 'kg', 'liter', 'ltr', 'gr', 'gram'],
    ];

    // ----------------------------------------------------------------
    // PUBLIC API
    // ----------------------------------------------------------------

    /**
     * Parse and normalize the raw AI JSON response.
     *
     * @param  mixed  $rawAiResponse  Already-decoded PHP array from AI
     * @return array{
     *   items: array,
     *   invoice_total: float|null,
     *   detected_columns: array,
     *   warnings: string[]
     * }
     */
    public function analyze($rawAiResponse): array
    {
        $warnings = [];
        $items    = [];
        $invoiceTotal = null;

        // --- Unwrap common envelope shapes ---
        if (is_array($rawAiResponse)) {
            if (isset($rawAiResponse['items']) && is_array($rawAiResponse['items'])) {
                // { items: [...], total: 123 }
                $invoiceTotal = isset($rawAiResponse['total'])
                    ? $this->parsePrice($rawAiResponse['total'])
                    : null;
                $rawItems = $rawAiResponse['items'];
            } elseif (isset($rawAiResponse[0])) {
                // Direct array
                $rawItems = $rawAiResponse;
            } elseif (isset($rawAiResponse['data']) && is_array($rawAiResponse['data'])) {
                $rawItems = $rawAiResponse['data'];
            } else {
                // Single item object
                $rawItems = [$rawAiResponse];
            }
        } else {
            $warnings[] = 'AI response tidak berupa array';
            return compact('items', 'invoiceTotal', 'warnings') + ['detected_columns' => []];
        }

        // --- Detect columns from first item ---
        $detectedColumns = $this->detectColumns($rawItems[0] ?? []);

        // --- Normalize each item ---
        foreach ($rawItems as $idx => $raw) {
            if (!is_array($raw)) {
                $warnings[] = "Item ke-{$idx} bukan array, dilewati";
                continue;
            }

            $normalized = $this->normalizeItem($raw, $detectedColumns);
            if ($normalized !== null) {
                $items[] = $normalized;
            }
        }

        return [
            'items'            => $items,
            'invoice_total'    => $invoiceTotal,
            'detected_columns' => $detectedColumns,
            'warnings'         => $warnings,
        ];
    }

    /**
     * Infer packaging level from a unit string.
     */
    public function inferPackagingLevel(string $unit): int
    {
        $unit = strtolower(trim($unit));
        if (empty($unit)) return 0; // Return 0 if unknown so we don't wrongly bias Level 1
        foreach (self::PACKAGING_LEVEL_HINTS as $level => $hints) {
            foreach ($hints as $hint) {
                if ($unit === $hint || strpos($unit, $hint) !== false) {
                    return $level;
                }
            }
        }
        return 0; // Unknown
    }

    /**
     * Map a raw header string to its canonical field name.
     */
    public function mapHeader(string $header): ?string
    {
        $h = strtolower(trim($header));
        foreach (self::HEADER_ALIASES as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                if ($h === $alias || strpos($h, $alias) !== false || strpos($alias, $h) !== false) {
                    return $canonical;
                }
            }
        }
        return null;
    }

    // ----------------------------------------------------------------
    // PRIVATE HELPERS
    // ----------------------------------------------------------------

    /**
     * Detect column mapping from the first item's keys.
     */
    private function detectColumns(array $firstItem): array
    {
        $mapping = [];
        foreach (array_keys($firstItem) as $key) {
            $canonical = $this->mapHeader($key);
            if ($canonical && !isset($mapping[$canonical])) {
                $mapping[$canonical] = $key;
            }
        }
        return $mapping;
    }

    /**
     * Normalize a single raw item array into a canonical structure.
     */
    private function normalizeItem(array $raw, array $detectedColumns): ?array
    {
        // Helper: get value by canonical or fallback keys
        $get = function (string $canonical, array $fallbacks = []) use ($raw, $detectedColumns) {
            // Try detected column mapping first
            if (isset($detectedColumns[$canonical]) && array_key_exists($detectedColumns[$canonical], $raw)) {
                return $raw[$detectedColumns[$canonical]];
            }
            // Try direct canonical name
            if (array_key_exists($canonical, $raw)) {
                return $raw[$canonical];
            }
            // Try fallbacks
            foreach ($fallbacks as $fb) {
                if (array_key_exists($fb, $raw)) {
                    return $raw[$fb];
                }
            }
            return null;
        };

        // --- Extract name ---
        $name = $get('name', ['nama', 'keterangan', 'description', 'item', 'produk', 'barang']);
        $name = is_string($name) ? trim($name) : '';

        // Skip empty/header rows
        if (empty($name) || $this->looksLikeHeader($name)) {
            return null;
        }

        // --- Extract qty ---
        $qtyRaw = $get('qty', ['quantity', 'jumlah', 'kuantitas', 'jml']);
        $qty    = $this->parseQty($qtyRaw);

        // --- Extract unit ---
        $unitRaw = $get('unit', ['satuan', 'kemasan', 'uom']);
        $unit    = is_string($unitRaw) ? trim($unitRaw) : '';

        // --- Extract explicit BSR/TGH/KCL quantities (Overriding unit and qty if found) ---
        $qtyBsr = $this->parseQty($get('qty_bsr', ['bsr']), 0);
        $qtyTgh = $this->parseQty($get('qty_tgh', ['tgh']), 0);
        $qtyKcl = $this->parseQty($get('qty_kcl', ['kcl']), 0);

        // Only override if they actually have a valid number > 0
        // And if the AI outputs exactly the number, we trust it more than the generic unit column
        if ($qtyBsr > 0) {
            $qty  = $qtyBsr;
            $unit = 'bsr';
        } elseif ($qtyTgh > 0) {
            $qty  = $qtyTgh;
            $unit = 'tgh';
        } elseif ($qtyKcl > 0) {
            $qty  = $qtyKcl;
            $unit = 'kcl';
        }

        // --- Extract prices ---
        $unitPriceRaw = $get('unit_price', ['harga', 'price', 'harga_satuan', 'unit_price', 'harga satuan']);
        $totalRaw     = $get('total', ['total_price', 'total', 'amount', 'jumlah_harga', 'subtotal']);
        $discountRaw  = $get('discount', ['diskon', 'disc', 'potongan']);

        $unitPrice = $this->parsePrice($unitPriceRaw);
        $total     = $this->parsePrice($totalRaw);
        $discount  = $this->parsePrice($discountRaw);

        // --- Derive missing price fields ---
        if ($unitPrice <= 0 && $total > 0 && $qty > 0) {
            $unitPrice = $total / $qty;
        }
        if ($total <= 0 && $unitPrice > 0 && $qty > 0) {
            $total = $unitPrice * $qty;
        }

        // --- Auto-scale abbreviated Rupiah (e.g. "5.5" → 5500) ---
        $unitPrice = $this->autoScaleRupiah($unitPrice);
        $total     = $this->autoScaleRupiah($total, $unitPrice * $qty);

        // --- Other fields ---
        $supplierInvoiceName = trim((string)$get('supplier_invoice_name', ['supplier_invoice_name']));
        $supplierCode        = trim((string)$get('code', ['supplier_code', 'kode', 'sku']));
        $brand               = trim((string)$get('brand', ['merk', 'brand']));
        $variant             = trim((string)$get('variant', []));
        $weight              = $get('weight', []);
        $weightUnit          = trim((string)$get('weight_unit', ['unit_berat']));
        $size                = trim((string)$get('size', ['ukuran', 'size']));
        $barcode             = trim((string)$get('barcode', []));
        $notes               = trim((string)$get('notes', ['catatan', 'keterangan_tambahan']));

        // Infer packaging level from unit
        $packagingLevel = $this->inferPackagingLevel($unit);

        return [
            'name'                 => $name,
            'supplier_invoice_name'=> $supplierInvoiceName ?: $name,
            'supplier_code'        => $supplierCode,
            'qty'                  => $qty > 0 ? $qty : 1,
            'unit'                 => $unit,
            'packaging_level_hint' => $packagingLevel,
            'unit_price'           => $unitPrice,
            'total_price'          => $total,
            'discount'             => $discount,
            'brand'                => $brand,
            'variant'              => $variant,
            'weight'               => is_numeric($weight) ? (float)$weight : null,
            'weight_unit'          => $weightUnit,
            'size'                 => $size,
            'barcode'              => $barcode,
            'notes'                => $notes,
            // Raw original for debugging
            '_raw'                 => $raw,
        ];
    }

    /**
     * Parse a quantity value from various formats.
     */
    private function parseQty($raw, float $default = 1): float
    {
        if ($raw === null || $raw === '') return $default;
        if (is_numeric($raw)) return max(0, (float)$raw);

        $str = preg_replace('/[^0-9.,]/', '', (string)$raw);
        // Handle "1,5" as 1.5
        $str = str_replace(',', '.', $str);
        return max(0, (float)$str);
    }

    /**
     * Parse a price value, handling Indonesian number formats.
     * "5.000" → 5000, "5,000.50" → 5000.5, "5.000,50" → 5000.5
     */
    public function parsePrice($raw): float
    {
        if ($raw === null || $raw === '') return 0;
        if (is_numeric($raw)) return max(0, (float)$raw);

        $str = trim((string)$raw);

        // Remove currency symbols
        $str = preg_replace('/[Rp\s\$€£]/iu', '', $str);
        $str = trim($str);

        // Detect format: Indonesian (1.000.000,50) vs Western (1,000,000.50)
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $str)) {
            // Indonesian format: remove dots, replace comma with dot
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $str)) {
            // Western format: remove commas
            $str = str_replace(',', '', $str);
        } else {
            // Fallback: remove all non-numeric except last separator
            $str = preg_replace('/[^\d.,]/', '', $str);
            // If both present, assume last separator is decimal
            if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
                $lastDot   = strrpos($str, '.');
                $lastComma = strrpos($str, ',');
                if ($lastDot > $lastComma) {
                    $str = str_replace(',', '', $str);
                } else {
                    $str = str_replace('.', '', $str);
                    $str = str_replace(',', '.', $str);
                }
            }
        }

        return max(0, (float)$str);
    }

    /**
     * Auto-scale abbreviated Rupiah values.
     * If unit_price looks like "5.5" (< 1000), scale to 5500.
     * Uses optional context (expected ≈ qty × price) for smarter decision.
     */
    private function autoScaleRupiah(float $value, float $contextExpected = 0): float
    {
        if ($value <= 0) return $value;

        // If value >= 1000, assume already correct
        if ($value >= 1000) return $value;

        // If value has decimal part and < 1000, it's likely abbreviated (e.g. 5.5 → 5500)
        if ($value < 1000) {
            // Only scale if it looks like a meaningful abbreviation
            // 5.5 → 5500, 12 → 12000, 0.5 → 500
            $scaled = $value * 1000;

            // Sanity check: if context is given, pick the one closer to it
            if ($contextExpected > 0) {
                if (abs($scaled - $contextExpected) < abs($value - $contextExpected)) {
                    return $scaled;
                }
                return $value;
            }

            return $scaled;
        }

        return $value;
    }

    /**
     * Check if a string looks like a table header row (should be skipped).
     */
    private function looksLikeHeader(string $str): bool
    {
        $lower = strtolower(trim($str));

        $headerKeywords = [
            'no', 'no.', 'nama barang', 'keterangan', 'qty', 'quantity',
            'jumlah', 'harga satuan', 'unit price', 'total', 'amount',
            'satuan', 'diskon', 'description', 'item', 'produk',
        ];

        // Exact match with common header words
        if (in_array($lower, $headerKeywords, true)) {
            return true;
        }

        // Very short strings that are just column labels
        if (strlen($lower) <= 3 && !is_numeric($lower)) {
            return in_array($lower, ['no', 'qty', 'harga', 'total', 'sat', 'unt'], true);
        }

        return false;
    }
}
