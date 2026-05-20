<?php
/**
 * App Configuration - Load .env and set app constants
 */

// Load .env file
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || empty($line)) continue;
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!defined($key)) {
            define($key, $value);
        }
        putenv("$key=$value");
    }
}

// Define BASE_URL
// Priority: APP_URL from .env > auto-detect from server
if (defined('APP_URL') && !empty(APP_URL)) {
    define('BASE_URL', rtrim(APP_URL, '/') . '/');
} else {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if ($scriptDir === '/') $scriptDir = '';
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? 'https://' : 'http://';
    define('BASE_URL', $protocol . $_SERVER['HTTP_HOST'] . $scriptDir . '/');
}

// Set timezone
date_default_timezone_set(defined('APP_TIMEZONE') ? APP_TIMEZONE : 'Asia/Jakarta');

// Set error display based on environment
if (defined('APP_DEBUG') && APP_DEBUG === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ERROR | E_PARSE); // Log critical errors even in production
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Create required directories
$dirs = [
    STORAGE_PATH . '/database',
    STORAGE_PATH . '/logs',
    STORAGE_PATH . '/transactions',
    STORAGE_PATH . '/invoices',
    STORAGE_PATH . '/backups',
    STORAGE_PATH . '/uploads',
    STORAGE_PATH . '/uploads/invoice_photos',
    STORAGE_PATH . '/uploads/product_images',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

return true;
