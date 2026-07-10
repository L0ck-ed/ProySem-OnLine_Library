<?php

namespace App\Core;

use App\Config\Config;

class Router
{
    private array $routes = [];

    public function get(string $ruta, array $accion): void
    {
        $this->routes['GET'][$this->normalizeRoute($ruta)] = $accion;
    }

    public function post(string $ruta, array $accion): void
    {
        $this->routes['POST'][$this->normalizeRoute($ruta)] = $accion;
    }

    public function dispatch(string $url, string $method): void
    {
        $path = $this->normalizeRequestPath($url);

        if (!isset($this->routes[$method][$path])) {
            http_response_code(404);
            echo '404 - Página no encontrada';
            return;
        }

        [$controller, $function] = $this->routes[$method][$path];

        if (!class_exists($controller)) {
            http_response_code(500);
            echo 'Error: controlador no encontrado.';
            return;
        }

        $obj = new $controller();

        if (!method_exists($obj, $function)) {
            http_response_code(500);
            echo 'Error: método no encontrado.';
            return;
        }

        $obj->$function();
    }

    private function normalizeRequestPath(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '/';
        $path = str_replace('\\', '/', $path);

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = rtrim(dirname($scriptName), '/');
        $projectDir = rtrim(dirname($scriptDir), '/');

        $bases = array_unique(array_filter([
            rtrim(Config::publicUrl(), '/'),
            rtrim(Config::baseUrl(), '/'),
            $scriptDir,
            $projectDir,
        ]));

        usort($bases, fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($bases as $base) {
            if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
                $path = substr($path, strlen($base));
                break;
            }
        }

        if (str_starts_with($path, '/Public/')) {
            $path = substr($path, strlen('/Public'));
        }

        if ($path === '' || $path === false || $path === '/index.php') {
            return '/';
        }

        return $this->normalizeRoute($path);
    }

    private function normalizeRoute(string $route): string
    {
        $route = '/' . trim($route, '/');
        return $route === '//' ? '/' : $route;
    }
}
