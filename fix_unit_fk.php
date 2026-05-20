<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', BASE_PATH . '/storage');
$_SERVER['HTTP_HOST'] = 'localhost';
require APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require APP_PATH . '/config/App.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Find the foreign key constraint name
    $stmt = $db->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = 'product_packagings' 
          AND COLUMN_NAME = 'unit_id' 
          AND REFERENCED_TABLE_NAME = 'units'
    ");
    
    $fkName = $stmt->fetchColumn();
    echo "Current FK: " . ($fkName ?: 'none') . "\n";
    
    if ($fkName) {
        $db->exec("ALTER TABLE product_packagings DROP FOREIGN KEY `$fkName`");
        echo "Dropped FK $fkName\n";
    }

    // 2. Modify column to allow NULL
    $db->exec("ALTER TABLE product_packagings MODIFY unit_id INT NULL");
    echo "Modified unit_id to allow NULL\n";
    
    // 3. Add FK back with ON DELETE SET NULL
    $db->exec("ALTER TABLE product_packagings ADD CONSTRAINT fk_packagings_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL");
    echo "Added new FK with ON DELETE SET NULL\n";
    echo "DONE - unit deletion should now work.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
