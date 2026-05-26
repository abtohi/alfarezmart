<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/app');
define('STORAGE_PATH', __DIR__ . '/storage');
$_SERVER['HTTP_HOST'] = 'localhost';
require APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require APP_PATH . '/config/App.php';
$productModel = new ProductModel();
$productsResult = $productModel->getProductsWithPrices(1, 999999, '', null);
$found = false;
foreach ($productsResult['data'] as $p) {
    if (!empty($p['packagings']) && count($p['packagings']) > 1) {
        print_r($p['packagings']);
        $found = true;
        break;
    }
}
if (!$found) echo "No product with > 1 packaging found.";
