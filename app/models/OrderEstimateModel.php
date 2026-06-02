<?php
/**
 * OrderEstimateModel - Menyimpan draft Hitung Orderan
 */
class OrderEstimateModel extends Model
{
    protected $table = 'order_estimates';

    public function __construct()
    {
        parent::__construct();
    }

    public function getAll($limit = 50)
    {
        $stmt = $this->db->prepare("
            SELECT e.*, s.name as supplier_name,
                   (SELECT SUM(quantity) FROM order_estimate_items WHERE estimate_id = e.id) as total_items
            FROM order_estimates e
            LEFT JOIN suppliers s ON e.supplier_id = s.id
            ORDER BY e.updated_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDetails($id)
    {
        $stmt = $this->db->prepare("
            SELECT e.*, s.name as supplier_name
            FROM order_estimates e
            LEFT JOIN suppliers s ON e.supplier_id = s.id
            WHERE e.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $estimate = $stmt->fetch();
        if (!$estimate) return null;

        $stmtItems = $this->db->prepare("
            SELECT i.* 
            FROM order_estimate_items i
            WHERE i.estimate_id = :id
            ORDER BY i.id ASC
        ");
        $stmtItems->execute([':id' => $id]);
        $estimate['items'] = $stmtItems->fetchAll();

        return $estimate;
    }

    public function createWithItems($data, $items)
    {
        try {
            $this->beginTransaction();

            $estimateId = $this->create([
                'title' => $data['title'],
                'supplier_id' => !empty($data['supplier_id']) ? $data['supplier_id'] : null,
                'total_amount' => $data['total_amount'] ?? 0
            ]);

            $stmt = $this->db->prepare("
                INSERT INTO order_estimate_items 
                (estimate_id, product_id, packaging_id, product_name, unit_name, quantity, buy_price, total_price)
                VALUES (:est, :pid, :pkg, :name, :unit, :qty, :price, :total)
            ");

            foreach ($items as $item) {
                $stmt->execute([
                    ':est' => $estimateId,
                    ':pid' => !empty($item['product_id']) ? $item['product_id'] : null,
                    ':pkg' => !empty($item['packaging_id']) ? $item['packaging_id'] : null,
                    ':name' => $item['name'] ?? $item['product_name'],
                    ':unit' => $item['unit_name'],
                    ':qty' => $item['qty'],
                    ':price' => $item['buy_price'],
                    ':total' => $item['qty'] * $item['buy_price']
                ]);
            }

            $this->commit();
            return $estimateId;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function updateWithItems($id, $data, $items)
    {
        try {
            $this->beginTransaction();

            $this->update($id, [
                'title' => $data['title'],
                'supplier_id' => !empty($data['supplier_id']) ? $data['supplier_id'] : null,
                'total_amount' => $data['total_amount'] ?? 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Replace items
            $this->db->prepare("DELETE FROM order_estimate_items WHERE estimate_id = :id")->execute([':id' => $id]);

            $stmt = $this->db->prepare("
                INSERT INTO order_estimate_items 
                (estimate_id, product_id, packaging_id, product_name, unit_name, quantity, buy_price, total_price)
                VALUES (:est, :pid, :pkg, :name, :unit, :qty, :price, :total)
            ");

            foreach ($items as $item) {
                $stmt->execute([
                    ':est' => $id,
                    ':pid' => !empty($item['product_id']) ? $item['product_id'] : null,
                    ':pkg' => !empty($item['packaging_id']) ? $item['packaging_id'] : null,
                    ':name' => $item['name'] ?? $item['product_name'],
                    ':unit' => $item['unit_name'],
                    ':qty' => $item['qty'],
                    ':price' => $item['buy_price'],
                    ':total' => $item['qty'] * $item['buy_price']
                ]);
            }

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
