<?php
/**
 * Debug endpoint - menggunakan bootstrap aplikasi yang sama
 * HAPUS file ini setelah debug selesai!
 */
$token = $_GET['t'] ?? '';
if ($token !== 'alfarez2024debug') { http_response_code(403); die('Forbidden'); }

// Bootstrap identik dengan public/index.php
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');

// Load App config (sets DB_DRIVER, DB_HOST, dll.)
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';
require_once APP_PATH . '/config/Database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $result = ['driver' => $db->getDriver()];

    // Count all
    $result['total_sales_reps'] = $pdo->query("SELECT COUNT(*) FROM sales_reps")->fetchColumn();

    // Status distribution
    $stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM sales_reps GROUP BY status");
    $result['status_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Test getAllWithSupplier query
    try {
        $stmt = $pdo->prepare("
            SELECT sr.id, sr.name, sr.status, s.name as supplier_name
            FROM sales_reps sr
            LEFT JOIN suppliers s ON sr.supplier_id = s.id
            WHERE (sr.status = 'Aktif')
            ORDER BY s.name ASC, sr.name ASC
        ");
        $stmt->execute([]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['aktif_rows_count'] = count($rows);
        $result['aktif_rows_sample'] = array_slice($rows, 0, 5);
    } catch (Exception $e) {
        $result['aktif_query_error'] = $e->getMessage();
    }

    // Check if table exists
    try {
        $driver = $db->getDriver();
        if ($driver === 'mysql') {
            $tableExists = $pdo->query("SHOW TABLES LIKE 'sales_reps'")->rowCount() > 0;
        } else {
            $tableExists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='sales_reps'")->rowCount() > 0;
        }
        $result['table_sales_reps_exists'] = $tableExists;
    } catch (Exception $e) {
        $result['table_check_error'] = $e->getMessage();
    }

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
