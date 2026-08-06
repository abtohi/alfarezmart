<?php
/**
 * InvoiceValidator
 *
 * Performs two rounds of validation:
 *
 * Round 1 — Logical validation per item:
 *   qty × unit_price ≈ total_price  (±5% tolerance)
 *
 * Round 2 — Cross validation (invoice level):
 *   sum(total_price of all items) ≈ invoice_grand_total  (±3% tolerance)
 *
 * Items that fail validation get flagged:
 *   'validation_failed' => true
 *   'correction_needed' => true
 *   'issues'            => ['description of issues']
 *
 * @package AlfarezMart\Services\Invoice
 */
class InvoiceValidator
{
    /** Tolerance ratio for per-item validation (5%) */
    const ITEM_TOLERANCE = 0.05;

    /** Tolerance ratio for invoice cross-validation (3%) */
    const INVOICE_TOLERANCE = 0.03;

    /** Minimum price to be considered real (avoid rounding noise) */
    const MIN_MEANINGFUL_PRICE = 100;

    // ----------------------------------------------------------------
    // PUBLIC API
    // ----------------------------------------------------------------

    /**
     * Validate all items and return them with validation flags.
     *
     * @param  array      $items         Items from TableParser
     * @param  float|null $invoiceTotal  Grand total from invoice footer (if detected)
     * @return array{
     *   items: array,
     *   all_valid: bool,
     *   invoice_cross_valid: bool,
     *   computed_total: float,
     *   correction_needed_items: array
     * }
     */
    public function validate(array $items, ?float $invoiceTotal = null): array
    {
        $validatedItems       = [];
        $computedTotal        = 0;
        $correctionNeeded     = [];
        $allValid             = true;

        foreach ($items as $item) {
            $result = $this->validateItem($item);
            $validatedItems[] = $result;

            if ($result['validation_failed'] ?? false) {
                $allValid = true; // Still continue, don't abort
                $allValid = false;
                $correctionNeeded[] = [
                    'name'   => $result['name'],
                    'issues' => $result['issues'] ?? [],
                ];
            }

            $computedTotal += (float)($result['total_price'] ?? 0);
        }

        // Cross validation against invoice footer total
        $invoiceCrossValid = true;
        if ($invoiceTotal !== null && $invoiceTotal > 0 && $computedTotal > 0) {
            $diff = abs($computedTotal - $invoiceTotal);
            $pct  = $diff / max($computedTotal, $invoiceTotal);
            $invoiceCrossValid = $pct <= self::INVOICE_TOLERANCE;
        }

        return [
            'items'                  => $validatedItems,
            'all_valid'              => $allValid,
            'invoice_cross_valid'    => $invoiceCrossValid,
            'computed_total'         => $computedTotal,
            'correction_needed_items'=> $correctionNeeded,
        ];
    }

    // ----------------------------------------------------------------
    // PRIVATE HELPERS
    // ----------------------------------------------------------------

    /**
     * Validate a single item for logical consistency.
     */
    private function validateItem(array $item): array
    {
        $item['validation_failed'] = false;
        $item['correction_needed'] = false;
        $item['issues']            = [];

        $qty       = (float)($item['qty']        ?? 0);
        $unitPrice = (float)($item['unit_price']  ?? 0);
        $total     = (float)($item['total_price'] ?? 0);

        // --- Check 1: Qty must be positive ---
        if ($qty <= 0) {
            $item['issues'][]          = 'Qty tidak valid atau nol';
            $item['validation_failed'] = true;
            $item['qty']               = 1; // Safe fallback
        }

        // --- Check 2: Unit price must be positive ---
        if ($unitPrice <= 0) {
            $item['issues'][] = 'Harga satuan tidak ditemukan';
            // Don't mark failed — might be derivable from total
        }

        // --- Check 3: Cross-check qty × unit_price ≈ total_price ---
        if ($qty > 0 && $unitPrice > self::MIN_MEANINGFUL_PRICE && $total > self::MIN_MEANINGFUL_PRICE) {
            $expected = $qty * $unitPrice;
            $diff     = abs($expected - $total);
            $maxVal   = max($expected, $total);
            $pct      = $maxVal > 0 ? $diff / $maxVal : 0;

            if ($pct > self::ITEM_TOLERANCE) {
                $item['issues'][] = sprintf(
                    'Inkonsistensi: %s × Rp%s = Rp%s, tapi total di invoice: Rp%s (selisih %.1f%%)',
                    number_format($qty, 2),
                    number_format($unitPrice, 0, ',', '.'),
                    number_format($expected, 0, ',', '.'),
                    number_format($total, 0, ',', '.'),
                    $pct * 100
                );
                $item['validation_failed'] = true;
                $item['correction_needed'] = true;

                // Try to self-correct: if total looks more reliable, re-derive unit_price
                if ($total > 0 && $qty > 0) {
                    $derivedUnitPrice = $total / $qty;
                    // Only override if the derived price makes sense (> minimum)
                    if ($derivedUnitPrice > self::MIN_MEANINGFUL_PRICE) {
                        $item['unit_price_original'] = $unitPrice;
                        $item['unit_price']          = $derivedUnitPrice;
                        $item['issues'][]            = sprintf(
                            'Harga satuan dikoreksi dari Rp%s menjadi Rp%s (dihitung dari total ÷ qty)',
                            number_format($unitPrice, 0, ',', '.'),
                            number_format($derivedUnitPrice, 0, ',', '.')
                        );
                    }
                }
            }
        }

        // --- Check 4: Detect obviously wrong prices (suspiciously low for Indonesia) ---
        if ($unitPrice > 0 && $unitPrice < 50 && $total > 1000) {
            $item['issues'][] = 'Harga satuan sangat rendah (< Rp50) — kemungkinan OCR error atau format disingkat';
            $item['correction_needed'] = true;
        }

        // --- Check 5: Total shouldn't be less than unit_price (unless qty < 1) ---
        if ($unitPrice > self::MIN_MEANINGFUL_PRICE && $total > self::MIN_MEANINGFUL_PRICE
            && $total < $unitPrice && $qty >= 1) {
            $item['issues'][] = 'Total lebih kecil dari harga satuan padahal qty >= 1';
            $item['correction_needed'] = true;
        }

        if (!empty($item['issues']) && ($item['validation_failed'] || $item['correction_needed'])) {
            // Mark as needing review
            $item['needs_review'] = true;
        }

        return $item;
    }
}
