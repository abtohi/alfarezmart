<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';
$db = Database::getInstance()->getConnection();
try {
    $db->exec("ALTER TABLE product_packagings MODIFY contained_qty DECIMAL(10,3) DEFAULT 1");
    $db->exec("ALTER TABLE product_packagings MODIFY base_qty DECIMAL(12,3) DEFAULT 1");
    $db->exec("ALTER TABLE stock MODIFY current_qty_base DECIMAL(12,3) DEFAULT 0");
    $db->exec("ALTER TABLE stock MODIFY last_restock_qty DECIMAL(12,3)");
    $db->exec("ALTER TABLE stock_movements MODIFY quantity DECIMAL(12,3) NOT NULL");
    $db->exec("ALTER TABLE consignment_items MODIFY quantity DECIMAL(10,3) NOT NULL");
    $db->exec("ALTER TABLE consignment_items MODIFY qty_sold DECIMAL(10,3) DEFAULT 0");
    $db->exec("ALTER TABLE consignment_items MODIFY qty_returned DECIMAL(10,3) DEFAULT 0");
    echo "DB Updated successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
