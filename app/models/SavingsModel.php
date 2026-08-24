<?php
/**
 * SavingsModel - Modul Tabungan, Financial Goals, Pos Alokasi Dana & Mutasi
 */
class SavingsModel extends Model
{
    protected $table = 'savings_goals';

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    /**
     * Auto-create tables for MySQL and SQLite if not exist
     */
    private function ensureSchema(): void
    {
        try {
            if (!$this->db) return;

            $isSqlite = Database::getInstance()->isOffline() || Database::getInstance()->getDriver() === 'sqlite';

            if ($isSqlite) {
                $sqls = [
                    "CREATE TABLE IF NOT EXISTS savings_goals (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name TEXT NOT NULL,
                        target_amount REAL NOT NULL DEFAULT 0,
                        target_date DATE,
                        category TEXT DEFAULT 'Lainnya',
                        icon TEXT DEFAULT 'bi-piggy-bank-fill',
                        color TEXT DEFAULT '#6366f1',
                        description TEXT,
                        status TEXT DEFAULT 'in_progress',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )",
                    "CREATE TABLE IF NOT EXISTS savings_allocations (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        goal_id INTEGER NOT NULL,
                        name TEXT NOT NULL,
                        account_type TEXT DEFAULT 'Bank / Rekening',
                        institution TEXT,
                        amount REAL NOT NULL DEFAULT 0,
                        notes TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )",
                    "CREATE TABLE IF NOT EXISTS savings_logs (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        goal_id INTEGER NOT NULL,
                        allocation_id INTEGER,
                        type TEXT NOT NULL,
                        amount REAL NOT NULL,
                        balance_after REAL NOT NULL,
                        log_date DATE NOT NULL,
                        notes TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )"
                ];
            } else {
                $sqls = [
                    "CREATE TABLE IF NOT EXISTS savings_goals (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(150) NOT NULL,
                        target_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
                        target_date DATE NULL,
                        category VARCHAR(50) DEFAULT 'Lainnya',
                        icon VARCHAR(50) DEFAULT 'bi-piggy-bank-fill',
                        color VARCHAR(20) DEFAULT '#6366f1',
                        description TEXT NULL,
                        status VARCHAR(20) DEFAULT 'in_progress',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                    "CREATE TABLE IF NOT EXISTS savings_allocations (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        goal_id INT NOT NULL,
                        name VARCHAR(150) NOT NULL,
                        account_type VARCHAR(50) DEFAULT 'Bank / Rekening',
                        institution VARCHAR(100) NULL,
                        amount DECIMAL(15,2) NOT NULL DEFAULT 0,
                        notes TEXT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_goal (goal_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                    "CREATE TABLE IF NOT EXISTS savings_logs (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        goal_id INT NOT NULL,
                        allocation_id INT NULL,
                        type VARCHAR(20) NOT NULL,
                        amount DECIMAL(15,2) NOT NULL,
                        balance_after DECIMAL(15,2) NOT NULL,
                        log_date DATE NOT NULL,
                        notes TEXT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_goal_log (goal_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
                ];
            }

            foreach ($sqls as $sql) {
                $this->db->exec($sql);
            }
        } catch (\Throwable $e) {
            error_log('[SavingsModel] ensureSchema error: ' . $e->getMessage());
        }
    }

    // ==========================================
    // GOALS CRUD & QUERIES
    // ==========================================

    /**
     * Get all goals with accumulated balance and progress calculations
     */
    public function getGoals(?string $status = null): array
    {
        try {
            $sql = "SELECT g.*, 
                           COALESCE(SUM(a.amount), 0) AS collected_amount,
                           COUNT(a.id) AS total_allocations
                    FROM savings_goals g
                    LEFT JOIN savings_allocations a ON g.id = a.goal_id
                    WHERE 1=1";
            $params = [];

            if ($status) {
                $sql .= " AND g.status = :status";
                $params[':status'] = $status;
            }

            $sql .= " GROUP BY g.id ORDER BY g.id DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $goals = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($goals as &$goal) {
                $target = (float)$goal['target_amount'];
                $collected = (float)$goal['collected_amount'];
                $goal['target_amount'] = $target;
                $goal['collected_amount'] = $collected;
                $goal['remaining_amount'] = max(0, $target - $collected);
                $goal['progress_percent'] = $target > 0 ? min(100, round(($collected / $target) * 100, 1)) : 0;
                
                // Days remaining
                if (!empty($goal['target_date'])) {
                    $now = new DateTime();
                    $targetDate = new DateTime($goal['target_date']);
                    $diff = $now->diff($targetDate);
                    $goal['days_left'] = $targetDate >= $now ? (int)$diff->format('%r%a') : -(int)$diff->format('%a');
                } else {
                    $goal['days_left'] = null;
                }

                // Fetch mini allocation summary for quick pills
                $goal['allocations'] = $this->getAllocationsByGoal((int)$goal['id']);
            }

            return $goals;
        } catch (\Throwable $e) {
            error_log('[SavingsModel] getGoals error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single goal by ID with allocations
     */
    public function getGoalById($id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT g.*, 
                       COALESCE(SUM(a.amount), 0) AS collected_amount,
                       COUNT(a.id) AS total_allocations
                FROM savings_goals g
                LEFT JOIN savings_allocations a ON g.id = a.goal_id
                WHERE g.id = :id
                GROUP BY g.id
                LIMIT 1
            ");
            $stmt->execute([':id' => $id]);
            $goal = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$goal) return null;

            $target = (float)$goal['target_amount'];
            $collected = (float)$goal['collected_amount'];
            $goal['target_amount'] = $target;
            $goal['collected_amount'] = $collected;
            $goal['remaining_amount'] = max(0, $target - $collected);
            $goal['progress_percent'] = $target > 0 ? min(100, round(($collected / $target) * 100, 1)) : 0;

            if (!empty($goal['target_date'])) {
                $now = new DateTime();
                $targetDate = new DateTime($goal['target_date']);
                $diff = $now->diff($targetDate);
                $goal['days_left'] = $targetDate >= $now ? (int)$diff->format('%r%a') : -(int)$diff->format('%a');
            } else {
                $goal['days_left'] = null;
            }

            $goal['allocations'] = $this->getAllocationsByGoal((int)$id);
            $goal['logs'] = $this->getLogsByGoal((int)$id, 20);

            return $goal;
        } catch (\Throwable $e) {
            error_log('[SavingsModel] getGoalById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new Goal
     */
    public function createGoal(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO savings_goals (name, target_amount, target_date, category, icon, color, description, status)
            VALUES (:name, :target_amount, :target_date, :category, :icon, :color, :description, :status)
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':target_amount' => (float)($data['target_amount'] ?? 0),
            ':target_date' => !empty($data['target_date']) ? $data['target_date'] : null,
            ':category' => $data['category'] ?? 'Lainnya',
            ':icon' => $data['icon'] ?? 'bi-piggy-bank-fill',
            ':color' => $data['color'] ?? '#6366f1',
            ':description' => $data['description'] ?? null,
            ':status' => $data['status'] ?? 'in_progress',
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update Goal
     */
    public function updateGoal($id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE savings_goals 
            SET name = :name,
                target_amount = :target_amount,
                target_date = :target_date,
                category = :category,
                icon = :icon,
                color = :color,
                description = :description,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':target_amount' => (float)($data['target_amount'] ?? 0),
            ':target_date' => !empty($data['target_date']) ? $data['target_date'] : null,
            ':category' => $data['category'] ?? 'Lainnya',
            ':icon' => $data['icon'] ?? 'bi-piggy-bank-fill',
            ':color' => $data['color'] ?? '#6366f1',
            ':description' => $data['description'] ?? null,
            ':status' => $data['status'] ?? 'in_progress',
        ]);
    }

    /**
     * Delete Goal and its related allocations and logs
     */
    public function deleteGoal($id): bool
    {
        try {
            $this->beginTransaction();
            $stmt1 = $this->db->prepare("DELETE FROM savings_logs WHERE goal_id = :id");
            $stmt1->execute([':id' => $id]);

            $stmt2 = $this->db->prepare("DELETE FROM savings_allocations WHERE goal_id = :id");
            $stmt2->execute([':id' => $id]);

            $stmt3 = $this->db->prepare("DELETE FROM savings_goals WHERE id = :id");
            $stmt3->execute([':id' => $id]);

            $this->commit();
            return true;
        } catch (\Throwable $e) {
            $this->rollback();
            error_log('[SavingsModel] deleteGoal error: ' . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // ALLOCATIONS (POS PENEMPATAN UANG)
    // ==========================================

    /**
     * Get allocations for a specific goal
     */
    public function getAllocationsByGoal(int $goalId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM savings_allocations 
                WHERE goal_id = :goal_id 
                ORDER BY amount DESC, id ASC
            ");
            $stmt->execute([':goal_id' => $goalId]);
            $allocations = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($allocations as &$alloc) {
                $alloc['amount'] = (float)$alloc['amount'];
            }
            return $allocations;
        } catch (\Throwable $e) {
            error_log('[SavingsModel] getAllocationsByGoal error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single allocation by ID
     */
    public function getAllocationById($id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM savings_allocations WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $alloc = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($alloc) {
                $alloc['amount'] = (float)$alloc['amount'];
            }
            return $alloc ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Create new allocation item
     */
    public function createAllocation(array $data): int
    {
        try {
            $this->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO savings_allocations (goal_id, name, account_type, institution, amount, notes)
                VALUES (:goal_id, :name, :account_type, :institution, :amount, :notes)
            ");
            $amount = (float)($data['amount'] ?? 0);
            $stmt->execute([
                ':goal_id' => $data['goal_id'],
                ':name' => $data['name'],
                ':account_type' => $data['account_type'] ?? 'Bank / Rekening',
                ':institution' => $data['institution'] ?? null,
                ':amount' => $amount,
                ':notes' => $data['notes'] ?? null,
            ]);
            $allocId = (int)$this->db->lastInsertId();

            // Record initial deposit log if amount > 0
            if ($amount > 0) {
                $logStmt = $this->db->prepare("
                    INSERT INTO savings_logs (goal_id, allocation_id, type, amount, balance_after, log_date, notes)
                    VALUES (:goal_id, :alloc_id, 'deposit', :amount, :balance_after, :log_date, :notes)
                ");
                $logStmt->execute([
                    ':goal_id' => $data['goal_id'],
                    ':alloc_id' => $allocId,
                    ':amount' => $amount,
                    ':balance_after' => $amount,
                    ':log_date' => date('Y-m-d'),
                    ':notes' => 'Saldo awal penempatan ' . $data['name'],
                ]);
            }

            $this->commit();
            return $allocId;
        } catch (\Throwable $e) {
            $this->rollback();
            error_log('[SavingsModel] createAllocation error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update allocation
     */
    public function updateAllocation($id, array $data): bool
    {
        try {
            $existing = $this->getAllocationById($id);
            if (!$existing) return false;

            $newAmount = isset($data['amount']) ? (float)$data['amount'] : (float)$existing['amount'];
            $diff = $newAmount - (float)$existing['amount'];

            $this->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE savings_allocations 
                SET name = :name,
                    account_type = :account_type,
                    institution = :institution,
                    amount = :amount,
                    notes = :notes,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $stmt->execute([
                ':id' => $id,
                ':name' => $data['name'] ?? $existing['name'],
                ':account_type' => $data['account_type'] ?? $existing['account_type'],
                ':institution' => $data['institution'] ?? $existing['institution'],
                ':amount' => $newAmount,
                ':notes' => $data['notes'] ?? $existing['notes'],
            ]);

            // If amount changed directly via edit, log adjustment
            if (abs($diff) > 0.01) {
                $type = $diff > 0 ? 'deposit' : 'withdraw';
                $logStmt = $this->db->prepare("
                    INSERT INTO savings_logs (goal_id, allocation_id, type, amount, balance_after, log_date, notes)
                    VALUES (:goal_id, :alloc_id, :type, :amount, :balance_after, :log_date, :notes)
                ");
                $logStmt->execute([
                    ':goal_id' => $existing['goal_id'],
                    ':alloc_id' => $id,
                    ':type' => $type,
                    ':amount' => abs($diff),
                    ':balance_after' => $newAmount,
                    ':log_date' => date('Y-m-d'),
                    ':notes' => 'Penyesuaian saldo pos ' . ($data['name'] ?? $existing['name']),
                ]);
            }

            $this->commit();
            return true;
        } catch (\Throwable $e) {
            $this->rollback();
            error_log('[SavingsModel] updateAllocation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete allocation
     */
    public function deleteAllocation($id): bool
    {
        try {
            $existing = $this->getAllocationById($id);
            if (!$existing) return false;

            $this->beginTransaction();
            $stmt1 = $this->db->prepare("DELETE FROM savings_logs WHERE allocation_id = :id");
            $stmt1->execute([':id' => $id]);

            $stmt2 = $this->db->prepare("DELETE FROM savings_allocations WHERE id = :id");
            $stmt2->execute([':id' => $id]);

            $this->commit();
            return true;
        } catch (\Throwable $e) {
            $this->rollback();
            error_log('[SavingsModel] deleteAllocation error: ' . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // MUTATIONS & LOGS (TOP UP / TARIK / MUTASI)
    // ==========================================

    /**
     * Record a mutation (Deposit/Withdraw) for an allocation
     */
    public function recordMutation(int $goalId, int $allocationId, string $type, float $amount, ?string $notes = null, ?string $logDate = null): array
    {
        try {
            $alloc = $this->getAllocationById($allocationId);
            if (!$alloc || (int)$alloc['goal_id'] !== $goalId) {
                throw new \Exception('Pos alokasi tidak ditemukan atau tidak sesuai dengan Goal');
            }

            $currentAmount = (float)$alloc['amount'];
            $mutationAmount = abs($amount);

            if ($type === 'withdraw' && $mutationAmount > $currentAmount) {
                throw new \Exception('Nominal penarikan (Rp ' . number_format($mutationAmount, 0, ',', '.') . ') melebihi saldo yang ada (Rp ' . number_format($currentAmount, 0, ',', '.') . ')');
            }

            $newBalance = $type === 'deposit' ? ($currentAmount + $mutationAmount) : ($currentAmount - $mutationAmount);
            $date = !empty($logDate) ? $logDate : date('Y-m-d');

            $this->beginTransaction();

            // Update allocation balance
            $upStmt = $this->db->prepare("UPDATE savings_allocations SET amount = :amt, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $upStmt->execute([
                ':amt' => $newBalance,
                ':id' => $allocationId
            ]);

            // Insert log
            $logStmt = $this->db->prepare("
                INSERT INTO savings_logs (goal_id, allocation_id, type, amount, balance_after, log_date, notes)
                VALUES (:goal_id, :alloc_id, :type, :amount, :balance_after, :log_date, :notes)
            ");
            $logStmt->execute([
                ':goal_id' => $goalId,
                ':alloc_id' => $allocationId,
                ':type' => $type,
                ':amount' => $mutationAmount,
                ':balance_after' => $newBalance,
                ':log_date' => $date,
                ':notes' => $notes ?: ($type === 'deposit' ? 'Setoran dana' : 'Penarikan dana'),
            ]);

            $this->commit();

            return [
                'success' => true,
                'allocation_id' => $allocationId,
                'new_balance' => $newBalance,
                'goal_id' => $goalId,
            ];
        } catch (\Throwable $e) {
            $this->rollback();
            error_log('[SavingsModel] recordMutation error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get mutation logs for a goal
     */
    public function getLogsByGoal(int $goalId, int $limit = 50): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT l.*, a.name AS allocation_name, a.account_type, a.institution
                FROM savings_logs l
                LEFT JOIN savings_allocations a ON l.allocation_id = a.id
                WHERE l.goal_id = :goal_id
                ORDER BY l.log_date DESC, l.id DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':goal_id', $goalId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($logs as &$log) {
                $log['amount'] = (float)$log['amount'];
                $log['balance_after'] = (float)$log['balance_after'];
            }
            return $logs;
        } catch (\Throwable $e) {
            error_log('[SavingsModel] getLogsByGoal error: ' . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // OVERALL SUMMARY & ASSET CLASSIFICATION
    // ==========================================

    /**
     * Get global metrics across all goals and asset distributions
     */
    public function getOverallSummary(): array
    {
        try {
            // 1. Goals metrics
            $goalStatsStmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_goals,
                    SUM(target_amount) as total_target,
                    SUM(CASE WHEN status = 'achieved' THEN 1 ELSE 0 END) as achieved_goals,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_goals
                FROM savings_goals
            ");
            $goalStats = $goalStatsStmt ? $goalStatsStmt->fetch(PDO::FETCH_ASSOC) : [];
            $totalTarget = (float)($goalStats['total_target'] ?? 0);
            $totalGoals = (int)($goalStats['total_goals'] ?? 0);
            $achievedGoals = (int)($goalStats['achieved_goals'] ?? 0);
            $inProgressGoals = (int)($goalStats['in_progress_goals'] ?? 0);

            // 2. Allocations total & classification
            $allocStatsStmt = $this->db->query("
                SELECT 
                    COALESCE(SUM(amount), 0) as total_collected,
                    COUNT(id) as total_allocations
                FROM savings_allocations
            ");
            $allocStats = $allocStatsStmt ? $allocStatsStmt->fetch(PDO::FETCH_ASSOC) : [];
            $totalCollected = (float)($allocStats['total_collected'] ?? 0);

            // 3. Breakdown by Account Type (Toko, Investasi, Bank, Piutang, Lainnya)
            $typeBreakdownStmt = $this->db->query("
                SELECT 
                    account_type,
                    COALESCE(SUM(amount), 0) as total_amount,
                    COUNT(id) as item_count
                FROM savings_allocations
                GROUP BY account_type
                ORDER BY total_amount DESC
            ");
            $typeBreakdown = $typeBreakdownStmt ? $typeBreakdownStmt->fetchAll(PDO::FETCH_ASSOC) : [];

            $formattedTypes = [];
            foreach ($typeBreakdown as $tb) {
                $amt = (float)$tb['total_amount'];
                $formattedTypes[] = [
                    'account_type' => $tb['account_type'] ?: 'Lainnya',
                    'total_amount' => $amt,
                    'item_count' => (int)$tb['item_count'],
                    'percentage' => $totalCollected > 0 ? round(($amt / $totalCollected) * 100, 1) : 0,
                ];
            }

            $overallProgress = $totalTarget > 0 ? min(100, round(($totalCollected / $totalTarget) * 100, 1)) : 0;

            return [
                'total_goals' => $totalGoals,
                'achieved_goals' => $achievedGoals,
                'in_progress_goals' => $inProgressGoals,
                'total_target' => $totalTarget,
                'total_collected' => $totalCollected,
                'total_remaining' => max(0, $totalTarget - $totalCollected),
                'overall_progress' => $overallProgress,
                'type_breakdown' => $formattedTypes,
            ];
        } catch (\Throwable $e) {
            error_log('[SavingsModel] getOverallSummary error: ' . $e->getMessage());
            return [
                'total_goals' => 0,
                'achieved_goals' => 0,
                'in_progress_goals' => 0,
                'total_target' => 0,
                'total_collected' => 0,
                'total_remaining' => 0,
                'overall_progress' => 0,
                'type_breakdown' => [],
            ];
        }
    }
}
