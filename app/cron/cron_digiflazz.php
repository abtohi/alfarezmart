<?php
/**
 * CRON JOB: Digiflazz Price Sync
 * 
 * Run this via Windows Task Scheduler or Linux Cron daily:
 * 0 1 * * * php /path/to/app/cron/cron_digiflazz.php
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
require_once APP_PATH . '/models/DigiflazzModel.php';
require_once APP_PATH . '/services/DigiflazzService.php';

echo "[".date('Y-m-d H:i:s')."] Starting Digiflazz Price Sync Cron Job...\n";

try {
    $digiService = new DigiflazzService();
    $digiModel = new DigiflazzModel();

    // 1. Sync Prepaid
    echo "Syncing Prepaid Prices...\n";
    $resPrepaid = $digiService->getPriceList('prepaid');
    if ($resPrepaid['success'] && isset($resPrepaid['data']) && is_array($resPrepaid['data']) && !isset($resPrepaid['data']['rc'])) {
        $countPrepaid = count($resPrepaid['data']);
        $digiModel->syncPriceList($resPrepaid['data'], 'prepaid');
        echo "SUCCESS: Synced $countPrepaid prepaid products.\n";
    } else {
        echo "FAILED: Prepaid sync error - " . ($resPrepaid['message'] ?? 'Unknown error') . "\n";
    }

    // 2. Sync Postpaid
    echo "Syncing Postpaid Prices...\n";
    $resPostpaid = $digiService->getPriceList('pasca');
    if ($resPostpaid['success'] && isset($resPostpaid['data']) && is_array($resPostpaid['data']) && !isset($resPostpaid['data']['rc'])) {
        $countPostpaid = count($resPostpaid['data']);
        $digiModel->syncPriceList($resPostpaid['data'], 'postpaid');
        echo "SUCCESS: Synced $countPostpaid postpaid products.\n";
    } else {
        echo "FAILED: Postpaid sync error - " . ($resPostpaid['message'] ?? 'Unknown error') . "\n";
    }

    echo "[".date('Y-m-d H:i:s')."] Cron Job Finished Successfully.\n";

} catch (Exception $e) {
    echo "[".date('Y-m-d H:i:s')."] CRITICAL ERROR: " . $e->getMessage() . "\n";
}
