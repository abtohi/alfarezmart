<?php
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';

$salesRepModel = new SalesRepModel();
$reps = $salesRepModel->getAllWithSupplier();
echo json_encode($reps, JSON_PRETTY_PRINT) . "\n";
?>
