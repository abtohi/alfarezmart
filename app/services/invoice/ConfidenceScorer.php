<?php
/**
 * ConfidenceScorer
 *
 * Calculates per-field confidence scores (0.0 – 1.0) for each extracted item.
 *
 * Score components:
 *   - product: match score normalized (0–1)
 *   - qty:     based on qty value validity
 *   - unit:    based on recognized unit name
 *   - price:   based on price format and Rupiah range
 *   - total:   based on price consistency
 *   - final:   weighted average
 *
 * @package AlfarezMart\Services\Invoice
 */
class ConfidenceScorer
{
    /** Minimum confidence below which self-correction is triggered (lowered to reduce double API calls) */
    const CORRECTION_THRESHOLD = 0.50;

    /** Max score ProductMatcher can produce (for normalization) */
    const MAX_MATCH_SCORE = 200.0;

    /** Weights for computing final confidence */
    const WEIGHTS = [
        'product' => 0.40,
        'qty'     => 0.20,
        'price'   => 0.25,
        'total'   => 0.15,
    ];

    // ----------------------------------------------------------------
    // PUBLIC API
    // ----------------------------------------------------------------

    /**
     * Score a single item after validation + matching.
     *
     * @param  array $item         Item with validation flags and match info
     * @return array  Item with 'confidence' array added
     */
    public function score(array $item): array
    {
        $confidence = [];

        // ---- Product matching confidence ----
        $matchScore = (float)($item['match_score'] ?? 0);
        $confidence['product'] = min(1.0, $matchScore / self::MAX_MATCH_SCORE);

        // ---- Qty confidence ----
        $qty = (float)($item['qty'] ?? 0);
        if ($qty <= 0) {
            $confidence['qty'] = 0.1;
        } elseif ($qty > 0 && $qty == round($qty)) {
            $confidence['qty'] = 1.0; // Integer qty is most common
        } else {
            $confidence['qty'] = 0.85; // Fractional qty is less common but valid
        }

        // ---- Price confidence ----
        $unitPrice = (float)($item['unit_price'] ?? 0);
        $confidence['price'] = $this->scorePriceConfidence($unitPrice);

        // ---- Total consistency confidence ----
        $total      = (float)($item['total_price'] ?? 0);
        $validFail  = $item['validation_failed'] ?? false;
        if ($validFail) {
            $confidence['total'] = 0.4;
        } elseif ($total > 0 && $unitPrice > 0 && $qty > 0) {
            $expected = $qty * $unitPrice;
            $diff     = abs($expected - $total);
            $pct      = $diff / max($expected, $total);
            $confidence['total'] = max(0.0, 1.0 - ($pct * 10));
        } else {
            $confidence['total'] = 0.6; // Unknown
        }

        // ---- Final weighted confidence ----
        $final = 0.0;
        foreach (self::WEIGHTS as $key => $weight) {
            $final += ($confidence[$key] ?? 0) * $weight;
        }
        $confidence['final'] = round($final, 3);

        // Flag items needing review
        $item['confidence']   = $confidence;
        $item['needs_review'] = ($confidence['final'] < self::CORRECTION_THRESHOLD)
                              || ($item['needs_review'] ?? false);

        return $item;
    }

    /**
     * Score a list of items.
     *
     * @param  array $items
     * @return array{items: array, has_low_confidence: bool, avg_confidence: float}
     */
    public function scoreAll(array $items): array
    {
        $scored          = [];
        $hasLowConfidence = false;
        $totalConf       = 0;

        foreach ($items as $item) {
            $s = $this->score($item);
            $scored[] = $s;

            $finalConf = $s['confidence']['final'] ?? 0;
            $totalConf += $finalConf;

            if ($finalConf < self::CORRECTION_THRESHOLD || ($s['needs_review'] ?? false)) {
                $hasLowConfidence = true;
            }
        }

        $avgConf = count($scored) > 0 ? ($totalConf / count($scored)) : 0;

        return [
            'items'              => $scored,
            'has_low_confidence' => $hasLowConfidence,
            'avg_confidence'     => round($avgConf, 3),
        ];
    }

    // ----------------------------------------------------------------
    // PRIVATE HELPERS
    // ----------------------------------------------------------------

    /**
     * Score a Rupiah price value.
     *
     * Heuristics for Indonesian market:
     *  - Very small (<100): suspicious
     *  - 100 – 100,000: reasonable for PCS/ecer
     *  - 100,000 – 10,000,000: reasonable for karton/grosir
     *  - > 10,000,000: suspicious (too large for a single item)
     */
    private function scorePriceConfidence(float $price): float
    {
        if ($price <= 0) return 0.1;
        if ($price < 100) return 0.3;       // Very low — likely OCR error
        if ($price < 500) return 0.55;      // Could be valid for ultra-cheap items
        if ($price < 1_000) return 0.75;
        if ($price < 100_000) return 1.0;   // Normal retail/ecer range
        if ($price < 10_000_000) return 1.0; // Normal karton range
        if ($price < 50_000_000) return 0.7; // Unusually large
        return 0.3;                          // Suspicious
    }
}
