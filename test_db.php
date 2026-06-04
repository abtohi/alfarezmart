<?php
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/models/PurchaseModel.php';
require_once __DIR__ . '/app/models/ProductModel.php';

$pm = new PurchaseModel();
$prod = new ProductModel();

// Get recent purchases to test
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id FROM purchases ORDER BY id DESC LIMIT 1");
$purchase = $stmt->fetch();

if ($purchase) {
    $id = $purchase['id'];
    $details = $pm->getDetails($id);
    echo "Purchase ID: $id\n";
    foreach ($details['items'] as $item) {
        echo "Item: {$item['product_name']} (Product ID: {$item['product_id']})\n";
        echo "Original Level in purchase_items: '{$item['level']}'\n";
        $packagings = $prod->getPackagings($item['product_id']);
        echo "Available levels in DB: ";
        foreach ($packagings as $p) {
            echo "'" . $p['level'] . "' ";
        }
        echo "\n";
    }
} else {
    echo "No purchase found.\n";
}
