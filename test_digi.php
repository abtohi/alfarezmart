<?php
define('BASE_PATH', __DIR__);
define('STORAGE_PATH', __DIR__ . '/storage');
require 'app/config/App.php';
require 'app/config/Database.php';
require 'app/models/Model.php';
require 'app/models/SettingModel.php';
require 'app/services/DigiflazzService.php';

$ds = new DigiflazzService();
$res = $ds->createDeposit(50000, 'SHOPEEPAY', 'Slamet Abtohi');
file_put_contents('test_digi_res.txt', print_r($res, true));
