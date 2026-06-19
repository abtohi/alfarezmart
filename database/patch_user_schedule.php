<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');
require_once __DIR__ . '/../app/core/Autoloader.php';
require_once __DIR__ . '/../app/config/App.php';
Autoloader::register();

$db = Database::getInstance()->getConnection();

try {
    echo "Adding schedule columns to users table...\n";
    $db->exec("ALTER TABLE users 
        ADD COLUMN work_days VARCHAR(255) NULL AFTER is_active,
        ADD COLUMN work_start TIME NULL AFTER work_days,
        ADD COLUMN work_end TIME NULL AFTER work_start
    ");
    echo "Columns added successfully!\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Columns already exist.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
