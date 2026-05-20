<?php
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';

try {
    $db = Database::getInstance()->getConnection();

    function dropForeignKey($db, $table, $column) {
        $stmt = $db->prepare("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :table
              AND COLUMN_NAME = :column
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $stmt->execute([':table' => $table, ':column' => $column]);
        while($fkName = $stmt->fetchColumn()) {
            $db->exec("ALTER TABLE {$table} DROP FOREIGN KEY {$fkName}");
        }
    }

    dropForeignKey($db, 'purchase_items', 'packaging_id');
    $db->exec("ALTER TABLE purchase_items MODIFY packaging_id INT NULL");
    $db->exec("ALTER TABLE purchase_items ADD CONSTRAINT fk_pi_pkg FOREIGN KEY (packaging_id) REFERENCES product_packagings(id) ON DELETE SET NULL");

    dropForeignKey($db, 'sale_items', 'packaging_id');
    $db->exec("ALTER TABLE sale_items MODIFY packaging_id INT NULL");
    $db->exec("ALTER TABLE sale_items ADD CONSTRAINT fk_si_pkg FOREIGN KEY (packaging_id) REFERENCES product_packagings(id) ON DELETE SET NULL");

    echo json_encode(["success" => true, "message" => "Foreign keys updated to ON DELETE SET NULL"]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
