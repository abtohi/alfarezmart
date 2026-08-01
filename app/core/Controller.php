<?php
/**
 * Base Controller
 */

if (!function_exists('invoicePhotoUrl')) {
    function invoicePhotoUrl(string $storedPath): string {
        if (empty($storedPath)) return '';
        $filename = basename($storedPath);
        return BASE_URL . 'api/storage/invoice-photo?file=' . urlencode($filename);
    }
}

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
            // Check POST/JSON body first, then X-CSRF-Token header
            $token = $this->input('csrf_token');
            if (empty($token)) {
                $token = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
            }
            if (!$this->security->validateCSRFToken($token)) {
                $this->json(['error' => 'Invalid CSRF token'], 403);
            }
        }
    }

    /**
     * Get current user level (kept simple, lowercase string).
     */
    protected function userLevel(): string
    {
        return strtolower((string)($_SESSION['user_level'] ?? ''));
    }

    protected function isStaff(): bool
    {
        return $this->userLevel() === 'staff';
    }

    protected function isSuperadmin(): bool
    {
        return $this->userLevel() === 'superadmin';
    }

    /**
     * Guard: block access for non-superadmin (web view: redirect; API: 403 JSON).
     */
    protected function requireSuperadmin(): void
    {
        if ($this->isSuperadmin()) return;
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/api/') !== false) {
            $this->json(['error' => 'Akses ditolak. Fitur ini hanya untuk Superadmin.'], 403);
        }
        $_SESSION['_flash']['error'] = 'Akses ditolak. Fitur ini hanya untuk Superadmin.';
        header('Location: ' . BASE_URL);
        exit;
    }

    /**
     * Guard: block staff from destructive product actions (edit/delete).
     * Staff hanya boleh CREATE produk (untuk POS custom).
     */
    protected function blockStaffMutations(string $action = 'mengubah'): void
    {
        if (!$this->isStaff()) return;
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $msg = 'Staff tidak diizinkan ' . $action . ' produk. Hubungi Superadmin.';
        if (strpos($uri, '/api/') !== false) {
            $this->json(['error' => $msg], 403);
        }
        $_SESSION['_flash']['error'] = $msg;
        header('Location: ' . BASE_URL . 'products');
        exit;
    }

    /**
     * Check if a specific service/feature is allowed for the user's role.
     * Superadmin always has full access.
     */
    public function hasServiceAccess(string $serviceKey, ?string $userLevel = null): bool
    {
        $level = strtolower((string)($userLevel ?? $this->userLevel()));
        if ($level === 'superadmin') {
            return true;
        }

        // Default allowed services per role (PPOB is strictly OFF for admin & staff by default)
        $defaults = [
            'admin' => [
                'finance', 'reports', 'debts', 'purchases', 'suppliers',
                'customers', 'products', 'catalog', 'multivariant',
                'product_history', 'supplier_analysis', 'export_data', 'order_estimate'
            ],
            'staff' => [
                'suppliers', 'customers', 'products', 'catalog',
                'product_history', 'supplier_analysis', 'order_estimate'
            ],
        ];

        try {
            $settingModel = new SettingModel();
            $savedJson = $settingModel->get('role_permissions_' . $level, null);
            if ($savedJson !== null) {
                $allowed = json_decode($savedJson, true);
                if (is_array($allowed)) {
                    return in_array($serviceKey, $allowed, true);
                }
            }
        } catch (\Throwable $e) {
            error_log('[Controller] Error reading role permissions: ' . $e->getMessage());
        }

        $roleDefaults = $defaults[$level] ?? [];
        return in_array($serviceKey, $roleDefaults, true);
    }

    /**
     * Guard: Block access if the requested service is disabled for the user's role.
     */
    public function requireService(string $serviceKey): void
    {
        if ($this->hasServiceAccess($serviceKey)) {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $msg = 'Akses ditolak. Layanan ini tidak diizinkan oleh Superadmin.';
        if (strpos($uri, '/api/') !== false) {
            $this->json(['error' => $msg], 403);
        }
        $_SESSION['_flash']['error'] = $msg;
        header('Location: ' . BASE_URL);
        exit;
    }
    }
}
