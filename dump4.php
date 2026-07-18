<?php
require 'app/config/App.php';
require 'app/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, amount, status FROM digi_deposits ORDER BY id DESC LIMIT 5");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
