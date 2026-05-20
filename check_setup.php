<?php
/**
 * Setup Diagnostic - Check if AlfarezMart is properly configured
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$baseDir = __DIR__;
?>
<!DOCTYPE html>
<html>
<head>
    <title>AlfarezMart - Setup Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .pass { color: green; font-weight: bold; } 
        .fail { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 3px; font-family: monospace; margin: 10px 0; overflow-x: auto; }
        .debug { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 10px 0; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 AlfarezMart Setup Diagnostic</h1>
        <p>Last checked: <?php echo date('Y-m-d H:i:s'); ?></p>
        
        <h2>File & Directory Checks</h2>
        <table>
            <tr>
                <th>Item</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
            
            <?php
            $checks = [];
            
            // Check PHP version
            $checks[] = [
                'name' => 'PHP Version',
                'pass' => version_compare(PHP_VERSION, '7.4.0', '>='),
                'details' => 'Required: 7.4+, Current: ' . PHP_VERSION
            ];
            
            // Check core files existence
            $coreFiles = ['Session.php', 'Security.php', 'Router.php', 'Autoloader.php', 'Controller.php', 'Model.php'];
            foreach ($coreFiles as $file) {
                $path = $baseDir . '/app/core/' . $file;
                $exists = file_exists($path);
                $checks[] = [
                    'name' => '📄 Core: ' . $file,
                    'pass' => $exists,
                    'details' => $exists ? 'Found (' . filesize($path) . ' bytes)' : 'Missing'
                ];
            }
            
            // Check directories
            $dirs = ['app', 'app/core', 'app/models', 'app/controllers', 'app/views', 'public', 'storage'];
            foreach ($dirs as $dir) {
                $path = $baseDir . '/' . $dir;
                $exists = is_dir($path);
                $checks[] = [
                    'name' => '📁 Dir: ' . $dir,
                    'pass' => $exists,
                    'details' => $exists ? 'Readable' : 'Missing'
                ];
            }
            
            // Display results
            foreach ($checks as $check) {
                $class = $check['pass'] ? 'pass' : 'fail';
                $status = $check['pass'] ? '✓ PASS' : '✗ FAIL';
                echo "<tr>";
                echo "<td>" . htmlspecialchars($check['name']) . "</td>";
                echo "<td class='$class'>$status</td>";
                echo "<td>" . htmlspecialchars($check['details']) . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
        
        <h2>Class Loading Tests</h2>
        <table>
            <tr>
                <th>Class</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
            
            <?php
            // Test Session class
            $sessionPath = $baseDir . '/app/core/Session.php';
            $sessionPass = false;
            $sessionMsg = 'File not found';
            
            if (file_exists($sessionPath)) {
                require_once $sessionPath;
                if (class_exists('Session')) {
                    $sessionPass = true;
                    $sessionMsg = 'Class loaded & available';
                } else {
                    $sessionMsg = 'File loaded but class NOT defined';
                }
            }
            
            // Test Security class
            $securityPath = $baseDir . '/app/core/Security.php';
            $securityPass = false;
            $securityMsg = 'File not found';
            
            if (file_exists($securityPath)) {
                require_once $securityPath;
                if (class_exists('Security')) {
                    $securityPass = true;
                    $securityMsg = 'Class loaded & available';
                } else {
                    $securityMsg = 'File loaded but class NOT defined';
                }
            }
            
            $classChecks = [
                ['name' => 'Session', 'pass' => $sessionPass, 'msg' => $sessionMsg],
                ['name' => 'Security', 'pass' => $securityPass, 'msg' => $securityMsg],
            ];
            
            foreach ($classChecks as $check) {
                $class = $check['pass'] ? 'pass' : 'fail';
                $status = $check['pass'] ? '✓ PASS' : '✗ FAIL';
                echo "<tr>";
                echo "<td>" . htmlspecialchars($check['name']) . "</td>";
                echo "<td class='$class'>$status</td>";
                echo "<td>" . htmlspecialchars($check['msg']) . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
        
        <h2>Declared Classes</h2>
        <div class="code">
            <?php
            $declared = get_declared_classes();
            $userClasses = array_filter($declared, function($c) {
                return strpos($c, 'Session') !== false || 
                       strpos($c, 'Security') !== false || 
                       strpos($c, 'Router') !== false;
            });
            
            if (!empty($userClasses)) {
                echo implode(", ", $userClasses);
            } else {
                echo "No user-defined classes found";
            }
            ?>
        </div>
        
        <h2>Session.php Content Check</h2>
        <div class="debug">
            <?php
            $sessionContent = file_get_contents($baseDir . '/app/core/Session.php');
            
            if (strpos($sessionContent, 'class Session') !== false) {
                echo '<span class="pass">✓</span> Session class definition found in file<br>';
            } else {
                echo '<span class="fail">✗</span> Session class definition NOT found!<br>';
            }
            
            if (strpos($sessionContent, '<?php') !== false) {
                echo '<span class="pass">✓</span> PHP opening tag found<br>';
            } else {
                echo '<span class="fail">✗</span> PHP opening tag missing!<br>';
            }
            
            $lines = count(file($baseDir . '/app/core/Session.php'));
            echo "File has $lines lines<br>";
            ?>
        </div>
        
        <h2>Quick Test: index.php Loading</h2>
        <div class="code">
            <strong>Attempting to include index.php...</strong><br><br>
            <?php
            // Simulate what index.php does
            define('BASE_PATH', $baseDir);
            define('APP_PATH', BASE_PATH . '/app');
            
            // Try to load Session directly
            $sessionFile = APP_PATH . '/core/Session.php';
            echo "Loading: " . htmlspecialchars($sessionFile) . "<br>";
            
            if (file_exists($sessionFile)) {
                echo '<span class="pass">✓</span> File exists<br>';
                require_once $sessionFile;
                
                if (class_exists('Session')) {
                    echo '<span class="pass">✓</span> Session class now available<br>';
                    echo 'Can instantiate: ';
                    try {
                        $test = new Session();
                        echo '<span class="pass">✓ YES</span><br>';
                    } catch (Exception $e) {
                        echo '<span class="fail">✗ NO - ' . $e->getMessage() . '</span><br>';
                    }
                } else {
                    echo '<span class="fail">✗</span> Session class NOT available after require_once<br>';
                    echo "Defined classes: " . implode(", ", get_declared_classes()) . "<br>";
                }
            } else {
                echo '<span class="fail">✗</span> File does not exist!<br>';
            }
            ?>
        </div>
        
        <h2>Summary & Recommendations</h2>
        <?php
        $allPass = $sessionPass && $securityPass;
        if ($allPass) {
            echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;">';
            echo '<span class="pass">✓ All checks passed!</span><br>';
            echo 'Your setup appears to be working correctly. If you\'re still seeing errors, the issue may be with:<br>';
            echo '• File permissions on the server<br>';
            echo '• Missing .env configuration<br>';
            echo '• Database connection issues<br>';
            echo '</div>';
        } else {
            echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;">';
            echo '<span class="fail">✗ Issues found:</span><br>';
            if (!$sessionPass) echo '• Session class not loading<br>';
            if (!$securityPass) echo '• Security class not loading<br>';
            echo '<br><strong>Next steps:</strong><br>';
            echo '1. Check that files are uploaded to the correct location<br>';
            echo '2. Verify file permissions (755 for directories, 644 for PHP files)<br>';
            echo '3. Check server error logs: /storage/logs/<br>';
            echo '4. Try: <code>php -l app/core/Session.php</code> to check syntax<br>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
