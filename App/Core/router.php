<?php

namespace App\Core;

use App\Config\Config;

class Router
{
    private array $routes = [];

    public function get(string $ruta, array $accion): void
    {
        $this->routes['GET'][$ruta] = $accion;
    }

    public function post(string $ruta, array $accion): void
    {
        $this->routes['POST'][$ruta] = $accion;
    }

    public function dispatch(string $url, string $method): void
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '/';
        $base = Config::BASE_URL;

        if (str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        if ($path === '' || $path === false) {
            $path = '/';
        }

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
}
