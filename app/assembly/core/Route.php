<?php

namespace App\Assembly\Core;

class Route
{
    private static array $routes = [];
    private static array $namedRoutes = [];
    private static array $middlewareGroups = [];
    private static array $groupStack = [];

    // Register routes dynamically (GET, POST, PUT, DELETE, PATCH)
    public static function get(string $uri, $callback, string $name = null) { self::addRoute('GET', $uri, $callback, $name); }
    public static function post(string $uri, $callback, string $name = null) { self::addRoute('POST', $uri, $callback, $name); }
    public static function put(string $uri, $callback, string $name = null) { self::addRoute('PUT', $uri, $callback, $name); }
    public static function delete(string $uri, $callback, string $name = null) { self::addRoute('DELETE', $uri, $callback, $name); }
    public static function patch(string $uri, $callback, string $name = null) { self::addRoute('PATCH', $uri, $callback, $name); }

    // Add route with middleware and prefix handling
    private static function addRoute(string $method, string $uri, $callback, string $name = null)
    {
        $prefix = self::getCurrentGroupPrefix();
        $middleware = self::getCurrentGroupMiddleware();
        $uri = trim($prefix . '/' . trim($uri, '/'), '/');
        
        $route = [
            'method' => $method,
            'uri' => $uri,
            'callback' => $callback,
            'middleware' => $middleware
        ];

        self::$routes[] = $route;

        if ($name) {
            self::$namedRoutes[$name] = $uri;
        }
    }

    // Group Routes with Prefix and Middleware
    public static function group(array $attributes, callable $callback)
    {
        self::$groupStack[] = $attributes;
        call_user_func($callback);
        array_pop(self::$groupStack);
    }

    private static function getCurrentGroupPrefix(): string
    {
        $prefix = '';
        foreach (self::$groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }
        return $prefix;
    }

    private static function getCurrentGroupMiddleware(): array
    {
        $middlewares = [];
        foreach (self::$groupStack as $group) {
            if (isset($group['middleware'])) {
                $middlewares = array_merge($middlewares, (array) $group['middleware']);
            }
        }
        return $middlewares;
    }

    // Dispatch Request
    public static function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        
        foreach (self::$routes as $route) {
            if ($route['method'] === $requestMethod && self::matchRoute($route['uri'], $requestUri, $params)) {
                return self::handleRequest($route, $params);
            }
        }
        
        http_response_code(404);
        echo "404 - Not Found";
        exit;
    }

    // Match Routes with Parameters
    private static function matchRoute(string $routeUri, string $requestUri, &$params): bool
    {
        $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $routeUri);
        $routePattern = "@^" . $routePattern . "$@";
        
        if (preg_match($routePattern, $requestUri, $matches)) {
            array_shift($matches);
            $params = $matches;
            return true;
        }

        return false;
    }

    // Handle the Request with Middleware
    private static function handleRequest(array $route, array $params)
    {
        // Execute Middleware Before Request
        foreach ($route['middleware'] as $middleware) {
            if (class_exists($middleware)) {
                (new $middleware())->handle();
            }
        }
        
        // Execute the Route Callback
        return self::executeCallback($route['callback'], $params);
    }

    private static function executeCallback($callback, array $params)
    {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }

        if (is_array($callback) && isset($callback[0], $callback[1])) {
            [$controller, $method] = $callback;
            if (class_exists($controller) && method_exists($controller, $method)) {
                $controllerInstance = new $controller();
                return call_user_func_array([$controllerInstance, $method], $params);
            }
        }

        http_response_code(500);
        echo "500 - Server Error: Invalid Route Handler";
        exit;
    }

    // Generate a URL for Named Routes
    public static function route(string $name, array $params = []): string
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new \Exception("Route named '{$name}' not found.");
        }

        $route = self::$namedRoutes[$name];

        foreach ($params as $key => $value) {
            $route = preg_replace('/\{' . $key . '\}/', $value, $route);
        }

        return '/' . trim($route, '/');
    }
}
