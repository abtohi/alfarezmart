<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
require_once APP_PATH . '/config/Database.php'; // Load only Database config
$db = Database::getInstance()->getConnection();

try {
    // Ambil semua produk yang memiliki kemasan
    $stmt = $db->query("SELECT DISTINCT product_id FROM product_packagings");
    $productIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $fixedCount = 0;

    foreach ($productIds as $pid) {
        $stmtPkgs = $db->prepare("SELECT id, level FROM product_packagings WHERE product_id = :pid ORDER BY level ASC");
        $stmtPkgs->execute([':pid' => $pid]);
        $packagings = $stmtPkgs->fetchAll(PDO::FETCH_ASSOC);

        $expectedLevel = 1;
        $needsFix = false;

        // Cek apakah ada level yang tidak berurutan atau tidak mulai dari 1
        foreach ($packagings as $pkg) {
            if ($pkg['level'] != $expectedLevel) {
                $needsFix = true;
                break;
            }
            $expectedLevel++;
        }

        // Jika perlu diperbaiki, urutkan ulang levelnya
        if ($needsFix) {
            $newLevel = 1;
            foreach ($packagings as $pkg) {
                $updateStmt = $db->prepare("UPDATE product_packagings SET level = :lvl WHERE id = :id");
                $updateStmt->execute([':lvl' => $newLevel, ':id' => $pkg['id']]);
                $newLevel++;
            }
            $fixedCount++;
        }
    }

    echo "<h3>Perbaikan Selesai!</h3>";
    echo "<p>Berhasil memperbaiki dan mengurutkan ulang level kemasan untuk <strong>$fixedCount</strong> produk.</p>";
    echo "<p>Silakan hapus file ini jika sudah tidak digunakan.</p>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
