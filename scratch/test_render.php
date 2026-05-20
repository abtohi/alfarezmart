<?php
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';

// Mock variables passed by Controller view() method
$csrfToken = "mock_csrf_token";
$title = "Input Barang Masuk";
$activeNav = "purchase";

$supplierModel = new SupplierModel();
$salesRepModel = new SalesRepModel();

$salesReps = $salesRepModel->getAllWithSupplier();
$suppliers = $supplierModel->all('name', 'ASC');

// Enable output buffering to capture output
ob_start();
try {
    require APP_PATH . '/views/purchases/create.php';
} catch (Throwable $e) {
    echo "ERROR during require: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
$output = ob_get_clean();

// Check if there are any PHP notices/warnings in the output
if (preg_match('/(Notice|Warning|Error|Fatal):/i', $output, $matches)) {
    echo "Found PHP messages in output: \n";
    // Print lines containing notice/warning
    $lines = explode("\n", $output);
    foreach ($lines as $i => $line) {
        if (preg_match('/(Notice|Warning|Error|Fatal)/i', $line)) {
            echo "Line " . ($i + 1) . ": " . strip_tags($line) . "\n";
        }
    }
} else {
    echo "No PHP notices/warnings detected in rendered output!\n";
}

// Save output to scratch/rendered.html for manual inspection
file_put_contents(__DIR__ . '/rendered.html', $output);
echo "Rendered HTML saved to scratch/rendered.html\n";
?>
