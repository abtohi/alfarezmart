<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require 'index.php'; // this requires App.php
$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE products");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
