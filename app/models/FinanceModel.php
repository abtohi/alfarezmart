<?php
/**
 * FinanceModel - Logika bisnis dan query untuk Laporan/Pencatatan Keuangan Harian
 */
class FinanceModel extends Model
{
    protected $table = 'finance_logs';

    /**
     * Get all active finance accounts (POS Keuangan)
     */
    public function getActiveAccounts()
    {
        $stmt = $this->db->prepare("
            SELECT a.*, d.name as dependency_name 
            FROM finance_accounts a 
            LEFT JOIN finance_accounts d ON a.dependency_account_id = d.id
            WHERE a.is_active = 1 
            ORDER BY a.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all active finance categories (Jenis Transaksi)
     */
    public function getActiveCategories($type = null)
    {
        $sql = "SELECT * FROM finance_categories WHERE is_active = 1";
        $params = [];
        if ($type) {
            $sql .= " AND type = :type";
            $params[':type'] = $type;
        }
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getDailySummary(string $date)
    {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN category = 'Pemasukan' THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN category = 'Pengeluaran' THEN amount ELSE 0 END), 0) as expense
            FROM finance_logs
            WHERE log_date = :date
        ");
        $stmt->execute([':date' => $date]);
        return $stmt->fetch();
    }

    public function getDailySummaryByPost(string $date)
    {
        // Ambil semua POS Keuangan yang aktif secara dinamis
        $accounts = $this->getActiveAccounts();
        
        $result = [];
        foreach ($accounts as $acc) {
            $post = $acc['name'];
            $result[$post] = ['income' => 0, 'expense' => 0, 'net' => 0];
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                balance_type,
                COALESCE(SUM(CASE WHEN category = 'Pemasukan' THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN category = 'Pengeluaran' THEN amount ELSE 0 END), 0) as expense
            FROM finance_logs
            WHERE log_date = :date
            GROUP BY balance_type
        ");
        
        $stmt->execute([':date' => $date]);
        $rows = $stmt->fetchAll();
        
        foreach ($rows as $row) {
            $post = $row['balance_type'];
            if (!isset($result[$post])) {
                $result[$post] = ['income' => 0, 'expense' => 0, 'net' => 0];
            }
            $result[$post]['income'] = (float)$row['income'];
            $result[$post]['expense'] = (float)$row['expense'];
            $result[$post]['net'] = (float)$row['income'] - (float)$row['expense'];
        }
        
        return $result;
    }

    public function getLogsByDate(string $date)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM finance_logs
            WHERE log_date = :date
            ORDER BY id DESC
        ");
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll();
    }

    public function addLog(array $data)
    {
        $period = date('Ym', strtotime($data['log_date']));
        $this->db->beginTransaction();

        try {
            // Check if balance_type has a dependency account
            $stmtCheck = $this->db->prepare("
                SELECT a.id, a.name, a.dependency_account_id, target.name as target_name
                FROM finance_accounts a
                LEFT JOIN finance_accounts target ON a.dependency_account_id = target.id
                WHERE a.name = :name AND a.is_active = 1
            ");
            $stmtCheck->execute([':name' => $data['balance_type']]);
            $account = $stmtCheck->fetch();

            $stmtInsert = $this->db->prepare("
                INSERT INTO finance_logs (log_date, period_yyyymm, amount, balance_type, category, detail, description, reference_type, reference_id)
                VALUES (:log_date, :period, :amount, :balance_type, :category, :detail, :description, :ref_type, :ref_id)
            ");

            if ($account && $account['dependency_account_id']) {
                $targetPos = $account['target_name'];
                $sourcePos = $data['balance_type'];
                
                // LOGIC: Dependent account always records to target account
                // Input to Uang Rokok -> Records to Saldo Rokok
                // Expense from Uang Rokok -> Records as expense in Saldo Rokok
                
                if ($data['category'] === 'Pemasukan') {
                    // Record income to target account
                    $stmtInsert->execute([
                        ':log_date' => $data['log_date'],
                        ':period' => $period,
                        ':amount' => $data['amount'],
                        ':balance_type' => $targetPos,
                        ':category' => 'Pemasukan',
                        ':detail' => $data['detail'] ?? null,
                        ':description' => ($data['description'] ? $data['description'] . ' ' : '') . "(dari {$sourcePos})",
                        ':ref_type' => $data['reference_type'] ?? null,
                        ':ref_id' => $data['reference_id'] ?? null
                    ]);
                } else if ($data['category'] === 'Pengeluaran') {
                    // Record BOTH Pemasukan and Pengeluaran to target account
                    
                    // 1. Pemasukan
                    $stmtInsert->execute([
                        ':log_date' => $data['log_date'],
                        ':period' => $period,
                        ':amount' => $data['amount'],
                        ':balance_type' => $targetPos,
                        ':category' => 'Pemasukan',
                        ':detail' => $data['detail'] ?? null,
                        ':description' => "Pemasukan sistem (untuk Pengeluaran dari {$sourcePos})",
                        ':ref_type' => $data['reference_type'] ?? null,
                        ':ref_id' => $data['reference_id'] ?? null
                    ]);
                    
                    // 2. Pengeluaran
                    $stmtInsert->execute([
                        ':log_date' => $data['log_date'],
                        ':period' => $period,
                        ':amount' => $data['amount'],
                        ':balance_type' => $targetPos,
                        ':category' => 'Pengeluaran',
                        ':detail' => $data['detail'] ?? null,
                        ':description' => ($data['description'] ? $data['description'] . ' ' : '') . "(dari {$sourcePos})",
                        ':ref_type' => $data['reference_type'] ?? null,
                        ':ref_id' => $data['reference_id'] ?? null
                    ]);
                }
                
                $lastId = $this->db->lastInsertId();
                $this->db->commit();
                return $lastId;
            }

            // No dependency - record normally to the specified balance_type
            $stmt = $this->db->prepare("
                INSERT INTO finance_logs (log_date, period_yyyymm, amount, balance_type, category, detail, description, reference_type, reference_id)
                VALUES (:log_date, :period, :amount, :balance_type, :category, :detail, :description, :ref_type, :ref_id)
            ");
            $stmt->execute([
                ':log_date' => $data['log_date'],
                ':period' => $period,
                ':amount' => $data['amount'],
                ':balance_type' => $data['balance_type'],
                ':category' => $data['category'],
                ':detail' => $data['detail'] ?? null,
                ':description' => $data['description'] ?? null,
                ':ref_type' => $data['reference_type'] ?? null,
                ':ref_id' => $data['reference_id'] ?? null
            ]);
            
            $lastId = $this->db->lastInsertId();
            $this->db->commit();
            return $lastId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateLog(int $id, array $data)
    {
        $period = date('Ym', strtotime($data['log_date']));
        $stmt = $this->db->prepare("
            UPDATE finance_logs
            SET log_date = :log_date,
                period_yyyymm = :period,
                amount = :amount,
                balance_type = :balance_type,
                category = :category,
                detail = :detail,
                description = :description
            WHERE id = :id
        ");
        return $stmt->execute([
            ':log_date' => $data['log_date'],
            ':period' => $period,
            ':amount' => $data['amount'],
            ':balance_type' => $data['balance_type'],
            ':category' => $data['category'],
            ':detail' => $data['detail'] ?? null,
            ':description' => $data['description'] ?? null,
            ':id' => $id
        ]);
    }

    public function deleteLog(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM finance_logs WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function findLog(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM finance_logs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
