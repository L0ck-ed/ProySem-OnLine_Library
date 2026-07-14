<?php

declare(strict_types=1);

$composerAutoload = __DIR__ . '/../vendor/autoload.php';

if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

spl_autoload_register(function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }

    static $classMap = null;

    if ($classMap === null) {
        $classMap = [];
        $basePath = realpath(__DIR__ . '/../App');

        if ($basePath === false) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            if ($content === false) {
                continue;
            }

            if (
                preg_match('/namespace\\s+([^;]+);/', $content, $namespaceMatch) &&
                preg_match('/(?:class|interface|trait)\\s+([A-Za-z_][A-Za-z0-9_]*)/', $content, $classMatch)
            ) {
                $classMap[$namespaceMatch[1] . '\\' . $classMatch[1]] = $file->getPathname();
            }
        }
    }

    if (isset($classMap[$class])) {
        require_once $classMap[$class];
    }
});

use App\Core\Router;
use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\UsuarioController;
use App\Controllers\EstudianteAuthController;
use App\Controllers\PortalController;

$router = new Router();

$router->get('/', [LoginController::class, 'index']);
$router->post('/login', [LoginController::class, 'autenticar']);
$router->get('/logout', [LoginController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/usuarios', [UsuarioController::class, 'index']);
$router->get('/usuarios/crear', [UsuarioController::class, 'crear']);
$router->post('/usuarios/guardar', [UsuarioController::class, 'guardar']);

// Portal del estudiante
$router->get('/portal/login', [EstudianteAuthController::class, 'index']);
$router->post('/portal/login', [EstudianteAuthController::class, 'autenticar']);
$router->get('/portal/logout', [EstudianteAuthController::class, 'logout']);
$router->get('/portal/inicio', [PortalController::class, 'inicio']);
$router->get('/portal/catalogo', [PortalController::class, 'catalogo']);
$router->get('/portal/catalogo/detalle', [PortalController::class, 'detalle']);
$router->get('/portal/prestamos', [PortalController::class, 'prestamos']);
$router->get('/portal/solicitudes', [PortalController::class, 'solicitudes']);
$router->post('/portal/solicitudes', [PortalController::class, 'guardarSolicitud']);
$router->get('/portal/perfil', [PortalController::class, 'perfil']);

$router->dispatch(
    $_SERVER['REQUEST_URI'],
    $_SERVER['REQUEST_METHOD']
);
