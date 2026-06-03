<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';

try {
    $model = new FinanceModel();
    $date = date('Y-m-d');
    echo "Testing getDailySummary...\n";
    $summary = $model->getDailySummary($date);
    print_r($summary);
    
    echo "\nTesting getDailySummaryByPost...\n";
    $breakdown = $model->getDailySummaryByPost($date);
    print_r($breakdown);
    
    echo "\nTesting getLogsByDate...\n";
    $logs = $model->getLogsByDate($date);
    echo "Found " . count($logs) . " logs.\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
