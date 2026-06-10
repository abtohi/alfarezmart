<?php
$_SERVER['HTTP_HOST'] = 'localhost';
define('BASE_PATH', __DIR__ . '/');
define('STORAGE_PATH', BASE_PATH . 'storage/');
require_once BASE_PATH . 'app/config/App.php';
require_once BASE_PATH . 'app/core/Database.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE products");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hasSupplierInvoiceName = false;
foreach ($columns as $col) {
    if ($col['Field'] === 'supplier_invoice_name') {
        $hasSupplierInvoiceName = true;
    }
}
echo "supplier_invoice_name exists: " . ($hasSupplierInvoiceName ? 'Yes' : 'No') . "\n";
