<?php
/**
 * Security - CSRF protection, XSS prevention, input sanitization
 */

if (!class_exists('Security')) {
    class Security
    {
        /**
         * Generate CSRF token
         */
        public function getCSRFToken()
        {
            if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
                $this->regenerateCSRFToken();
            }

            return $_SESSION['csrf_token'];
        }

        /**
         * Regenerate CSRF token
         */
        private function regenerateCSRFToken()
        {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }

        /**
         * Validate CSRF token
         * @param string $token
         */
        public function validateCSRFToken($token)
        {
            if (!isset($_SESSION['csrf_token'])) return false;
            return hash_equals($_SESSION['csrf_token'], $token);
        }

        /**
         * Sanitize string input (XSS prevention)
         * @param mixed $input
         */
        public static function sanitize($input)
        {
            if (is_null($input)) return '';
            if (is_numeric($input)) return $input;
            // Strip tags first, then decode HTML entities to preserve special chars like &
            $stripped = trim(strip_tags($input));
            return html_entity_decode($stripped, ENT_QUOTES, 'UTF-8');
        }

        /**
         * Sanitize array of inputs
         */
        public static function sanitizeArray(array $data)
        {
            $sanitized = [];
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $sanitized[$key] = self::sanitizeArray($value);
                } else {
                    $sanitized[$key] = self::sanitize($value);
                }
            }
            return $sanitized;
        }

        /**
         * Generate CSRF hidden input field
         */
        public function csrfField()
        {
            $token = $this->getCSRFToken();
            return '<input type="hidden" name="csrf_token" value="' . $token . '">';
        }

        /**
         * Sanitize filename
         * @param string $filename
         */
        public static function sanitizeFilename($filename)
        {
            return preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
        }
    }
}
