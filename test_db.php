<?php
define('BASE_PATH', __DIR__);
require_once 'app/config/App.php';
require_once 'app/config/Database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query('DESCRIBE product_packaging');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
