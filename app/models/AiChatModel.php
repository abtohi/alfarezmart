<?php
/**
 * AiChatModel - Manajemen tabel chat_history
 */
class AiChatModel extends Model
{
    protected $table = 'chat_history';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
    }

    private function ensureTableExists()
    {
        static $checked = false;
        if ($checked) return;
        
        try {
            // Cek apakah tabel ada
            $this->db->query("SELECT 1 FROM chat_history LIMIT 1");
        } catch (PDOException $e) {
            $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $sql = "CREATE TABLE IF NOT EXISTS chat_history (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    session_id TEXT NOT NULL,
                    role TEXT NOT NULL,
                    content TEXT NOT NULL,
                    token_count INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )";
                $this->db->exec($sql);
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_user_session_chat ON chat_history (user_id, session_id)");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_created_at_chat ON chat_history (created_at)");
            } else {
                // Buat tabel MySQL
                $sql = "CREATE TABLE IF NOT EXISTS chat_history (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    user_id INT NOT NULL,
                    session_id VARCHAR(64) NOT NULL,
                    role ENUM('user','assistant') NOT NULL,
                    content TEXT NOT NULL,
                    token_count INT DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_session (user_id, session_id),
                    INDEX idx_created_at (created_at)
                )";
                $this->db->exec($sql);
            }
        }
        $checked = true;
    }

    public function saveMessage(int $userId, string $sessionId, string $role, string $content, int $tokenCount = 0): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO chat_history (user_id, session_id, role, content, token_count, created_at)
            VALUES (:user_id, :session_id, :role, :content, :token_count, CURRENT_TIMESTAMP)
        ");
        return $stmt->execute([
            ':user_id' => $userId,
            ':session_id' => $sessionId,
            ':role' => $role,
            ':content' => $content,
            ':token_count' => $tokenCount
        ]);
    }

    public function getHistory(int $userId, string $sessionId, int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM (
                SELECT * FROM chat_history
                WHERE user_id = :user_id AND session_id = :session_id
                ORDER BY created_at DESC
                LIMIT :limit
            ) sub
            ORDER BY created_at ASC
        ");
        // PDO needs limit as INT
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':session_id', $sessionId, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clearHistory(int $userId, string $sessionId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM chat_history WHERE user_id = :user_id AND session_id = :session_id");
        return $stmt->execute([
            ':user_id' => $userId,
            ':session_id' => $sessionId
        ]);
    }
}
