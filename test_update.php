<?php
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/models/DigiflazzModel.php';

$model = new DigiflazzModel();

$refId = 'TEST-' . time();
$status = 'failed';
$message = 'Test Gagal';

// Insert a fake pending transaction
$db = Database::getInstance()->getConnection();
$db->exec("INSERT INTO digi_transactions (ref_id, buyer_sku_code, customer_no, category, product_name, sell_price, modal_price, status) VALUES ('$refId', 'test', '08123', 'Pulsa', 'Test', 1000, 1000, 'pending')");

echo "Inserted $refId as pending.\n";

$result = $model->updateTransactionStatus($refId, $status, $message, '', 'trx-123', ['tele' => '@test']);

if ($result) {
    echo "Successfully updated to failed.\n";
} else {
    echo "Failed to update!\n";
}

$stmt = $db->query("SELECT status, message, raw_response FROM digi_transactions WHERE ref_id = '$refId'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($row);
