<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
require_once APP_PATH . '/config/App.php';
require_once APP_PATH . '/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, amount, status, notes, raw_response FROM digi_deposits ORDER BY id DESC LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $row) {
        echo "ID: " . $row['id'] . "\n";
        echo "Amount (DB): " . $row['amount'] . "\n";
        echo "Status: " . $row['status'] . "\n";
        echo "Raw: " . $row['raw_response'] . "\n\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
