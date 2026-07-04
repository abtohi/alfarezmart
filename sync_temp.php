<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
require 'app/config/App.php';
require 'app/config/Database.php';
require 'app/core/Model.php';
require 'app/models/SettingModel.php';
require 'app/models/DigiflazzModel.php';
require 'app/services/DigiflazzService.php';
$s = new DigiflazzService();
$m = new DigiflazzModel();
$r = $s->getPriceList('prepaid');
var_dump($r);
if(isset($r['data']) && is_array($r['data']) && !isset($r['data']['message'])) {
    $m->syncPriceList($r['data'], 'prepaid');
    echo "Synced prepaid\n";
} else {
    echo "Failed prepaid\n";
}
$r2 = $s->getPriceList('pasca');
if(isset($r2['data']) && is_array($r2['data'])) {
    $m->syncPriceList($r2['data'], 'postpaid');
    echo "Synced postpaid\n";
} else {
    echo "Failed postpaid: " . json_encode($r2) . "\n";
}
echo 'Done';
