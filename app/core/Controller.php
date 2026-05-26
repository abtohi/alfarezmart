<?php
/**
 * Base Controller
 */

if (!class_exists('Controller')) {
    class Controller
    {
        protected $security;

        public function __construct()
        {
            $this->security = new Security();
        }

    /**
     * Render a view with layout
     */
    protected function view($viewPath, $data = [])
    {
        // Make data available as variables
        // Inject current user into all views
        $data['currentUser'] = AuthController::currentUser();
        extract($data);
        
        // Generate CSRF token for forms
        $csrfToken = $this->security->getCSRFToken();
        
        // Set content view path
        $contentView = APP_PATH . '/views/' . str_replace('.', '/', $viewPath) . '.php';
        
        if (!file_exists($contentView)) {
            http_response_code(404);
            echo "View not found: {$viewPath}";
            return;
        }

        // Start output buffering for content
        ob_start();
        require $contentView;
        $content = ob_get_clean();

        // Load layout
        require APP_PATH . '/views/layouts/app.php';
    }

    /**
     * Return JSON response (for API/AJAX)
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        $output = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($output === false) {
            $output = json_encode(['error' => 'Failed to encode JSON response: ' . json_last_error_msg()]);
        }
        echo $output;
        exit;
    }

    /**
     * Redirect to URL
     */
    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    private $_jsonData = null;

    /**
     * Get sanitized POST data
     */
    protected function input($key = null, $default = null)
    {
        // Check JSON body first if POST is empty or key not in POST
        if ($this->_jsonData === null) {
            $raw = file_get_contents('php://input');
            $this->_jsonData = json_decode($raw, true);
            if (!is_array($this->_jsonData)) {
                $this->_jsonData = [];
            }
        }

        if ($key === null) {
            $merged = array_merge($this->_jsonData, $_POST);
            return Security::sanitizeArray($merged);
        }
        
        $value = isset($_POST[$key]) ? $_POST[$key] : (isset($this->_jsonData[$key]) ? $this->_jsonData[$key] : $default);
        return $value !== null ? Security::sanitize($value) : $default;
    }

    /**
     * Get sanitized GET data
     */
    protected function query($key = null, $default = null)
    {
        if ($key === null) {
            return Security::sanitizeArray($_GET);
        }
        $value = isset($_GET[$key]) ? $_GET[$key] : $default;
        return $value !== null ? Security::sanitize($value) : $default;
    }

    /**
     * Validate CSRF token on POST requests
     */
    protected function validateCSRF()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check POST body first, then X-CSRF-Token header
            $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
            if (empty($token)) {
                $token = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
            }
            if (!$this->security->validateCSRFToken($token)) {
                $this->json(['error' => 'Invalid CSRF token'], 403);
            }
        }
    }
    }
}
