<?php
define('BASE_PATH', __DIR__);
require 'app/config/App.php';
require 'app/core/Database.php';
require 'app/models/SettingModel.php';
require 'app/services/DigiflazzService.php';

$ds = new DigiflazzService();
$res = $ds->createDeposit(50000, 'SHOPEEPAY', 'Slamet Abtohi');
print_r($res);
