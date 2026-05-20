<?php
/**
 * Router - Simple URL router for MVC
 */

if (!class_exists('Router')) {
    class Router
    {
        private $routes = [];
        private $apiRoutes = [];

    /**
     * Register a GET route
     */
    public function get($path, $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * Register a POST route
     */
    public function post($path, $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }

    /**
     * Register a PUT route (via POST with _method)
     */
    public function put($path, $handler)
    {
        $this->routes['PUT'][$path] = $handler;
    }

    /**
     * Register a DELETE route (via POST with _method)
     */
    public function delete($path, $handler)
    {
        $this->routes['DELETE'][$path] = $handler;
    }

    /**
     * Dispatch the current request
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();

        // Handle method override for PUT/DELETE
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // Handle JSON body for API
        if (strpos($uri, '/api/') === 0) {
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            if (strpos($contentType, 'application/json') !== false) {
                $jsonBody = json_decode(file_get_contents('php://input'), true);
                if ($jsonBody) {
                    $_POST = array_merge($_POST, $jsonBody);
                }
            }
        }

        // Try exact match first
        if (isset($this->routes[$method][$uri])) {
            $this->callHandler($this->routes[$method][$uri]);
            return;
        }

        // Try pattern matching (e.g., /products/{id})
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $_GET = array_merge($_GET, $params);
                $this->callHandler($handler, $params);
                return;
            }
        }

        // 404 - Check if it's an API request
        if (strpos($uri, '/api/') === 0) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Endpoint not found']);
        } else {
            // For SPA-like behavior, serve the main app for unknown routes
            http_response_code(404);
            $this->callHandler('DashboardController@notFound');
        }
    }

    /**
     * Get clean URI path
     */
    private function getUri()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? urldecode($_SERVER['REQUEST_URI']) : '/';
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Get script name (e.g. /AlfarezMart App/index.php)
        $scriptName = $_SERVER['SCRIPT_NAME'];
        
        // Get base path (e.g. /AlfarezMart App)
        $basePath = dirname($scriptName);
        
        // If URI starts with base path, remove it
        if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Remove trailing slash (except for root)
        $uri = rtrim($uri, '/');
        if (empty($uri)) $uri = '/';
        
        return $uri;
    }

    /**
     * Call controller method
     */
    private function callHandler($handler, $params = [])
    {
        if (is_string($handler)) {
            list($controllerName, $method) = explode('@', $handler);
            
            if (!class_exists($controllerName)) {
                http_response_code(500);
                echo "Controller not found: {$controllerName}";
                return;
            }

            $controller = new $controllerName();
            
            if (!method_exists($controller, $method)) {
                http_response_code(500);
                echo "Method not found: {$controllerName}@{$method}";
                return;
            }

            // In PHP 8+, associative arrays passed to call_user_func_array are treated as named arguments.
            // Using array_values ensures they are treated as positional arguments.
            call_user_func_array([$controller, $method], array_values($params));
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, array_values($params));
        }
    }
    }
}
