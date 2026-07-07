<?php
/**
 * CRON JOB: Digiflazz Price Sync
 * 
 * Run this via Windows Task Scheduler or Linux Cron daily:
 * 0 1 * * * php /path/to/cron_digiflazz.php
 */

define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');

require_once __DIR__ . '/app/config/App.php';
require_once __DIR__ . '/app/config/Database.php';

// Include models and services manually for cron
require_once __DIR__ . '/app/models/SettingModel.php';
require_once __DIR__ . '/app/models/DigiflazzModel.php';
require_once __DIR__ . '/app/services/DigiflazzService.php';

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
