<?php
/**
 * Migration Script: Dynamic Finance Accounts & Categories
 */
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();
require_once APP_PATH . '/config/App.php';

$db = Database::getInstance()->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    echo "Starting migration...\n";

    // 1. Create finance_accounts table
    $db->exec("CREATE TABLE IF NOT EXISTS finance_accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        dependency_account_id INT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (dependency_account_id) REFERENCES finance_accounts(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✅ Table `finance_accounts` created.\n";

    // 2. Create finance_categories table
    $db->exec("CREATE TABLE IF NOT EXISTS finance_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        type ENUM('Pemasukan', 'Pengeluaran') NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_cat_type_name (type, name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✅ Table `finance_categories` created.\n";

    // 3. Migrate distinct balance_type from finance_logs
    $stmt = $db->query("SELECT DISTINCT balance_type FROM finance_logs WHERE balance_type IS NOT NULL AND balance_type != ''");
    $existingPosts = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Also include default ones even if not in logs yet
    $defaultPosts = ['Uang Laci', 'Uang Pulsa', 'Uang Beras', 'Uang Rokok', 'Saldo Utama', 'Saldo Rokok'];
    $allPosts = array_unique(array_merge($existingPosts, $defaultPosts));

    $insertAccStmt = $db->prepare("INSERT IGNORE INTO finance_accounts (name) VALUES (:name)");
    foreach ($allPosts as $post) {
        $insertAccStmt->execute([':name' => $post]);
    }
    echo "✅ Migrated finance accounts.\n";

    // 4. Setup Default Dependencies
    // Uang Laci -> Saldo Utama
    $db->exec("UPDATE finance_accounts a 
               JOIN finance_accounts target ON target.name = 'Saldo Utama'
               SET a.dependency_account_id = target.id 
               WHERE a.name = 'Uang Laci' AND a.dependency_account_id IS NULL");
               
    // Uang Rokok -> Saldo Rokok (Main mapping)
    $db->exec("UPDATE finance_accounts a 
               JOIN finance_accounts target ON target.name = 'Saldo Rokok'
               SET a.dependency_account_id = target.id 
               WHERE a.name = 'Uang Rokok' AND a.dependency_account_id IS NULL");
               
    // Uang Pulsa -> Saldo Pulsa (if Saldo Pulsa exists, otherwise not set)
    $checkSaldoPulsa = $db->query("SELECT id FROM finance_accounts WHERE name = 'Saldo Pulsa' LIMIT 1");
    if ($checkSaldoPulsa->fetch()) {
        $db->exec("UPDATE finance_accounts a 
                   JOIN finance_accounts target ON target.name = 'Saldo Pulsa'
                   SET a.dependency_account_id = target.id 
                   WHERE a.name = 'Uang Pulsa' AND a.dependency_account_id IS NULL");
    }
    
    // Uang Beras -> Saldo Beras (if Saldo Beras exists, otherwise not set)
    $checkSaldoBeras = $db->query("SELECT id FROM finance_accounts WHERE name = 'Saldo Beras' LIMIT 1");
    if ($checkSaldoBeras->fetch()) {
        $db->exec("UPDATE finance_accounts a 
                   JOIN finance_accounts target ON target.name = 'Saldo Beras'
                   SET a.dependency_account_id = target.id 
                   WHERE a.name = 'Uang Beras' AND a.dependency_account_id IS NULL");
    }
    
    echo "✅ Setup default dependencies.\n";

    // 5. Migrate distinct categories from finance_logs
    $stmt = $db->query("SELECT DISTINCT category, detail FROM finance_logs WHERE detail IS NOT NULL AND detail != ''");
    $existingCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $insertCatStmt = $db->prepare("INSERT IGNORE INTO finance_categories (name, type) VALUES (:name, :type)");
    foreach ($existingCategories as $cat) {
        $type = in_array($cat['category'], ['Pemasukan', 'Pengeluaran']) ? $cat['category'] : 'Pengeluaran';
        $insertCatStmt->execute([
            ':name' => $cat['detail'],
            ':type' => $type
        ]);
    }
    
    // Insert some common defaults if empty
    $defaultCategories = [
        ['Belanja Modal', 'Pengeluaran'],
        ['Operasional (Listrik/Air/Internet)', 'Pengeluaran'],
        ['Gaji Karyawan', 'Pengeluaran'],
        ['Konsumsi', 'Pengeluaran'],
        ['Lain-lain', 'Pengeluaran'],
        ['Setoran Tunai', 'Pemasukan'],
        ['Lain-lain', 'Pemasukan']
    ];
    foreach ($defaultCategories as $def) {
        $insertCatStmt->execute([
            ':name' => $def[0],
            ':type' => $def[1]
        ]);
    }
    echo "✅ Migrated finance categories.\n";

    echo "\n🎉 Migration completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
