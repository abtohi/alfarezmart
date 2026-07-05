<?php
/**
 * SalesRepModel - Data salesman/sales per supplier
 */
class SalesRepModel extends Model
{
    protected $table = 'sales_reps';

    /**
     * Get all sales reps for a specific supplier
     */
    public function getBySupplier($supplierId, $activeOnly = false)
    {
        $sql = "
            SELECT sr.*, s.name as supplier_name
            FROM sales_reps sr
            LEFT JOIN suppliers s ON sr.supplier_id = s.id
            WHERE sr.supplier_id = :sid
        ";
        if ($activeOnly) {
            $sql .= " AND sr.status = 'Aktif'";
        }
        $sql .= " ORDER BY sr.status ASC, sr.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sid' => $supplierId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all active sales reps for an array of supplier IDs
     */
    public function getActiveBySupplierIds(array $supplierIds)
    {
        if (empty($supplierIds)) return [];
        $in = implode(',', array_map('intval', $supplierIds));
        $stmt = $this->db->query("
            SELECT sr.*, s.name as supplier_name 
            FROM sales_reps sr 
            JOIN suppliers s ON sr.supplier_id = s.id 
            WHERE sr.supplier_id IN ($in) AND sr.status = 'Aktif'
            ORDER BY s.name ASC, sr.name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get all active sales reps with supplier name, and optionally include a specific ID even if inactive
     */
    public function getAllWithSupplier($includeId = null)
    {
        $sql = "
            SELECT sr.*, s.name as supplier_name
            FROM sales_reps sr
            LEFT JOIN suppliers s ON sr.supplier_id = s.id
            WHERE (sr.status = 'Aktif'";
            
        if ($includeId) {
            $sql .= " OR sr.id = :include_id";
        }
        
        $sql .= ") ORDER BY s.name ASC, sr.name ASC";

        $stmt = $this->db->prepare($sql);
        
        $params = [];
        if ($includeId) {
            $params[':include_id'] = $includeId;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find with supplier info
     */
    public function findWithSupplier($id)
    {
        $stmt = $this->db->prepare("
            SELECT sr.*, s.name as supplier_name
            FROM sales_reps sr
            LEFT JOIN suppliers s ON sr.supplier_id = s.id
            WHERE sr.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Hapus sales duplikat per supplier (nama sales sama, supplier sama).
     * Mempertahankan ID terkecil; relasi dipindahkan ke record yang dipertahankan.
     *
     * @return array{removed:int, groups:int, details:array}
     */
    public function removeDuplicatesBySupplier(): array
    {
        $salesNameExpr = 'LOWER(TRIM(sr.name))';
        $supplierNameExpr = 'LOWER(TRIM(s.name))';

        $stmt = $this->db->query("
            SELECT MIN(sr.supplier_id) AS supplier_id,
                   {$supplierNameExpr} AS norm_supplier,
                   {$salesNameExpr} AS norm_name,
                   GROUP_CONCAT(sr.id) AS ids
            FROM sales_reps sr
            INNER JOIN suppliers s ON s.id = sr.supplier_id
            WHERE sr.status = 'Aktif'
            GROUP BY {$supplierNameExpr}, {$salesNameExpr}
            HAVING COUNT(*) > 1
        ");
        $groups = $stmt->fetchAll();

        $removed = 0;
        $details = [];

        foreach ($groups as $g) {
            $ids = array_map('intval', explode(',', (string)$g['ids']));
            sort($ids, SORT_NUMERIC);
            $keepId = array_shift($ids);
            if (empty($ids)) {
                continue;
            }

            foreach ($ids as $dupId) {
                $this->reassignSalesRepReferences($dupId, $keepId);
                $del = $this->db->prepare('DELETE FROM sales_reps WHERE id = :id');
                $del->execute([':id' => $dupId]);
                $removed++;
            }

            $details[] = [
                'supplier_id' => (int)$g['supplier_id'],
                'supplier_name' => $g['norm_supplier'] ?? '',
                'name' => $g['norm_name'],
                'kept_id' => $keepId,
                'removed_ids' => $ids,
            ];
        }

        return [
            'removed' => $removed,
            'groups' => count($details),
            'details' => $details,
        ];
    }

    /**
     * Pindahkan referensi sales_rep_id ke record yang dipertahankan.
     */
    private function reassignSalesRepReferences(int $fromId, int $toId): void
    {
        $tables = [
            'purchases' => 'sales_rep_id',
            'supplier_products' => 'sales_rep_id',
        ];

        foreach ($tables as $table => $column) {
            try {
                $stmt = $this->db->prepare("
                    UPDATE {$table}
                    SET {$column} = :to_id
                    WHERE {$column} = :from_id
                ");
                $stmt->execute([':to_id' => $toId, ':from_id' => $fromId]);
            } catch (PDOException $e) {
                // Tabel/kolom mungkin belum ada di instalasi lama — abaikan
            }
        }
    }
}
