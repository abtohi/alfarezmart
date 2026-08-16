<?php
/**
 * DebtModel - Modul Catatan Hutang (Pelanggan & Toko)
 */
class DebtModel extends Model
{
    // ==========================================
    // CUSTOMER DEBTS (PIUTANG PELANGGAN)
    // ==========================================

    /**
     * Get customer debts with optional status filter and search term.
     *
     * @param string|null $filterStatus
     * @param string $search
     * @return array
     */
    public function getCustomerDebts(?string $filterStatus = null, string $search = '')
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
            $sql .= " AND (c.name LIKE :s1 
                        OR c.notes LIKE :s2 
                        OR cd.customer_name_fallback LIKE :s3 
                        OR cd.notes LIKE :s4 
                        OR st.invoice_number LIKE :s5)";
            $s = "%{$search}%";
            $params[':s1'] = $s;
            $params[':s2'] = $s;
            $params[':s3'] = $s;
            $params[':s4'] = $s;
            $params[':s5'] = $s;
        }

        $sql .= " ORDER BY cd.debt_date DESC, cd.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer debt detail by ID.
     *
     * @param int|string $id
     * @return array|false
     */
    public function getCustomerDebtById(int|string $id)
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

    /**
     * Create a new customer debt record.
     *
     * @param array $data
     * @return string|false
     */
    public function createCustomerDebt(array $data)
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

    /**
     * Get payment history for a customer debt.
     *
     * @param int|string $debtId
     * @return array
     */
    public function getCustomerDebtPayments(int|string $debtId)
    {
        $stmt = $this->db->prepare("SELECT * FROM customer_debt_payments WHERE debt_id = :debt_id ORDER BY payment_date DESC, id DESC");
        $stmt->execute([':debt_id' => $debtId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add a payment installment for a customer debt.
     *
     * @param int|string $debtId
     * @param float|int $amount
     * @param string $date
     * @param string $notes
     * @return bool
     * @throws Exception
     */
    public function addCustomerPayment(int|string $debtId, float|int $amount, string $date, string $notes = '')
    {
        try {
            $this->beginTransaction();

            // 1. Fetch and lock the current debt first
            $stmt = $this->db->prepare("SELECT amount, remaining_amount FROM customer_debts WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $debtId]);
            $debt = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$debt) {
                throw new Exception("Hutang tidak ditemukan");
            }

            // 2. Insert payment record
            $stmt = $this->db->prepare("INSERT INTO customer_debt_payments (debt_id, amount, payment_date, notes) VALUES (:debt_id, :amount, :payment_date, :notes)");
            $stmt->execute([
                ':debt_id' => $debtId,
                ':amount' => $amount,
                ':payment_date' => $date,
                ':notes' => $notes
            ]);

            // 3. Calculate new remaining amount
            $newRemaining = max(0, $debt['remaining_amount'] - $amount);
            $status = ($newRemaining <= 0) ? 'lunas' : 'belum_lunas';

            // 4. Update debt record
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

    /**
     * Update an existing customer debt.
     *
     * @param int|string $id
     * @param array $data
     * @return bool
     */
    public function updateCustomerDebt(int|string $id, array $data)
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total_paid FROM customer_debt_payments WHERE debt_id = :id");
        $stmt->execute([':id' => $id]);
        $paidRes = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalPaid = (float)($paidRes['total_paid'] ?? 0);

        $amount = (float)($data['amount'] ?? 0);
        $newRemaining = max(0, $amount - $totalPaid);
        $status = ($newRemaining <= 0) ? 'lunas' : 'belum_lunas';

        $sql = "UPDATE customer_debts SET 
                    customer_id = :customer_id,
                    customer_name_fallback = :customer_name_fallback,
                    amount = :amount,
                    remaining_amount = :remaining_amount,
                    status = :status,
                    debt_date = :debt_date,
                    due_date = :due_date,
                    notes = :notes
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':customer_id' => !empty($data['customer_id']) ? (int)$data['customer_id'] : null,
            ':customer_name_fallback' => !empty($data['customer_name_fallback']) ? $data['customer_name_fallback'] : null,
            ':amount' => $amount,
            ':remaining_amount' => $newRemaining,
            ':status' => $status,
            ':debt_date' => $data['debt_date'] ?? date('Y-m-d'),
            ':due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
            ':notes' => $data['notes'] ?? '',
            ':id' => (int)$id
        ]);
    }

    /**
     * Delete a customer debt record.
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteCustomerDebt(int|string $id)
    {
        try {
            $this->beginTransaction();
            $this->db->prepare("DELETE FROM customer_debt_payments WHERE debt_id = :id")->execute([':id' => $id]);
            $stmt = $this->db->prepare("DELETE FROM customer_debts WHERE id = :id");
            $res = $stmt->execute([':id' => $id]);
            $this->commit();
            return $res;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }



    // ==========================================
    // SHOP DEBTS (HUTANG TOKO)
    // ==========================================

    /**
     * Get shop debts with optional status filter and search term.
     *
     * @param string|null $filterStatus
     * @param string $search
     * @return array
     */
    public function getShopDebts(?string $filterStatus = null, string $search = '')
    {
        $sql = "SELECT sd.*, 
                       s.name as supplier_name, 
                       ds.name as source_name,
                       p.purchase_code
                FROM shop_debts sd
                LEFT JOIN suppliers s ON sd.supplier_id = s.id
                LEFT JOIN debt_sources ds ON sd.debt_source_id = ds.id
                LEFT JOIN purchases p ON sd.purchase_id = p.id
                WHERE 1=1";
        $params = [];

        if ($filterStatus) {
            $sql .= " AND sd.status = :status";
            $params[':status'] = $filterStatus;
        }

        if ($search !== '') {
            $sql .= " AND (s.name LIKE :s1 
                        OR ds.name LIKE :s2 
                        OR sd.supplier_name_fallback LIKE :s3 
                        OR sd.notes LIKE :s4 
                        OR p.purchase_code LIKE :s5)";
            $s = "%{$search}%";
            $params[':s1'] = $s;
            $params[':s2'] = $s;
            $params[':s3'] = $s;
            $params[':s4'] = $s;
            $params[':s5'] = $s;
        }

        $sql .= " ORDER BY sd.debt_date DESC, sd.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get shop debt detail by ID.
     *
     * @param int|string $id
     * @return array|false
     */
    public function getShopDebtById(int|string $id)
    {
        $sql = "SELECT sd.*, 
                       s.name as supplier_name, 
                       ds.name as source_name,
                       p.purchase_code
                FROM shop_debts sd
                LEFT JOIN suppliers s ON sd.supplier_id = s.id
                LEFT JOIN debt_sources ds ON sd.debt_source_id = ds.id
                LEFT JOIN purchases p ON sd.purchase_id = p.id
                WHERE sd.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new shop debt record.
     *
     * @param array $data
     * @return string|false
     */
    public function createShopDebt(array $data)
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

    /**
     * Get payment history for a shop debt.
     *
     * @param int|string $debtId
     * @return array
     */
    public function getShopDebtPayments(int|string $debtId)
    {
        $stmt = $this->db->prepare("SELECT * FROM shop_debt_payments WHERE debt_id = :debt_id ORDER BY payment_date DESC, id DESC");
        $stmt->execute([':debt_id' => $debtId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add a payment installment for a shop debt.
     *
     * @param int|string $debtId
     * @param float|int $amount
     * @param string $date
     * @param string $notes
     * @return bool
     * @throws Exception
     */
    public function addShopPayment(int|string $debtId, float|int $amount, string $date, string $notes = '')
    {
        try {
            $this->beginTransaction();

            // 1. Fetch and lock current debt first
            $stmt = $this->db->prepare("SELECT amount, remaining_amount FROM shop_debts WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $debtId]);
            $debt = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$debt) {
                throw new Exception("Hutang tidak ditemukan");
            }

            // 2. Insert payment record
            $stmt = $this->db->prepare("INSERT INTO shop_debt_payments (debt_id, amount, payment_date, notes) VALUES (:debt_id, :amount, :payment_date, :notes)");
            $stmt->execute([
                ':debt_id' => $debtId,
                ':amount' => $amount,
                ':payment_date' => $date,
                ':notes' => $notes
            ]);

            // 3. Calculate new remaining amount
            $newRemaining = max(0, $debt['remaining_amount'] - $amount);
            $status = ($newRemaining <= 0) ? 'lunas' : 'belum_lunas';

            // 4. Update debt record
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

    /**
     * Update an existing shop debt.
     *
     * @param int|string $id
     * @param array $data
     * @return bool
     */
    public function updateShopDebt(int|string $id, array $data)
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total_paid FROM shop_debt_payments WHERE debt_id = :id");
        $stmt->execute([':id' => $id]);
        $paidRes = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalPaid = (float)($paidRes['total_paid'] ?? 0);

        $amount = (float)($data['amount'] ?? 0);
        $newRemaining = max(0, $amount - $totalPaid);
        $status = ($newRemaining <= 0) ? 'lunas' : 'belum_lunas';

        $sql = "UPDATE shop_debts SET 
                    supplier_id = :supplier_id,
                    debt_source_id = :debt_source_id,
                    supplier_name_fallback = :supplier_name_fallback,
                    amount = :amount,
                    remaining_amount = :remaining_amount,
                    status = :status,
                    debt_date = :debt_date,
                    due_date = :due_date,
                    notes = :notes
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':supplier_id' => !empty($data['supplier_id']) ? (int)$data['supplier_id'] : null,
            ':debt_source_id' => !empty($data['debt_source_id']) ? (int)$data['debt_source_id'] : null,
            ':supplier_name_fallback' => !empty($data['supplier_name_fallback']) ? $data['supplier_name_fallback'] : null,
            ':amount' => $amount,
            ':remaining_amount' => $newRemaining,
            ':status' => $status,
            ':debt_date' => $data['debt_date'] ?? date('Y-m-d'),
            ':due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
            ':notes' => $data['notes'] ?? '',
            ':id' => (int)$id
        ]);
    }

    /**
     * Delete a shop debt record.
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteShopDebt(int|string $id)
    {
        try {
            $this->beginTransaction();
            $this->db->prepare("DELETE FROM shop_debt_payments WHERE debt_id = :id")->execute([':id' => $id]);
            $stmt = $this->db->prepare("DELETE FROM shop_debts WHERE id = :id");
            $res = $stmt->execute([':id' => $id]);
            $this->commit();
            return $res;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }


    // ==========================================
    // CUSTOMER MANAGEMENT (PELANGGAN)
    // ==========================================

    /**
     * Get customers list with optional search term.
     *
     * @param string $search
     * @return array
     */
    public function getCustomers(string $search = '')
    {
        $sql = "SELECT c.*, ct.name as type_name, ct.price_tier 
                FROM customers c
                LEFT JOIN customer_types ct ON c.type_id = ct.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (c.name LIKE :s1 OR c.phone LIKE :s2 OR c.address LIKE :s3 OR c.notes LIKE :s4)";
            $s = "%{$search}%";
            $params[':s1'] = $s;
            $params[':s2'] = $s;
            $params[':s3'] = $s;
            $params[':s4'] = $s;
        }

        $sql .= " ORDER BY c.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new customer record.
     *
     * @param array $data
     * @return string|false
     */
    public function createCustomer(array $data)
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

    /**
     * Update customer details.
     *
     * @param int|string $id
     * @param array $data
     * @return bool
     */
    public function updateCustomer(int|string $id, array $data)
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

    /**
     * Delete a customer record.
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteCustomer(int|string $id)
    {
        $stmt = $this->db->prepare("DELETE FROM customers WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get list of customer types.
     *
     * @return array
     */
    public function getCustomerTypes()
    {
        $stmt = $this->db->prepare("SELECT * FROM customer_types ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // DEBT SOURCES (SUMBER HUTANG)
    // ==========================================

    /**
     * Get list of debt sources.
     *
     * @return array
     */
    public function getDebtSources()
    {
        $stmt = $this->db->prepare("SELECT * FROM debt_sources ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new debt source.
     *
     * @param string $name
     * @return string|false
     */
    public function createDebtSource(string $name)
    {
        $stmt = $this->db->prepare("INSERT INTO debt_sources (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        return $this->db->lastInsertId();
    }

    /**
     * Update a debt source name.
     *
     * @param int|string $id
     * @param string $name
     * @return bool
     */
    public function updateDebtSource(int|string $id, string $name)
    {
        $stmt = $this->db->prepare("UPDATE debt_sources SET name = :name WHERE id = :id");
        return $stmt->execute([':name' => $name, ':id' => $id]);
    }

    /**
     * Delete a debt source.
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteDebtSource(int|string $id)
    {
        $stmt = $this->db->prepare("DELETE FROM debt_sources WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
