<?php
define('APP_PATH', __DIR__ . '/app');
require APP_PATH . '/core/Security.php';
require APP_PATH . '/core/Controller.php';
require APP_PATH . '/config/Database.php';
require APP_PATH . '/core/Model.php';
require APP_PATH . '/models/UnitModel.php';
require APP_PATH . '/controllers/ApiController.php';

// Mock $_SERVER and POST for CSRF
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['csrf_token'] = 'dummy';
$_POST['name'] = 'TestKarton';
$_POST['abbreviation'] = 'krt';

// Mock Security class to bypass CSRF validation
class MockSecurity extends Security {
    public function validateCSRFToken($token) { return true; }
}

$api = new ApiController();
// Inject mock security
$reflection = new ReflectionClass($api);
$property = $reflection->getProperty('security');
$property->setAccessible(true);
$property->setValue($api, new MockSecurity());

try {
    $api->createUnit();
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
