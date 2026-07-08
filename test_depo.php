<?php
require 'app/core/Database.php';
require 'app/models/SettingModel.php';
require 'app/services/DigiflazzService.php';

// Mock SettingModel dependencies if any or just run
$ds = new DigiflazzService();
$res = $ds->createDeposit(50000, 'SHOPEEPAY', 'Test User');
print_r($res);
