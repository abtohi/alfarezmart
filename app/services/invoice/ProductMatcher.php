<?php
require_once __DIR__ . '/skills/InvoiceSkillInterface.php';

/**
 * ProductMatcher
 *
 * Multi-strategy product matching pipeline with intelligent scoring.
 *
 * Strategy chain (all strategies contribute scores, best total wins):
 *  1. exactCodeMatch         — supplier_product_code or product code exact (score=200, instant match)
 *  2. normalizedNameMatch    — lowercase + strip special chars exact (score=90)
 *  3. supplierInvoiceMatch   — match supplier_invoice_name (multi-name support) (score=95)
 *  4. abbreviationExpand     — expand common abbreviations then rematch (score=80)
 *  5. smartTokenOverlap      — weighted token matching with brand/variant/weight awareness (score=0-85)
 *  6. fuzzyMatch             — similar_text() for close matches (score based on similarity %)
 *  7. barcodeMatch           — barcode field (score=150)
 *  8. brandVariantWeightBoost— composite boost if brand+variant+weight all match (up to +45)
 *  9. expandedNameMatch      — match expanded_name from skill (MDR abbreviations expanded)
 * 10. supplierAffinityBoost  — +25 if product belongs to same supplier
 *
 * @package AlfarezMart\Services\Invoice
 */
class ProductMatcher
{
    /** Minimum score to consider a match valid */
    const MATCH_THRESHOLD = 55;

    /** Score for exact supplier code match (highest priority) */
    const SCORE_EXACT_CODE = 200;

    /** Score for exact supplier_invoice_name match */
    const SCORE_EXACT_INVOICE_NAME = 95;

    /** Score for exact product name match */
    const SCORE_EXACT_NAME = 90;

    /**
     * Common abbreviations used in Indonesian supplier invoices.
     */
    const ABBREVIATIONS = [
        'bsr'   => 'besar',
        'tgh'   => 'tengah',
        'kcl'   => 'kecil',
        'ctn'   => 'karton',
        'dus'   => 'karton',
        'krt'   => 'karton',
        'crt'   => 'karton',
        'btl'   => 'botol',
        'bks'   => 'bungkus',
        'scht'  => 'sachet',
        'sct'   => 'sachet',
        'sch'   => 'sachet',
        'saset' => 'sachet',
        'lbr'   => 'lembar',
        'klg'   => 'kaleng',
        'can'   => 'kaleng',
        'pch'   => 'pouch',
        'dz'    => 'lusin',
        'pcs'   => 'pcs',
        'mie'   => 'mi',
        'mi'    => 'mie',
        'snk'   => 'snack',
        'choc'  => 'cokelat',
        'chk'   => 'cokelat',
        'coklat'=> 'cokelat',
        'chocolate' => 'cokelat',
        'bis'   => 'biscuit',
        'bsc'   => 'biscuit',
        'kopi'  => 'coffee',
        'teh'   => 'tea',
        'air'   => 'water',
        'mnu'   => 'minuman',
        'mkn'   => 'makanan',
        'det'   => 'detergent',
        'spc'   => 'special',
        'chkn'  => 'chicken',
        'prem'  => 'premium',
        'orig'  => 'original',
        'reg'   => 'regular',
        'liq'   => 'liquid',
        'pwdr'  => 'powder',
        'cln'   => 'clean',
        'frsh'  => 'fresh',
        'spcy'  => 'spicy',
        'flr'   => 'floor',
        'clnr'  => 'cleaner',
        'wht'   => 'putih',
        'white' => 'putih',
        'grn'   => 'green',
        'blu'   => 'blue',
    ];

    /**
     * Tokens to SKIP during token overlap matching (only non-differentiating filler words).
     */
    const SKIP_TOKENS = [
        'isi', 'prg', 'x', 'dan', 'yang', 'dgn', 'dg',
    ];

    /** @var LayoutAnalyzer */
    private $layoutAnalyzer;

