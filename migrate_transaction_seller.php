<?php
/**
 * Migration: Add seller_name to digi_transactions and clean up development data
 */

$host   = '153.92.15.83';
$port   = '3306';
$dbname = 'u573283697_alfarezmart';
$user   = 'u573283697_alfarez';
$pass   = ''; 

$envPaths = [
    __DIR__ . '/.env',
    dirname(__DIR__) . '/private/.env',
    dirname(__DIR__) . '/.env',
];
foreach ($envPaths as $envFile) {
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k); $v = trim($v);
            if ($k === 'DB_HOST')     $host   = $v;
            if ($k === 'DB_DATABASE') $dbname = $v;
            if ($k === 'DB_USERNAME') $user   = $v;
            if ($k === 'DB_PASSWORD') $pass   = $v;
            if ($k === 'DB_PORT')     $port   = $v;
        }
        break;
    }
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 10,
    ]);

    echo "<pre style='font-family:monospace; padding:20px; background:#1e293b; color:#e2e8f0; border-radius:12px;'>\n";
    echo "✅ Connected to MySQL: $dbname\n\n";

    // 1. Delete Development Transactions
    // Development transactions usually have "testing": true or "testing":true in raw_response
    // Or Digiflazz Sandbox sets SN to "testing"
    $stmt = $pdo->query("DELETE FROM digi_transactions WHERE raw_response LIKE '%\"testing\":true%' OR raw_response LIKE '%\"testing\": true%' OR sn LIKE '%testing%'");
    $deleted = $stmt->rowCount();
    echo "✅ Dihapus $deleted baris data transaksi dummy (Development Mode).\n";

    // 2. Add seller_name column to digi_transactions
    $stmt = $pdo->query("SHOW COLUMNS FROM digi_transactions LIKE 'seller_name'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE digi_transactions ADD COLUMN seller_name VARCHAR(100) NULL AFTER sn");
        echo "✅ Column 'seller_name' berhasil ditambahkan ke tabel digi_transactions.\n";
    } else {
        echo "ℹ️  Column 'seller_name' sudah ada di tabel digi_transactions.\n";
    }

    // 3. Extract seller_name from digi_products or raw_response for existing transactions
    $stmt = $pdo->query("
        UPDATE digi_transactions t
        LEFT JOIN digi_products p ON t.buyer_sku_code = p.buyer_sku_code
        SET t.seller_name = p.seller_name
        WHERE t.seller_name IS NULL AND p.seller_name IS NOT NULL AND p.seller_name != ''
    ");
    $updatedCount1 = $stmt->rowCount();
    echo "✅ Berhasil memperbarui $updatedCount1 seller_name dari tabel digi_products.\n";

    // 4. Fallback: extract from raw_response if seller_name is still NULL
    $stmt = $pdo->query("SELECT id, raw_response FROM digi_transactions WHERE seller_name IS NULL AND raw_response IS NOT NULL");
    $transactions = $stmt->fetchAll();
    $updatedCount2 = 0;

    $updateStmt = $pdo->prepare("UPDATE digi_transactions SET seller_name = :seller WHERE id = :id");

    foreach ($transactions as $t) {
        $raw = json_decode($t['raw_response'], true);
        if ($raw) {
            $sellerName = null;
            if (!empty($raw['tele'])) {
                $sellerName = $raw['tele'];
            } elseif (!empty($raw['wa'])) {
                $sellerName = $raw['wa'];
            }

            if ($sellerName) {
                $updateStmt->execute(['seller' => $sellerName, 'id' => $t['id']]);
                $updatedCount2++;
            }
        }
    }

    echo "✅ Berhasil mengekstrak $updatedCount2 seller_name dari raw_response (fallback).\n";
    
    echo "\n✅ Proses selesai!\n";
    echo "</pre>";

} catch (PDOException $e) {
    echo "<pre style='font-family:monospace; padding:20px; background:#1e293b; color:#ef4444; border-radius:12px;'>";
    echo "❌ MySQL Error: " . $e->getMessage() . "\n";
    echo "</pre>";
}
