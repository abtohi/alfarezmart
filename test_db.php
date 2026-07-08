<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=alfarezmart', 'root', '');
$stmt = $pdo->query('SELECT raw FROM digi_deposit_logs ORDER BY id DESC LIMIT 1');
if ($stmt) {
    file_put_contents('db_test_result.txt', print_r(json_decode($stmt->fetch()['raw'], true), true));
}
