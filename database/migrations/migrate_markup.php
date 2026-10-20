<?php
define("BASE_PATH", __DIR__);
define("APP_PATH", BASE_PATH . "/app");
define("PUBLIC_PATH", BASE_PATH . "/public");
define("STORAGE_PATH", dirname(BASE_PATH) . "/storage");
require_once __DIR__ . "/app/config/App.php";
require_once __DIR__ . "/app/config/Database.php";

try {
    $db = Database::getInstance()->getConnection();

    $db->exec("CREATE TABLE IF NOT EXISTS digi_markup_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category VARCHAR(50),
        brand VARCHAR(100),
        markup_type ENUM(\"fixed\",\"percentage\") DEFAULT \"fixed\",
        markup_value DECIMAL(15,2) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_cat_brand (category, brand)
    )");
    echo "Table created.\n";

    $defaults = [
        ["pulsa",null,"fixed",500],
        ["data",null,"fixed",1000],
        ["pln",null,"fixed",1500],
        ["ewallet",null,"fixed",500],
        ["game",null,"fixed",1000],
        ["bpjs",null,"fixed",2500],
        ["multifinance",null,"fixed",2500],
        ["bank",null,"fixed",3500],
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO digi_markup_rules (category, brand, markup_type, markup_value) VALUES (:cat, :brand, :type, :val)");
    foreach ($defaults as [$cat, $brand, $type, $val]) {
        $stmt->execute(["cat"=>$cat,"brand"=>$brand,"type"=>$type,"val"=>$val]);
    }
    echo "Defaults inserted.\n";

    $db->exec("
        UPDATE digi_products p
        LEFT JOIN digi_markup_rules r ON r.category = p.category AND r.brand IS NULL AND r.is_active = 1
        SET p.markup = COALESCE(
            CASE WHEN r.markup_type = \"percentage\" THEN (p.seller_price * (r.markup_value / 100)) ELSE r.markup_value END,
            2000
        )
    ");
    $db->exec("UPDATE digi_products SET sell_price = CEIL((seller_price + markup) / 100) * 100");
    echo "Markups applied.\nDone!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
