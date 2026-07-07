<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\UsuarioController;

$router = new Router();

$router->get('/', [LoginController::class, 'index']);
$router->post('/login', [LoginController::class, 'autenticar']);
$router->get('/logout', [LoginController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/usuarios', [UsuarioController::class, 'index']);
$router->get('/usuarios/crear', [UsuarioController::class, 'crear']);
$router->post('/usuarios/guardar', [UsuarioController::class, 'guardar']);

$router->dispatch(
    $_SERVER['REQUEST_URI'],
    $_SERVER['REQUEST_METHOD']
);
