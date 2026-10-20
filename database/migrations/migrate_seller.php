<?php
/**
 * Migration: Add seller_name column to digi_products
 * Connects directly to MySQL using hardcoded credentials from Database.php
 */

// Direct MySQL connection - no framework bootstrap needed
$host   = '153.92.15.83';
$port   = '3306';
$dbname = 'u573283697_alfarezmart';
$user   = 'u573283697_alfarez';
$pass   = ''; // Will be filled from .env if available

// Try to load password from .env file
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

    // Check and add seller_name column
    $stmt = $pdo->query("SHOW COLUMNS FROM digi_products LIKE 'seller_name'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE digi_products ADD COLUMN seller_name VARCHAR(100) NULL AFTER brand");
        echo "✅ Column 'seller_name' berhasil ditambahkan ke tabel digi_products.\n";
    } else {
        echo "ℹ️  Column 'seller_name' sudah ada di tabel digi_products.\n";
    }

    // Show current count of products with seller_name filled
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(seller_name IS NOT NULL AND seller_name != '') as filled FROM digi_products");
    $row = $stmt->fetch();
    echo "\n📊 Statistik:\n";
    echo "   Total produk       : {$row['total']}\n";
    echo "   Sudah punya seller : {$row['filled']}\n";
    echo "   Belum ada seller   : " . ($row['total'] - $row['filled']) . "\n";
    echo "\n✅ Migrasi selesai!\n";
    echo "\n⚠️  Jika 'Belum ada seller' masih banyak, silakan klik tombol Sync (↻)\n";
    echo "   di halaman PPOB untuk mengisi seller_name dari Digiflazz API.\n";
    echo "</pre>";

} catch (PDOException $e) {
    echo "<pre style='font-family:monospace; padding:20px; background:#1e293b; color:#ef4444; border-radius:12px;'>";
    echo "❌ MySQL Error: " . $e->getMessage() . "\n";
    echo "\nHost: $host | Port: $port | DB: $dbname | User: $user\n";
    echo "</pre>";
}
