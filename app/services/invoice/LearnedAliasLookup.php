<?php
/**
 * LearnedAliasLookup
 *
 * Fast deterministic product matching using learned aliases.
 * Builds an in-memory hashmap of normalized alias -> product data
 * from ai_invoice_learning_logs and product.supplier_invoice_name.
 *
 * This enables the FAST PATH: invoices from known suppliers with
 * previously learned products can be matched WITHOUT any AI call.
 *
 * @package AlfarezMart\Services\Invoice
 */
class LearnedAliasLookup
{
    /** @var \PDO */
    private $db;

    /** @var array normalized_alias => product data (in-memory cache) */
    private $aliasMap = [];

    /** @var bool */
    private $loaded = false;

    /** @var int|null Current supplier filter */
    private $currentSupplierId = null;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Load all learned aliases into memory for fast lookup.
     * Called once per scan session.
     *
     * @param int|null $supplierId Filter aliases by supplier (null = all)
     */
    public function loadForSupplier(?int $supplierId = null): void
    {
        $this->aliasMap = [];
        $this->currentSupplierId = $supplierId;

        // Source 1: product.supplier_invoice_name (multi-line aliases)
        $this->loadFromProductAliases($supplierId);

        // Source 2: ai_invoice_learning_logs (confirmed scan history)
        $this->loadFromLearningLogs($supplierId);

        $this->loaded = true;
    }

    /**
     * Look up a product by invoice text (name or code).
     * Returns product data if found with high confidence.
     *
     * @param string $invoiceText Raw text from invoice (product name)
     * @param string $supplierCode Supplier product code (if available)
     * @return array|null Product data with match info, or null
     */
    public function lookup(string $invoiceText, string $supplierCode = ''): ?array
    {
        if (!$this->loaded) return null;

        // Strategy 1: Exact supplier code match
        if (!empty($supplierCode)) {
            $normCode = $this->normalize($supplierCode);
            $codeKey = 'code:' . $normCode;
            if (isset($this->aliasMap[$codeKey])) {
                return array_merge($this->aliasMap[$codeKey], [
                    'match_type'  => 'learned_alias_code',
                    'match_score' => 200,
                    'confidence'  => 0.98,
                ]);
            }
            // Try without leading zeros
            $codeKeyStripped = 'code:' . ltrim($normCode, '0');
            if (isset($this->aliasMap[$codeKeyStripped])) {
                return array_merge($this->aliasMap[$codeKeyStripped], [
                    'match_type'  => 'learned_alias_code',
                    'match_score' => 195,
                    'confidence'  => 0.97,
                ]);
            }
        }

        // Strategy 2: Exact normalized name match
        $normText = $this->normalize($invoiceText);
        if (!empty($normText) && isset($this->aliasMap[$normText])) {
            return array_merge($this->aliasMap[$normText], [
                'match_type'  => 'learned_alias_name',
                'match_score' => 180,
                'confidence'  => 0.96,
            ]);
        }

        // Strategy 3: Token-stripped match (remove packaging/unit tokens)
        $stripped = $this->stripPackagingTokens($normText);
        if (!empty($stripped) && $stripped !== $normText && isset($this->aliasMap[$stripped])) {
            return array_merge($this->aliasMap[$stripped], [
                'match_type'  => 'learned_alias_stripped',
                'match_score' => 160,
                'confidence'  => 0.92,
            ]);
        }

        return null;
    }

    /**
     * Get the count of loaded aliases.
     */
    public function getAliasCount(): int
    {
        return count($this->aliasMap);
    }

