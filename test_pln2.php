<?php
$username = 'masehaoJj9qo';
$apiKey = 'dev-e3189fc0-7774-11f1-95fe-9372a8e9e204';
$refId = uniqid('PLNINQ');
$sign = md5($username . $apiKey . $refId);

$payload = [
    'commands' => 'pln-subscribe',
    'buyer_sku_code' => 'pln',
    'username' => $username,
    'customer_no' => '14356145996',
    'ref_id' => $refId,
    'sign' => $sign,
    'testing' => true
];

$ch = curl_init('https://api.digiflazz.com/v1/transaction');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
curl_close($ch);

echo $res;
