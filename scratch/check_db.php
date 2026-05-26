<?php
define('BASE_PATH', dirname(__DIR__));
// Define STORAGE_PATH which is needed by App.php
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');
require 'app/config/App.php';
require 'app/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    print_r($db->query('DESCRIBE purchase_items')->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
