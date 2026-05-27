<?php

declare(strict_types=1);

/**
 * LaunchStack — Application Router
 * Lightweight, fast custom router
 */

namespace LaunchStack;

use LaunchStack\Helpers\Response;

class Router
{
    private array $routes = [];
    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function patch(string $path, callable|array $handler): void
    {
        $this->addRoute('PATCH', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        // Convert :param to named capture groups
        $pattern = preg_replace('/\/:([a-zA-Z_][a-zA-Z0-9_]*)/', '/(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $this->basePath . $pattern . '$#';

        $this->routes[] = [
            'method'  => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri    = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            // Extract named params
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            $handler = $route['handler'];

            if (is_array($handler)) {
                [$class, $method] = $handler;
                $instance = new $class();
                $instance->$method(...array_values($params));
            } else {
                $handler(...array_values($params));
            }

            return;
        }

        // No route matched
        Response::notFound("Route not found: {$method} {$uri}");
    }
}
