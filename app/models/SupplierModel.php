<?php
class SupplierModel extends Model 
{ 
    protected $table = 'suppliers'; 

    public function getAllWithType()
    {
        $stmt = $this->db->prepare("
            SELECT s.*, st.name as type_name
            FROM suppliers s
            LEFT JOIN supplier_types st ON s.type_id = st.id
            WHERE s.is_active = 1
            ORDER BY s.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findWithType($id)
    {
        $stmt = $this->db->prepare("
            SELECT s.*, st.name as type_name
            FROM suppliers s
            LEFT JOIN supplier_types st ON s.type_id = st.id
            WHERE s.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function searchSuppliersAndSales($keyword)
    {
        $q = "%{$keyword}%";
        $stmt = $this->db->prepare("
            SELECT DISTINCT s.id, s.name, st.name as type_name, 'supplier' as match_type, s.name as match_name
            FROM suppliers s
            LEFT JOIN supplier_types st ON s.type_id = st.id
            WHERE s.is_active = 1 AND s.name LIKE :q1
            
            UNION
            
            SELECT s.id, s.name, st.name as type_name, 'sales' as match_type, sr.name as match_name
            FROM sales_reps sr
            JOIN suppliers s ON sr.supplier_id = s.id
            LEFT JOIN supplier_types st ON s.type_id = st.id
            WHERE s.is_active = 1 AND sr.name LIKE :q2
            
            ORDER BY name ASC
            LIMIT 20
        ");
        $stmt->execute([':q1' => $q, ':q2' => $q]);
        return $stmt->fetchAll();
    }
}
