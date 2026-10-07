<?php
/**
 * Router Core Engine
 * Light-weight, clean RESTful router supporting parameterized URLs, HTTP verbs, and Controller dispatching.
 */

class Router {
    private static $routes = [];

    public static function get($pattern, $handler, $middleware = []) {
        self::add('GET', $pattern, $handler, $middleware);
    }

    public static function post($pattern, $handler, $middleware = []) {
        self::add('POST', $pattern, $handler, $middleware);
    }

    public static function add($method, $pattern, $handler, $middleware = []) {
        self::$routes[] = [
            'method'     => strtoupper($method),
            'pattern'    => '/' . trim($pattern, '/'),
            'handler'    => $handler,
            'middleware' => is_array($middleware) ? $middleware : [$middleware]
        ];
    }

    /**
     * Dispatch incoming request
     */
    public static function dispatch() {
        $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $rawUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $requestUri = parse_url($rawUri, PHP_URL_PATH);
        $requestUri = rawurldecode($requestUri);

        // Strip subfolder prefix if present (e.g., /Projects All/per/gym)
        $scriptDir = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
        $scriptDir = str_replace('\\', '/', rawurldecode($scriptDir));
        
        if ($scriptDir !== '/' && $scriptDir !== '' && strpos($requestUri, $scriptDir) === 0) {
            $requestUri = substr($requestUri, strlen($scriptDir));
        }

        // Strip index.php if explicitly visited
        if (strpos($requestUri, '/index.php') === 0) {
            $requestUri = substr($requestUri, 10);
        }

        $uri = '/' . trim($requestUri, '/');

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod && $route['method'] !== 'ANY') {
                continue;
            }

            // Convert route pattern with parameters (e.g. /members/:id) to Regex
            $patternRegex = preg_replace('#:([a-zA-Z0-9_]+)#', '(?P<$1>[^/]+)', $route['pattern']);
            $patternRegex = '#^' . $patternRegex . '$#';

            if (preg_match($patternRegex, $uri, $matches)) {
                // Filter named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run Middleware if any
                foreach ($route['middleware'] as $mw) {
                    if (is_callable($mw)) {
                        call_user_func($mw);
                    } elseif (is_string($mw)) {
                        self::runMiddleware($mw);
                    }
                }

                // Execute handler
                self::executeHandler($route['handler'], $params);
                return;
            }
        }

        // Route not found -> 404
        http_response_code(404);
        View::render('errors/404', ['uri' => $uri], 'layouts/app');
    }

    private static function runMiddleware($name) {
        switch ($name) {
            case 'auth':
                Auth::requireLogin();
                break;
            case 'csrf':
                CSRF::validateOrAbort();
                break;
            case 'superadmin':
                RBAC::requireRole('superadmin');
                break;
            case 'admin':
                RBAC::requireRole(['superadmin', 'owner', 'manager']);
                break;
            case 'staff':
                RBAC::requireRole(['superadmin', 'owner', 'manager', 'staff']);
                break;
            case 'member':
                RBAC::requireRole('member');
                break;
        }
    }

    private static function executeHandler($handler, $params) {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controllerName, $method) = explode('@', $handler);
            $controllerFile = BASE_PATH . '/controllers/' . $controllerName . '.php';

            if (!file_exists($controllerFile)) {
                die("Controller file {$controllerName}.php not found.");
            }

            require_once $controllerFile;

            if (!class_exists($controllerName)) {
                die("Controller class {$controllerName} does not exist.");
            }

            $controller = new $controllerName();
            if (!method_exists($controller, $method)) {
                die("Method {$method} does not exist on controller {$controllerName}.");
            }

            call_user_func_array([$controller, $method], $params);
            return;
        }

        die("Invalid route handler specified.");
    }
}
