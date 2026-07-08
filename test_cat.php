<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('DB_DRIVER', 'mysql');
define('DB_HOST', '153.92.15.83');
define('DB_DATABASE', 'u573283697_alfarezmart');
define('DB_USERNAME', 'u573283697_alfarez');
define('DB_PASSWORD', 'ba5rRwhkKmM&b');

require APP_PATH . '/config/Database.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT category, COUNT(*) as c FROM digi_products GROUP BY category");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
