<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');

require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();

require_once APP_PATH . '/core/Session.php';
require_once APP_PATH . '/core/Security.php';
require_once APP_PATH . '/config/App.php';

$session = new Session();
$session->start();
$security = new Security();

$router = new Router();
require_once APP_PATH . '/config/Routes.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/ppob/settings';
$router->dispatch();
