<?php
define('BASE_PATH', 'c:/xampp/htdocs/AlfarezMart');
define('APP_PATH', BASE_PATH.'/app');
define('STORAGE_PATH', 'c:/xampp/htdocs/storage');
$_SERVER['HTTP_HOST'] = 'localhost';
require 'app/core/Autoloader.php';
Autoloader::register();
require 'app/config/App.php';
$db = Database::getInstance()->getConnection();

$db->exec("CREATE TABLE IF NOT EXISTS user_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    page_url VARCHAR(512) NOT NULL,
    page_title VARCHAR(255) DEFAULT NULL,
    action_type VARCHAR(50) DEFAULT 'page_view',
    lat DECIMAL(10,7) DEFAULT NULL,
    lng DECIMAL(10,7) DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(512) DEFAULT NULL,
    session_id VARCHAR(128) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ual_user (user_id),
    INDEX idx_ual_created (created_at),
    INDEX idx_ual_user_date (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "✅ user_activity_logs table created/ensured.\n";
