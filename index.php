<?php
/**
 * AlfarezMart PWA - Front Controller
 * 
 * Semua request masuk melalui file ini.
 * Router akan mengarahkan ke Controller yang sesuai.
 */

// Serve static files for PHP built-in server
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
    $cleanUri = substr($uri, strlen($scriptDir));
} else {
    $cleanUri = $uri;
}
$isStorageRequest = strpos($cleanUri, '/storage/') === 0;
$staticFile = $isStorageRequest ? dirname(__DIR__) . $cleanUri : __DIR__ . $cleanUri;

if ($cleanUri !== '/' && file_exists($staticFile) && is_file($staticFile)) {
    $ext = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'webp' => 'image/webp',
    ];
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        if ($isStorageRequest) {
            header('Cache-Control: public, max-age=86400');
        }
        readfile($staticFile);
        return;
    }
    if ($isStorageRequest) {
        http_response_code(404);
        exit;
    }
    return false; // Let PHP built-in server handle it
} elseif ($isStorageRequest) {
    http_response_code(404);
    exit;
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path - handle cases where index.php might be in a subdirectory
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');

// Verify app directory exists
if (!is_dir(APP_PATH)) {
    die('Error: App directory not found at ' . APP_PATH);
}

// Load core classes with error handling
$coreFiles = [
    APP_PATH . '/core/Autoloader.php',
    APP_PATH . '/core/Session.php',
    APP_PATH . '/core/Security.php',
];

$loadedClasses = [];
foreach ($coreFiles as $file) {
    if (!file_exists($file)) {
        die("ERROR: Required file not found\nFile: $file\nPath: " . realpath($file));
    }
    
    $beforeClasses = get_declared_classes();
    
    try {
        require_once $file;
    } catch (Throwable $e) {
        die("ERROR: Failed to load $file\nError: " . $e->getMessage());
    }
    
    $afterClasses = get_declared_classes();
    $newClasses = array_diff($afterClasses, $beforeClasses);
    $loadedClasses[$file] = $newClasses;
}

// Verify Autoloader class exists and register
if (!class_exists('Autoloader')) {
    $debugInfo = "ERROR: Autoloader class not found after loading.\n\n";
    $debugInfo .= "File checked: " . APP_PATH . '/core/Autoloader.php' . "\n";
    $debugInfo .= "File exists: " . (file_exists(APP_PATH . '/core/Autoloader.php') ? 'YES' : 'NO') . "\n";
    $debugInfo .= "File readable: " . (is_readable(APP_PATH . '/core/Autoloader.php') ? 'YES' : 'NO') . "\n\n";
    $debugInfo .= "Classes loaded from files:\n";
    foreach ($loadedClasses as $file => $classes) {
        $debugInfo .= "  " . basename($file) . ": " . (count($classes) ? implode(', ', $classes) : "NONE") . "\n";
    }
    $debugInfo .= "\nAll defined classes: " . implode(', ', array_slice(get_declared_classes(), -20)) . "\n";
    die($debugInfo);
}

try {
    Autoloader::register();
} catch (Throwable $e) {
    die("ERROR: Failed to register Autoloader\nError: " . $e->getMessage());
}

// Load environment FIRST (before initializing session)
$app = require_once APP_PATH . '/config/App.php';

// Initialize session with debug output
if (!class_exists('Session')) {
    $debugInfo = "Session class not found after loading " . APP_PATH . '/core/Session.php' . "\n";
    $debugInfo .= "Loaded files: " . implode(', ', $coreFiles) . "\n";
    $debugInfo .= "Defined classes: " . implode(', ', get_declared_classes()) . "\n";
    die($debugInfo);
}
$session = new Session();
$session->start();

// Initialize security
if (!class_exists('Security')) {
    $debugInfo = "Error: Security class not found after loading " . APP_PATH . '/core/Security.php' . "\n";
    $debugInfo .= "Defined classes: " . implode(', ', get_declared_classes()) . "\n";
    die($debugInfo);
}
$security = new Security();

// ============================================
// AUTH MIDDLEWARE
// Check authentication for all routes except public ones
// ============================================
$uri = isset($_SERVER['REQUEST_URI']) ? urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) : '/';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
    $uri = substr($uri, strlen($scriptDir));
}
$uri = rtrim($uri, '/');
if (empty($uri)) $uri = '/';

// Public routes that don't require authentication
$publicRoutes = ['/login', '/api/auth/login', '/logout', '/setup', '/api/ppob/webhook'];
$isPublicRoute = in_array($uri, $publicRoutes);
$isStaticFile = preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|webp|json)$/i', $uri);

if (!$isPublicRoute && !$isStaticFile && !isset($_SESSION['user_id'])) {
    // Not logged in — redirect to login page
    if (strpos($uri, '/api/') === 0) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Unauthorized. Please login.']);
        exit;
    }
    $loginUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'login';
    header('Location: ' . $loginUrl);
    exit;
}

// Make current user available globally to views
$currentUser = AuthController::currentUser();

// ============================================
// STAFF ROUTE RESTRICTIONS
// Block staff from accessing restricted pages (server-side)
// ============================================
if (($currentUser['level'] ?? '') === 'staff') {
    $staffBlockedRoutes = [
        '/reports', '/debts', '/finance', '/users',
        '/settings/master-data',
    ];
    foreach ($staffBlockedRoutes as $blocked) {
        if ($uri === $blocked || strpos($uri, $blocked . '/') === 0) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/'));
            exit;
        }
    }
    // Block API endpoints for sensitive data
    $staffBlockedApis = [
        '/api/reports', '/api/debts', '/api/finance',
        '/api/users', '/api/dashboard/stats',
    ];
    foreach ($staffBlockedApis as $blockedApi) {
        if (strpos($uri, $blockedApi) === 0) {
            // Allow staff to change their own password
            if ($uri === '/api/users/change-password') {
                continue;
            }
            
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Akses ditolak untuk level staff']);
            exit;
        }
    }
}

// Initialize router
$router = new Router();

// Load routes
$routesPath = APP_PATH . '/config/Routes.php';
if (function_exists('opcache_invalidate')) {
    opcache_invalidate($routesPath, true);
}
require_once $routesPath;

// Release session lock for GET API requests to prevent hanging/timeouts
if (strpos($uri, '/api/') === 0 && $_SERVER['REQUEST_METHOD'] === 'GET') {
    session_write_close();
}

// Dispatch request
$router->dispatch();

