<?php
/**
 * AiChatModel - Manajemen tabel chat_history & ai_knowledge
 */
class AiChatModel extends Model
{
    protected $table = 'chat_history';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
        $this->ensureKnowledgeTableExists();
    }

    // ============================================================
    // CHAT HISTORY
    // ============================================================

    private function ensureTableExists(): void
    {
        static $checked = false;
        if ($checked) return;

        try {
            $this->db->query("SELECT 1 FROM chat_history LIMIT 1");
        } catch (PDOException $e) {
            $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->db->exec("CREATE TABLE IF NOT EXISTS chat_history (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    session_id TEXT NOT NULL,
                    role TEXT NOT NULL,
                    content TEXT NOT NULL,
                    token_count INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");
            } else {
                $this->db->exec("CREATE TABLE IF NOT EXISTS chat_history (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    user_id INT NOT NULL,
                    session_id VARCHAR(64) NOT NULL,
                    role ENUM('user','assistant') NOT NULL,
                    content TEXT NOT NULL,
                    token_count INT DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_session_chat (user_id, session_id),
                    INDEX idx_created_at_chat (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
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
            ':user_id'     => $userId,
            ':session_id'  => $sessionId,
            ':role'        => $role,
            ':content'     => $content,
            ':token_count' => $tokenCount,
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
        $stmt->bindValue(':user_id',   $userId,  PDO::PARAM_INT);
        $stmt->bindValue(':session_id',$sessionId,PDO::PARAM_STR);
        $stmt->bindValue(':limit',     (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clearHistory(int $userId, string $sessionId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM chat_history WHERE user_id = :user_id AND session_id = :session_id");
        return $stmt->execute([':user_id' => $userId, ':session_id' => $sessionId]);
    }

    // ============================================================
    // AI KNOWLEDGE BASE
    // ============================================================

    private function ensureKnowledgeTableExists(): void
    {
        static $kChecked = false;
        if ($kChecked) return;

        try {
            $this->db->query("SELECT 1 FROM ai_knowledge LIMIT 1");
        } catch (PDOException $e) {
            $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->db->exec("CREATE TABLE IF NOT EXISTS ai_knowledge (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    topic TEXT NOT NULL,
                    content TEXT NOT NULL,
                    source TEXT DEFAULT 'user_feedback',
                    is_approved INTEGER DEFAULT 1,
                    use_count INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");
            } else {
                $this->db->exec("CREATE TABLE IF NOT EXISTS ai_knowledge (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    topic VARCHAR(255) NOT NULL,
                    content TEXT NOT NULL,
                    source VARCHAR(30) DEFAULT 'user_feedback',
                    is_approved TINYINT(1) DEFAULT 1,
                    use_count INT DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_topic (topic(100))
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            }
        }
        $kChecked = true;
    }

    /**
     * Simpan item pengetahuan baru ke ai_knowledge.
     * Hindari duplikat berdasarkan kesamaan topic + 100 karakter pertama content.
     */
    public function saveKnowledge(string $topic, string $content, string $source = 'user_feedback'): bool
    {
        try {
            // Cek duplikat
            $stmt = $this->db->prepare("
                SELECT id FROM ai_knowledge
                WHERE topic = :topic AND LEFT(content, 150) = LEFT(:content, 150)
                LIMIT 1
            ");
            $stmt->execute([':topic' => $topic, ':content' => $content]);
            if ($stmt->fetch()) {
                return true; // Sudah ada
            }

            $stmt = $this->db->prepare("
                INSERT INTO ai_knowledge (topic, content, source, is_approved)
                VALUES (:topic, :content, :source, 1)
            ");
            return $stmt->execute([
                ':topic'   => mb_substr($topic, 0, 255),
                ':content' => $content,
                ':source'  => $source,
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Cari knowledge yang relevan berdasarkan satu atau beberapa kata kunci.
     * Mengembalikan array ['topic', 'content', 'source'].
     */
    public function searchKnowledge(array $keywords, int $limit = 5): array
    {
        if (empty($keywords)) return [];

        try {
            // Buat kondisi WHERE dinamis: topic LIKE :kw0 OR content LIKE :kw0 OR ...
            $conditions = [];
            $params = [':limit' => $limit];
            foreach ($keywords as $i => $kw) {
                $key = ':kw' . $i;
                $conditions[] = "(topic LIKE {$key} OR content LIKE {$key})";
                $params[$key] = '%' . $kw . '%';
            }
            $where = implode(' OR ', $conditions);

            $stmt = $this->db->prepare("
                SELECT id, topic, content, source, use_count
                FROM ai_knowledge
                WHERE is_approved = 1 AND ({$where})
                ORDER BY use_count DESC, created_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            foreach ($params as $key => $val) {
                if ($key === ':limit') continue;
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Increment use_count
            foreach ($results as $row) {
                $this->db->prepare("UPDATE ai_knowledge SET use_count = use_count + 1 WHERE id = :id")
                         ->execute([':id' => $row['id']]);
            }

            return $results;
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Ambil semua knowledge items (untuk tampilan admin)
     */
    public function getAllKnowledge(int $limit = 100): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM ai_knowledge
                ORDER BY created_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Hapus knowledge item
     */
    public function deleteKnowledge(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM ai_knowledge WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Throwable $e) {
            return false;
        }
    }
}
