<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
require_once APP_PATH . '/core/Database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE product_packagings");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
    echo $r['Field'] . " - " . $r['Type'] . "\n";
}
