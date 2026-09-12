<?php
/**
 * SupplierProductModel - Tracking produk per supplier
 * Auto-populated saat purchase, digunakan untuk filter produk di halaman input barang masuk
 */
class SupplierProductModel extends Model
{
    protected $table = 'supplier_products';

    /**
     * Get products associated with a supplier (from purchase history)
     */
    public function getProductsBySupplier(int $supplierId, ?int $salesRepId = null)
    {
        $where = "WHERE sp.supplier_id = :sid";
        $params = [':sid' => $supplierId];

        if ($salesRepId) {
            $where .= " AND sp.sales_rep_id = :srid";
            $params[':srid'] = $salesRepId;
        }

        $stmt = $this->db->prepare("
            SELECT p.id, p.full_name, p.short_label, p.product_type, p.variant, p.photo,
                   b.name as brand_name, c.name as category_name,
                   sp.last_buy_price, sp.last_purchase_date, sp.purchase_count
            FROM supplier_products sp
            JOIN products p ON sp.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            {$where}
            ORDER BY sp.purchase_count DESC, p.full_name ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get distinct suppliers that supply a specific product
     */
    public function getProductSuppliers(int $productId)
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT s.id, s.name
            FROM supplier_products sp
            JOIN suppliers s ON sp.supplier_id = s.id
            WHERE sp.product_id = :pid
            ORDER BY s.name ASC
        ");
        $stmt->execute([':pid' => $productId]);
        return $stmt->fetchAll();
    }

    /**
     * Search products by supplier with keyword filter
     */
    public function searchBySupplier(int $supplierId, string $keyword, ?int $salesRepId = null, int $limit = 20)
    {
        $words = array_filter(explode(' ', trim($keyword)), 'strlen');
        $params = [':sid' => $supplierId];
        $whereSql = "sp.supplier_id = :sid";

        if ($salesRepId) {
            $whereSql .= " AND sp.sales_rep_id = :srid";
            $params[':srid'] = $salesRepId;
        }

        if (!empty($words)) {
            $whereClauses = [];
            foreach ($words as $idx => $word) {
                $p_name  = ":kw_{$idx}_name";
                $p_label = ":kw_{$idx}_label";
                $p_brand = ":kw_{$idx}_brand";
                $p_code  = ":kw_{$idx}_code";
                $p_scode = ":kw_{$idx}_scode";
                $p_bar   = ":kw_{$idx}_bar";
                $p_inv   = ":kw_{$idx}_inv";
                $p_sinv  = ":kw_{$idx}_sinv";
                $p_price_r = ":kw_{$idx}_price_r";
                $p_price_w = ":kw_{$idx}_price_w";
                $p_price_b = ":kw_{$idx}_price_b";
                
                $whereClauses[] = "(p.full_name LIKE $p_name OR p.short_label LIKE $p_label OR b.name LIKE $p_brand OR p.code LIKE $p_code OR p.supplier_product_code LIKE $p_scode OR p.invoice_name LIKE $p_inv OR p.supplier_invoice_name LIKE $p_sinv OR EXISTS (SELECT 1 FROM product_packagings pp WHERE pp.product_id = p.id AND (pp.barcode LIKE $p_bar OR CAST(ROUND(pp.sell_price_retail) AS CHAR) LIKE $p_price_r OR CAST(ROUND(pp.sell_price_wholesale) AS CHAR) LIKE $p_price_w OR CAST(ROUND(pp.buy_price) AS CHAR) LIKE $p_price_b)))";
                
                $like = "%{$word}%";
                $params[$p_name]  = $like;
                $params[$p_label] = $like;
                $params[$p_brand] = $like;
                $params[$p_code]  = $like;
                $params[$p_scode] = $like;
                $params[$p_bar]   = $like;
                $params[$p_inv]   = $like;
                $params[$p_sinv]  = $like;
                
                $cleanNum = preg_replace('/[^\d]/', '', $word);
                $priceVal = !empty($cleanNum) ? "%{$cleanNum}%" : $like;
                $params[$p_price_r] = $priceVal;
                $params[$p_price_w] = $priceVal;
                $params[$p_price_b] = $priceVal;
            }
            $whereSql .= ' AND ' . implode(' AND ', $whereClauses);
        }

        $stmt = $this->db->prepare("
            SELECT p.id, p.full_name, p.short_label, p.product_type, p.variant, p.photo,
                   b.name as brand_name, c.name as category_name,
                   sp.last_buy_price, sp.purchase_count,
                   1 as is_supplier_product
            FROM supplier_products sp
            JOIN products p ON sp.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereSql}
            ORDER BY sp.purchase_count DESC, p.full_name ASC
            LIMIT :lim
        ");
        $params[':lim'] = $limit;

        foreach ($params as $key => $val) {
            if ($key === ':lim') {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Upsert supplier-product relationship
     * Called automatically when a purchase is saved
     */
    public function trackSupplierProduct(int $supplierId, int $productId, ?int $salesRepId = null, ?float $buyPrice = null)
    {
        // Check if exists
        $stmt = $this->db->prepare("
            SELECT id, purchase_count FROM supplier_products 
            WHERE supplier_id = :sid AND product_id = :pid
        ");
        $stmt->execute([':sid' => $supplierId, ':pid' => $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update
            $update = $this->db->prepare("
                UPDATE supplier_products 
                SET purchase_count = purchase_count + 1,
                    last_purchase_date = CURRENT_DATE,
                    last_buy_price = COALESCE(:price, last_buy_price),
                    sales_rep_id = COALESCE(:srid, sales_rep_id),
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $update->execute([
                ':price' => $buyPrice,
                ':srid' => $salesRepId,
                ':id' => $existing['id']
            ]);
            return $existing['id'];
        } else {
            // Insert
            $insert = $this->db->prepare("
                INSERT INTO supplier_products (supplier_id, product_id, sales_rep_id, last_purchase_date, last_buy_price)
                VALUES (:sid, :pid, :srid, CURRENT_DATE, :price)
            ");
            $insert->execute([
                ':sid' => $supplierId,
                ':pid' => $productId,
                ':srid' => $salesRepId,
                ':price' => $buyPrice
            ]);
            return $this->db->lastInsertId();
        }
    }

    /**
     * Remove product from supplier
     */
    public function removeSupplierProduct(int $supplierId, int $productId)
    {
        $stmt = $this->db->prepare("DELETE FROM supplier_products WHERE supplier_id = :sid AND product_id = :pid");
        return $stmt->execute([':sid' => $supplierId, ':pid' => $productId]);
    }

    /**
     * Get suppliers that sell this product with packaging-based price estimation.
     * Accurately checks purchase history per packaging and extrapolates across same packagings.
     *
     * @param int $productId
     * @return array
     */
    public function getProductSupplierPricing(int $productId): array
    {
        // 1. Get packagings for this product
        $stmtPkg = $this->db->prepare("
            SELECT pp.id, pp.level, pp.base_qty, pp.buy_price, u.name as unit_name
            FROM product_packagings pp
            LEFT JOIN units u ON pp.unit_id = u.id
            WHERE pp.product_id = :pid
            ORDER BY pp.level ASC
        ");
        $stmtPkg->execute([':pid' => $productId]);
        $packagings = $stmtPkg->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 2. Query all distinct suppliers associated with this product
        $stmtSup = $this->db->prepare("
            SELECT 
                s.id as supplier_id,
                s.name as supplier_name,
                (SELECT sr.phone FROM sales_reps sr WHERE sr.supplier_id = s.id AND sr.phone IS NOT NULL AND sr.phone != '' LIMIT 1) as supplier_phone,
                COALESCE(sp.last_purchase_date, MAX(pu.purchase_date)) as last_purchase_date,
                COALESCE(sp.purchase_count, COUNT(DISTINCT pu.id)) as purchase_count,
                COALESCE(sp.last_buy_price, 0) as sp_last_buy_price
            FROM suppliers s
            LEFT JOIN supplier_products sp ON sp.supplier_id = s.id AND sp.product_id = :pid1
            LEFT JOIN purchases pu ON pu.supplier_id = s.id
            LEFT JOIN purchase_items pi ON pi.purchase_id = pu.id AND pi.product_id = :pid2
            WHERE sp.id IS NOT NULL OR pi.id IS NOT NULL
            GROUP BY s.id, s.name, sp.last_purchase_date, sp.purchase_count, sp.last_buy_price
            ORDER BY last_purchase_date DESC, s.name ASC
        ");
        $stmtSup->execute([':pid1' => $productId, ':pid2' => $productId]);
        $suppliers = $stmtSup->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (empty($suppliers)) {
            return [
                'product_id' => $productId,
                'packagings' => $packagings,
                'suppliers' => []
            ];
        }

        // 3. For each supplier, get latest purchase details per packaging
        $stmtHist = $this->db->prepare("
            SELECT 
                pi.packaging_id,
                pp.level,
                COALESCE(pp.base_qty, 1) as base_qty,
                COALESCE(u.name, 'pcs') as unit_name,
                COALESCE(NULLIF(pi.nett_price, 0), pi.buy_price) as price,
                pi.buy_price as gross_price,
                pu.purchase_date,
                pu.id as purchase_id
            FROM purchase_items pi
            JOIN purchases pu ON pi.purchase_id = pu.id
            LEFT JOIN product_packagings pp ON pi.packaging_id = pp.id
            LEFT JOIN units u ON pp.unit_id = u.id
            WHERE pi.product_id = :pid AND pu.supplier_id = :sid
            ORDER BY pu.purchase_date DESC, pu.id DESC
        ");

        $supplierList = [];
        foreach ($suppliers as $sup) {
            $sid = (int)$sup['supplier_id'];
            $stmtHist->execute([':pid' => $productId, ':sid' => $sid]);
            $historyRows = $stmtHist->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $pkgPrices = [];
            $latestRecord = null;
            foreach ($historyRows as $row) {
                if (!$latestRecord) {
                    $latestRecord = $row;
                }
                $pkgId = (int)$row['packaging_id'];
                if (!isset($pkgPrices[$pkgId])) {
                    $pkgPrices[$pkgId] = [
                        'packaging_id' => $pkgId,
                        'base_qty' => (float)$row['base_qty'],
                        'unit_name' => $row['unit_name'],
                        'price' => (float)$row['price'],
                        'gross_price' => (float)$row['gross_price'],
                        'purchase_date' => $row['purchase_date']
                    ];
                }
            }

            // Determine unit base price (price per 1 base_qty)
            $baseUnitPrice = 0;
            if ($latestRecord && (float)$latestRecord['base_qty'] > 0) {
                $baseUnitPrice = (float)$latestRecord['price'] / (float)$latestRecord['base_qty'];
            } elseif ((float)$sup['sp_last_buy_price'] > 0) {
                $baseUnitPrice = (float)$sup['sp_last_buy_price'];
            }

            // Calculate estimates for all packagings
            $estimates = [];
            foreach ($packagings as $pkg) {
                $pkgId = (int)$pkg['id'];
                $targetBaseQty = (float)($pkg['base_qty'] ?? 1);
                
                if (isset($pkgPrices[$pkgId]) && $pkgPrices[$pkgId]['price'] > 0) {
                    $estPrice = $pkgPrices[$pkgId]['price'];
                    $isExact = true;
                    $recordDate = $pkgPrices[$pkgId]['purchase_date'];
                } elseif ($baseUnitPrice > 0) {
                    $estPrice = round($baseUnitPrice * $targetBaseQty, 2);
                    $isExact = false;
                    $recordDate = $sup['last_purchase_date'];
                } else {
                    $estPrice = 0;
                    $isExact = false;
                    $recordDate = $sup['last_purchase_date'];
                }

                $estimates[$pkgId] = [
                    'packaging_id' => $pkgId,
                    'unit_name' => $pkg['unit_name'] ?? 'pcs',
                    'base_qty' => $targetBaseQty,
                    'estimated_price' => $estPrice,
                    'is_exact' => $isExact,
                    'date' => $recordDate
                ];
            }

            $supplierList[] = [
                'supplier_id' => $sid,
                'supplier_name' => $sup['supplier_name'],
                'supplier_phone' => $sup['supplier_phone'],
                'last_purchase_date' => $sup['last_purchase_date'],
                'purchase_count' => (int)$sup['purchase_count'],
                'base_unit_price' => $baseUnitPrice,
                'estimates' => $estimates
            ];
        }

        return [
            'product_id' => $productId,
            'packagings' => $packagings,
            'suppliers' => $supplierList
        ];
    }

    /**
     * Batch fetch supplier pricing for multiple products
     *
     * @param array $productIds
     * @return array [product_id => pricingData]
     */
    public function getBatchProductSupplierPricing(array $productIds): array
    {
        $cleanIds = array_filter(array_map('intval', $productIds), fn($id) => $id > 0);
        if (empty($cleanIds)) return [];

        $results = [];
        foreach ($cleanIds as $pid) {
            $results[$pid] = $this->getProductSupplierPricing($pid);
        }
        return $results;
    }
}
