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
            $sql = "
                SELECT p.id as product_id, p.full_name, p.code, p.supplier_invoice_name,
                       p.short_label, p.invoice_name, p.brand_id,
                       b.name as brand_name,
                       sp.supplier_product_code, sp.last_buy_price
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN supplier_products sp ON p.id = sp.product_id
            ";
            $params = [];

            if ($supplierId && $supplierId > 0) {
                $sql .= " AND sp.supplier_id = ?";
                $params[] = $supplierId;
            }

            $sql .= " WHERE p.is_active = 1 
                       AND (p.supplier_invoice_name IS NOT NULL AND p.supplier_invoice_name != '')";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $productData = [
                    'product_id'   => (int)$row['product_id'],
                    'full_name'    => $row['full_name'],
                    'code'         => $row['code'],
                    'brand_name'   => $row['brand_name'],
                    'last_buy_price' => $row['last_buy_price'] ? (float)$row['last_buy_price'] : null,
                ];

                // Index by each alias line
                $aliases = preg_split('/[\n\r,;]+/', $row['supplier_invoice_name']);
                foreach ($aliases as $alias) {
                    $alias = trim($alias);
                    if (empty($alias) || strlen($alias) < 3) continue;

                    $normAlias = $this->normalize($alias);
                    if (!empty($normAlias)) {
                        $this->aliasMap[$normAlias] = $productData;

                        // Also index stripped version
                        $stripped = $this->stripPackagingTokens($normAlias);
                        if ($stripped !== $normAlias && !empty($stripped)) {
                            if (!isset($this->aliasMap[$stripped])) {
                                $this->aliasMap[$stripped] = $productData;
                            }
                        }
                    }
                }

                // Index by supplier product code
                if (!empty($row['supplier_product_code'])) {
                    $codeNorm = $this->normalize($row['supplier_product_code']);
                    $this->aliasMap['code:' . $codeNorm] = $productData;
                    // Also without leading zeros
                    $codeStripped = ltrim($codeNorm, '0');
                    if ($codeStripped !== $codeNorm) {
                        $this->aliasMap['code:' . $codeStripped] = $productData;
                    }
                }

                // Index by product code
                if (!empty($row['code'])) {
                    $prodCode = $this->normalize($row['code']);
                    if (!isset($this->aliasMap['code:' . $prodCode])) {
                        $this->aliasMap['code:' . $prodCode] = $productData;
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