    public function __construct(LayoutAnalyzer $layoutAnalyzer)
    {
        $this->layoutAnalyzer = $layoutAnalyzer;
    }

    /**
     * Match a single extracted item against the product database.
     *
     * @param  array $item                 Normalized item from extraction
     * @param  array $allProducts          All products (with packagings attached)
     * @param  array $supplierProductIds   Product IDs that belong to this supplier
     * @param  InvoiceSkillInterface|null $skill Active supplier skill
     * @return array{
     *   product_id: int|null,
     *   product_name: string|null,
     *   matched_packaging_level: int,
     *   is_matched: bool,
     *   match_score: float,
     *   match_strategy: string,
     *   product_data: array|null
     * }
     */
    public function match(
        array $item,
        array $allProducts,
        array $supplierProductIds = [],
        ?InvoiceSkillInterface $skill = null
    ): array {
        $bestMatch     = null;
        $highestScore  = 0;
        $bestStrategy  = 'none';

        $name               = $item['name']                 ?? '';
        $supplierInvName    = $item['supplier_invoice_name'] ?? $name;
        $expandedName       = $item['expanded_name']         ?? '';
        $extractedCode      = trim($item['supplier_code']   ?? '');
        $extractedBarcode   = trim($item['barcode']         ?? '');
        $extractedBrand     = trim($item['brand']           ?? '');
        $extractedVariant   = trim($item['variant']         ?? '');
        $unitPrice          = (float)($item['unit_price']   ?? 0);
        $unit               = $item['unit']                 ?? '';
        $weightVal          = isset($item['weight']) ? (is_numeric($item['weight']) ? (float)$item['weight'] : null) : null;
        $weightUnit         = $item['weight_unit']          ?? '';

        // ---- STRATEGY 1: Exact supplier product code (Highest Priority) ----
        if (!empty($extractedCode)) {
            $cleanExtCode = ltrim($extractedCode, '0');
            foreach ($allProducts as $p) {
                $dbCode     = trim($p['supplier_product_code'] ?? '');
                $dbProdCode = trim($p['code'] ?? '');

                if ((!empty($dbCode) && (strcasecmp($extractedCode, $dbCode) === 0 || ltrim($dbCode, '0') === $cleanExtCode)) ||
                    (!empty($dbProdCode) && (strcasecmp($extractedCode, $dbProdCode) === 0 || ltrim($dbProdCode, '0') === $cleanExtCode))) {
                    $bestMatch    = $p;
                    $highestScore = self::SCORE_EXACT_CODE;
                    $bestStrategy = 'exact_code';
                    break;
                }
            }
        }

        // ---- STRATEGY 2-10: Score-based matching if no exact code match ----
        if (!$bestMatch) {
            foreach ($allProducts as $p) {
                $score    = 0;
                $strategy = 'score';

                // -- Supplier affinity boost (+25) --
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
                                $score += 30;
                            } elseif (!empty($normNameNorm) && $this->partialMatch($normNameNorm, $normEntry)) {
                                $score += 25;
                            }
                        }
                    }
                }

                // -- STRATEGY 4: Abbreviation expansion & exact rematch --
                $expandedMatchName = !empty($expandedName) ? $expandedName : $this->expandAbbreviations($name);
                $normExpanded = $this->normalize($expandedMatchName);
                if ($normExpanded === $normFull || $normExpanded === $normShort) {
                    $score    += 80;
                    $strategy  = 'abbreviation_expand';
                }

                // -- STRATEGY 5: Smart Token Overlap Match (with weight awareness) --
                $tokenScore = $this->calculateTokenOverlapScore($normExpanded, $normFull, $normShort, $normInvoice, $p);
                if ($tokenScore > 0 && $tokenScore > $score) {
                    $score    = $tokenScore;
                    $strategy = 'token_overlap';
                }

                // -- STRATEGY 6: Fuzzy match via similar_text (only if no higher score) --
                if ($score < 65) {
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

                    // Also fuzzy against expanded name
                    if (!empty($expandedMatchName)) {
                        similar_text(strtolower($expandedMatchName), strtolower($p['full_name'] ?? ''), $simExpFull);
                        $similarities[] = $simExpFull;
                    }

                    $bestSim = !empty($similarities) ? max($similarities) : 0;
                    if ($bestSim >= 60) {
                        $fuzzyScore = $bestSim * 0.85;
                        if ($fuzzyScore > $score) {
                            $score = $fuzzyScore;
                            $strategy = 'fuzzy';
                        }
                    }
                }

                // -- STRATEGY 7: Barcode match --
                if (!empty($extractedBarcode) && !empty($p['packagings'])) {
                    foreach ($p['packagings'] as $pkg) {
                        $dbBarcode = trim($pkg['barcode'] ?? '');
                        if (!empty($dbBarcode) && strcasecmp($extractedBarcode, $dbBarcode) === 0) {
                            $score    += 150;
                            $strategy  = 'barcode';
                        }
                    }
                }

                // -- STRATEGY 8: Brand + Variant + Weight composite boost --
                $compositeBoost = $this->calculateCompositeBoost($extractedBrand, $extractedVariant, $weightVal, $weightUnit, $p);
                if ($compositeBoost > 0) {
                    $score += $compositeBoost;
                    if ($compositeBoost >= 30 && $strategy === 'score') {
                        $strategy = 'brand_variant_weight';
                    }
                }

                // -- STRATEGY 9: Expanded name match against supplier_invoice_name --
                if (!empty($expandedMatchName) && !empty($p['supplier_invoice_name'])) {
                    $normExpandedLower = $this->normalize($expandedMatchName);
                    $invEntries = preg_split('/[\n\r,;]+/', $p['supplier_invoice_name']);
                    foreach ($invEntries as $entry) {
                        $normEntry = $this->normalize($entry);
                        if (!empty($normEntry) && !empty($normExpandedLower)) {
                            if ($normExpandedLower === $normEntry) {
                                $score = max($score, 92);
                                $strategy = 'expanded_invoice_match';
                            }
                        }
                    }
                }

                // -- STRATEGY 10: Price Proximity Boost (Distinguish variants/sizes sharing same brand) --
                if ($unitPrice > 0 && !empty($p['packagings'])) {
                    $bestPriceDiffPct = 999;
                    foreach ($p['packagings'] as $pkg) {
                        $bp = (float)($pkg['buy_price'] ?? 0);
                        if ($bp > 0) {
                            $diff = abs($bp - $unitPrice) / max($bp, $unitPrice);
                            if ($diff < $bestPriceDiffPct) {
                                $bestPriceDiffPct = $diff;
                            }
                        }
                    }
                    if ($bestPriceDiffPct <= 0.05) {
                        // Exact/very close packaging buy price match (within 5%)
                        $score += 30;
                    } elseif ($bestPriceDiffPct <= 0.15) {
                        // Close price match (within 15%)
                        $score += 15;
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
            if ($skill !== null) {
                $pkgDecision = $skill->determinePackagingLevel(
                    $unitPrice,
                    $bestMatch['packagings'],
                    $unit,
                    isset($bestMatch['last_buy_price']) ? (float)$bestMatch['last_buy_price'] : null
                );
                $matchedPackagingLevel = $pkgDecision['level'] ?? 1;
            } else {
                $matchedPackagingLevel = $this->detectBestPackagingLevel(
                    $bestMatch['packagings'],
                    $unitPrice,
                    $unit
                );
            }
        }

        // Log matching for debugging
        if (!$isMatched) {
            error_log("MATCHER_MISS: name='{$name}' code='{$extractedCode}' brand='{$extractedBrand}' variant='{$extractedVariant}' weight={$weightVal}{$weightUnit} bestScore={$highestScore} bestStrategy={$bestStrategy}");
        }

        return [
            'product_id'              => $isMatched ? (int)$bestMatch['id']         : null,
            'product_name'            => $isMatched ? $bestMatch['full_name']        : null,
            'matched_packaging_level' => $matchedPackagingLevel,
            'is_matched'              => $isMatched,
            'match_score'             => round($highestScore, 2),
            'match_strategy'          => $bestStrategy,
            'product_data'            => $isMatched ? $bestMatch                    : null,
        ];
    }

    /**
     * Smart token overlap scoring.
     *
     * Tokenizes both the extracted (expanded) name and database product name,
     * then calculates a weighted overlap score. Brand and variant tokens get
     * higher weight than generic category words.
     */
    private function calculateTokenOverlapScore(
        string $normExtracted,
        string $normFull,
        string $normShort,
        string $normInvoice,
        array $product
    ): float {
        // Build candidate tokens from extraction (exclude packaging/unit tokens)
        $extractTokens = $this->getSignificantTokens($normExtracted);
        if (empty($extractTokens)) return 0;

        // Build DB tokens from all product name fields + brand + variant
        $dbText = $normFull . ' ' . $normShort . ' ' . $normInvoice;
        if (!empty($product['brand_name'])) {
            $dbText .= ' ' . $this->normalize($product['brand_name']);
        }
        if (!empty($product['variant'])) {
            $dbText .= ' ' . $this->normalize($product['variant']);
        }
        if (!empty($product['supplier_invoice_name'])) {
            $dbText .= ' ' . $this->normalize($product['supplier_invoice_name']);
        }

        $dbTokens = array_unique(array_filter(explode(' ', $dbText), fn($t) => strlen($t) >= 2));

        // Calculate match
        $matchedCount = 0;
        $totalWeight = 0;
        $matchedWeight = 0;

        foreach ($extractTokens as $tok) {
            // Determine token weight
            $weight = $this->getTokenWeight($tok);
            $totalWeight += $weight;

            foreach ($dbTokens as $dbTok) {
                // Exact match
                if ($tok === $dbTok) {
                    $matchedCount++;
                    $matchedWeight += $weight;
                    break;
                }
                // Substring match for longer tokens (≥4 chars)
                if (strlen($tok) >= 4 && (strpos($dbTok, $tok) !== false || strpos($tok, $dbTok) !== false)) {
                    $matchedCount++;
                    $matchedWeight += $weight * 0.85; // Partial match penalty
                    break;
                }
                // Number match with tolerance (e.g., weight 45 vs 45)
                if (is_numeric($tok) && is_numeric($dbTok)) {
                    $numTok = (float)$tok;
                    $numDb  = (float)$dbTok;
                    if ($numTok > 0 && $numDb > 0 && abs($numTok - $numDb) / max($numTok, $numDb) < 0.05) {
                        $matchedCount++;
                        $matchedWeight += $weight;
                        break;
                    }
                }
            }
        }

        if ($totalWeight == 0) return 0;

        $overlapRatio = $matchedWeight / $totalWeight;
        $tokenRatio = count($extractTokens) > 0 ? $matchedCount / count($extractTokens) : 0;

        // Need at least 60% weighted match to be considered
        if ($overlapRatio < 0.60) return 0;

        // Score: base 55 + up to 30 bonus based on overlap quality
        $score = 55 + ($overlapRatio * 30);

        // Bonus for high token count matches (more tokens matched = more confident)
        if ($matchedCount >= 4) $score += 5;
        if ($matchedCount >= 5) $score += 5;

        return min($score, 88); // Cap at 88 (below exact match scores)
    }

    /**
     * Get significant tokens from a normalized string (excluding skip tokens).
     */
    private function getSignificantTokens(string $normalized): array
    {
        $tokens = explode(' ', $normalized);
        return array_values(array_filter($tokens, function($t) {
            return strlen($t) >= 2 && !in_array($t, self::SKIP_TOKENS);
        }));
    }

    /**
     * Get weight for a token (brand/variant words weigh more than generic terms).
     */
    private function getTokenWeight(string $token): float
    {
        // Category words (less distinctive)
        $genericTokens = ['powder', 'detergent', 'liquid', 'cream', 'soap', 'mie', 'mi', 'instant',
                          'noodle', 'premium', 'sabun', 'shampo', 'shampoo', 'pasta', 'gigi', 'minyak'];
        if (in_array($token, $genericTokens)) return 0.5;

        // Numeric tokens (weight/volume) - important for distinguishing variants
        if (is_numeric($token)) return 1.5;

        // Brand/variant words (most distinctive)
        return 1.0;
    }

    /**
     * Calculate composite boost from brand + variant + weight matching.
     *
     * When a product name from the invoice has brand=DAIA, variant=PUTIH, weight=23g,
     * and the DB product also has brand=Daia, variant=Putih, weight=23g,
     * this gives a significant boost to distinguish it from other similar products.
     */
    private function calculateCompositeBoost(
        string $extractedBrand,
        string $extractedVariant,
        ?float $weightVal,
        string $weightUnit,
        array $product
    ): float {
        $boost = 0;

        // Brand match (+15)
        if (!empty($extractedBrand) && !empty($product['brand_name'])) {
            $normBrand = strtolower(trim($extractedBrand));
            $normDbBrand = strtolower(trim($product['brand_name']));
            if ($normBrand === $normDbBrand ||
                stripos($normDbBrand, $normBrand) !== false ||
                stripos($normBrand, $normDbBrand) !== false) {
                $boost += 15;
            }
        }

        // Variant match (+20 for exact, +10 for partial)
        if (!empty($extractedVariant)) {
            $normVariant = $this->normalize($extractedVariant);
            $dbVariant = $this->normalize($product['variant'] ?? '');
            $dbFullName = $this->normalize($product['full_name'] ?? '');

            if (!empty($dbVariant)) {
                if ($normVariant === $dbVariant) {
                    $boost += 20;
                } elseif ($this->variantMatch($normVariant, $dbVariant)) {
                    $boost += 15;
                }
            }

            // Also check variant words in full product name
            if ($boost < 15 && !empty($dbFullName)) {
                $variantTokens = array_filter(explode(' ', $normVariant), fn($t) => strlen($t) >= 3);
                $matchedVarTokens = 0;
                foreach ($variantTokens as $vt) {
                    if (strpos($dbFullName, $vt) !== false) {
                        $matchedVarTokens++;
                    }
                }
                if (!empty($variantTokens) && $matchedVarTokens / count($variantTokens) >= 0.6) {
                    $boost += 10;
                }
            }
        }

        // Weight/volume match (+10 for exact, +5 for close)
        if ($weightVal !== null && $weightVal > 0) {
            $dbWeight = null;
            $dbWeightUnit = '';

            // Try from product fields
            if (!empty($product['weight_value'])) {
                $dbWeight = (float)$product['weight_value'];
                $dbWeightUnit = strtolower(trim($product['weight_unit'] ?? ''));
            }

            // Also try to extract weight from full_name (e.g., "23g" in "Daia Powder Detergent Putih (20 × 6 × 23g)")
            if ($dbWeight === null || $dbWeight == 0) {
                $fn = $product['full_name'] ?? '';
                if (preg_match('/(\d+(?:\.\d+)?)\s*(g|gr|ml|l|kg)\b/i', $fn, $fnMatch)) {
                    $dbWeight = (float)$fnMatch[1];
                    $dbWeightUnit = strtolower($fnMatch[2]);
                    if ($dbWeightUnit === 'gr') $dbWeightUnit = 'g';
                }
            }

            if ($dbWeight !== null && $dbWeight > 0) {
                // Normalize units for comparison
                $normExtUnit = strtolower(trim($weightUnit));
                if ($normExtUnit === 'gr') $normExtUnit = 'g';

                $unitsMatch = empty($normExtUnit) || empty($dbWeightUnit) ||
                              $normExtUnit === $dbWeightUnit;

                if ($unitsMatch) {
                    $diff = abs($dbWeight - $weightVal);
                    if ($diff < 0.5) {
                        $boost += 10; // Exact weight match
                    } elseif ($diff / max($dbWeight, $weightVal) <= 0.1) {
                        $boost += 5;  // Close weight match
                    }
                }
            }
        }

        return $boost;
    }

    /**
     * Check if two variant strings refer to the same variant.
     * Handles partial matches, word reordering, etc.
     */
    private function variantMatch(string $a, string $b): bool
    {
        if (empty($a) || empty($b)) return false;

        // Direct containment
        if (strpos($a, $b) !== false || strpos($b, $a) !== false) return true;

        // Token overlap (all words of shorter string found in longer)
        $tokensA = explode(' ', $a);
        $tokensB = explode(' ', $b);
        $shorter = count($tokensA) <= count($tokensB) ? $tokensA : $tokensB;
        $longer  = count($tokensA) <= count($tokensB) ? $tokensB : $tokensA;

        $matchCount = 0;
        foreach ($shorter as $st) {
            if (strlen($st) < 3) continue;
            foreach ($longer as $lt) {
                if ($st === $lt || (strlen($st) >= 4 && strpos($lt, $st) !== false)) {
                    $matchCount++;
                    break;
                }
            }
        }

        $significantTokens = count(array_filter($shorter, fn($t) => strlen($t) >= 3));
        return $significantTokens > 0 && $matchCount / $significantTokens >= 0.7;
    }

    private function normalize(string $str): string
    {
        $str = mb_strtolower(trim($str));
        $str = str_replace(['á','à','â','ä','ã'], 'a', $str);
        $str = str_replace(['é','è','ê','ë'], 'e', $str);
        $str = str_replace(['í','ì','î','ï'], 'i', $str);
        $str = str_replace(['ó','ò','ô','ö','õ'], 'o', $str);
        $str = str_replace(['ú','ù','û','ü'], 'u', $str);

        $str = preg_replace('/[^a-z0-9\s]/', ' ', $str);
        $str = preg_replace('/\s+/', ' ', $str);
        return trim($str);
    }

    private function expandAbbreviations(string $name): string
    {
        $words   = preg_split('/\s+/', strtolower(trim($name)));
        $result  = [];
        foreach ($words as $word) {
            $clean = preg_replace('/[^a-z0-9&]/', '', $word);
            $result[] = self::ABBREVIATIONS[$clean] ?? $word;
        }
        return implode(' ', $result);
    }

    private function partialMatch(string $a, string $b): bool
    {
        if (empty($a) || empty($b)) return false;
        return stripos($a, $b) !== false || stripos($b, $a) !== false;
    }

    private function detectBestPackagingLevel(array $packagings, float $unitPrice, string $unit): int
    {
        $bestLevel = 1;
        $bestScore = -1;

        foreach ($packagings as $pkg) {
            $score = 0;
            $lvl   = (int)$pkg['level'];

            // 1. Unit name match (+40)
            if (!empty($unit) && !empty($pkg['unit_name'])) {
                if (stripos($pkg['unit_name'], $unit) !== false ||
                    stripos($unit, $pkg['unit_name']) !== false) {
                    $score += 40;
                }
            }

            // 2. Price distance match (+30)
            $dbPrice = (float)($pkg['buy_price'] ?? 0);
            if ($unitPrice > 0 && $dbPrice > 0) {
                $diff = abs($dbPrice - $unitPrice);
                $pct  = $diff / max($dbPrice, $unitPrice);
                if ($pct <= 0.10) {
                    $score += 30;
                } elseif ($pct <= 0.25) {
                    $score += 15;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestLevel = $lvl;
            }
        }

        return $bestScore > 0 ? $bestLevel : 1;
    }
}
