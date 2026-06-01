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
                $lifetime = 315360000; // 10 years (effectively never expire)
                ini_set('session.gc_maxlifetime', $lifetime);
                
                session_set_cookie_params([
                    'lifetime' => $lifetime,
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);

                session_name('AMRT_SESSION');
                session_start();

                // Session ID regeneration on login only (see AuthController::login())
                // Periodic regeneration removed: it caused race conditions with CSRF tokens
                // when multiple parallel requests hit the server (e.g. background sync + page POST)
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
