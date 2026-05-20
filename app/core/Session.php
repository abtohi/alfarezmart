<?php
/**
 * Session Manager - Secure session handling
 */

if (!class_exists('Session')) {
    class Session
    {
        public function start()
        {
            if (session_status() === PHP_SESSION_NONE) {
                $lifetime = defined('SESSION_LIFETIME') ? (int)SESSION_LIFETIME : 7200;
                
                session_set_cookie_params([
                    'lifetime' => $lifetime,
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);

                session_name('AMRT_SESSION');
                session_start();

                // Regenerate session ID periodically
                if (!isset($_SESSION['_last_regeneration'])) {
                    $_SESSION['_last_regeneration'] = time();
                } elseif (time() - $_SESSION['_last_regeneration'] > 300) {
                    session_regenerate_id(true);
                    $_SESSION['_last_regeneration'] = time();
                }
            }
        }

        public static function set($key, $value)
        {
            $_SESSION[$key] = $value;
        }

        public static function get($key, $default = null)
        {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
        }

        public static function has($key)
        {
            return isset($_SESSION[$key]);
        }

        public static function remove($key)
        {
            unset($_SESSION[$key]);
        }

        public static function flash($key, $value = null)
        {
            if ($value !== null) {
                $_SESSION['_flash'][$key] = $value;
            } else {
                $val = isset($_SESSION['_flash'][$key]) ? $_SESSION['_flash'][$key] : null;
                unset($_SESSION['_flash'][$key]);
                return $val;
            }
        }

        public static function destroy()
        {
            session_destroy();
        }
    }
}
