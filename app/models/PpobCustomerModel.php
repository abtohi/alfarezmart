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

    public function getByType(string $type) {
        if (empty($type) || $type === 'all') {
            return $this->getAll();
        }

        $types = array_map('trim', explode(',', $type));
        
        // If requesting ewallet or hp, include both hp and ewallet since both use phone numbers
        if (in_array('ewallet', $types) || in_array('hp', $types)) {
            if (!in_array('hp', $types)) $types[] = 'hp';
            if (!in_array('ewallet', $types)) $types[] = 'ewallet';
        }

        $in  = str_repeat('?,', count($types) - 1) . '?';
        $stmt = $this->db->prepare("SELECT * FROM ppob_customers WHERE type IN ($in) ORDER BY created_at DESC");
        $stmt->execute($types);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $unique = [];
        $result = [];
        foreach ($rows as $r) {
            $no = trim($r['customer_no']);
            if (!isset($unique[$no])) {
                $unique[$no] = true;
                $result[] = $r;
            }
        }
        return $result;
    }

    public function getById(int|string $id) {
        $stmt = $this->db->prepare("SELECT * FROM ppob_customers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCustomerNo(string $customerNo) {
        $stmt = $this->db->prepare("SELECT * FROM ppob_customers WHERE customer_no = :customer_no LIMIT 1");
        $stmt->execute(['customer_no' => trim($customerNo)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->db->prepare("
            INSERT INTO ppob_customers (type, customer_name, customer_no, pln_name, pln_power, ewallet_accounts)
            VALUES (:type, :customer_name, :customer_no, :pln_name, :pln_power, :ewallet_accounts)
        ");
        
        return $stmt->execute([
            'type' => $data['type'],
            'customer_name' => $data['customer_name'] ?? null,
            'customer_no' => $data['customer_no'],
            'pln_name' => $data['pln_name'] ?? null,
            'pln_power' => $data['pln_power'] ?? null,
            'ewallet_accounts' => $data['ewallet_accounts'] ?? null
        ]);
    }

    public function update(int|string $id, array $data) {
        $stmt = $this->db->prepare("
            UPDATE ppob_customers 
            SET type = :type, 
                customer_name = :customer_name, 
                customer_no = :customer_no, 
                pln_name = :pln_name, 
                pln_power = :pln_power,
                ewallet_accounts = :ewallet_accounts
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'id' => $id,
            'type' => $data['type'],
            'customer_name' => $data['customer_name'] ?? null,
            'customer_no' => $data['customer_no'],
            'pln_name' => $data['pln_name'] ?? null,
            'pln_power' => $data['pln_power'] ?? null,
            'ewallet_accounts' => $data['ewallet_accounts'] ?? null
        ]);
    }

    public function delete(int|string $id) {
        $stmt = $this->db->prepare("DELETE FROM ppob_customers WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
