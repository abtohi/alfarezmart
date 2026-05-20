<?php
/**
 * One-time: hapus sales_reps duplikat (nama + supplier sama).
 * Jalankan: php database/dedupe_sales_reps.php
 */
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', BASE_PATH . '/storage');

require BASE_PATH . '/app/config/App.php';
require BASE_PATH . '/app/core/Autoloader.php';
Autoloader::register();

header('Content-Type: text/plain; charset=utf-8');

$model = new SalesRepModel();
$result = $model->removeDuplicatesBySupplier();

echo "Dedupe sales_reps selesai.\n";
echo "Grup duplikat: {$result['groups']}\n";
echo "Record dihapus: {$result['removed']}\n";

if (!empty($result['details'])) {
    echo "\nDetail:\n";
    foreach ($result['details'] as $d) {
        echo "- supplier_id={$d['supplier_id']} name={$d['name']} kept={$d['kept_id']} removed=" . implode(',', $d['removed_ids']) . "\n";
    }
}
