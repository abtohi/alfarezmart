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
            // PERF FIX: Persistent connections to a REMOTE MySQL host (Hostinger)
            // from a local XAMPP server are harmful — stale connections can hang
            // and trigger unnecessary fallback-to-SQLite on every request.
            // Non-persistent connections are far more stable for remote hosts.
            PDO::ATTR_PERSISTENT         => false,
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            // Raised from 3s → 5s: 3s is too aggressive for remote connections
            // on slower mobile/home networks causing false offline fallback.
            PDO::ATTR_TIMEOUT            => 5,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone='+07:00', SESSION wait_timeout=30, SESSION interactive_timeout=30",
        ];

        $this->pdo = new PDO($dsn, $user, $pass, $options);
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

        $this->ensureSQLiteSchema();
    }

    /**
     * Ensure SQLite database has required tables to prevent 'no such table' errors during offline fallback
     */
    private function ensureSQLiteSchema(): void
    {
        try {
            $sqls = [
                "CREATE TABLE IF NOT EXISTS brands (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT UNIQUE NOT NULL,
                    slug TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT UNIQUE NOT NULL,
                    slug TEXT,
                    icon TEXT,
                    sort_order INTEGER DEFAULT 0,
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS units (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT UNIQUE NOT NULL,
                    abbreviation TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    code TEXT UNIQUE,
                    brand_id INTEGER,
                    category_id INTEGER,
                    product_type TEXT,
                    variant TEXT,
                    full_name TEXT NOT NULL,
                    short_label TEXT,
                    invoice_name TEXT,
                    supplier_product_code TEXT,
                    supplier_invoice_name TEXT,
                    weight_value REAL,
                    weight_unit TEXT,
                    description TEXT,
                    image_path TEXT,
                    photo TEXT,
                    min_stock INTEGER DEFAULT 0,
                    max_stock INTEGER,
                    is_active INTEGER DEFAULT 1,
                    is_available INTEGER DEFAULT 1,
                    is_multivariant INTEGER DEFAULT 0,
                    is_custom_label INTEGER DEFAULT 0,
                    ref_product_id INTEGER,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS product_packagings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    product_id INTEGER NOT NULL,
                    level INTEGER NOT NULL,
                    unit_id INTEGER NOT NULL,
                    contained_qty INTEGER DEFAULT 1,
                    base_qty INTEGER DEFAULT 1,
                    barcode TEXT,
                    buy_price REAL DEFAULT 0,
                    sell_price_retail REAL DEFAULT 0,
                    margin_retail REAL DEFAULT 0,
                    sell_price_wholesale REAL DEFAULT 0,
                    margin_wholesale REAL DEFAULT 0,
                    is_default_scan INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS product_qty_prices (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    packaging_id INTEGER NOT NULL,
                    min_qty REAL NOT NULL DEFAULT 1,
                    unit_price REAL NOT NULL DEFAULT 0,
                    sale_mode TEXT DEFAULT 'both',
                    label TEXT,
                    sort_order INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS supplier_types (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT UNIQUE NOT NULL
                )",
                "CREATE TABLE IF NOT EXISTS suppliers (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    type_id INTEGER,
                    address TEXT,
                    products_sold TEXT,
                    is_consignment INTEGER DEFAULT 0,
                    notes TEXT,
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS sales_reps (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    supplier_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    phone TEXT,
                    sales_type TEXT,
                    visit_day TEXT,
                    delivery_day TEXT,
                    visit_period TEXT,
                    status TEXT DEFAULT 'Aktif',
                    notes TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS purchases (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    purchase_code TEXT UNIQUE,
                    supplier_id INTEGER,
                    sales_rep_id INTEGER,
                    purchase_date DATE NOT NULL,
                    total_amount REAL DEFAULT 0,
                    total_items INTEGER DEFAULT 0,
                    ppn_amount REAL DEFAULT 0,
                    discount_amount REAL DEFAULT 0,
                    shipping_cost REAL DEFAULT 0,
                    grand_total REAL DEFAULT 0,
                    payment_status TEXT DEFAULT 'Lunas',
                    invoice_photo TEXT,
                    notes TEXT,
                    synced_to_finance INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS purchase_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    purchase_id INTEGER NOT NULL,
                    product_id INTEGER NOT NULL,
                    packaging_id INTEGER NOT NULL,
                    quantity REAL NOT NULL,
                    buy_price REAL NOT NULL,
                    ppn_percent REAL DEFAULT 0,
                    discount_percent REAL DEFAULT 0,
                    discount_amount REAL DEFAULT 0,
                    nett_price REAL,
                    total_price REAL,
                    expiry_date DATE,
                    sell_price_retail REAL,
                    sell_price_wholesale REAL,
                    notes TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS stock (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    product_id INTEGER NOT NULL UNIQUE,
                    current_qty_base INTEGER DEFAULT 0,
                    last_restock_date DATE,
                    last_restock_qty INTEGER,
                    nearest_expiry DATE,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS stock_movements (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    product_id INTEGER NOT NULL,
                    movement_type TEXT NOT NULL,
                    quantity INTEGER NOT NULL,
                    reference_type TEXT,
                    reference_id INTEGER,
                    notes TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS customer_types (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT UNIQUE NOT NULL,
                    price_tier TEXT NOT NULL
                )",
                "CREATE TABLE IF NOT EXISTS customers (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    phone TEXT,
                    address TEXT,
                    type_id INTEGER,
                    notes TEXT,
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS sale_transactions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    invoice_number TEXT UNIQUE,
                    customer_id INTEGER,
                    sale_mode TEXT NOT NULL,
                    total_amount REAL DEFAULT 0,
                    payment_method TEXT DEFAULT 'Cash',
                    payment_status TEXT DEFAULT 'Lunas',
                    notes TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS sale_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    transaction_id INTEGER NOT NULL,
                    product_id INTEGER NOT NULL,
                    packaging_id INTEGER NOT NULL,
                    custom_name TEXT,
                    custom_unit TEXT,
                    quantity REAL NOT NULL,
                    unit_price REAL NOT NULL,
                    total_price REAL NOT NULL,
                    profit REAL DEFAULT 0
                )",
                "CREATE TABLE IF NOT EXISTS supplier_products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    supplier_id INTEGER NOT NULL,
                    product_id INTEGER NOT NULL,
                    sales_rep_id INTEGER,
                    last_purchase_date DATE,
                    last_buy_price REAL,
                    purchase_count INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS finance_accounts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    account_code TEXT UNIQUE NOT NULL,
                    account_name TEXT NOT NULL,
                    account_type TEXT NOT NULL,
                    category_id INTEGER,
                    description TEXT,
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS digi_products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    buyer_sku_code TEXT UNIQUE NOT NULL,
                    product_name TEXT NOT NULL,
                    category TEXT,
                    sub_category TEXT,
                    brand TEXT,
                    seller_name TEXT,
                    type TEXT DEFAULT 'prepaid',
                    seller_price REAL DEFAULT 0,
                    price REAL DEFAULT 0,
                    sell_price REAL DEFAULT 0,
                    buyer_product_status INTEGER DEFAULT 1,
                    seller_product_status INTEGER DEFAULT 1,
                    unlimited_stock INTEGER DEFAULT 1,
                    stock INTEGER DEFAULT 0,
                    multi INTEGER DEFAULT 0,
                    start_cut_off TEXT,
                    end_cut_off TEXT,
                    desc TEXT,
                    description TEXT,
                    is_active INTEGER DEFAULT 1,
                    is_custom_price INTEGER DEFAULT 0,
                    last_synced_at DATETIME,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS digi_transactions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ref_id TEXT UNIQUE NOT NULL,
                    buyer_sku_code TEXT NOT NULL,
                    customer_no TEXT NOT NULL,
                    customer_name TEXT,
                    product_name TEXT,
                    category TEXT,
                    brand TEXT,
                    type TEXT DEFAULT 'prepaid',
                    sell_price REAL DEFAULT 0,
                    modal_price REAL DEFAULT 0,
                    profit REAL DEFAULT 0,
                    status TEXT DEFAULT 'pending',
                    sn TEXT,
                    seller_name TEXT,
                    message TEXT,
                    digiflazz_trx_id TEXT,
                    payment_method TEXT DEFAULT 'cash',
                    paid_amount REAL DEFAULT 0,
                    change_amount REAL DEFAULT 0,
                    user_id INTEGER,
                    raw_response TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS digi_markup_rules (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    category TEXT UNIQUE,
                    brand TEXT,
                    markup_type TEXT DEFAULT 'fixed',
                    markup_value REAL DEFAULT 0,
                    min_price REAL DEFAULT 0,
                    max_price REAL DEFAULT 0,
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS digi_deposits (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    amount REAL DEFAULT 0,
                    bank TEXT,
                    owner_name TEXT,
                    status TEXT DEFAULT 'pending',
                    notes TEXT,
                    raw_response TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS app_settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_key TEXT UNIQUE NOT NULL,
                    setting_value TEXT,
                    setting_type TEXT DEFAULT 'string',
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    email TEXT UNIQUE NOT NULL,
                    phone TEXT,
                    password_hash TEXT NOT NULL,
                    user_level TEXT NOT NULL DEFAULT 'customer',
                    avatar_path TEXT,
                    is_active INTEGER DEFAULT 1,
                    work_days TEXT,
                    work_start TIME,
                    work_end TIME,
                    last_login_at DATETIME,
                    last_login_ip TEXT,
                    login_count INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS finance_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    log_date DATE NOT NULL,
                    period_yyyymm TEXT,
                    amount REAL NOT NULL,
                    balance_type TEXT,
                    category TEXT NOT NULL,
                    detail TEXT,
                    description TEXT,
                    reference_type TEXT,
                    reference_id INTEGER,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS finance_accounts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    type TEXT NOT NULL,
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS finance_categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    type TEXT NOT NULL,
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )"
            ];

            foreach ($sqls as $sql) {
                $this->pdo->exec($sql);
            }
            try { $this->pdo->exec("ALTER TABLE digi_products ADD COLUMN sub_category TEXT"); } catch (\Throwable $e) {}
            try { $this->pdo->exec("ALTER TABLE digi_products ADD COLUMN description TEXT"); } catch (\Throwable $e) {}
            try { $this->pdo->exec("ALTER TABLE digi_products ADD COLUMN last_synced_at TEXT"); } catch (\Throwable $e) {}
        } catch (\Exception $e) {
            error_log('[AlfarezMart] ensureSQLiteSchema warning: ' . $e->getMessage());
        }
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
     * Get PDO connection
     */
    public function getConnection(): \PDO
    {
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

    /**
     * Explicitly close PDO connection upon object destruction
     */
    public function __destruct()
    {
        $this->pdo = null;
    }
}

