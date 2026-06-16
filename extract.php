<?php
$c = file_get_contents('app/views/purchases/create.php');
preg_match('/<script>(.*?)<\/script>/s', $c, $m);
$js = preg_replace('/<\?php.*?\?>/s', '', $m[1]);
$js = preg_replace('/<\?=.*?\?>/s', '0', $js);
file_put_contents('temp_test.js', $js);