    /**
     * Check if aliases are loaded.
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    // ----------------------------------------------------------------
    // PRIVATE: Data Loading
    // ----------------------------------------------------------------

    private function loadFromProductAliases(?int $supplierId): void
    {
        try {
            // Load all active products with supplier codes and invoice aliases
            $sql = "
                SELECT p.id as product_id, p.full_name, p.code, p.supplier_invoice_name,
                       p.short_label, p.invoice_name, p.brand_id, p.variant,
                       b.name as brand_name,
                       COALESCE(
                           (SELECT sp.supplier_product_code FROM supplier_products sp WHERE sp.product_id = p.id " . ($supplierId ? "AND sp.supplier_id = " . (int)$supplierId : "") . " AND sp.supplier_product_code IS NOT NULL AND sp.supplier_product_code != '' LIMIT 1),
                           (SELECT sp2.supplier_product_code FROM supplier_products sp2 WHERE sp2.product_id = p.id AND sp2.supplier_product_code IS NOT NULL AND sp2.supplier_product_code != '' LIMIT 1)
                       ) as supplier_product_code,
                       (SELECT sp3.last_buy_price FROM supplier_products sp3 WHERE sp3.product_id = p.id " . ($supplierId ? "AND sp3.supplier_id = " . (int)$supplierId : "") . " ORDER BY sp3.id DESC LIMIT 1) as last_buy_price
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.is_active = 1
            ";

            $stmt = $this->db->query($sql);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $productData = [
                    'product_id'     => (int)$row['product_id'],
                    'full_name'      => $row['full_name'],
                    'code'           => $row['code'],
                    'brand_name'     => $row['brand_name'],
                    'last_buy_price' => $row['last_buy_price'] ? (float)$row['last_buy_price'] : null,
                ];

                // 1. Index by supplier product code (Highest Priority Exact Match)
                if (!empty($row['supplier_product_code'])) {
                    $codeNorm = $this->normalize($row['supplier_product_code']);
                    $this->aliasMap['code:' . $codeNorm] = $productData;
                    // Also without leading zeros
                    $codeStripped = ltrim($codeNorm, '0');
                    if ($codeStripped !== $codeNorm && !empty($codeStripped)) {
                        $this->aliasMap['code:' . $codeStripped] = $productData;
                    }
                }

                // 2. Index by internal product code
                if (!empty($row['code'])) {
                    $prodCode = $this->normalize($row['code']);
                    if (!isset($this->aliasMap['code:' . $prodCode])) {
                        $this->aliasMap['code:' . $prodCode] = $productData;
                    }
                    $codeStripped = ltrim($prodCode, '0');
                    if ($codeStripped !== $prodCode && !empty($codeStripped)) {
                        $this->aliasMap['code:' . $codeStripped] = $productData;
                    }
                }

                // 3. Index by multi-line supplier_invoice_name aliases
                if (!empty($row['supplier_invoice_name'])) {
                    $aliases = preg_split('/[\n\r,;]+/', $row['supplier_invoice_name']);
                    foreach ($aliases as $alias) {
                        $alias = trim($alias);
                        if (empty($alias) || strlen($alias) < 2) continue;

                        $normAlias = $this->normalize($alias);
                        if (!empty($normAlias)) {
                            $this->aliasMap[$normAlias] = $productData;

                            $stripped = $this->stripPackagingTokens($normAlias);
                            if ($stripped !== $normAlias && !empty($stripped)) {
                                if (!isset($this->aliasMap[$stripped])) {
                                    $this->aliasMap[$stripped] = $productData;
                                }
                            }
                        }
                    }
                }

                // 4. Index by invoice_name
                if (!empty($row['invoice_name'])) {
                    $normInv = $this->normalize($row['invoice_name']);
                    if (!empty($normInv) && !isset($this->aliasMap[$normInv])) {
                        $this->aliasMap[$normInv] = $productData;
                    }
                }

                // 5. Index by short_label
                if (!empty($row['short_label'])) {
                    $normShort = $this->normalize($row['short_label']);
                    if (!empty($normShort) && !isset($this->aliasMap[$normShort])) {
                        $this->aliasMap[$normShort] = $productData;
                    }
                }

                // 6. Index by normalized full_name
                if (!empty($row['full_name'])) {
                    $normFull = $this->normalize($row['full_name']);
                    if (!empty($normFull) && !isset($this->aliasMap[$normFull])) {
                        $this->aliasMap[$normFull] = $productData;
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log("LearnedAliasLookup::loadFromProductAliases error: " . $e->getMessage());
        }
    }

    private function loadFromLearningLogs(?int $supplierId): void
    {
        try {
            // Check if table exists
            try {
                $this->db->query("SELECT 1 FROM ai_invoice_learning_logs LIMIT 1");
            } catch (\PDOException $e) {
                return; // Table doesn't exist yet
            }

            $sql = "
                SELECT ll.product_id, ll.invoice_raw_name, ll.supplier_code, ll.match_count, ll.buy_price,
                       p.full_name, p.code, b.name as brand_name
                FROM ai_invoice_learning_logs ll
                JOIN products p ON ll.product_id = p.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.is_active = 1
            ";
            $params = [];

            if ($supplierId && $supplierId > 0) {
                $sql .= " AND (ll.supplier_id = ? OR ll.supplier_id IS NULL)";
                $params[] = $supplierId;
            }

            // Prioritize most-used aliases
            $sql .= " ORDER BY ll.match_count DESC LIMIT 500";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $productData = [
                    'product_id'     => (int)$row['product_id'],
                    'full_name'      => $row['full_name'],
                    'code'           => $row['code'],
                    'brand_name'     => $row['brand_name'],
                    'last_buy_price' => $row['buy_price'] ? (float)$row['buy_price'] : null,
                ];

                // Index by learned invoice name
                $normName = $this->normalize($row['invoice_raw_name']);
                if (!empty($normName) && !isset($this->aliasMap[$normName])) {
                    $this->aliasMap[$normName] = $productData;
                }

                // Index by learned supplier code
                if (!empty($row['supplier_code'])) {
                    $codeKey = 'code:' . $this->normalize($row['supplier_code']);
                    if (!isset($this->aliasMap[$codeKey])) {
                        $this->aliasMap[$codeKey] = $productData;
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log("LearnedAliasLookup::loadFromLearningLogs error: " . $e->getMessage());
        }
    }

    // ----------------------------------------------------------------
    // PRIVATE: Normalization
    // ----------------------------------------------------------------

    private function normalize(string $str): string
    {
        $str = mb_strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9\s]/', ' ', $str);
        $str = preg_replace('/\s+/', ' ', $str);
        return trim($str);
    }

    /**
     * Remove packaging/unit tokens that don't contribute to product identity.
     */
    private function stripPackagingTokens(string $normalized): string
    {
        $skipTokens = [
            'bag', 'sct', 'cup', 'btl', 'isi', 'prg', 'box', 'pcs',
            'sachet', 'bungkus', 'botol', 'karton', 'pack', 'pouch',
            'renteng', 'slop', 'lusin', 'dozen', 'bsr', 'tgh', 'kcl',
            'ctn', 'dus', 'krt', 'bks', 'scht', 'lbr', 'klg', 'dz',
            'x', 'ml', 'gr', 'g', 'kg', 'l', 'oz', 'cc',
        ];

        $tokens = explode(' ', $normalized);
        $filtered = array_filter($tokens, function ($t) use ($skipTokens) {
            return strlen($t) >= 2 && !in_array($t, $skipTokens) && !is_numeric($t);
        });

        return trim(implode(' ', $filtered));
    }
}
