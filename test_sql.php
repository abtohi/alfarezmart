<?php
require_once "index.php"; // Load autoloader and config

$db = new Database();
$sql = "UPDATE digi_transactions SET status = :status, message = :message, updated_at = NOW() WHERE ref_id = :ref_id AND (status = 'pending' OR :status_check != 'pending')";
$params = [
    "ref_id" => "DIGI-123",
    "status" => "failed",
    "message" => "test",
    "status_check" => "failed"
];

try {
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($params);
    echo "Success: " . $stmt->rowCount();
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage();
}

