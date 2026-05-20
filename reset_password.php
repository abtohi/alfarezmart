<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', BASE_PATH . '/storage');
require 'app/config/App.php';
require 'app/core/Autoloader.php';
Autoloader::register();

$testPassword = 'test123';
$hashedPassword = password_hash($testPassword, PASSWORD_BCRYPT);

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('UPDATE users SET password_hash = :hash WHERE id = 1');
$stmt->execute([':hash' => $hashedPassword]);

echo "Updated test user password to: test123\n";
echo "Hash: " . substr($hashedPassword, 0, 20) . "...\n";
