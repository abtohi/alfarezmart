<?php
/**
 * PPOB Customer Model
 * Handles database operations for PPOB Customers (PLN, HP, Game, TV)
 */
require_once __DIR__ . '/../config/Database.php';

class PpobCustomerModel {
    private \PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM ppob_customers ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByType($type) {
        $stmt = $this->db->prepare("SELECT * FROM ppob_customers WHERE type = :type ORDER BY created_at DESC");
        $stmt->execute(['type' => $type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM ppob_customers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO ppob_customers (type, customer_name, customer_no, pln_name, pln_power)
            VALUES (:type, :customer_name, :customer_no, :pln_name, :pln_power)
        ");
        
        return $stmt->execute([
            'type' => $data['type'],
            'customer_name' => $data['customer_name'] ?? null,
            'customer_no' => $data['customer_no'],
            'pln_name' => $data['pln_name'] ?? null,
            'pln_power' => $data['pln_power'] ?? null
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE ppob_customers 
            SET type = :type, 
                customer_name = :customer_name, 
                customer_no = :customer_no, 
                pln_name = :pln_name, 
                pln_power = :pln_power
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'id' => $id,
            'type' => $data['type'],
            'customer_name' => $data['customer_name'] ?? null,
            'customer_no' => $data['customer_no'],
            'pln_name' => $data['pln_name'] ?? null,
            'pln_power' => $data['pln_power'] ?? null
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM ppob_customers WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
