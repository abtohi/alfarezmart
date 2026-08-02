<?php
/**
 * Database Connection - PDO wrapper for SQLite/MySQL
 *
 * Supports hybrid mode: tries MySQL first (online), automatically falls back
 * to SQLite (offline) when MySQL is unreachable. This ensures the app keeps
 * working even when there is no internet connection or the remote DB is down.
 */
class Database
{
    /** @var self|null */
    private static $instance = null;

    /** @var \PDO */
    private $pdo;

    /** @var string Which driver is actually active: 'mysql' or 'sqlite' */
    private $activeDriver = 'sqlite';

    private function __construct()
    {
        $configDriver = defined('DB_DRIVER') ? DB_DRIVER : 'sqlite';

        if ($configDriver === 'mysql') {
            // Attempt MySQL first; fall back to SQLite on any failure
            try {
                $this->connectMySQL();
                $this->activeDriver = 'mysql';
                return;
            } catch (\Throwable $e) {
                // MySQL unreachable (offline / server down / connection limit / high load)
                error_log('[AlfarezMart] MySQL connection failed: ' . $e->getMessage() . '. Falling back to SQLite/Offline mode.');
                try {
                    $this->connectSQLite();
                    $this->activeDriver = 'sqlite';
                    return;
                } catch (\Throwable $sqe) {
                    error_log('[AlfarezMart] SQLite fallback also failed: ' . $sqe->getMessage());
                }
            }
        }

        // SQLite path (primary when driver=sqlite, or fallback when MySQL fails)
        try {
            $this->connectSQLite();
            $this->activeDriver = 'sqlite';
        } catch (\Throwable $e) {
            try {
                $this->pdo = new PDO('sqlite::memory:');
                $this->activeDriver = 'sqlite';
            } catch (\Throwable $e2) {
                error_log('[AlfarezMart] Memory DB fallback failed: ' . $e2->getMessage());
            }
        }
    }

    // ─── Internal connection helpers ──────────────────────────────────────────

    private function connectMySQL(): void
    {
        $host   = defined('DB_HOST')     ? DB_HOST     : '153.92.15.83';
        $port   = defined('DB_PORT')     ? DB_PORT     : '3306';
        $dbname = defined('DB_DATABASE') ? DB_DATABASE : 'u573283697_alfarezmart';
        $user   = defined('DB_USERNAME') ? DB_USERNAME : 'u573283697_alfarez';
        $pass   = defined('DB_PASSWORD') ? DB_PASSWORD : '';

        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_PERSISTENT         => false, // Disabled persistent to prevent stale socket 'MySQL server has gone away' errors
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 10, // Increased timeout to 10s for stability
        ];

        $this->pdo = new PDO($dsn, $user, $pass, $options);
        $this->pdo->exec("SET time_zone = '+07:00'");
        $this->pdo->exec("SET SESSION wait_timeout = 28800");
        $this->pdo->exec("SET SESSION interactive_timeout = 28800");
    }

    private function connectSQLite(): void
    {
        $sqlitePath = defined('DB_SQLITE_PATH') ? DB_SQLITE_PATH : 'storage/database/alfarezmart.sqlite';

        if (strpos($sqlitePath, 'storage/') === 0) {
            $dbPath = defined('STORAGE_PATH')
                ? STORAGE_PATH . substr($sqlitePath, 7)
                : dirname(BASE_PATH) . '/' . $sqlitePath;
        } else {
            $dbPath = BASE_PATH . '/' . $sqlitePath;
        }

        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->pdo = new PDO("sqlite:$dbPath");
        $this->pdo->exec('PRAGMA journal_mode=WAL');
        $this->pdo->exec('PRAGMA foreign_keys=ON');
        $this->pdo->exec('PRAGMA busy_timeout=5000');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false);
    }

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection (auto pings and reconnects if stale)
     */
    public function getConnection(): \PDO
    {
        if ($this->activeDriver === 'mysql' && $this->pdo !== null) {
            try {
                @$this->pdo->query('SELECT 1');
            } catch (\Throwable $e) {
                error_log('[AlfarezMart] MySQL connection lost, reconnecting...');
                try {
                    $this->connectMySQL();
                } catch (\Throwable $e2) {
                    error_log('[AlfarezMart] MySQL reconnect failed: ' . $e2->getMessage());
                }
            }
        }
        return $this->pdo;
    }

    /**
     * Returns true when running on the local SQLite fallback (offline mode).
     */
    public function isOffline(): bool
    {
        return $this->activeDriver === 'sqlite';
    }

    /**
     * Returns which driver is currently active: 'mysql' or 'sqlite'
     */
    public function getDriver(): string
    {
        return $this->activeDriver;
    }

    /**
     * Ping database to check if connection is alive
     */
    public function ping(): bool
    {
        try {
            @$this->pdo->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reconnect to the database (tries MySQL first, falls back to SQLite)
     */
    public function reconnect(): \PDO
    {
        // Explicitly close old PDO connection first to prevent connection leak
        $this->pdo = null;
        self::$instance = null;
        return self::getInstance()->getConnection();
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}

