<?php

declare(strict_types=1);

namespace App\Services;

use App\Middleware\MiddlewareInterface;
use App\Middleware\CsrfMiddleware;

class Router
{
    private static array $routes = [];
    private static array $groupStack = [];

    public static function get(string $uri, array|callable $action, array $middlewares = []): void
    {
        self::addRoute('GET', $uri, $action, $middlewares);
    }

    public static function post(string $uri, array|callable $action, array $middlewares = []): void
    {
        self::addRoute('POST', $uri, $action, $middlewares);
    }

    public static function group(array $attributes, callable $callback): void
    {
        self::$groupStack[] = $attributes;
        $callback();
        array_pop(self::$groupStack);
    }

    private static function addRoute(string $method, string $uri, array|callable $action, array $middlewares = []): void
    {
        $prefix = '';
        $groupMiddlewares = [];

        foreach (self::$groupStack as $group) {
            if (!empty($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (!empty($group['middleware'])) {
                $m = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $groupMiddlewares = array_merge($groupMiddlewares, $m);
            }
        }

        $fullUri = rtrim($prefix, '/') . '/' . ltrim($uri, '/');
        $fullUri = $fullUri === '' ? '/' : $fullUri;

        $allMiddlewares = array_merge($groupMiddlewares, $middlewares);

        // Auto-attach CSRF protection for non-GET routes if not disabled
        if ($method !== 'GET' && !in_array(CsrfMiddleware::class, $allMiddlewares, true)) {
            array_unshift($allMiddlewares, CsrfMiddleware::class);
        }

        self::$routes[] = [
            'method' => $method,
            'uri' => $fullUri,
            'action' => $action,
            'middlewares' => $allMiddlewares,
        ];
    }

    public static function dispatch(string $requestMethod, string $requestUri): void
    {
        // Strip query string
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';

        // Normalize base path if project runs in subfolder (e.g. /Projects All/per/gym/public)
        $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName !== '/' && $scriptName !== '\\' && !empty($scriptName)) {
            $scriptName = str_replace('\\', '/', $scriptName);
            if (str_starts_with($path, $scriptName)) {
                $path = substr($path, strlen($scriptName));
            }
        }

        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Convert route uri to regex pattern: e.g. /members/{id} -> #^/members/(?P<id>[^/]+)$#
            $pattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[^/]+)', $route['uri']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                // Extract named params
                $params = [];
                foreach ($matches as $key => $val) {
                    if (is_string($key)) {
                        $params[$key] = $val;
                    }
                }

                // Execute middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $instance = is_object($middleware) ? $middleware : new $middleware();
                    if ($instance instanceof MiddlewareInterface) {
                        if (!$instance->handle($params)) {
                            return;
                        }
                    }
                }

                // Execute controller action
                $action = $route['action'];
                if (is_callable($action)) {
                    $response = call_user_func_array($action, $params);
                    if (is_string($response)) {
                        echo $response;
                    }
                    return;
                }

                if (is_array($action)) {
                    [$controllerClass, $method] = $action;
                    $controller = new $controllerClass();
                    $response = call_user_func_array([$controller, $method], $params);
                    if (is_string($response)) {
                        echo $response;
                    }
                    return;
                }
            }
        }

        // Route not found 404
        http_response_code(404);
        echo view('errors.404', ['message' => 'The requested page was not found.']);
    }
}
