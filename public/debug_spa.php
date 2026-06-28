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

header('Content-Type: application/json');
try {
    $db = Database::getInstance()->getConnection();
    
    // Simulate the API Controller
    $supplierId = 933; // From our previous check
    $stmt = $db->prepare("
        SELECT
            pi.product_id,
            p.full_name AS product_name,
            p.short_label,
            cat.name AS category_name,
            (SELECT pi2.buy_price
             FROM purchase_items pi2
             JOIN purchases pu2 ON pi2.purchase_id = pu2.id
             WHERE pi2.product_id = pi.product_id AND pu2.supplier_id = :sid2
             ORDER BY pu2.purchase_date DESC, pu2.id DESC LIMIT 1
            ) AS selected_buy_price,
            (SELECT pkg2.base_qty
             FROM purchase_items pi2
             JOIN purchases pu2 ON pi2.purchase_id = pu2.id
             JOIN product_packagings pkg2 ON pi2.packaging_id = pkg2.id
             WHERE pi2.product_id = pi.product_id AND pu2.supplier_id = :sid3
             ORDER BY pu2.purchase_date DESC, pu2.id DESC LIMIT 1
            ) AS selected_base_qty,
            (SELECT u2.name
             FROM purchase_items pi2
             JOIN purchases pu2 ON pi2.purchase_id = pu2.id
             JOIN product_packagings pkg2 ON pi2.packaging_id = pkg2.id
             JOIN units u2 ON pkg2.unit_id = u2.id
             WHERE pi2.product_id = pi.product_id AND pu2.supplier_id = :sid4
             ORDER BY pu2.purchase_date DESC, pu2.id DESC LIMIT 1
            ) AS selected_unit_name,
            (SELECT pu2.purchase_date
             FROM purchases pu2
             JOIN purchase_items pi2 ON pi2.purchase_id = pu2.id
             WHERE pi2.product_id = pi.product_id AND pu2.supplier_id = :sid5
             ORDER BY pu2.purchase_date DESC, pu2.id DESC LIMIT 1
            ) AS selected_last_date,
            (SELECT MIN(pi3.buy_price / pkg3.base_qty)
             FROM purchase_items pi3
             JOIN purchases pu3 ON pi3.purchase_id = pu3.id
             JOIN product_packagings pkg3 ON pi3.packaging_id = pkg3.id
             WHERE pi3.product_id = pi.product_id
            ) AS min_norm_price
        FROM purchase_items pi
        JOIN purchases pu ON pi.purchase_id = pu.id
        JOIN products p ON pi.product_id = p.id
        LEFT JOIN categories cat ON p.category_id = cat.id
        WHERE pu.supplier_id = :sid
        GROUP BY pi.product_id
    ");
    
    $stmt->execute([
        ':sid' => $supplierId,
        ':sid2' => $supplierId,
        ':sid3' => $supplierId,
        ':sid4' => $supplierId,
        ':sid5' => $supplierId
    ]);
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'count' => count($products), 'data' => $products]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
