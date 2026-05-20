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
            SELECT p.id, p.full_name, p.short_label, p.product_type, p.variant,
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
        $params = [
            ':sid' => $supplierId,
            ':kw1' => "%{$keyword}%",
            ':kw2' => "%{$keyword}%",
            ':kw3' => "%{$keyword}%",
        ];

        $salesFilter = "";
        if ($salesRepId) {
            $salesFilter = "AND sp.sales_rep_id = :srid";
            $params[':srid'] = $salesRepId;
        }

        $stmt = $this->db->prepare("
            SELECT p.id, p.full_name, p.short_label, p.product_type, p.variant,
                   b.name as brand_name, c.name as category_name,
                   sp.last_buy_price, sp.purchase_count,
                   1 as is_supplier_product
            FROM supplier_products sp
            JOIN products p ON sp.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE sp.supplier_id = :sid {$salesFilter}
              AND (p.full_name LIKE :kw1 OR p.short_label LIKE :kw2 OR b.name LIKE :kw3)
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
