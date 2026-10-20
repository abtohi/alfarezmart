<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');
require_once __DIR__ . '/app/config/App.php';
require_once __DIR__ . '/app/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "Starting Deposit Migration...\n";

    $db->exec("CREATE TABLE IF NOT EXISTS digi_deposits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        amount DECIMAL(15,2) NOT NULL,
        bank VARCHAR(50) NOT NULL,
        owner_name VARCHAR(100) NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        notes TEXT,
        raw_response JSON,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_date (created_at)
    )");

    echo "Table 'digi_deposits' created or already exists.\n";
    echo "Migration successful!\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
