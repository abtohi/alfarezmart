<?php
/**
 * CRON JOB: Hourly Mutual Fund (Reksadana) NAV & Asset Auto-Update
 * 
 * Memperbarui data NAB reksadana setiap jam secara otomatis,
 * menghitung total aset (unit * NAB), mencatat log history di database,
 * dan menyimpan cache JSON dinamis.
 * 
 * Jalankan via Windows Task Scheduler atau Linux Cron (setiap 1 jam):
 * 0 * * * * php /path/to/cron_reksadana.php
 */

define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');

require_once __DIR__ . '/app/core/Autoloader.php';
Autoloader::register();
require_once __DIR__ . '/app/config/App.php';
require_once __DIR__ . '/app/config/Database.php';

// Include models and services manually for cron
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/models/SettingModel.php';
require_once __DIR__ . '/app/models/SavingsModel.php';
require_once __DIR__ . '/app/services/MutualFundService.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting Hourly Mutual Fund NAV & Asset Auto-Update...\n";

try {
    $savingsModel = new SavingsModel();
    $res = $savingsModel->refreshAllMutualFundsNav();

    if ($res['success']) {
        echo "SUCCESS: Updated " . $res['updated_count'] . " of " . $res['total_funds'] . " user mutual funds.\n";
        echo "Timestamp: " . $res['timestamp'] . "\n";
    } else {
        echo "WARNING: Update finished with notice.\n";
    }
    echo "[" . date('Y-m-d H:i:s') . "] Cron Job Finished Successfully.\n";

} catch (\Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] CRITICAL ERROR: " . $e->getMessage() . "\n";
}
