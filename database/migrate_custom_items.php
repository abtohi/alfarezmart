<?php
/**
 * Migration: Add custom_name and custom_unit to sale_items
 * Also ensure CUSTOM placeholder product and packaging exist.
 * Run once: php database/migrate_custom_items.php
 */
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';

$db = Database::getInstance()->getConnection();

// 1. Add custom_name column
try {
    $db->exec("ALTER TABLE sale_items ADD COLUMN custom_name VARCHAR(255) NULL AFTER packaging_id");
    echo "✅ Added column: custom_name\n";
} catch (Exception $e) {
    echo "ℹ️  custom_name: " . $e->getMessage() . "\n";
}

// 2. Add custom_unit column
try {
    $db->exec("ALTER TABLE sale_items ADD COLUMN custom_unit VARCHAR(50) NULL AFTER custom_name");
    echo "✅ Added column: custom_unit\n";
} catch (Exception $e) {
    echo "ℹ️  custom_unit: " . $e->getMessage() . "\n";
}

// 3. Verify columns
$cols = $db->query("SHOW COLUMNS FROM sale_items")->fetchAll(PDO::FETCH_COLUMN);
echo "sale_items columns: " . implode(", ", $cols) . "\n";

// 4. Ensure a default unit "Pcs" exists (needed for placeholder product)
$unitRow = $db->query("SELECT id FROM units WHERE name='Pcs' OR name='pcs' LIMIT 1")->fetch();
if (!$unitRow) {
    $db->exec("INSERT INTO units (name, abbreviation) VALUES ('Pcs','pcs')");
    $unitId = $db->lastInsertId();
    echo "✅ Created unit: Pcs (id={$unitId})\n";
} else {
    $unitId = $unitRow['id'];
    echo "ℹ️  Unit Pcs exists (id={$unitId})\n";
}

// 5. Ensure CUSTOM placeholder product exists
$prodRow = $db->query("SELECT id FROM products WHERE code='CUSTOM' LIMIT 1")->fetch();
if (!$prodRow) {
    $stmt = $db->prepare("INSERT INTO products (code, full_name, short_label, invoice_name, is_active) VALUES ('CUSTOM', 'Barang Custom', 'Custom', 'Custom', 1)");
    $stmt->execute();
    $productId = $db->lastInsertId();
    echo "✅ Created placeholder product (id={$productId})\n";
} else {
    $productId = $prodRow['id'];
    echo "ℹ️  Placeholder product exists (id={$productId})\n";
}

// 6. Ensure placeholder product has a stock row
$stockRow = $db->query("SELECT id FROM stock WHERE product_id={$productId} LIMIT 1")->fetch();
if (!$stockRow) {
    $db->exec("INSERT INTO stock (product_id, current_qty_base) VALUES ({$productId}, 999999)");
    echo "✅ Created stock row for placeholder product\n";
} else {
    echo "ℹ️  Stock row for placeholder product exists\n";
}

// 7. Ensure CUSTOM placeholder packaging exists (level 1)
$stmt = $db->prepare("SELECT id FROM product_packagings WHERE product_id=:pid AND level=1 LIMIT 1");
$stmt->execute([':pid' => $productId]);
$pkgRow = $stmt->fetch();
if (!$pkgRow) {
    $stmt2 = $db->prepare("INSERT INTO product_packagings (product_id, level, unit_id, contained_qty, base_qty, barcode, buy_price, sell_price_retail, sell_price_wholesale) VALUES (:pid, 1, :uid, 1, 1, 'CUSTOM', 0, 0, 0)");
    $stmt2->execute([':pid' => $productId, ':uid' => $unitId]);
    $packagingId = $db->lastInsertId();
    echo "✅ Created placeholder packaging (id={$packagingId})\n";
} else {
    $packagingId = $pkgRow['id'];
    echo "ℹ️  Placeholder packaging exists (id={$packagingId})\n";
}

echo "\n🎉 Migration complete! product_id={$productId}, packaging_id={$packagingId}\n";
