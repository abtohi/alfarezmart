<?php
/**
 * CRON JOB: Mutual Fund (Reksadana) Live NAV & Asset Auto-Update
 * 
 * Memperbarui data NAB reksadana secara otomatis (setiap jam & pukul 22:00 WIB / 10 malam GMT+7),
 * menghitung total aset (unit * NAB), mencatat log history di database,
 * dan menyimpan cache JSON dinamis.
 * 
 * Jadwal Cron Server (misal setiap hari jam 22:00 WIB):
 * 0 22 * * * php /path/to/app/cron/cron_reksadana.php
 */

defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__, 2));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');
defined('PUBLIC_PATH') || define('PUBLIC_PATH', BASE_PATH . '/public');
defined('STORAGE_PATH') || define('STORAGE_PATH', is_dir(BASE_PATH . '/storage') ? BASE_PATH . '/storage' : dirname(BASE_PATH) . '/storage');

require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';
require_once APP_PATH . '/config/Database.php';

// Include models and services manually for cron
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/models/SettingModel.php';
require_once APP_PATH . '/models/SavingsModel.php';
require_once APP_PATH . '/services/MutualFundService.php';

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
