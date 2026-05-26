<?php
/**
 * Database Connection - PDO wrapper for SQLite/MySQL
 * 
 * Supports hybrid mode: SQLite for offline, MySQL for online.
 * All connections use PDO with prepared statements.
 */
class Database
{
    /** @var self|null */
    private static $instance = null;
    
    /** @var \PDO */
    private $pdo;

    private function __construct()
    {
        $driver = defined('DB_DRIVER') ? DB_DRIVER : 'sqlite';

        try {
            if ($driver === 'sqlite') {
                $sqlitePath = defined('DB_SQLITE_PATH') ? DB_SQLITE_PATH : 'storage/database/alfarezmart.sqlite';
                if (strpos($sqlitePath, 'storage/') === 0) {
                    $dbPath = defined('STORAGE_PATH') ? STORAGE_PATH . substr($sqlitePath, 7) : dirname(BASE_PATH) . '/' . $sqlitePath;
                } else {
                    $dbPath = BASE_PATH . '/' . $sqlitePath;
                }
                $dir = dirname($dbPath);
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                
                $this->pdo = new PDO("sqlite:$dbPath");
                $this->pdo->exec('PRAGMA journal_mode=WAL');
                $this->pdo->exec('PRAGMA foreign_keys=ON');
                $this->pdo->exec('PRAGMA busy_timeout=5000');
            } else {
                $host = defined('DB_HOST') ? DB_HOST : '153.92.15.83';
                $port = defined('DB_PORT') ? DB_PORT : '3306';
                $dbname = defined('DB_DATABASE') ? DB_DATABASE : 'u573283697_alfarezmart';
                $user = defined('DB_USERNAME') ? DB_USERNAME : 'u573283697_alfarez';
                $pass = defined('DB_PASSWORD') ? DB_PASSWORD : '';

                $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                $this->pdo = new PDO($dsn, $user, $pass);
                
                // Set Timezone MySQL ke WIB (GMT+7)
                $this->pdo->exec("SET time_zone = '+07:00'");
            }

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        } catch (PDOException $e) {
            if (defined('APP_DEBUG') && APP_DEBUG === 'true') {
                die("Database Error: " . $e->getMessage());
            }
            die("Database connection failed. Please check configuration.");
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}
