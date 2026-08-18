<?php
/**
 * Sync MySQL data to SQLite local fallback database.
 * This ensures that whenever MySQL goes offline or hits connection limits,
 * the local SQLite database still has full product data and search works seamlessly!
 */
define('BASE_PATH', 'C:/xampp/htdocs/AlfarezMart');
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');
require BASE_PATH . '/app/config/App.php';

$host   = defined('DB_HOST')     ? DB_HOST     : '153.92.15.83';
$port   = defined('DB_PORT')     ? DB_PORT     : '3306';
$dbname = defined('DB_DATABASE') ? DB_DATABASE : 'u573283697_alfarezmart';
$user   = defined('DB_USERNAME') ? DB_USERNAME : 'u573283697_alfarez';
$pass   = defined('DB_PASSWORD') ? DB_PASSWORD : '';

$mysqlDsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
$mysqlPdo = new PDO($mysqlDsn, $user, $pass, [
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$sqlitePath = STORAGE_PATH . '/database/alfarezmart.sqlite';
$sqlitePdo = new PDO("sqlite:$sqlitePath");
$sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlitePdo->exec('PRAGMA journal_mode=WAL;');
$sqlitePdo->exec('PRAGMA synchronous=NORMAL;');

echo "Starting sync from MySQL to SQLite...\n";

// 1. Sync brands
echo "Syncing brands...\n";
$brands = $mysqlPdo->query("SELECT * FROM brands")->fetchAll();
$sqlitePdo->exec("DELETE FROM brands");
$stmt = $sqlitePdo->prepare("INSERT INTO brands (id, name, slug, created_at, updated_at) VALUES (:id, :name, :slug, :created_at, :updated_at)");
$sqlitePdo->beginTransaction();
foreach ($brands as $b) {
    $stmt->execute([
        ':id' => $b['id'],
        ':name' => $b['name'],
        ':slug' => $b['slug'] ?? null,
        ':created_at' => $b['created_at'] ?? null,
        ':updated_at' => $b['updated_at'] ?? null,
    ]);
}
$sqlitePdo->commit();
echo "Synced " . count($brands) . " brands.\n";

// 2. Sync categories
echo "Syncing categories...\n";
$categories = $mysqlPdo->query("SELECT * FROM categories")->fetchAll();
$sqlitePdo->exec("DELETE FROM categories");
$stmt = $sqlitePdo->prepare("INSERT INTO categories (id, name, slug, icon, sort_order, is_active, created_at) VALUES (:id, :name, :slug, :icon, :sort_order, :is_active, :created_at)");
$sqlitePdo->beginTransaction();
foreach ($categories as $c) {
    $stmt->execute([
        ':id' => $c['id'],
        ':name' => $c['name'],
        ':slug' => $c['slug'] ?? null,
        ':icon' => $c['icon'] ?? null,
        ':sort_order' => $c['sort_order'] ?? 0,
        ':is_active' => $c['is_active'] ?? 1,
        ':created_at' => $c['created_at'] ?? null,
    ]);
}
$sqlitePdo->commit();
echo "Synced " . count($categories) . " categories.\n";

// 3. Sync units
echo "Syncing units...\n";
$units = $mysqlPdo->query("SELECT * FROM units")->fetchAll();
$sqlitePdo->exec("DELETE FROM units");
$stmt = $sqlitePdo->prepare("INSERT INTO units (id, name, abbreviation, created_at) VALUES (:id, :name, :abbreviation, :created_at)");
$sqlitePdo->beginTransaction();
foreach ($units as $u) {
    $stmt->execute([
        ':id' => $u['id'],
        ':name' => $u['name'],
        ':abbreviation' => $u['abbreviation'] ?? null,
        ':created_at' => $u['created_at'] ?? null,
    ]);
}
$sqlitePdo->commit();
echo "Synced " . count($units) . " units.\n";

// 4. Sync products
echo "Syncing products...\n";
$products = $mysqlPdo->query("SELECT id, code, brand_id, category_id, product_type, variant, full_name, short_label, invoice_name, supplier_product_code, supplier_invoice_name, weight_value, weight_unit, description, image_path, min_stock, max_stock, is_active, is_available, is_multivariant, is_custom_label, ref_product_id, created_at, updated_at FROM products")->fetchAll();
$sqlitePdo->exec("DELETE FROM products");
$stmt = $sqlitePdo->prepare("INSERT INTO products (id, code, brand_id, category_id, product_type, variant, full_name, short_label, invoice_name, supplier_product_code, supplier_invoice_name, weight_value, weight_unit, description, image_path, min_stock, max_stock, is_active, is_available, is_multivariant, is_custom_label, ref_product_id, created_at, updated_at) VALUES (:id, :code, :brand_id, :category_id, :product_type, :variant, :full_name, :short_label, :invoice_name, :supplier_product_code, :supplier_invoice_name, :weight_value, :weight_unit, :description, :image_path, :min_stock, :max_stock, :is_active, :is_available, :is_multivariant, :is_custom_label, :ref_product_id, :created_at, :updated_at)");
$sqlitePdo->beginTransaction();
foreach ($products as $p) {
    $stmt->execute([
        ':id' => $p['id'],
        ':code' => $p['code'],
        ':brand_id' => $p['brand_id'],
        ':category_id' => $p['category_id'],
        ':product_type' => $p['product_type'],
        ':variant' => $p['variant'],
        ':full_name' => $p['full_name'],
        ':short_label' => $p['short_label'],
        ':invoice_name' => $p['invoice_name'],
        ':supplier_product_code' => $p['supplier_product_code'],
        ':supplier_invoice_name' => $p['supplier_invoice_name'],
        ':weight_value' => $p['weight_value'],
        ':weight_unit' => $p['weight_unit'],
        ':description' => $p['description'],
        ':image_path' => $p['image_path'],
        ':min_stock' => $p['min_stock'] ?? 0,
        ':max_stock' => $p['max_stock'] ?? null,
        ':is_active' => $p['is_active'] ?? 1,
        ':is_available' => $p['is_available'] ?? 1,
        ':is_multivariant' => $p['is_multivariant'] ?? 0,
        ':is_custom_label' => $p['is_custom_label'] ?? 0,
        ':ref_product_id' => $p['ref_product_id'] ?? null,
        ':created_at' => $p['created_at'] ?? null,
        ':updated_at' => $p['updated_at'] ?? null,
    ]);
}
$sqlitePdo->commit();
echo "Synced " . count($products) . " products.\n";

// 5. Sync product_packagings
echo "Syncing product_packagings...\n";
$packagings = $mysqlPdo->query("SELECT id, product_id, level, unit_id, contained_qty, base_qty, barcode, buy_price, sell_price_retail, margin_retail, sell_price_wholesale, margin_wholesale, is_default_scan, created_at FROM product_packagings")->fetchAll();
$sqlitePdo->exec("DELETE FROM product_packagings");
$stmt = $sqlitePdo->prepare("INSERT INTO product_packagings (id, product_id, level, unit_id, contained_qty, base_qty, barcode, buy_price, sell_price_retail, margin_retail, sell_price_wholesale, margin_wholesale, is_default_scan, created_at) VALUES (:id, :product_id, :level, :unit_id, :contained_qty, :base_qty, :barcode, :buy_price, :sell_price_retail, :margin_retail, :sell_price_wholesale, :margin_wholesale, :is_default_scan, :created_at)");
$sqlitePdo->beginTransaction();
foreach ($packagings as $pkg) {
    $stmt->execute([
        ':id' => $pkg['id'],
        ':product_id' => $pkg['product_id'],
        ':level' => $pkg['level'],
        ':unit_id' => !empty($pkg['unit_id']) ? (int)$pkg['unit_id'] : 1,
        ':contained_qty' => $pkg['contained_qty'] ?? 1,
        ':base_qty' => $pkg['base_qty'] ?? 1,
        ':barcode' => $pkg['barcode'],
        ':buy_price' => $pkg['buy_price'] ?? 0,
        ':sell_price_retail' => $pkg['sell_price_retail'] ?? 0,
        ':margin_retail' => $pkg['margin_retail'] ?? 0,
        ':sell_price_wholesale' => $pkg['sell_price_wholesale'] ?? 0,
        ':margin_wholesale' => $pkg['margin_wholesale'] ?? 0,
        ':is_default_scan' => $pkg['is_default_scan'] ?? 0,
        ':created_at' => $pkg['created_at'] ?? null,
    ]);
}
$sqlitePdo->commit();
echo "Synced " . count($packagings) . " packagings.\n";

// 6. Sync product_qty_prices (tier prices)
echo "Syncing product_qty_prices...\n";
try {
    $qtyPrices = $mysqlPdo->query("SELECT id, packaging_id, min_qty, unit_price, sale_mode, label, sort_order, created_at FROM product_qty_prices")->fetchAll();
    $sqlitePdo->exec("DELETE FROM product_qty_prices");
    $stmt = $sqlitePdo->prepare("INSERT INTO product_qty_prices (id, packaging_id, min_qty, unit_price, sale_mode, label, sort_order, created_at) VALUES (:id, :packaging_id, :min_qty, :unit_price, :sale_mode, :label, :sort_order, :created_at)");
    $sqlitePdo->beginTransaction();
    foreach ($qtyPrices as $qp) {
        $stmt->execute([
            ':id' => $qp['id'],
            ':packaging_id' => $qp['packaging_id'],
            ':min_qty' => $qp['min_qty'],
            ':unit_price' => $qp['unit_price'],
            ':sale_mode' => $qp['sale_mode'] ?? 'both',
            ':label' => $qp['label'] ?? null,
            ':sort_order' => $qp['sort_order'] ?? 0,
            ':created_at' => $qp['created_at'] ?? null,
        ]);
    }
    $sqlitePdo->commit();
    echo "Synced " . count($qtyPrices) . " qty prices.\n";
} catch (Exception $e) {
    echo "Qty prices sync notice: " . $e->getMessage() . "\n";
}

// 7. Sync stock
echo "Syncing stock...\n";
try {
    $stock = $mysqlPdo->query("SELECT id, product_id, current_qty_base, last_restock_date, last_restock_qty, nearest_expiry, updated_at FROM stock")->fetchAll();
    $sqlitePdo->exec("DELETE FROM stock");
    $stmt = $sqlitePdo->prepare("INSERT INTO stock (id, product_id, current_qty_base, last_restock_date, last_restock_qty, nearest_expiry, updated_at) VALUES (:id, :product_id, :current_qty_base, :last_restock_date, :last_restock_qty, :nearest_expiry, :updated_at)");
    $sqlitePdo->beginTransaction();
    foreach ($stock as $s) {
        $stmt->execute([
            ':id' => $s['id'],
            ':product_id' => $s['product_id'],
            ':current_qty_base' => $s['current_qty_base'] ?? 0,
            ':last_restock_date' => $s['last_restock_date'] ?? null,
            ':last_restock_qty' => $s['last_restock_qty'] ?? null,
            ':nearest_expiry' => $s['nearest_expiry'] ?? null,
            ':updated_at' => $s['updated_at'] ?? null,
        ]);
    }
    $sqlitePdo->commit();
    echo "Synced " . count($stock) . " stock rows.\n";
} catch (Exception $e) {
    echo "Stock sync notice: " . $e->getMessage() . "\n";
}

echo "Sync completed successfully!\n";
