<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', BASE_PATH . '/storage');
require 'app/config/App.php';
require 'app/core/Autoloader.php';
Autoloader::register();

$db = Database::getInstance()->getConnection();
$users = $db->query('SELECT id, email, phone, name FROM users')->fetchAll();
echo "Users found: " . count($users) . "\n";
if (!empty($users)) {
    print_r($users);
} else {
    echo "No users found. Creating test user...\n";
}
