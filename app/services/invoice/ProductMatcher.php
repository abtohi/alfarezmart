<?php
/**
 * ProductMatcher
 *
 * Multi-strategy product matching pipeline.
 *
 * Strategy chain (stops at first confident match):
 *  1. exactCodeMatch       — supplier_product_code or product code exact
 *  2. normalizedNameMatch  — lowercase + strip special chars exact
 *  3. supplierInvoiceMatch — match supplier_invoice_name (multi-name support)
 *  4. abbreviationExpand   — expand common abbreviations then rematch
 *  5. fuzzyMatch           — similar_text() + optional levenshtein
 *  6. barcodeMatch         — barcode field
 *  7. priceMatch           — harga ≈ buy_price packaging (±5%)
 *  8. packagingSemanticMatch — infer packaging level, boost score
 *  9. supplierBoost        — boost if product belongs to current supplier
 * 10. brandVariantBoost    — boost if brand/variant matches
 *
 * @package AlfarezMart\Services\Invoice
 */
class ProductMatcher
{
    /** Minimum score to consider a match valid */
    const MATCH_THRESHOLD = 65;

    /** Score for exact supplier code match (highest priority) */
    const SCORE_EXACT_CODE = 200;

    /** Score for exact supplier_invoice_name match */
    const SCORE_EXACT_INVOICE_NAME = 95;

    /** Score for exact product name match */
    const SCORE_EXACT_NAME = 90;

