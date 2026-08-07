<?php
/**
 * AiChatModel v5.0 - Chat History, Knowledge Base, & Learned Facts
 *
 * Tables managed:
 * - chat_history       : Riwayat percakapan user ↔ AI
 * - ai_knowledge       : Koreksi & pengetahuan dari user feedback
 * - ai_learned_facts   : Fakta yang dipelajari AI dari interaksi (TTL-based cache)
 */
class AiChatModel extends Model
{
    protected $table = 'chat_history';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
        $this->ensureKnowledgeTableExists();
        $this->ensureLearnedFactsTableExists();
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
    // AI KNOWLEDGE BASE (User Corrections & Feedback)
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

    public function searchKnowledge(array $keywords, int $limit = 5): array
    {
        if (empty($keywords)) return [];

        try {
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

    public function deleteKnowledge(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM ai_knowledge WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Throwable $e) {
            return false;
        }
    }

    // ============================================================
    // AI LEARNED FACTS (Auto-learning cache with TTL)
    // ============================================================

    private function ensureLearnedFactsTableExists(): void
    {
        static $fChecked = false;
        if ($fChecked) return;

        try {
            $this->db->query("SELECT 1 FROM ai_learned_facts LIMIT 1");
        } catch (PDOException $e) {
            $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->db->exec("CREATE TABLE IF NOT EXISTS ai_learned_facts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    fact_key TEXT NOT NULL,
                    fact_value TEXT NOT NULL,
                    category TEXT DEFAULT 'general',
                    source TEXT DEFAULT 'sql_result',
                    hit_count INTEGER DEFAULT 1,
                    expires_at DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");
            } else {
                $this->db->exec("CREATE TABLE IF NOT EXISTS ai_learned_facts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    fact_key VARCHAR(255) NOT NULL,
                    fact_value TEXT NOT NULL,
                    category VARCHAR(50) DEFAULT 'general',
                    source VARCHAR(50) DEFAULT 'sql_result',
                    hit_count INT DEFAULT 1,
                    expires_at DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_fact_key (fact_key(100)),
                    INDEX idx_category (category),
                    INDEX idx_expires (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            }
        }
        $fChecked = true;
    }

    /**
     * Simpan fakta yang dipelajari AI.
     * TTL otomatis berdasarkan kategori:
     * - 'finance'  → 1 jam
     * - 'product'  → 24 jam
     * - 'tutorial' → tidak expire
     * - 'general'  → 6 jam
     */
    public function saveFact(string $factKey, string $factValue, string $category = 'general', string $source = 'sql_result'): bool
    {
        try {
            // Hitung TTL
            $ttlMap = [
                'finance'  => '+1 hour',
                'product'  => '+24 hours',
                'stock'    => '+2 hours',
                'sales'    => '+1 hour',
                'business' => '+2 hours',
                'tutorial' => null, // Tidak expire
                'general'  => '+6 hours',
            ];
            $ttl = $ttlMap[$category] ?? '+6 hours';
            $expiresAt = $ttl ? date('Y-m-d H:i:s', strtotime($ttl)) : null;

            // Upsert: update jika key sudah ada
            $stmt = $this->db->prepare("SELECT id FROM ai_learned_facts WHERE fact_key = :key LIMIT 1");
            $stmt->execute([':key' => $factKey]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE ai_learned_facts
                    SET fact_value = :val, category = :cat, source = :src,
                        hit_count = hit_count + 1, expires_at = :exp,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");
                return $stmt->execute([
                    ':val' => $factValue,
                    ':cat' => $category,
                    ':src' => $source,
                    ':exp' => $expiresAt,
                    ':id'  => $existing['id'],
                ]);
            }

            $stmt = $this->db->prepare("
                INSERT INTO ai_learned_facts (fact_key, fact_value, category, source, hit_count, expires_at)
                VALUES (:key, :val, :cat, :src, 1, :exp)
            ");
            return $stmt->execute([
                ':key' => mb_substr($factKey, 0, 255),
                ':val' => $factValue,
                ':cat' => $category,
                ':src' => $source,
                ':exp' => $expiresAt,
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Cari fakta yang masih valid (belum expire) berdasarkan keywords.
     */
    public function searchFacts(array $keywords, int $limit = 3): array
    {
        if (empty($keywords)) return [];

        try {
            $conditions = [];
            $params = [];
            foreach ($keywords as $i => $kw) {
                $key = ':fkw' . $i;
                $conditions[] = "(fact_key LIKE {$key} OR fact_value LIKE {$key})";
                $params[$key] = '%' . $kw . '%';
            }
            $where = implode(' OR ', $conditions);

            $stmt = $this->db->prepare("
                SELECT id, fact_key, fact_value, category, hit_count
                FROM ai_learned_facts
                WHERE ({$where})
                AND (expires_at IS NULL OR expires_at > NOW())
                ORDER BY hit_count DESC, updated_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Increment hit_count
            foreach ($results as $row) {
                $this->db->prepare("UPDATE ai_learned_facts SET hit_count = hit_count + 1 WHERE id = :id")
                         ->execute([':id' => $row['id']]);
            }

            return $results;
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Hapus fakta yang sudah expired (cleanup).
     */
    public function cleanupExpiredFacts(): int
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM ai_learned_facts WHERE expires_at IS NOT NULL AND expires_at < NOW()");
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Throwable $e) {
            return 0;
        }
    }
}
