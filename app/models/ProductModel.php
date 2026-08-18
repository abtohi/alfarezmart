<?php
/**
 * ProductModel - Master produk
 * Semua query database untuk produk ada di sini
 */
class ProductModel extends Model
{
    protected $table = 'products';

    public function __construct()
    {
        parent::__construct();
        $this->ensureCustomLabelColumn();
        $this->ensureIsAvailableColumn();
    }

    private function ensureCustomLabelColumn()
    {
        static $checked = false;
        if ($checked) return;
        try {
            $this->db->query("SELECT is_custom_label FROM products LIMIT 1");
        } catch (PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE products ADD COLUMN is_custom_label TINYINT(1) DEFAULT 0");
            } catch (PDOException $e2) {
                // Ignore
            }
        }
        $checked = true;
    }

    private function ensureIsAvailableColumn()
    {
        static $checked = false;
        if ($checked) return;
        try {
            $this->db->query("SELECT is_available FROM products LIMIT 1");
        } catch (PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE products ADD COLUMN is_available TINYINT(1) DEFAULT 1");
            } catch (PDOException $e2) {
                // Ignore
            }
        }
        $checked = true;
    }

    public function findWithDetails(int|string $id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name,
                   COALESCE(s.current_qty_base, 0) as current_qty_base
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN stock s ON s.product_id = p.id
            WHERE p.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function searchProducts(string $keyword, int $limit = 20, bool $forPos = false)
    {
        $rawKeyword = trim($keyword);
        $words = array_filter(explode(' ', $rawKeyword), 'strlen');
        $whereSql = "p.is_active = 1"; // base condition
        if ($forPos) {
            $whereSql .= " AND p.is_available = 1";
        }
        $params = [];
        
        if (!empty($words)) {
            $whereClauses = [];
            foreach ($words as $idx => $word) {
                $p_name  = ":kw_{$idx}_name";
                $p_label = ":kw_{$idx}_label";
                $p_brand = ":kw_{$idx}_brand";
                $p_cat   = ":kw_{$idx}_cat";
                $p_code  = ":kw_{$idx}_code";
                $p_scode = ":kw_{$idx}_scode";
                $p_bar   = ":kw_{$idx}_bar";
                $p_inv   = ":kw_{$idx}_inv";
                $p_sinv  = ":kw_{$idx}_sinv";
                $p_price = ":kw_{$idx}_price";
                
                $whereClauses[] = "(p.full_name LIKE $p_name OR p.short_label LIKE $p_label OR p.invoice_name LIKE $p_inv OR p.supplier_invoice_name LIKE $p_sinv OR p.code LIKE $p_code OR p.supplier_product_code LIKE $p_scode OR b.name LIKE $p_brand OR c.name LIKE $p_cat OR EXISTS (SELECT 1 FROM product_packagings pp WHERE pp.product_id = p.id AND (pp.barcode LIKE $p_bar OR CAST(ROUND(pp.sell_price_retail) AS CHAR) LIKE $p_price OR CAST(ROUND(pp.sell_price_wholesale) AS CHAR) LIKE $p_price OR CAST(ROUND(pp.buy_price) AS CHAR) LIKE $p_price)))";
                
                $like = "%{$word}%";
                $params[$p_name]  = $like;
                $params[$p_label] = $like;
                $params[$p_brand] = $like;
                $params[$p_cat]   = $like;
                $params[$p_code]  = $like;
                $params[$p_scode] = $like;
                $params[$p_bar]   = $like;
                $params[$p_inv]   = $like;
                $params[$p_sinv]  = $like;
                
                $cleanNum = preg_replace('/[^\d]/', '', $word);
                $params[$p_price] = !empty($cleanNum) ? "%{$cleanNum}%" : $like;
            }
            $whereSql .= ' AND ' . implode(' AND ', $whereClauses);
        }

        $orderSql = "ORDER BY COALESCE(p.updated_at, p.created_at) DESC, COALESCE(NULLIF(TRIM(p.short_label), ''), p.full_name) ASC";
        if (!empty($words)) {
            $params[':pref_kw1'] = $rawKeyword . '%';
            $params[':pref_kw2'] = $rawKeyword . '%';
            
            // Check if any word is numeric (price-like) for price-match boost
            $priceBoostSql = '';
            foreach ($words as $idx => $word) {
                $cleanNum = preg_replace('/[^\d]/', '', $word);
                if (!empty($cleanNum) && is_numeric($cleanNum)) {
                    $pExact = ":ord_price_{$idx}";
                    $params[$pExact] = (int)$cleanNum;
                    $priceBoostSql .= " + (CASE WHEN EXISTS (SELECT 1 FROM product_packagings pp2 WHERE pp2.product_id = p.id AND (ROUND(pp2.sell_price_retail) = $pExact OR ROUND(pp2.sell_price_wholesale) = $pExact OR ROUND(pp2.buy_price) = $pExact)) THEN 0 ELSE 10 END)";
                }
            }
            
            $orderSql = "ORDER BY (
                CASE 
                    WHEN p.short_label LIKE :pref_kw1 OR p.full_name LIKE :pref_kw2 THEN 1
                    ELSE 2
                END
            ){$priceBoostSql} ASC, COALESCE(NULLIF(TRIM(p.short_label), ''), p.full_name) ASC";
        }

        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $whereSql
            $orderSql
            LIMIT :lim
        ");
        
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByBarcode(string $barcode, bool $forPos = false)
    {
        // Trim and remove all spaces from the input barcode
        $barcode = str_replace(' ', '', trim($barcode));

        // Use positional params (?) because PDO does NOT allow the same named
        // parameter to appear more than once in a single query (SQLSTATE HY093).
        // COLLATE utf8mb4_unicode_ci prevents MySQL Error 1267 (Illegal mix of collations).
        $whereSql = "(REPLACE(pp.barcode, ' ', '') COLLATE utf8mb4_unicode_ci = ?
               OR p.code COLLATE utf8mb4_unicode_ci = ?
               OR REPLACE(pp.barcode, ' ', '') COLLATE utf8mb4_unicode_ci = CONCAT('0', ?)
               OR CONCAT('0', REPLACE(pp.barcode, ' ', '')) COLLATE utf8mb4_unicode_ci = ?
               OR REPLACE(pp.barcode, ' ', '') COLLATE utf8mb4_unicode_ci = CONCAT('00', ?)
               OR CONCAT('00', REPLACE(pp.barcode, ' ', '')) COLLATE utf8mb4_unicode_ci = ?)";

        $params = [$barcode, $barcode, $barcode, $barcode, $barcode, $barcode];

        if ($forPos) {
            $whereSql .= " AND p.is_available = 1";
        }

        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name,
                   pp.barcode, pp.level, pp.unit_id, u.name as unit_name,
                   pp.buy_price, pp.sell_price_retail, pp.sell_price_wholesale
            FROM product_packagings pp
            JOIN products p ON pp.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN units u ON pp.unit_id = u.id
            WHERE $whereSql
            ORDER BY pp.level ASC
            LIMIT 1
        ");
        $stmt->execute($params);
        return $stmt->fetch();
    }


    /**
     * Cek apakah tabel tier harga ada (tanpa DDL — aman saat transaksi DB aktif).
     */
    public function qtyPriceTableExists(): bool
    {
        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }
        try {
            $this->db->query('SELECT 1 FROM product_qty_prices LIMIT 1');
            $exists = true;
        } catch (PDOException $e) {
            $exists = false;
        }
        return $exists;
    }

    public function ensureQtyPriceSchema(): void
    {
        static $ready = false;
        if ($ready) {
            return;
        }
        if ($this->qtyPriceTableExists()) {
            $ready = true;
            return;
        }
        $this->db->exec("CREATE TABLE IF NOT EXISTS product_qty_prices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            packaging_id INT NOT NULL,
            min_qty DECIMAL(10,2) NOT NULL DEFAULT 1,
            unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
            sale_mode VARCHAR(10) NOT NULL DEFAULT 'both',
            label VARCHAR(100) DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (packaging_id) REFERENCES product_packagings(id) ON DELETE CASCADE,
            INDEX idx_pqp_packaging (packaging_id),
            INDEX idx_pqp_min_qty (packaging_id, min_qty)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $ready = true;
    }

    public function getQtyPricesByPackaging(int $packagingId): array
    {
        if (!$this->qtyPriceTableExists()) {
            return [];
        }
        $stmt = $this->db->prepare("
            SELECT id, packaging_id, min_qty, unit_price, sale_mode, label, sort_order
            FROM product_qty_prices
            WHERE packaging_id = :pid
            ORDER BY min_qty ASC, sort_order ASC, id ASC
        ");
        $stmt->execute([':pid' => $packagingId]);
        return $stmt->fetchAll();
    }

    public function getQtyPricesForProduct(int $productId): array
    {
        if (!$this->qtyPriceTableExists()) {
            return [];
        }
        $stmt = $this->db->prepare("
            SELECT pqp.*
            FROM product_qty_prices pqp
            INNER JOIN product_packagings pp ON pp.id = pqp.packaging_id
            WHERE pp.product_id = :pid
            ORDER BY pp.level ASC, pqp.min_qty ASC, pqp.sort_order ASC
        ");
        $stmt->execute([':pid' => $productId]);
        return $stmt->fetchAll();
    }

    public function attachQtyPricesToPackagings(array $packagings): array
    {
        if (empty($packagings)) {
            return $packagings;
        }
        if (!$this->qtyPriceTableExists()) {
            foreach ($packagings as $i => $pkg) {
                $packagings[$i]['qty_prices'] = [];
            }
            return $packagings;
        }
        $byPkg = [];
        foreach ($packagings as $pkg) {
            $byPkg[(int)$pkg['id']] = $pkg;
            $byPkg[(int)$pkg['id']]['qty_prices'] = [];
        }
        $ids = array_keys($byPkg);
        $in = implode(',', array_map('intval', $ids));
        $rows = $this->db->query("
            SELECT id, packaging_id, min_qty, unit_price, sale_mode, label, sort_order
            FROM product_qty_prices
            WHERE packaging_id IN ($in)
            ORDER BY min_qty ASC, sort_order ASC
        ")->fetchAll();
        foreach ($rows as $row) {
            $pid = (int)$row['packaging_id'];
            if (isset($byPkg[$pid])) {
                $byPkg[$pid]['qty_prices'][] = $row;
            }
        }
        return array_values($byPkg);
    }

    public function saveQtyPricesForPackaging(int $packagingId, array $tiers): void
    {
        $this->ensureQtyPriceSchema();
        $packagingId = (int)$packagingId;
        if ($packagingId <= 0) {
            throw new Exception('Kemasan tidak valid');
        }

        $stmt = $this->db->prepare('DELETE FROM product_qty_prices WHERE packaging_id = :pid');
        $stmt->execute([':pid' => $packagingId]);

        if (empty($tiers)) {
            return;
        }

        $insert = $this->db->prepare("
            INSERT INTO product_qty_prices (packaging_id, min_qty, unit_price, sale_mode, label, sort_order)
            VALUES (:pid, :min_qty, :unit_price, :sale_mode, :label, :sort_order)
        ");

        $order = 0;
        foreach ($tiers as $tier) {
            $minQty = (float)($tier['min_qty'] ?? 0);
            $unitPrice = (float)($tier['unit_price'] ?? 0);
            if ($minQty <= 0 || $unitPrice < 0) {
                continue;
            }
            $mode = $tier['sale_mode'] ?? 'both';
            if (!in_array($mode, ['both', 'retail', 'wholesale'], true)) {
                $mode = 'both';
            }
            $label = isset($tier['label']) ? trim((string)$tier['label']) : null;
            $insert->execute([
                ':pid' => $packagingId,
                ':min_qty' => $minQty,
                ':unit_price' => $unitPrice,
                ':sale_mode' => $mode,
                ':label' => $label !== '' ? $label : null,
                ':sort_order' => $order++,
            ]);
        }
    }

    public function getPackagings(int|string $productId)
    {
        $stmt = $this->db->prepare("
            SELECT pp.*, u.name as unit_name, u.abbreviation as unit_abbr
            FROM product_packagings pp
            JOIN units u ON pp.unit_id = u.id
            WHERE pp.product_id = :pid 
            ORDER BY pp.level ASC
        ");
        $stmt->execute([':pid' => $productId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            if (isset($row['contained_qty'])) $row['contained_qty'] = (float)$row['contained_qty'];
            if (isset($row['base_qty'])) $row['base_qty'] = (float)$row['base_qty'];
        }
        unset($row);
        return $this->attachQtyPricesToPackagings($rows);
    }

    public function getProductsWithPrices($page = 1, $perPage = 20, $search = '', $categoryId = null, $minPrice = null, $maxPrice = null)
    {
        try {
            if (!$this->db) throw new \Exception("No DB connection");
            $where = "WHERE p.is_active = 1";
            $params = [];

            if (!empty(trim($search))) {
                $words = array_filter(explode(' ', trim($search)), 'strlen');
                foreach ($words as $idx => $word) {
                    $p_name  = ":s_{$idx}_name";
                    $p_brand = ":s_{$idx}_brand";
                    $p_cat   = ":s_{$idx}_cat";
                    $p_bar   = ":s_{$idx}_bar";
                    $p_label = ":s_{$idx}_label";
                    $p_inv   = ":s_{$idx}_inv";
                    $p_code  = ":s_{$idx}_code";
                    $p_scode = ":s_{$idx}_scode";
                    $p_sinv  = ":s_{$idx}_sinv";
                    $p_price = ":s_{$idx}_price";
                    
                    $where .= " AND (p.full_name LIKE $p_name OR p.short_label LIKE $p_label OR p.invoice_name LIKE $p_inv OR p.supplier_invoice_name LIKE $p_sinv OR p.code LIKE $p_code OR p.supplier_product_code LIKE $p_scode OR b.name LIKE $p_brand OR c.name LIKE $p_cat OR EXISTS (SELECT 1 FROM product_packagings pp WHERE pp.product_id = p.id AND (pp.barcode LIKE $p_bar OR CAST(ROUND(pp.sell_price_retail) AS CHAR) LIKE $p_price OR CAST(ROUND(pp.sell_price_wholesale) AS CHAR) LIKE $p_price OR CAST(ROUND(pp.buy_price) AS CHAR) LIKE $p_price)))";
                    
                    $like = "%{$word}%";
                    $params[$p_name]  = $like;
                    $params[$p_label] = $like;
                    $params[$p_inv]   = $like;
                    $params[$p_sinv]  = $like;
                    $params[$p_code]  = $like;
                    $params[$p_scode] = $like;
                    $params[$p_brand] = $like;
                    $params[$p_cat]   = $like;
                    $params[$p_bar]   = $like;
                    
                    $cleanNum = preg_replace('/[^\d]/', '', $word);
                    $params[$p_price] = !empty($cleanNum) ? "%{$cleanNum}%" : $like;
                }
            }
            if ($categoryId) {
                $where .= " AND p.category_id = :cat_id";
                $params[':cat_id'] = $categoryId;
            }
            // Filter by price range (smallest packaging retail price)
            if ($minPrice !== null && $minPrice >= 0) {
                $where .= " AND (SELECT sell_price_retail FROM product_packagings WHERE product_id = p.id ORDER BY level ASC LIMIT 1) >= :min_price";
                $params[':min_price'] = (float)$minPrice;
            }
            if ($maxPrice !== null && $maxPrice >= 0) {
                $where .= " AND (SELECT sell_price_retail FROM product_packagings WHERE product_id = p.id ORDER BY level ASC LIMIT 1) <= :max_price";
                $params[':max_price'] = (float)$maxPrice;
            }

            $countStmt = $this->db->prepare("
                SELECT COUNT(*) as total FROM products p 
                LEFT JOIN brands b ON p.brand_id = b.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                {$where}
            ");
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            $offset = ($page - 1) * $perPage;
            $params[':limit'] = $perPage;
            $params[':offset'] = $offset;

            $orderSql = "ORDER BY COALESCE(p.updated_at, p.created_at) DESC, COALESCE(NULLIF(TRIM(p.short_label), ''), p.full_name) ASC";
            if (!empty(trim($search))) {
                $orderSql = "ORDER BY COALESCE(NULLIF(TRIM(p.short_label), ''), p.full_name) ASC";
            }

            // Fetch product with smallest level packaging info
            $stmt = $this->db->prepare("
                SELECT p.*, b.name as brand_name, c.name as category_name,
                       (SELECT current_qty_base FROM stock WHERE product_id = p.id LIMIT 1) as current_qty_base
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN categories c ON p.category_id = c.id
                {$where}
                {$orderSql}
                LIMIT :limit OFFSET :offset
            ");
            
            foreach ($params as $key => $val) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $val);
                }
            }
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $this->attachPackagingsForProductList($rows);

            return [
                'data' => $rows,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
            ];
        } catch (\Throwable $e) {
            error_log("[ProductModel getProductsWithPrices error] " . $e->getMessage());
            return [
                'data' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => 1,
            ];
        }
    }

    /**
     * Lampirkan semua level kemasan + harga untuk daftar produk (1 query).
     */
    public function attachPackagingsForProductList(array &$products): void
    {
        if (empty($products)) {
            return;
        }
        $ids = array_map('intval', array_column($products, 'id'));
        $in = implode(',', $ids);
        $stmt = $this->db->query("
            SELECT pp.id, pp.product_id, pp.level, pp.sell_price_retail, pp.sell_price_wholesale,
                   pp.ppn_pct, pp.discount_mode, pp.discount_value, pp.buy_price, pp.base_qty, pp.contained_qty, pp.barcode,
                   u.name AS unit_name, u.abbreviation AS unit_abbr
            FROM product_packagings pp
            JOIN units u ON u.id = pp.unit_id
            WHERE pp.product_id IN ($in)
            ORDER BY pp.product_id ASC, pp.level ASC
        ");
        $allPackagings = $stmt->fetchAll();
        foreach ($allPackagings as &$row) {
            if (isset($row['contained_qty'])) $row['contained_qty'] = (float)$row['contained_qty'];
            if (isset($row['base_qty'])) $row['base_qty'] = (float)$row['base_qty'];
        }
        unset($row);
        $allPackagings = $this->attachQtyPricesToPackagings($allPackagings);
        
        $byProduct = [];
        foreach ($allPackagings as $row) {
            $pid = (int)$row['product_id'];
            $byProduct[$pid][] = $row;
        }
        foreach ($products as &$p) {
            $pid = (int)$p['id'];
            $p['packagings'] = $byProduct[$pid] ?? [];
            
            if (!empty($p['packagings'])) {
                if (!isset($p['price_small_retail'])) {
                    $p['price_small_retail'] = $p['packagings'][0]['sell_price_retail'];
                    $p['price_small_wholesale'] = $p['packagings'][0]['sell_price_wholesale'];
                    $p['buy_price_small'] = $p['packagings'][0]['buy_price'];
                    $p['unit_small_name'] = $p['packagings'][0]['unit_name'];
                }
            }
            
            if (empty($p['packagings']) && !empty($p['price_small_retail'])) {
                $p['packagings'] = [[
                    'level' => 1,
                    'unit_name' => $p['unit_small_name'] ?? 'pcs',
                    'base_qty' => 1,
                    'buy_price' => $p['buy_price_small'] ?? 0,
                    'sell_price_retail' => $p['price_small_retail'],
                    'sell_price_wholesale' => $p['price_small_wholesale'] ?? 0,
                ]];
            }
        }
        unset($p);
    }

    public function createWithDetails(array $productData, array $packagings = [])
    {
        try {
            $this->beginTransaction();

            $productId = $this->create($productData);

            if (!empty($packagings)) {
                $pkStmt = $this->db->prepare("
                    INSERT INTO product_packagings 
                    (product_id, level, unit_id, contained_qty, base_qty, barcode, 
                     buy_price, sell_price_retail, margin_retail, sell_price_wholesale, margin_wholesale) 
                    VALUES (:pid, :lvl, :uid, :cqty, :bqty, :bc, :buy, :retail, :mr, :wholesale, :mw)
                ");
                
                $currentBaseQty = 1;

                foreach ($packagings as $pk) {
                    // Kalkulasi base_qty
                    // Level 1: cqty = 1, base_qty = 1
                    // Level 2: cqty = 10, base_qty = 1 * 10 = 10
                    // Level 3: cqty = 4, base_qty = 10 * 4 = 40
                    $cqty = isset($pk['contained_qty']) && $pk['contained_qty'] > 0 ? $pk['contained_qty'] : 1;
                    if ($pk['level'] == 1) {
                        $currentBaseQty = 1;
                        $cqty = 1;
                    } else {
                        $currentBaseQty = $currentBaseQty * $cqty;
                    }

                    $pkStmt->execute([
                        ':pid'      => $productId,
                        ':lvl'      => $pk['level'],
                        ':uid'      => $pk['unit_id'],
                        ':cqty'     => $cqty,
                        ':bqty'     => $currentBaseQty,
                        ':bc'       => $pk['barcode'] ?: null,
                        ':buy'      => $pk['buy_price'] ?? 0,
                        ':retail'   => $pk['sell_price_retail'] ?? 0,
                        ':mr'       => $pk['margin_retail'] ?? 0,
                        ':wholesale'=> $pk['sell_price_wholesale'] ?? 0,
                        ':mw'       => $pk['margin_wholesale'] ?? 0,
                    ]);
                    
                    $pkgId = $this->db->lastInsertId();
                    if (!empty($pk['qty_prices'])) {
                        $this->saveQtyPricesForPackaging((int)$pkgId, $pk['qty_prices']);
                    }
                }
            }

            // Create stock entry
            $this->db->prepare("INSERT INTO stock (product_id, current_qty_base) VALUES (:pid, 0)")
                ->execute([':pid' => $productId]);

            $this->commit();
            return $productId;

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Products with same brand + product_type (multivariant siblings)
     */
    public function findVariantSiblings(int|string $productId)
    {
        $product = $this->find($productId);
        if (!$product || empty($product['brand_id']) || empty($product['product_type'])) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT p.id, p.variant, p.short_label, p.full_name, p.weight_value, p.weight_unit,
                   b.name AS brand_name
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.brand_id = :bid
              AND p.product_type = :ptype
              AND p.is_active = 1
            ORDER BY p.variant ASC, p.full_name ASC
        ");
        $stmt->execute([
            ':bid' => $product['brand_id'],
            ':ptype' => $product['product_type'],
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Update print label for one product
     */
    public function updatePrintLabel(int|string $productId, string $shortLabel, ?string $invoiceName = null)
    {
        $shortLabel = trim($shortLabel);
        if ($shortLabel === '') {
            throw new Exception('Label cetak tidak boleh kosong');
        }
        if (strlen($shortLabel) > 35) {
            $shortLabel = substr($shortLabel, 0, 35);
        }
        if ($invoiceName === null || trim($invoiceName) === '') {
            $invoiceName = $shortLabel;
        }

        return $this->update($productId, [
            'short_label' => $shortLabel,
            'invoice_name' => trim($invoiceName),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Apply label base to all variant siblings (brand + product_type)
     */
    public function distributePrintLabel(int|string $productId, string $labelBase)
    {
        $product = $this->find($productId);
        if (!$product) {
            throw new Exception('Produk tidak ditemukan');
        }

        $labelBase = trim($labelBase);
        if ($labelBase === '') {
            throw new Exception('Label dasar tidak boleh kosong');
        }

        $siblings = $this->findVariantSiblings($productId);
        if (count($siblings) <= 1) {
            throw new Exception('Tidak ada produk varian lain dengan jenis yang sama');
        }

        $updated = 0;
        foreach ($siblings as $sib) {
            $label = $labelBase;
            if (!empty($sib['variant'])) {
                $label = trim($labelBase . ' ' . trim($sib['variant']));
            }
            if (!empty($sib['weight_value']) && !empty($sib['weight_unit'])) {
                $label = trim($label . ' ' . $sib['weight_value'] . $sib['weight_unit']);
            }
            if (strlen($label) > 35) {
                $label = substr($label, 0, 32) . '...';
            }

            $this->update($sib['id'], [
                'short_label' => $label,
                'invoice_name' => $label,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $updated++;
        }

        return ['updated' => $updated, 'siblings' => count($siblings)];
    }

    public function allWithDetails()
    {
        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.full_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getProductNames()
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.full_name as name, p.short_label, b.name as brand_name
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.is_active = 1
            ORDER BY p.full_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStats()
    {
        $stats = [];
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
        $stats['total_products'] = $stmt->fetch()['total'];
        $stmt = $this->db->query("SELECT COUNT(DISTINCT brand_id) as total FROM products WHERE brand_id IS NOT NULL");
        $stats['total_brands'] = $stmt->fetch()['total'];
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM categories");
        $stats['total_categories'] = $stmt->fetch()['total'];
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM suppliers WHERE is_active = 1");
        $stats['total_suppliers'] = $stmt->fetch()['total'];
        
        // Count products with low stock (current_qty_base <= 5)
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM products p 
            LEFT JOIN stock s ON p.id = s.product_id 
            WHERE p.is_active = 1 AND COALESCE(s.current_qty_base, 0) <= 5
        ");
        $stats['low_stock_count'] = $stmt->fetch()['total'];
        
        return $stats;
    }
}