    /**
     * Common abbreviations used in Indonesian supplier invoices.
     * Maps abbreviation → full words (for expansion before matching).
     */
    const ABBREVIATIONS = [
        // Units / packaging
        'bsr'  => 'besar',
        'tgh'  => 'tengah',
        'kcl'  => 'kecil',
        'ctn'  => 'karton',
        'dus'  => 'karton',
        'krt'  => 'karton',
        'btl'  => 'botol',
        'bks'  => 'bungkus',
        'scht' => 'sachet',
        'lbr'  => 'lembar',
        'klg'  => 'kaleng',
        'dz'   => 'lusin',
        'pcs'  => 'pcs',
        // Common product abbreviations
        'mie'  => 'mi',
        'mi'   => 'mie',
        'snk'  => 'snack',
        'choc' => 'chocolate',
        'chk'  => 'chocolate',
        'bis'  => 'biscuit',
        'bsc'  => 'biscuit',
        'kopi' => 'coffee',
        'teh'  => 'tea',
        'air'  => 'water',
        'mnu'  => 'minuman',
        'mkn'  => 'makanan',
    ];

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
     * Match a single extracted item against the product database.
     *
     * @param  array $item           Normalized item from TableParser/LayoutAnalyzer
     * @param  array $allProducts    All products (with packagings attached)
     * @param  array $supplierProductIds  Product IDs that belong to this supplier
     * @return array{
     *   product_id: int|null,
     *   product_name: string|null,
     *   matched_packaging_level: int,
     *   is_matched: bool,
     *   match_score: float,
     *   match_strategy: string
     * }
     */
    public function match(array $item, array $allProducts, array $supplierProductIds = []): array
    {
        $bestMatch     = null;
        $highestScore  = 0;
        $bestStrategy  = 'none';

        $name               = $item['name']                 ?? '';
        $supplierInvName    = $item['supplier_invoice_name'] ?? $name;
        $extractedCode      = trim($item['supplier_code']   ?? '');
        $extractedBarcode   = trim($item['barcode']         ?? '');
        $extractedBrand     = trim($item['brand']           ?? '');
        $extractedVariant   = trim($item['variant']         ?? '');
        $unitPrice          = (float)($item['unit_price']   ?? 0);
        $packagingHint      = (int)($item['packaging_level_hint'] ?? 1);
        $unit               = $item['unit']                 ?? '';
        $weightVal          = isset($item['weight']) ? (float)$item['weight'] : null;
        $weightUnit         = $item['weight_unit']          ?? '';

        // ---- STRATEGY 1: Exact supplier product code ----
        if (!empty($extractedCode)) {
            foreach ($allProducts as $p) {
                $dbCode     = trim($p['supplier_product_code'] ?? '');
                $dbProdCode = trim($p['code'] ?? '');

                if ((!empty($dbCode)     && strcasecmp($extractedCode, $dbCode)     === 0) ||
                    (!empty($dbProdCode) && strcasecmp($extractedCode, $dbProdCode) === 0)) {
                    $bestMatch    = $p;
                    $highestScore = self::SCORE_EXACT_CODE;
                    $bestStrategy = 'exact_code';
                    break;
                }
            }
        }

        // ---- STRATEGY 2-10: Score-based matching ----
        if (!$bestMatch) {
            foreach ($allProducts as $p) {
                $score    = 0;
                $strategy = 'score';

                // -- Supplier affinity boost (25 pts) --
                if (!empty($supplierProductIds) && in_array($p['id'], $supplierProductIds, false)) {
                    $score += 25;
                }

                // -- STRATEGY 2: Normalized name exact match --
                $normExtracted = $this->normalize($name);
                $normFull      = $this->normalize($p['full_name'] ?? '');
                $normShort     = $this->normalize($p['short_label'] ?? '');
                $normInvoice   = $this->normalize($p['invoice_name'] ?? '');

                if ($normExtracted && ($normExtracted === $normFull || $normExtracted === $normShort || $normExtracted === $normInvoice)) {
                    $score    += self::SCORE_EXACT_NAME;
                    $strategy  = 'exact_name';
                }

                // -- STRATEGY 3: Supplier invoice name match (multi-name) --
                if (!empty($p['supplier_invoice_name'])) {
                    $invEntries = preg_split('/[\n\r,;]+/', $p['supplier_invoice_name']);
                    foreach ($invEntries as $entry) {
                        $normEntry    = $this->normalize($entry);
                        $normSuppInv  = $this->normalize($supplierInvName);
                        $normNameNorm = $this->normalize($name);

                        if (!empty($normEntry)) {
                            if (!empty($normSuppInv) && $normSuppInv === $normEntry) {
                                $score    = max($score, $score + self::SCORE_EXACT_INVOICE_NAME);
                                $strategy = 'exact_invoice_name';
                            } elseif (!empty($normNameNorm) && $normNameNorm === $normEntry) {
                                $score    = max($score, $score + 88);
                                $strategy = 'exact_invoice_name_via_name';
                            } elseif (!empty($normSuppInv) && $this->partialMatch($normSuppInv, $normEntry)) {
                                $score += 28;
                            } elseif (!empty($normNameNorm) && $this->partialMatch($normNameNorm, $normEntry)) {
                                $score += 25;
                            }
                        }
                    }
                }

                // -- STRATEGY 4: Abbreviation expansion --
                $expandedName = $this->expandAbbreviations($name);
                if ($expandedName !== $name) {
                    $normExpanded = $this->normalize($expandedName);
                    if ($normExpanded === $normFull || $normExpanded === $normShort) {
                        $score    += 80;
                        $strategy  = 'abbreviation_expand';
                    }
                }

                // -- STRATEGY 5: Fuzzy match via similar_text --
                if ($score < self::SCORE_EXACT_NAME) {
                    $similarities = [];

                    similar_text(strtolower($name), strtolower($p['full_name'] ?? ''), $simFull);
                    $similarities[] = $simFull;

                    if (!empty($p['short_label'])) {
                        similar_text(strtolower($name), strtolower($p['short_label']), $simShort);
                        $similarities[] = $simShort;
                    }
                    if (!empty($p['invoice_name'])) {
                        similar_text(strtolower($name), strtolower($p['invoice_name']), $simInv);
                        $similarities[] = $simInv;
                    }
                    if (!empty($p['supplier_invoice_name'])) {
                        $entries = preg_split('/[\n\r,]+/', $p['supplier_invoice_name']);
                        foreach ($entries as $entry) {
                            if (!empty(trim($entry))) {
                                similar_text(strtolower($name), strtolower(trim($entry)), $simSuppInv);
                                $similarities[] = $simSuppInv;
                            }
                        }
                    }

                    $bestSim = !empty($similarities) ? max($similarities) : 0;
                    // Apply only if no strong exact match
                    if ($score < self::SCORE_EXACT_NAME) {
                        $score += $bestSim * 0.65;
                    }
                }

                // -- STRATEGY 6: Barcode match --
                if (!empty($extractedBarcode) && !empty($p['packagings'])) {
                    foreach ($p['packagings'] as $pkg) {
                        $dbBarcode = trim($pkg['barcode'] ?? '');
                        if (!empty($dbBarcode) && strcasecmp($extractedBarcode, $dbBarcode) === 0) {
                            $score    += 150; // Very strong signal
                            $strategy  = 'barcode';
                        }
                    }
                }

                // -- STRATEGY 7: Price-based match (±5%) --
                if ($unitPrice > 100 && !empty($p['packagings'])) {
                    $bestPriceScore = 0;
                    foreach ($p['packagings'] as $pkg) {
                        $dbPrice = (float)($pkg['buy_price'] ?? 0);
                        if ($dbPrice > 0) {
                            $diff = abs($dbPrice - $unitPrice);
                            $pct  = $diff / max($dbPrice, $unitPrice);
                            if ($pct <= 0.05) {
                                $priceScore = 25;
                                // Bonus if unit also matches
                                if (!empty($unit) && !empty($pkg['unit_name'])) {
                                    if (stripos($pkg['unit_name'], $unit) !== false ||
                                        stripos($unit, $pkg['unit_name']) !== false) {
                                        $priceScore += 10;
                                    }
                                }
                                $bestPriceScore = max($bestPriceScore, $priceScore);
                            } elseif ($pct <= 0.15) {
                                $bestPriceScore = max($bestPriceScore, 12);
                            }
                        }
                    }
                    $score += $bestPriceScore;
                }

                // -- STRATEGY 8: Packaging semantic match --
                if (!empty($unit)) {
                    $inferredLevel = $this->layoutAnalyzer->inferPackagingLevel($unit);
                    if (!empty($p['packagings'])) {
                        foreach ($p['packagings'] as $pkg) {
                            if ((int)$pkg['level'] === $inferredLevel) {
                                $score += 8;
                                break;
                            }
                        }
                    }
                }

                // -- STRATEGY 9: Brand match (12 pts) --
                if (!empty($extractedBrand) && !empty($p['brand_name'])) {
                    if (stripos($p['brand_name'], $extractedBrand) !== false ||
                        stripos($extractedBrand, $p['brand_name']) !== false) {
                        $score += 12;
                    }
                }

                // -- STRATEGY 10: Variant / weight match --
                if (!empty($extractedVariant) && !empty($p['variant'])) {
                    if (stripos($p['variant'], $extractedVariant) !== false ||
                        stripos($extractedVariant, $p['variant']) !== false) {
                        $score += 8;
                    }
                }
                if ($weightVal !== null && !empty($p['weight_value'])) {
                    if (abs((float)$p['weight_value'] - $weightVal) < 0.01) {
                        $score += 10;
                        if (!empty($weightUnit) && !empty($p['weight_unit'])) {
                            if (strtolower(trim($weightUnit)) === strtolower(trim($p['weight_unit']))) {
                                $score += 3;
                            }
                        }
                    }
                }

                if ($score > $highestScore) {
                    $highestScore = $score;
                    $bestMatch    = $p;
                    $bestStrategy = $strategy;
                }
            }
        }

        $isMatched = $highestScore >= self::MATCH_THRESHOLD;

        // Determine the best packaging level for this item
        $matchedPackagingLevel = 1;
        if ($isMatched && !empty($bestMatch['packagings'])) {
            $matchedPackagingLevel = $this->detectBestPackagingLevel(
                $bestMatch['packagings'],
                $unitPrice,
                $unit,
                $packagingHint
            );
        }

        return [
            'product_id'              => $isMatched ? (int)$bestMatch['id']        : null,
            'product_name'            => $isMatched ? $bestMatch['full_name']       : null,
            'matched_packaging_level' => $matchedPackagingLevel,
            'is_matched'              => $isMatched,
            'match_score'             => round($highestScore, 2),
            'match_strategy'          => $bestStrategy,
        ];
    }

