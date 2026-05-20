<?php
/**
 * Migrasi: tabel harga spesial per kuantitas
 * CLI: php database/migrate_qty_prices.php
 */
define('BASE_PATH', dirname(__DIR__));
define('STORAGE_PATH', BASE_PATH . '/public/storage');
$_SERVER['HTTP_HOST'] = 'localhost';
require BASE_PATH . '/app/config/App.php';
require BASE_PATH . '/app/config/Database.php';

$db = Database::getInstance()->getConnection();
$db->exec("CREATE TABLE IF NOT EXISTS product_qty_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    packaging_id INT NOT NULL,
    min_qty DECIMAL(10,2) NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    sale_mode VARCHAR(10) NOT NULL DEFAULT 'both',
    label VARCHAR(100) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (packaging_id) REFERENCES product_packagings(id) ON DELETE CASCADE,
    INDEX idx_pqp_packaging (packaging_id),
    INDEX idx_pqp_min_qty (packaging_id, min_qty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "OK: product_qty_prices siap.\n";
