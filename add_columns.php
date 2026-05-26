<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', BASE_PATH . '/storage');
$_SERVER['HTTP_HOST'] = 'localhost';
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';

try {
    $db = Database::getInstance()->getConnection();
    $db->exec("ALTER TABLE product_packagings ADD COLUMN ppn_pct DECIMAL(5,2) DEFAULT 0");
    $db->exec("ALTER TABLE product_packagings ADD COLUMN discount_mode VARCHAR(10) DEFAULT 'rp'");
    $db->exec("ALTER TABLE product_packagings ADD COLUMN discount_value DECIMAL(12,2) DEFAULT 0");
    echo 'Columns added successfully';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
