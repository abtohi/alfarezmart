<?php
$token = $_GET['t'] ?? '';
if ($token !== 'alfarez2024debug') { http_response_code(403); die('Forbidden'); }

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');

require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';
require_once APP_PATH . '/config/Database.php';

// Mock session and auth to render the view
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'admin',
    'level' => 'admin'
];

$supplierModel = new SupplierModel();
$salesRepModel = new SalesRepModel();
$salesReps = $salesRepModel->getAllWithSupplier();
$suppliers = $supplierModel->all('name', 'ASC');

ob_start();
extract([
    'csrfToken' => 'dummy',
    'salesReps' => $salesReps,
    'suppliers' => $suppliers,
]);
require APP_PATH . '/views/purchases/create.php';
$html = ob_get_clean();

// Check if salesRepsOptions is correctly formed
preg_match('/const salesRepsOptions = (\[.*?\]);/s', $html, $matches);
$salesRepsOptionsStr = $matches[1] ?? 'NOT FOUND';

// Look for potential syntax errors in the JSON
$jsonError = null;
if ($salesRepsOptionsStr !== 'NOT FOUND') {
    // try to parse the array using a simple check or return it
    // Note: It's valid JS but maybe not valid JSON (because keys aren't quoted)
}

header('Content-Type: application/json');
echo json_encode([
    'sales_reps_count' => count($salesReps),
    'suppliers_count' => count($suppliers),
    'html_length' => strlen($html),
    'salesRepsOptions' => substr($salesRepsOptionsStr, 0, 1000) . '...' // snippet
]);
