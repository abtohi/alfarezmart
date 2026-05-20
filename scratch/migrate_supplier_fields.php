<?php
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if supplier_product_code already exists
    $check = $db->query("SHOW COLUMNS FROM products LIKE 'supplier_product_code'");
    if ($check->rowCount() == 0) {
        $db->exec("ALTER TABLE products ADD COLUMN supplier_product_code VARCHAR(100) NULL AFTER invoice_name");
        echo "✅ Added supplier_product_code column\n";
    } else {
        echo "ℹ️ supplier_product_code column already exists\n";
    }
    
    // Check if supplier_invoice_name already exists
    $check = $db->query("SHOW COLUMNS FROM products LIKE 'supplier_invoice_name'");
    if ($check->rowCount() == 0) {
        $db->exec("ALTER TABLE products ADD COLUMN supplier_invoice_name VARCHAR(255) NULL AFTER supplier_product_code");
        echo "✅ Added supplier_invoice_name column\n";
    } else {
        echo "ℹ️ supplier_invoice_name column already exists\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
