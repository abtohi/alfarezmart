<?php
/**
 * Migration script for order estimates
 */
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');
$_SERVER['HTTP_HOST'] = 'localhost';
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Create order_estimates
    $db->exec("CREATE TABLE IF NOT EXISTS order_estimates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        supplier_id INT NULL,
        total_amount DECIMAL(15,2) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Created table order_estimates\n";
    
    // Create order_estimate_items
    $db->exec("CREATE TABLE IF NOT EXISTS order_estimate_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        estimate_id INT NOT NULL,
        product_id INT NULL,
        packaging_id INT NULL,
        product_name VARCHAR(255) NOT NULL,
        unit_name VARCHAR(50) NOT NULL,
        quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
        buy_price DECIMAL(12,2) NOT NULL DEFAULT 0,
        total_price DECIMAL(15,2) NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (estimate_id) REFERENCES order_estimates(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
        FOREIGN KEY (packaging_id) REFERENCES product_packagings(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Created table order_estimate_items\n";
    
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
