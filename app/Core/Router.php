<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, callable|array $handler): void
    {
        $this->add('GET', $uri, $handler);
    }

    public function post(string $uri, callable|array $handler): void
    {
        $this->add('POST', $uri, $handler);
    }

    public function put(string $uri, callable|array $handler): void
    {
        $this->add('PUT', $uri, $handler);
    }

    public function delete(string $uri, callable|array $handler): void
    {
        $this->add('DELETE', $uri, $handler);
    }

    private function add(
        string $method,
        string $uri,
        callable|array $handler
    ): void {
        $uri = $this->normalizeUri($uri);

        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'handler' => $handler
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $requestUri = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        $requestUri = $this->normalizeUri($requestUri);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace(
                '/\{([a-zA-Z0-9_]+)\}/',
                '(?P<$1>[^/]+)',
                $route['uri']
            );

            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $requestUri, $matches)) {
                continue;
            }

            $params = array_filter(
                $matches,
                fn ($key) => !is_int($key),
                ARRAY_FILTER_USE_KEY
            );

            $this->runHandler($route['handler'], $params);

            return;
        }

        http_response_code(404);

        echo '<h1>404</h1><p>Ruta no encontrada.</p>';
    }

    private function runHandler(
        callable|array $handler,
        array $params = []
    ): void {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (
            is_array($handler)
            && count($handler) === 2
            && is_string($handler[0])
        ) {
            [$controllerClass, $method] = $handler;

            $controller = new $controllerClass();

            call_user_func_array(
                [$controller, $method],
                $params
            );

            return;
        }

        throw new \RuntimeException(
            'Handler de ruta no valido.'
        );
    }

    private function normalizeUri(string $uri): string
    {
        $uri = '/' . trim($uri, '/');

        return $uri === '//' ? '/' : $uri;
    }
}