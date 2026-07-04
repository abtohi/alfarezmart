<?php
require __DIR__ . '/app/config/config.php';
require __DIR__ . '/app/services/DigiflazzService.php';

$service = new DigiflazzService();
$res = $service->inquiryPLN('14356145996');

print_r($res);
