<?php
$dbPath = __DIR__ . '/storage/database/alfarezmart.sqlite';
$pdo = new PDO("sqlite:$dbPath");
$stmt = $pdo->query("SELECT p.id, p.full_name, pp.id as pkg_id, pp.level, u.name as unit_name, pp.contained_qty, pp.base_qty, pp.sell_price_retail, pp.buy_price FROM products p JOIN product_packagings pp ON p.id = pp.product_id LEFT JOIN units u ON pp.unit_id = u.id WHERE p.full_name LIKE '%Nyam Nyam Fantasy%' ORDER BY pp.level ASC");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
