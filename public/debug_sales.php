<?php
/**
 * Debug endpoint - cek sales_reps di live server
 * HAPUS file ini setelah debug selesai!
 */

// Bootstrap minimal
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Load config
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($val);
        }
    }
}

// Auth check - simple token
$token = $_GET['t'] ?? '';
if ($token !== 'alfarez2024debug') {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: application/json');

try {
    $dbDriver = $_ENV['DB_DRIVER'] ?? 'sqlite';
    
    if ($dbDriver === 'mysql') {
        $dsn = 'mysql:host=' . ($_ENV['DB_HOST'] ?? 'localhost') . 
               ';port=' . ($_ENV['DB_PORT'] ?? '3306') .
               ';dbname=' . ($_ENV['DB_NAME'] ?? '') . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? '', $_ENV['DB_PASS'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } else {
        $dbPath = BASE_PATH . '/../storage/database.sqlite';
        if (!file_exists($dbPath)) {
            $dbPath = BASE_PATH . '/storage/database.sqlite';
        }
        $pdo = new PDO('sqlite:' . $dbPath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    // Check sales_reps
    $result = [];
    
    // Count all
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sales_reps");
    $result['total_sales_reps'] = $stmt->fetchColumn();
    
    // Status distribution
    $stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM sales_reps GROUP BY status");
    $result['status_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sample data
    $stmt = $pdo->query("SELECT sr.id, sr.name, sr.status, sr.supplier_id, s.name as supplier_name 
                         FROM sales_reps sr 
                         LEFT JOIN suppliers s ON sr.supplier_id = s.id
                         LIMIT 10");
    $result['sample_rows'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Test the exact query from getAllWithSupplier
    try {
        $stmt = $pdo->prepare("
            SELECT sr.*, s.name as supplier_name
            FROM sales_reps sr
            LEFT JOIN suppliers s ON sr.supplier_id = s.id
            WHERE (sr.status = 'Aktif')
            ORDER BY s.name ASC, sr.name ASC
        ");
        $stmt->execute([]);
        $result['aktif_count'] = count($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        $result['aktif_query_error'] = $e->getMessage();
    }
    
    $result['db_driver'] = $dbDriver;
    $result['php_version'] = PHP_VERSION;
    
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
