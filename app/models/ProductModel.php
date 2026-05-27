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

    public function findWithDetails($id)
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

    public function searchProducts($keyword, $limit = 20)
    {
        $words = array_filter(explode(' ', trim($keyword)), 'strlen');
        $whereSql = "p.is_active = 1"; // base condition
        $params = [];
        
        if (!empty($words)) {
            $whereClauses = [];
            foreach ($words as $idx => $word) {
                $paramKey = ":kw_{$idx}";
                $whereClauses[] = "(p.full_name LIKE $paramKey OR p.short_label LIKE $paramKey OR b.name LIKE $paramKey OR p.code LIKE $paramKey OR EXISTS (SELECT 1 FROM product_packagings pp WHERE pp.product_id = p.id AND pp.barcode LIKE $paramKey))";
                $params[$paramKey] = "%{$word}%";
            }
            $whereSql .= ' AND ' . implode(' AND ', $whereClauses);
        }

        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $whereSql
            ORDER BY p.full_name ASC
            LIMIT :lim
        ");
        
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByBarcode($barcode)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name,
                   pp.barcode, pp.level, pp.unit_id, u.name as unit_name,
                   pp.buy_price, pp.sell_price_retail, pp.sell_price_wholesale
            FROM product_packagings pp
            JOIN products p ON pp.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN units u ON pp.unit_id = u.id
            WHERE pp.barcode = :barcode
            LIMIT 1
        ");
        $stmt->execute([':barcode' => $barcode]);
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

    public function getPackagings($productId)
    {
        $stmt = $this->db->prepare("
            SELECT pp.*, u.name as unit_name
            FROM product_packagings pp
            JOIN units u ON pp.unit_id = u.id
            WHERE pp.product_id = :pid 
            ORDER BY pp.level ASC
        ");
        $stmt->execute([':pid' => $productId]);
        $rows = $stmt->fetchAll();
        return $this->attachQtyPricesToPackagings($rows);
    }

    public function getProductsWithPrices($page = 1, $perPage = 20, $search = '', $categoryId = null)
    {
        $where = "WHERE p.is_active = 1";
        $params = [];

        if (!empty(trim($search))) {
            $words = array_filter(explode(' ', trim($search)), 'strlen');
            foreach ($words as $idx => $word) {
                $paramKey = ":search_{$idx}";
                $where .= " AND (p.full_name LIKE $paramKey OR b.name LIKE $paramKey OR EXISTS (SELECT 1 FROM product_packagings pp WHERE pp.product_id = p.id AND pp.barcode LIKE $paramKey))";
                $params[$paramKey] = "%{$word}%";
            }
        }
        if ($categoryId) {
            $where .= " AND p.category_id = :cat_id";
            $params[':cat_id'] = $categoryId;
        }

        $countStmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id {$where}
        ");
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];

        $offset = ($page - 1) * $perPage;
        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        // Fetch product with smallest level packaging info
        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name,
                   pp1.sell_price_retail as price_small_retail,
                   pp1.sell_price_wholesale as price_small_wholesale,
                   pp1.buy_price as buy_price_small,
                   u1.name as unit_small_name,
                   s.current_qty_base
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_packagings pp1 ON pp1.product_id = p.id AND pp1.level = 1
            LEFT JOIN units u1 ON pp1.unit_id = u1.id
            LEFT JOIN stock s ON s.product_id = p.id
            {$where}
            ORDER BY p.updated_at DESC, p.full_name ASC
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
                   pp.ppn_pct, pp.discount_mode, pp.discount_value, pp.buy_price, pp.base_qty,
                   u.name AS unit_name
            FROM product_packagings pp
            JOIN units u ON u.id = pp.unit_id
            WHERE pp.product_id IN ($in)
            ORDER BY pp.product_id ASC, pp.level ASC
        ");
        $allPackagings = $stmt->fetchAll();
        $allPackagings = $this->attachQtyPricesToPackagings($allPackagings);
        
        $byProduct = [];
        foreach ($allPackagings as $row) {
            $pid = (int)$row['product_id'];
            $byProduct[$pid][] = $row;
        }
        foreach ($products as &$p) {
            $pid = (int)$p['id'];
            $p['packagings'] = $byProduct[$pid] ?? [];
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

    public function createWithDetails($productData, $packagings = [])
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
    public function findVariantSiblings($productId)
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
    public function updatePrintLabel($productId, $shortLabel, $invoiceName = null)
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
    public function distributePrintLabel($productId, $labelBase)
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
