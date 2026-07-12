<?php
/**
 * Migration: Add seller_name column to digi_products
 * Run this once via: http://yourdomain/AlfarezMart/migrate_seller.php
 * Or include it in your setup flow.
 */
require_once __DIR__ . '/app/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column already exists
    $stmt = $db->query("SHOW COLUMNS FROM digi_products LIKE 'seller_name'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE digi_products ADD COLUMN seller_name VARCHAR(100) NULL AFTER brand");
        echo "✅ Column 'seller_name' added successfully to digi_products.\n";
    } else {
        echo "ℹ️ Column 'seller_name' already exists in digi_products.\n";
    }
    
    echo "Migration complete.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