    // ----------------------------------------------------------------
    // PRIVATE HELPERS
    // ----------------------------------------------------------------

    /**
     * Normalize a string for comparison: lowercase, strip accents, 
     * remove special chars, collapse spaces.
     */
    private function normalize(string $str): string
    {
        $str = mb_strtolower(trim($str));

        // Transliterate common Indonesian characters
        $str = str_replace(['á','à','â','ä','ã'], 'a', $str);
        $str = str_replace(['é','è','ê','ë'], 'e', $str);
        $str = str_replace(['í','ì','î','ï'], 'i', $str);
        $str = str_replace(['ó','ò','ô','ö','õ'], 'o', $str);
        $str = str_replace(['ú','ù','û','ü'], 'u', $str);

        // Remove special characters (keep alphanumeric + space)
        $str = preg_replace('/[^a-z0-9\s]/', ' ', $str);
        $str = preg_replace('/\s+/', ' ', $str);
        return trim($str);
    }

    /**
     * Expand abbreviations in the extracted product name.
     */
    private function expandAbbreviations(string $name): string
    {
        $words   = preg_split('/\s+/', strtolower(trim($name)));
        $result  = [];
        foreach ($words as $word) {
            $result[] = self::ABBREVIATIONS[$word] ?? $word;
        }
        return implode(' ', $result);
    }

