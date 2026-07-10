<?php
$db = new PDO('mysql:host=localhost;dbname=AlfarezMart', 'root', '');
$stmt = $db->query("SELECT sn FROM digi_transactions WHERE product_name LIKE '%PLN%' AND status = 'success' ORDER BY id DESC LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
