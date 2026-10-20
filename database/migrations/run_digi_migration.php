<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');
require_once __DIR__ . '/app/config/App.php';
require_once __DIR__ . '/app/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Create digi_products table
    $db->exec("CREATE TABLE IF NOT EXISTS digi_products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        buyer_sku_code VARCHAR(100) NOT NULL UNIQUE,
        product_name VARCHAR(255) NOT NULL,
        category VARCHAR(50) NOT NULL,
        brand VARCHAR(100),
        type ENUM('prepaid','postpaid') DEFAULT 'prepaid',
        seller_price DECIMAL(15,2) DEFAULT 0,
        sell_price DECIMAL(15,2) DEFAULT 0,
        markup DECIMAL(15,2) DEFAULT 0,
        buyer_product_status TINYINT(1) DEFAULT 1,
        seller_product_status TINYINT(1) DEFAULT 1,
        description TEXT,
        start_cut_off VARCHAR(10),
        end_cut_off VARCHAR(10),
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        last_synced_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category (category),
        INDEX idx_brand (brand),
        INDEX idx_active (is_active, buyer_product_status)
    )");

    // Create digi_transactions table
    $db->exec("CREATE TABLE IF NOT EXISTS digi_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ref_id VARCHAR(100) NOT NULL UNIQUE,
        buyer_sku_code VARCHAR(100) NOT NULL,
        customer_no VARCHAR(50) NOT NULL,
        customer_name VARCHAR(255),
        product_name VARCHAR(255),
        category VARCHAR(50),
        brand VARCHAR(50),
        type ENUM('prepaid','postpaid') DEFAULT 'prepaid',
        sell_price DECIMAL(15,2) DEFAULT 0,
        modal_price DECIMAL(15,2) DEFAULT 0,
        profit DECIMAL(15,2) DEFAULT 0,
        status ENUM('pending','processing','success','failed','refunded') DEFAULT 'pending',
        sn VARCHAR(255),
        message TEXT,
        digiflazz_trx_id VARCHAR(100),
        payment_method VARCHAR(50) DEFAULT 'cash',
        paid_amount DECIMAL(15,2) DEFAULT 0,
        change_amount DECIMAL(15,2) DEFAULT 0,
        user_id INT,
        raw_response JSON,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_customer (customer_no),
        INDEX idx_date (created_at),
        INDEX idx_ref (ref_id)
    )");

    // Create digi_markup_rules table
    $db->exec("CREATE TABLE IF NOT EXISTS digi_markup_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category VARCHAR(50),
        brand VARCHAR(100),
        markup_type ENUM('fixed','percentage') DEFAULT 'fixed',
        markup_value DECIMAL(15,2) DEFAULT 0,
        min_price DECIMAL(15,2) DEFAULT 0,
        max_price DECIMAL(15,2) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    echo "Migration successful!\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
