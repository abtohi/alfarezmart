<?php
require_once __DIR__ . '/skills/InvoiceSkillInterface.php';

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
 *  7. priceDistanceMatch   — packaging buy_price distance (closest price level)
 *  8. brandVariantBoost    — boost if brand/variant/weight matches
 *
 * @package AlfarezMart\Services\Invoice
 */
class ProductMatcher
{
    /** Minimum score to consider a match valid */
    const MATCH_THRESHOLD = 60;

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
        'bsr'  => 'besar',
        'tgh'  => 'tengah',
        'kcl'  => 'kecil',
        'ctn'  => 'karton',
        'dus'  => 'karton',
        'krt'  => 'karton',
        'btl'  => 'botol',
        'bks'  => 'bungkus',
        'scht' => 'sachet',
        'sct'  => 'sachet',
        'lbr'  => 'lembar',
        'klg'  => 'kaleng',
        'dz'   => 'lusin',
        'pcs'  => 'pcs',
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
        'det'  => 'detergent',
        'spc'  => 'special',
        'chkn' => 'chicken',
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
        $extractedCode      = trim($item['supplier_code']   ?? '');
        $extractedBarcode   = trim($item['barcode']         ?? '');
        $extractedBrand     = trim($item['brand']           ?? '');
        $extractedVariant   = trim($item['variant']         ?? '');
        $unitPrice          = (float)($item['unit_price']   ?? 0);
        $unit               = $item['unit']                 ?? '';
        $weightVal          = isset($item['weight']) ? (float)$item['weight'] : null;
        $weightUnit         = $item['weight_unit']          ?? '';

        // ---- STRATEGY 1: Exact supplier product code (Highest Priority) ----
        if (!empty($extractedCode)) {
            // Trim leading zeroes or spaces for comparison
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

        // ---- STRATEGY 2-8: Score-based matching if no exact code match ----
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

                // -- STRATEGY 4: Abbreviation expansion & Token Overlap --
                $expandedName = $this->expandAbbreviations($name);
                $normExpanded = $this->normalize($expandedName);
                if ($normExpanded === $normFull || $normExpanded === $normShort) {
                    $score    += 80;
                    $strategy  = 'abbreviation_expand';
                }

                // -- STRATEGY 5: Smart Token Overlap Match --
                $extractTokens = array_filter(explode(' ', $normExpanded), fn($t) => strlen($t) >= 2 && !in_array($t, ['bag', 'sct', 'cup', 'btl', 'isi', 'prg']));
                $dbTokens      = explode(' ', $normFull . ' ' . $normShort . ' ' . $normInvoice);
                
                if (!empty($extractTokens)) {
                    $matchedCount = 0;
                    foreach ($extractTokens as $tok) {
                        foreach ($dbTokens as $dbTok) {
                            if ($tok === $dbTok || (strlen($tok) >= 4 && strpos($dbTok, $tok) !== false)) {
                                $matchedCount++;
                                break;
                            }
                        }
                    }
                    $overlapRatio = $matchedCount / count($extractTokens);
                    if ($overlapRatio >= 0.75) {
                        $tokenScore = 70 + ($overlapRatio * 15);
                        if ($tokenScore > $score) {
                            $score = $tokenScore;
                            $strategy = 'token_overlap';
                        }
                    }
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

                    $bestSim = !empty($similarities) ? max($similarities) : 0;
                    if ($bestSim >= 65) {
                        $score = max($score, $bestSim * 0.75);
                    }
                }

                // -- STRATEGY 6: Barcode match --
                if (!empty($extractedBarcode) && !empty($p['packagings'])) {
                    foreach ($p['packagings'] as $pkg) {
                        $dbBarcode = trim($pkg['barcode'] ?? '');
                        if (!empty($dbBarcode) && strcasecmp($extractedBarcode, $dbBarcode) === 0) {
                            $score    += 150;
                            $strategy  = 'barcode';
                        }
                    }
                }

                // -- STRATEGY 7: Brand boost --
                if (!empty($extractedBrand) && !empty($p['brand_name'])) {
                    if (stripos($p['brand_name'], $extractedBrand) !== false ||
                        stripos($extractedBrand, $p['brand_name']) !== false) {
                        $score += 15;
                    }
                }

                // -- STRATEGY 8: Variant & weight boost --
                if (!empty($extractedVariant) && !empty($p['variant'])) {
                    if (stripos($p['variant'], $extractedVariant) !== false ||
                        stripos($extractedVariant, $p['variant']) !== false) {
                        $score += 10;
                    }
                }
                if ($weightVal !== null && !empty($p['weight_value'])) {
                    if (abs((float)$p['weight_value'] - $weightVal) < 0.01) {
                        $score += 10;
                        if (!empty($weightUnit) && !empty($p['weight_unit']) &&
                            strtolower(trim($weightUnit)) === strtolower(trim($p['weight_unit']))) {
                            $score += 5;
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
            $result[] = self::ABBREVIATIONS[$word] ?? $word;
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
