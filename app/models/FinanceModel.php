<?php
/**
 * FinanceModel - Logika bisnis dan query untuk Laporan/Pencatatan Keuangan Harian
 */
class FinanceModel extends Model
{
    protected $table = 'finance_logs';

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
        // 4 pos: Uang Laci, Uang Pulsa, Uang Beras, Uang Rokok
        $posts = ['Uang Laci', 'Uang Pulsa', 'Uang Beras', 'Uang Rokok'];
        $result = [];
        foreach ($posts as $post) {
            $stmt = $this->db->prepare("
                SELECT 
                    COALESCE(SUM(CASE WHEN category = 'Pemasukan' THEN amount ELSE 0 END), 0) as income,
                    COALESCE(SUM(CASE WHEN category = 'Pengeluaran' THEN amount ELSE 0 END), 0) as expense
                FROM finance_logs
                WHERE log_date = :date AND balance_type = :post
            ");
            $stmt->execute([':date' => $date, ':post' => $post]);
            $row = $stmt->fetch();
            $result[$post] = [
                'income' => (float)$row['income'],
                'expense' => (float)$row['expense'],
                'net' => (float)$row['income'] - (float)$row['expense']
            ];
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
        return $this->db->lastInsertId();
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
