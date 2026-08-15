<?php
/**
 * ScanCache
 *
 * Image hash-based scan result caching.
 * Prevents redundant AI calls for identical invoices.
 *
 * Features:
 * - MD5 hash of image_base64 as cache key
 * - TTL-based expiration (default 24h)
 * - Auto-creates table if not exists
 * - Version-aware invalidation
 *
 * @package AlfarezMart\Services\Invoice
 */
class ScanCache
{
    /** @var \PDO */
    private $db;

    /** Default cache TTL in hours */
    const DEFAULT_TTL_HOURS = 24;

    /** @var bool */
    private $tableReady = false;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Generate hash key from image base64 data.
     *
     * @param string $imageB64
     * @return string MD5 hash
     */
    public function hashImage(string $imageB64): string
    {
        // Strip data URI prefix if present
        if (strpos($imageB64, 'base64,') !== false) {
            $imageB64 = substr($imageB64, strpos($imageB64, 'base64,') + 7);
        }
        return md5($imageB64);
    }

    /**
     * Check if a cached result exists for the given image hash.
     *
     * @param string $imageHash
     * @param int|null $supplierId
     * @return array|null Cached scan result or null
     */
    public function get(string $imageHash, ?int $supplierId = null): ?array
    {
        $this->ensureTable();

        try {
            $sql = "SELECT result_json, item_count, created_at 
                    FROM ai_scan_cache 
                    WHERE image_hash = ? AND expires_at > NOW()";
            $params = [$imageHash];

            if ($supplierId && $supplierId > 0) {
                $sql .= " AND (supplier_id = ? OR supplier_id IS NULL)";
                $params[] = $supplierId;
            }

            $sql .= " ORDER BY created_at DESC LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row && !empty($row['result_json'])) {
                $result = json_decode($row['result_json'], true);
                if (is_array($result)) {
                    return $result;
                }
            }
        } catch (\Throwable $e) {
            error_log("ScanCache::get error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Store a scan result in cache.
     *
     * @param string $imageHash
     * @param int|null $supplierId
     * @param array $result Scan result data
     * @param int $ttlHours TTL in hours
     */
    public function set(string $imageHash, ?int $supplierId, array $result, int $ttlHours = self::DEFAULT_TTL_HOURS): void
    {
        $this->ensureTable();

        try {
            $resultJson = json_encode($result, JSON_UNESCAPED_UNICODE);
            $itemCount = isset($result['data']) ? count($result['data']) : 0;

            // Use REPLACE INTO for upsert (MySQL compatible)
            $stmt = $this->db->prepare("
                REPLACE INTO ai_scan_cache 
                (image_hash, supplier_id, result_json, item_count, created_at, expires_at)
                VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? HOUR))
            ");
            $stmt->execute([$imageHash, $supplierId, $resultJson, $itemCount, $ttlHours]);
        } catch (\Throwable $e) {
            error_log("ScanCache::set error: " . $e->getMessage());
        }
    }

    /**
     * Check for duplicate invoice fingerprint.
     *
     * @param int|null $supplierId
     * @param int $itemCount
     * @param float $totalPrice
     * @return bool True if a similar invoice was previously scanned
     */
    public function isDuplicate(?int $supplierId, int $itemCount, float $totalPrice): bool
    {
        $this->ensureTable();

        try {
            $fingerprint = md5(($supplierId ?? 0) . ':' . $itemCount . ':' . round($totalPrice, 0));

            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM ai_scan_cache 
                WHERE fingerprint = ? AND expires_at > NOW()
            ");
            $stmt->execute([$fingerprint]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (\Throwable $e) {
            // fingerprint column might not exist yet
            return false;
        }
    }

    /**
     * Store invoice fingerprint for duplicate detection.
     *
     * @param string $imageHash
     * @param int|null $supplierId
     * @param int $itemCount
     * @param float $totalPrice
     */
    public function storeFingerprint(string $imageHash, ?int $supplierId, int $itemCount, float $totalPrice): void
    {
        try {
            $fingerprint = md5(($supplierId ?? 0) . ':' . $itemCount . ':' . round($totalPrice, 0));

            $stmt = $this->db->prepare("
                UPDATE ai_scan_cache SET fingerprint = ? WHERE image_hash = ?
            ");
            $stmt->execute([$fingerprint, $imageHash]);
        } catch (\Throwable $e) {
            // Silently fail — fingerprint is optional
        }
    }

    /**
     * Clear expired cache entries.
     */
    public function cleanup(): void
    {
        $this->ensureTable();

        try {
            $this->db->exec("DELETE FROM ai_scan_cache WHERE expires_at < NOW()");
        } catch (\Throwable $e) {
            error_log("ScanCache::cleanup error: " . $e->getMessage());
        }
    }

    // ----------------------------------------------------------------
    // PRIVATE
    // ----------------------------------------------------------------

    private function ensureTable(): void
    {
        if ($this->tableReady) return;

        try {
            $this->db->query("SELECT 1 FROM ai_scan_cache LIMIT 1");
            $this->tableReady = true;
        } catch (\PDOException $e) {
            try {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS ai_scan_cache (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        image_hash VARCHAR(32) NOT NULL,
                        supplier_id INT DEFAULT NULL,
                        result_json LONGTEXT,
                        item_count INT DEFAULT 0,
                        fingerprint VARCHAR(32) DEFAULT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        expires_at DATETIME,
                        UNIQUE KEY uq_hash (image_hash),
                        INDEX idx_fingerprint (fingerprint),
                        INDEX idx_expires (expires_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $this->tableReady = true;
            } catch (\PDOException $e2) {
                // SQLite fallback
                try {
                    $this->db->exec("
                        CREATE TABLE IF NOT EXISTS ai_scan_cache (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            image_hash TEXT NOT NULL UNIQUE,
                            supplier_id INTEGER,
                            result_json TEXT,
                            item_count INTEGER DEFAULT 0,
                            fingerprint TEXT,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            expires_at DATETIME
                        )
                    ");
                    $this->tableReady = true;
                } catch (\Throwable $e3) {
                    $this->tableReady = true;
                }
            }
        }
    }
}
