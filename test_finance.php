<?php
require_once 'app/init.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM finance_logs ORDER BY id DESC LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