    /**
     * Check if two normalized strings partially match.
     */
    private function partialMatch(string $a, string $b): bool
    {
        if (empty($a) || empty($b)) return false;
        return stripos($a, $b) !== false || stripos($b, $a) !== false;
    }

    /**
     * Detect the best packaging level for the matched product.
     *
     * Priority:
     *   1. Unit name match
     *   2. Price match (±5%)
     *   3. Packaging level hint from invoice layout
     *   4. Fallback: level 1
     */
    private function detectBestPackagingLevel(
        array $packagings,
        float $unitPrice,
        string $unit,
        int $hintLevel
    ): int {
        $bestLevel = 1;
        $bestScore = 0;

        foreach ($packagings as $pkg) {
            $score = 0;
            $lvl   = (int)$pkg['level'];

            // Unit name match
            if (!empty($unit) && !empty($pkg['unit_name'])) {
                if (stripos($pkg['unit_name'], $unit) !== false ||
                    stripos($unit, $pkg['unit_name']) !== false) {
                    $score += 40;
                }
            }

            // Price match
            $dbPrice = (float)($pkg['buy_price'] ?? 0);
            if ($unitPrice > 0 && $dbPrice > 0) {
                $diff = abs($dbPrice - $unitPrice);
                $pct  = $diff / max($dbPrice, $unitPrice);
                if ($pct <= 0.05) {
                    $score += 30;
                } elseif ($pct <= 0.20) {
                    $score += 15;
                }
            }

            // Hint level match
            if ($lvl === $hintLevel) {
                $score += 15;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestLevel = $lvl;
            }
        }

        // Fallback to level 1 if no meaningful match
        if ($bestScore === 0) {
            $lvl1 = array_filter($packagings, fn($p) => (int)$p['level'] === 1);
            return !empty($lvl1) ? 1 : ($packagings[0]['level'] ?? 1);
        }

        return $bestLevel;
    }
}
