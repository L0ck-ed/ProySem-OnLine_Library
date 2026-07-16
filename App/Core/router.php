<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\Config;
use App\Helpers\Csrf;

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
        $method = strtoupper($method);
        $path = $this->normalizeRequestPath($url);

        if ($method === 'POST' && !Csrf::validarSolicitud()) {
            throw new HttpException('Token CSRF inválido o expirado.', 419);
        }

        if (!isset($this->routes[$method][$path])) {
            throw new HttpException('Ruta no encontrada: ' . $path, 404);
        }

        [$controller, $function] = $this->routes[$method][$path];

        if (!class_exists($controller)) {
            throw new HttpException('Controlador no encontrado.', 500);
        }

        $obj = new $controller();

        if (!method_exists($obj, $function)) {
            throw new HttpException('Método de controlador no encontrado.', 500);
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

        usort($bases, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

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
