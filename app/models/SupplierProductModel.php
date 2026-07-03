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
    public function getProductsBySupplier($supplierId, $salesRepId = null)
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
     * Search products by supplier with keyword filter
     */
    public function searchBySupplier($supplierId, $keyword, $salesRepId = null, $limit = 20)
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
                
                $whereClauses[] = "(p.full_name LIKE $p_name OR p.short_label LIKE $p_label OR b.name LIKE $p_brand OR p.code LIKE $p_code OR p.supplier_product_code LIKE $p_scode OR p.invoice_name LIKE $p_inv OR p.supplier_invoice_name LIKE $p_sinv OR EXISTS (SELECT 1 FROM product_packagings pp WHERE pp.product_id = p.id AND pp.barcode LIKE $p_bar))";
                
                $like = "%{$word}%";
                $params[$p_name]  = $like;
                $params[$p_label] = $like;
                $params[$p_brand] = $like;
                $params[$p_code]  = $like;
                $params[$p_scode] = $like;
                $params[$p_bar]   = $like;
                $params[$p_inv]   = $like;
                $params[$p_sinv]  = $like;
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
    public function trackSupplierProduct($supplierId, $productId, $salesRepId = null, $buyPrice = null)
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
    public function removeSupplierProduct($supplierId, $productId)
    {
        $stmt = $this->db->prepare("DELETE FROM supplier_products WHERE supplier_id = :sid AND product_id = :pid");
        return $stmt->execute([':sid' => $supplierId, ':pid' => $productId]);
    }
}
