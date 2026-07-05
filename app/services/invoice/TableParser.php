<?php
/**
 * TableParser
 *
 * Reconstructs a clean table from LayoutAnalyzer output.
 * Handles:
 *   - Multi-line item names (merged across rows)
 *   - Continuation rows (name only, no qty/price)
 *   - Subtotal/total rows (should be excluded from items)
 *   - OCR misalignment (value in wrong column)
 *
 * @package AlfarezMart\Services\Invoice
 */
class TableParser
{
    /** @var LayoutAnalyzer */
    private $layoutAnalyzer;

    public function __construct(LayoutAnalyzer $layoutAnalyzer)
    {
        $this->layoutAnalyzer = $layoutAnalyzer;
    }

    // ----------------------------------------------------------------
    // PUBLIC API
    // ----------------------------------------------------------------

    /**
     * Parse and clean up items from LayoutAnalyzer output.
     *
     * @param  array $items  Normalized items from LayoutAnalyzer::analyze()
     * @return array  Cleaned items with merged continuation rows
     */
    public function parse(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        // Pass 1: Merge continuation rows
        $merged = $this->mergeContinuationRows($items);

        // Pass 2: Remove subtotal/total rows
        $filtered = $this->filterSummaryRows($merged);

        // Pass 3: Fix obvious OCR misalignment
        $fixed = $this->fixMisalignment($filtered);

        // Pass 4: Derive missing prices
        $derived = $this->deriveMissingPrices($fixed);

        return $derived;
    }

    // ----------------------------------------------------------------
    // PRIVATE HELPERS
    // ----------------------------------------------------------------

    /**
     * Merge "continuation" rows (rows with only a name, no qty/price)
     * into the preceding row's name.
     */
    private function mergeContinuationRows(array $items): array
    {
        $result = [];
        $prev   = null;

        foreach ($items as $item) {
            $isContinuation = (
                !empty($item['name']) &&
                $item['qty'] <= 0 &&
                $item['unit_price'] <= 0 &&
                $item['total_price'] <= 0
            );

            if ($isContinuation && $prev !== null) {
                // Append name to previous row
                $result[count($result) - 1]['name'] .= ' ' . $item['name'];
                continue;
            }

            $result[] = $item;
            $prev     = $item;
        }

        return $result;
    }

    /**
     * Filter out summary/subtotal/total rows that shouldn't be items.
     */
    private function filterSummaryRows(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            $nameLower = strtolower(trim($item['name'] ?? ''));

            // Skip rows that are clearly summary/total markers
            if ($this->isSummaryRow($nameLower, $item)) {
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Determine if a row is a summary/total row, not a product item.
     */
    private function isSummaryRow(string $nameLower, array $item): bool
    {
        $summaryKeywords = [
            'total', 'subtotal', 'grand total', 'jumlah', 'total keseluruhan',
            'total invoice', 'total nota', 'total tagihan', 'ppn', 'pajak',
            'diskon', 'discount', 'discount total', 'freight', 'ongkos kirim', 'biaya kirim',
            'shipping', 'handling', 'admin fee', 'biaya admin', 'potongan', 'potongan harga'
        ];

        // Exact match
        if (in_array($nameLower, $summaryKeywords, true)) {
            return true;
        }

        // Starts with prefix and has NO code
        $prefixes = ['total ', 'jumlah ', 'sub total ', 'diskon ', 'discount ', 'potongan '];
        foreach ($prefixes as $prefix) {
            if (strpos($nameLower, $prefix) === 0 && empty($item['supplier_code'])) {
                // To avoid matching "Total Mineral Water 600ml" as a summary row,
                // check if it lacks a positive qty, has a negative price, or contains '%'
                if ($item['qty'] <= 0 || $item['total_price'] < 0 || strpos($nameLower, '%') !== false) {
                    return true;
                }
            }
        }

        // Check if row has no qty but has a very large "total" (looks like invoice total)
        if ($item['qty'] <= 0 && $item['total_price'] > 0 && $item['unit_price'] <= 0) {
            // Could be a footer total — check if name suggests it
            if (strpos($nameLower, 'total') !== false || strpos($nameLower, 'tagihan') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fix obvious OCR column misalignment.
     *
     * Common issue: OCR puts the qty in the unit column or price in qty column.
     * We detect this by checking if qty looks like a price and vice versa.
     */
    private function fixMisalignment(array $items): array
    {
        foreach ($items as &$item) {
            $qty       = $item['qty'];
            $unitPrice = $item['unit_price'];
            $total     = $item['total_price'];

            // Case 1: qty looks extremely large (> 10000) but total is small
            // Probably OCR confused qty with price
            if ($qty > 10000 && $total > 0 && $unitPrice <= 0) {
                // Assume qty is actually unit_price
                $item['unit_price'] = $qty;
                $item['qty']        = ($total > 0) ? round($total / $qty) : 1;
                if ($item['qty'] <= 0) $item['qty'] = 1;
            }

            // Case 2: unit_price is very small (< 10) but total is large
            // Maybe unit_price and total are swapped
            if ($unitPrice > 0 && $unitPrice < 10 && $total > 1000 && $qty > 0) {
                $derivedPrice = $total / $qty;
                if ($derivedPrice > 1000) {
                    $item['unit_price'] = $derivedPrice;
                }
            }

            // Case 3: total < unit_price (impossible: total should be >= unit_price if qty >= 1)
            if ($total > 0 && $unitPrice > 0 && $total < $unitPrice && $qty >= 1) {
                // Maybe total and unit_price are swapped
                if (abs($unitPrice - $total * $qty) < abs($total - $unitPrice * $qty)) {
                    // Swap them
                    [$item['unit_price'], $item['total_price']] = [$total, $unitPrice];
                }
            }
        }
        unset($item);

        return $items;
    }

    /**
     * Derive missing price fields from available data.
     */
    private function deriveMissingPrices(array $items): array
    {
        foreach ($items as &$item) {
            $qty       = max(1, (float)($item['qty'] ?? 1));
            $unitPrice = (float)($item['unit_price'] ?? 0);
            $total     = (float)($item['total_price'] ?? 0);

            if ($unitPrice <= 0 && $total > 0) {
                $item['unit_price'] = $total / $qty;
            }
            if ($total <= 0 && $unitPrice > 0) {
                $item['total_price'] = $unitPrice * $qty;
            }

            // Ensure qty is at least 1
            if ((float)($item['qty'] ?? 0) <= 0) {
                $item['qty'] = 1;
            }
        }
        unset($item);

        return $items;
    }
}
