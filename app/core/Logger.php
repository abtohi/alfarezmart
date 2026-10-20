<?php
/**
 * Logger - Centralized Logging Service for AlfarezMart
 * 
 * Records structured logs to logs/app-YYYY-MM-DD.log with automatic rotation (14 days).
 * 
 * @package AlfarezMart\Core
 */
class Logger
{
    const LEVEL_DEBUG    = 'DEBUG';
    const LEVEL_INFO     = 'INFO';
    const LEVEL_WARNING  = 'WARNING';
    const LEVEL_ERROR    = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';

    /** @var string Path ke direktori log */
    private static $logDir = null;

    /**
     * Inisialisasi direktori log
     */
    private static function init(): string
    {
        if (self::$logDir === null) {
            $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
            self::$logDir = $base . '/logs';
            if (!is_dir(self::$logDir)) {
                @mkdir(self::$logDir, 0755, true);
            }

            // Bersihkan file log lama (1 in 50 chance per request)
            if (mt_rand(1, 50) === 1) {
                self::purgeOldLogs(14);
            }
        }
        return self::$logDir;
    }

    /**
     * Tulis pesan log
     *
     * @param string $level   Level log
     * @param string $message Pesan
     * @param array  $context Informasi tambahan
     * @return bool
     */
    public static function log(string $level, string $message, array $context = []): bool
    {
        $dir = self::init();
        $date = date('Y-m-d');
        $file = $dir . "/app-{$date}.log";

        $time = date('Y-m-d H:i:s');
        $userStr = 'Guest';

        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            $userStr = "User: {$_SESSION['user_id']}" . (isset($_SESSION['user_role']) ? "|{$_SESSION['user_role']}" : '');
        }

        $uri = $_SERVER['REQUEST_URI'] ?? 'CLI';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';

        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';

        $logLine = "[{$time}] [{$level}] [{$method} {$uri}] [{$userStr}] {$message}{$contextStr}\n";

        return (bool)@file_put_contents($file, $logLine, FILE_APPEND | LOCK_EX);
    }

    public static function debug(string $message, array $context = []): bool
    {
        return self::log(self::LEVEL_DEBUG, $message, $context);
    }

    public static function info(string $message, array $context = []): bool
    {
        return self::log(self::LEVEL_INFO, $message, $context);
    }

    public static function warning(string $message, array $context = []): bool
    {
        return self::log(self::LEVEL_WARNING, $message, $context);
    }

    public static function error(string $message, array $context = []): bool
    {
        return self::log(self::LEVEL_ERROR, $message, $context);
    }

    public static function critical(string $message, array $context = []): bool
    {
        return self::log(self::LEVEL_CRITICAL, $message, $context);
    }

    /**
     * Hapus file log yang lebih tua dari N hari
     *
     * @param int $days
     * @return void
     */
    public static function purgeOldLogs(int $days = 14): void
    {
        $dir = self::init();
        $cutoff = time() - ($days * 86400);

        $files = glob($dir . '/app-*.log');
        if (!$files) return;

        foreach ($files as $f) {
            if (is_file($f) && filemtime($f) < $cutoff) {
                @unlink($f);
            }
        }
    }
}
