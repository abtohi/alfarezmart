<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
require_once APP_PATH . '/config/App.php';
require_once APP_PATH . '/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to DB\n";
    $stmt = $db->query('DESCRIBE digi_deposits');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
