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
    $controller = new ApiController();
    $startTime = microtime(true);
    // Simulate syncAllData
    $limit = 500;
    
    echo "Fetching products...\n";
    $productModel = new ProductModel();
    $productsResult = $productModel->getProductsWithPrices(1, 999999, '', null);
    
    echo "Fetching sales...\n";
    $saleModel = new SaleModel();
    $salesResult = $saleModel->getList(1, $limit);
    
    echo "Fetching suppliers...\n";
    $supplierModel = new SupplierModel();
    $suppliers = $supplierModel->getAllWithType();
    
    echo "Fetching purchases...\n";
    $purchaseModel = new PurchaseModel();
    $purchasesResult = $purchaseModel->getList(1, $limit);
    
    $endTime = microtime(true);
    echo "Success! Time taken: " . ($endTime - $startTime) . " seconds\n";
    
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
