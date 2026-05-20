<?php
/**
 * DebtModel - Modul Catatan Hutang (Pelanggan & Toko)
 */
class DebtModel extends Model
{
    // ==========================================
    // CUSTOMER DEBTS (PIUTANG PELANGGAN)
    // ==========================================

    public function getCustomerDebts($filterStatus = null, $search = '')
    {
        $sql = "SELECT cd.*, 
                       c.name as customer_name, 
                       c.phone as customer_phone, 
                       c.address as customer_address, 
                       c.notes as customer_notes, 
                       st.invoice_number
                FROM customer_debts cd
                LEFT JOIN customers c ON cd.customer_id = c.id
                LEFT JOIN sale_transactions st ON cd.sale_id = st.id
                WHERE 1=1";
        $params = [];

        if ($filterStatus) {
            $sql .= " AND cd.status = :status";
            $params[':status'] = $filterStatus;
        }

        if ($search !== '') {
            $sql .= " AND (c.name LIKE :search 
                        OR c.notes LIKE :search 
                        OR cd.customer_name_fallback LIKE :search 
                        OR cd.notes LIKE :search 
                        OR st.invoice_number LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $sql .= " ORDER BY cd.debt_date DESC, cd.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCustomerDebtById($id)
    {
        $sql = "SELECT cd.*, 
                       c.name as customer_name, 
                       c.phone as customer_phone, 
                       c.address as customer_address, 
                       c.notes as customer_notes, 
                       st.invoice_number
                FROM customer_debts cd
                LEFT JOIN customers c ON cd.customer_id = c.id
                LEFT JOIN sale_transactions st ON cd.sale_id = st.id
                WHERE cd.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createCustomerDebt($data)
    {
        $data['remaining_amount'] = $data['amount'];
        $data['status'] = 'belum_lunas';
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->db->prepare("INSERT INTO customer_debts ({$columns}) VALUES ({$placeholders})");
        foreach ($data as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function getCustomerDebtPayments($debtId)
    {
        $stmt = $this->db->prepare("SELECT * FROM customer_debt_payments WHERE debt_id = :debt_id ORDER BY payment_date DESC, id DESC");
        $stmt->execute([':debt_id' => $debtId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCustomerPayment($debtId, $amount, $date, $notes = '')
    {
        try {
            $this->beginTransaction();

            // Insert payment record
            $stmt = $this->db->prepare("INSERT INTO customer_debt_payments (debt_id, amount, payment_date, notes) VALUES (:debt_id, :amount, :payment_date, :notes)");
            $stmt->execute([
                ':debt_id' => $debtId,
                ':amount' => $amount,
                ':payment_date' => $date,
                ':notes' => $notes
            ]);

            // Fetch the current debt
            $stmt = $this->db->prepare("SELECT amount, remaining_amount FROM customer_debts WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $debtId]);
            $debt = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$debt) {
                throw new Exception("Hutang tidak ditemukan");
            }

            // Calculate new remaining amount
            $newRemaining = max(0, $debt['remaining_amount'] - $amount);
            $status = ($newRemaining <= 0) ? 'lunas' : 'belum_lunas';

            // Update debt record
            $stmt = $this->db->prepare("UPDATE customer_debts SET remaining_amount = :remaining, status = :status WHERE id = :id");
            $stmt->execute([
                ':remaining' => $newRemaining,
                ':status' => $status,
                ':id' => $debtId
            ]);

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function deleteCustomerDebt($id)
    {
        $stmt = $this->db->prepare("DELETE FROM customer_debts WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }


    // ==========================================
    // SHOP DEBTS (HUTANG TOKO)
    // ==========================================

    public function getShopDebts($filterStatus = null, $search = '')
    {
        $sql = "SELECT sd.*, 
                       s.name as supplier_name, 
                       p.purchase_code
                FROM shop_debts sd
                LEFT JOIN suppliers s ON sd.supplier_id = s.id
                LEFT JOIN purchases p ON sd.purchase_id = p.id
                WHERE 1=1";
        $params = [];

        if ($filterStatus) {
            $sql .= " AND sd.status = :status";
            $params[':status'] = $filterStatus;
        }

        if ($search !== '') {
            $sql .= " AND (s.name LIKE :search 
                        OR sd.supplier_name_fallback LIKE :search 
                        OR sd.notes LIKE :search 
                        OR p.purchase_code LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $sql .= " ORDER BY sd.debt_date DESC, sd.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getShopDebtById($id)
    {
        $sql = "SELECT sd.*, 
                       s.name as supplier_name, 
                       p.purchase_code
                FROM shop_debts sd
                LEFT JOIN suppliers s ON sd.supplier_id = s.id
                LEFT JOIN purchases p ON sd.purchase_id = p.id
                WHERE sd.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createShopDebt($data)
    {
        $data['remaining_amount'] = $data['amount'];
        $data['status'] = 'belum_lunas';
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->db->prepare("INSERT INTO shop_debts ({$columns}) VALUES ({$placeholders})");
        foreach ($data as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function getShopDebtPayments($debtId)
    {
        $stmt = $this->db->prepare("SELECT * FROM shop_debt_payments WHERE debt_id = :debt_id ORDER BY payment_date DESC, id DESC");
        $stmt->execute([':debt_id' => $debtId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addShopPayment($debtId, $amount, $date, $notes = '')
    {
        try {
            $this->beginTransaction();

            // Insert payment record
            $stmt = $this->db->prepare("INSERT INTO shop_debt_payments (debt_id, amount, payment_date, notes) VALUES (:debt_id, :amount, :payment_date, :notes)");
            $stmt->execute([
                ':debt_id' => $debtId,
                ':amount' => $amount,
                ':payment_date' => $date,
                ':notes' => $notes
            ]);

            // Fetch current debt
            $stmt = $this->db->prepare("SELECT amount, remaining_amount FROM shop_debts WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $debtId]);
            $debt = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$debt) {
                throw new Exception("Hutang tidak ditemukan");
            }

            // Calculate new remaining amount
            $newRemaining = max(0, $debt['remaining_amount'] - $amount);
            $status = ($newRemaining <= 0) ? 'lunas' : 'belum_lunas';

            // Update debt record
            $stmt = $this->db->prepare("UPDATE shop_debts SET remaining_amount = :remaining, status = :status WHERE id = :id");
            $stmt->execute([
                ':remaining' => $newRemaining,
                ':status' => $status,
                ':id' => $debtId
            ]);

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function deleteShopDebt($id)
    {
        $stmt = $this->db->prepare("DELETE FROM shop_debts WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }


    // ==========================================
    // CUSTOMER MANAGEMENT (PELANGGAN)
    // ==========================================

    public function getCustomers($search = '')
    {
        $sql = "SELECT c.*, ct.name as type_name, ct.price_tier 
                FROM customers c
                LEFT JOIN customer_types ct ON c.type_id = ct.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (c.name LIKE :search OR c.phone LIKE :search OR c.address LIKE :search OR c.notes LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $sql .= " ORDER BY c.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createCustomer($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->db->prepare("INSERT INTO customers ({$columns}) VALUES ({$placeholders})");
        foreach ($data as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function updateCustomer($id, $data)
    {
        $setParts = [];
        foreach (array_keys($data) as $key) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setString = implode(', ', $setParts);

        $stmt = $this->db->prepare("UPDATE customers SET {$setString} WHERE id = :id");
        $data['id'] = $id;
        foreach ($data as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        return $stmt->execute();
    }

    public function deleteCustomer($id)
    {
        $stmt = $this->db->prepare("DELETE FROM customers WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getCustomerTypes()
    {
        $stmt = $this->db->prepare("SELECT * FROM customer_types ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
