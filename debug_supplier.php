<?php
/**
 * Debug script - test supplier query directly
 * Access: https://alfarezmart.com/debug_supplier.php?pid=PRODUCT_ID
 * DELETE THIS FILE after debugging!
 */
require_once __DIR__ . '/app/core/bootstrap.php';

$productId = (int)($_GET['pid'] ?? 0);
if (!$productId) {
    die(json_encode(['error' => 'Pass ?pid=PRODUCT_ID'], JSON_PRETTY_PRINT));
}

$errors = [];
$results = [];

// Get DB connection same way ApiController does
try {
    $db = \App\Core\Database::getInstance();
    $results['db_ok'] = true;
} catch (\Throwable $e) {
    die(json_encode(['error' => 'DB connection failed: ' . $e->getMessage()], JSON_PRETTY_PRINT));
}

// Test 1: Last purchase query (same as working one)
try {
    $stmt = $db->prepare("
        SELECT pu.purchase_date, pu.purchase_code, s.name as supplier_name, pi.buy_price, u.name as unit_name
        FROM purchase_items pi
        JOIN purchases pu ON pi.purchase_id = pu.id
        LEFT JOIN suppliers s ON pu.supplier_id = s.id
        LEFT JOIN product_packagings pp ON pi.packaging_id = pp.id
        LEFT JOIN units u ON pp.unit_id = u.id
        WHERE pi.product_id = :pid
        ORDER BY pu.purchase_date DESC, pu.id DESC
        LIMIT 1
    ");
    $stmt->execute([':pid' => $productId]);
    $results['last_purchase'] = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    $errors['last_purchase'] = $e->getMessage();
}

// Test 2: Get distinct supplier IDs from purchases
try {
    $stmt = $db->prepare("
        SELECT DISTINCT pu.supplier_id
        FROM purchase_items pi
        JOIN purchases pu ON pi.purchase_id = pu.id
        WHERE pi.product_id = :pid AND pu.supplier_id IS NOT NULL AND pu.supplier_id > 0
    ");
    $stmt->execute([':pid' => $productId]);
    $results['supplier_ids_from_purchases'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (\Throwable $e) {
    $errors['supplier_ids_from_purchases'] = $e->getMessage();
}

// Test 3: Get distinct supplier IDs from supplier_products
try {
    $stmt = $db->prepare("
        SELECT DISTINCT supplier_id
        FROM supplier_products
        WHERE product_id = :pid AND supplier_id IS NOT NULL AND supplier_id > 0
    ");
    $stmt->execute([':pid' => $productId]);
    $results['supplier_ids_from_supplier_products'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (\Throwable $e) {
    $errors['supplier_ids_from_supplier_products'] = $e->getMessage();
}

// Test 4: Check suppliers table - does 'phone' column exist?
try {
    $stmt = $db->query("DESCRIBE suppliers");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $results['suppliers_columns'] = $cols;
} catch (\Throwable $e) {
    $errors['suppliers_describe'] = $e->getMessage();
}

// Test 5: If we got supplier IDs, try fetching supplier info
$supplierIds = array_merge(
    $results['supplier_ids_from_purchases'] ?? [],
    $results['supplier_ids_from_supplier_products'] ?? []
);
$supplierIds = array_unique(array_map('intval', $supplierIds));

if (!empty($supplierIds)) {
    foreach ($supplierIds as $sid) {
        // Test 5a: fetch with phone column
        try {
            $stmt = $db->prepare("SELECT id, name, address, notes, phone FROM suppliers WHERE id = :sid");
            $stmt->execute([':sid' => $sid]);
            $results['supplier_info'][$sid] = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $errors["supplier_info_$sid"] = $e->getMessage();

            // Try without phone column
            try {
                $stmt = $db->prepare("SELECT id, name, address, notes FROM suppliers WHERE id = :sid");
                $stmt->execute([':sid' => $sid]);
                $results['supplier_info_nophone'][$sid] = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (\Throwable $e2) {
                $errors["supplier_info_nophone_$sid"] = $e2->getMessage();
            }
        }

        // Test 5b: fetch purchase history
        try {
            $stmt = $db->prepare("
                SELECT pu.id as purchase_id, pu.purchase_code, pu.purchase_date,
                       pi.quantity, pi.buy_price as item_buy_price
                FROM purchase_items pi
                JOIN purchases pu ON pi.purchase_id = pu.id
                WHERE pi.product_id = :pid AND pu.supplier_id = :sid
                ORDER BY pu.purchase_date DESC
                LIMIT 5
            ");
            $stmt->execute([':pid' => $productId, ':sid' => $sid]);
            $results['purchase_history'][$sid] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $errors["purchase_history_$sid"] = $e->getMessage();
        }
    }
}

header('Content-Type: application/json');
echo json_encode([
    'product_id' => $productId,
    'errors' => $errors,
    'results' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
