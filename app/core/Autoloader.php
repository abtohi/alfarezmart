<?php
/**
 * Autoloader - Auto-load classes from app/ directory
 * Note: Added to invalidate OPcache
 */

if (!class_exists('Autoloader')) {
    class Autoloader
    {
        private static $directories = [
            'app/core/',
            'app/models/',
            'app/controllers/',
            'app/config/',
            'app/services/',
        ];

        public static function register()
        {
            spl_autoload_register([self::class, 'load'], true, false);
        }

        public static function load($className)
        {
            foreach (self::$directories as $dir) {
                $file = BASE_PATH . '/' . $dir . $className . '.php';
                clearstatcache(true, $file);
                if (file_exists($file)) {
                    if (function_exists('opcache_invalidate')) {
                        opcache_invalidate($file, true);
                    }
                    require_once $file;
                    return true;
                }
            }
            // Log missing class if needed
            // error_log("Autoloader: Class '{$className}' not found in any directory");
            return false;
        }
    }
}
