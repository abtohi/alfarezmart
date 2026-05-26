<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/app');
define('STORAGE_PATH', __DIR__ . '/storage');
$_SERVER['HTTP_HOST'] = 'localhost';
require APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require APP_PATH . '/config/App.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query('SELECT * FROM product_packagings WHERE level > 1 LIMIT 5');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
