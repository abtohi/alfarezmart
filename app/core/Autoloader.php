<?php
/**
 * Autoloader - Auto-load classes from app/ directory
 *
 * PERF FIX: Removed per-request opcache_invalidate() and clearstatcache()
 * calls that were destroying OPcache benefits on every single class load.
 * Now uses an in-memory resolution map so each class is resolved once per
 * process and PHP can fully leverage OPcache for compiled bytecodes.
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

        /** @var array<string,string> In-process cache: className → absolute file path */
        private static $resolved = [];

        public static function register(): void
        {
            spl_autoload_register([self::class, 'load'], true, false);
        }

        public static function load(string $className): bool
        {
            // Return immediately if already resolved in this process
            if (isset(self::$resolved[$className])) {
                require_once self::$resolved[$className];
                return true;
            }

            foreach (self::$directories as $dir) {
                $file = BASE_PATH . '/' . $dir . $className . '.php';
                // file_exists() is cheap; clearstatcache per-file is NOT — removed.
                if (file_exists($file)) {
                    self::$resolved[$className] = $file;
                    // Only invalidate OPcache in development mode to allow
                    // hot-reload. In production OPcache should cache compiled
                    // bytecodes — invalidating it every request destroys perf.
                    if (function_exists('opcache_invalidate') && defined('APP_DEBUG') && APP_DEBUG === 'true') {
                        opcache_invalidate($file, false);
                    }
                    require_once $file;
                    return true;
                }
            }
            return false;
        }
    }
}
