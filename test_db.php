<?php
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

    $seller = 'BCA PULSA CV BALI CAKRA AMERTA';
    $stmt = $pdo->prepare("SELECT * FROM digi_transactions WHERE seller_name = :s");
    $stmt->execute(['s' => $seller]);
    $res = $stmt->fetchAll();
    
    echo "Found exactly matching: " . count($res) . "\n";
    
    $stmt = $pdo->prepare("SELECT seller_name, COUNT(*) FROM digi_transactions WHERE seller_name LIKE :s GROUP BY seller_name");
    $stmt->execute(['s' => "%BCA PULSA%"]);
    print_r($stmt->fetchAll());
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
