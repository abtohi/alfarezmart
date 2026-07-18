<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
require_once APP_PATH . '/config/App.php';
require_once APP_PATH . '/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $amount = 500244;
    $amount_like = '%"amount":' . $amount . '%';
    
    $stmt = $db->prepare("
        UPDATE digi_deposits 
        SET status = 'success'
        WHERE status = 'pending' AND (
            amount = :amount OR 
            raw_response LIKE :amount_like
        )
        ORDER BY created_at ASC LIMIT 1
    ");
    $result = $stmt->execute([
        'amount' => $amount,
        'amount_like' => $amount_like
    ]);
    
    echo "Update result: " . ($result ? "true" : "false") . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
