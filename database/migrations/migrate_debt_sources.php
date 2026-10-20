<?php
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Create debt_sources table
    $db->exec("CREATE TABLE IF NOT EXISTS debt_sources (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Seed default data
    $sources = ['Bank Mandiri', 'Uang Tabungan', 'Pinjaman Orang Tua', 'Kas Keluarga'];
    $stmt = $db->prepare("INSERT IGNORE INTO debt_sources (name) VALUES (:name)");
    foreach ($sources as $name) {
        $stmt->execute([':name' => $name]);
    }
    
    // Check if shop_debts has debt_source_id
    try {
        $db->exec("ALTER TABLE shop_debts ADD COLUMN debt_source_id INT NULL AFTER supplier_id");
        $db->exec("ALTER TABLE shop_debts ADD FOREIGN KEY (debt_source_id) REFERENCES debt_sources(id) ON DELETE SET NULL");
        echo "Added debt_source_id column.\n";
    } catch (Exception $e) {
        echo "Column debt_source_id might already exist: " . $e->getMessage() . "\n";
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
