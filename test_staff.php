<?php
define('BASE_PATH', 'c:/xampp/htdocs/AlfarezMart');
define('APP_PATH', BASE_PATH.'/app');
define('STORAGE_PATH', 'c:/xampp/htdocs/storage');
$_SERVER['HTTP_HOST']='localhost';
$_SERVER['REQUEST_URI']='/';
require 'app/core/Autoloader.php';
Autoloader::register();
require 'app/config/App.php';
$db = Database::getInstance()->getConnection();
$rows = $db->query("SELECT id, name, email, user_level, work_days, work_start, work_end FROM users")->fetchAll(PDO::FETCH_ASSOC);
file_put_contents('test_output.json', json_encode($rows, JSON_PRETTY_PRINT));
