<?php
/**
 * Simple Session Test - Verify Session class works
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$baseDir = __DIR__;
$sessionFile = $baseDir . '/app/core/Session.php';

echo "=== Session Class Direct Test ===\n\n";

echo "1. Checking file existence...\n";
if (!file_exists($sessionFile)) {
    die("ERROR: File not found at $sessionFile\n");
}
echo "   ✓ File found: $sessionFile\n";
echo "   ✓ File size: " . filesize($sessionFile) . " bytes\n\n";

echo "2. Checking file syntax...\n";
$output = shell_exec("php -l " . escapeshellarg($sessionFile) . " 2>&1");
echo "   $output\n";

echo "3. Requiring file...\n";
require_once $sessionFile;
echo "   ✓ File included\n\n";

echo "4. Checking if Session class exists...\n";
if (class_exists('Session')) {
    echo "   ✓ Session class is available\n\n";
    
    echo "5. Attempting to instantiate...\n";
    try {
        $session = new Session();
        echo "   ✓ Successfully instantiated Session\n";
        echo "   ✓ Instance: " . get_class($session) . "\n";
    } catch (Exception $e) {
        echo "   ✗ Error instantiating: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✗ Session class NOT found!\n";
    echo "   Defined classes: " . implode(", ", get_declared_classes()) . "\n";
}

echo "\n=== END TEST ===\n";
