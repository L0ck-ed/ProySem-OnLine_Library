<?php

declare(strict_types=1);

/* Autoload de Composer */
$composerAutoload = __DIR__ . '/../vendor/autoload.php';

if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

/* Autoload alternativo del proyecto */
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
            new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            if ($content === false) {
                continue;
            }

            $tieneNamespace = preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch);

            $tieneClase = preg_match(
                '/(?:class|interface|trait)\s+([A-Za-z_][A-Za-z0-9_]*)/',
                $content,
                $classMatch,
            );

            if (!$tieneNamespace || !$tieneClase) {
                continue;
            }

            $nombreCompletoClase = trim($namespaceMatch[1]) . '\\' . trim($classMatch[1]);

            $classMap[$nombreCompletoClase] = $file->getPathname();
        }
    }

    if (isset($classMap[$class])) {
        require_once $classMap[$class];
    }
});

/* Importación de clases */

use App\Core\Router;

use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\UsuarioController;
use App\Controllers\EstudianteAuthController;
use App\Controllers\PortalController;
use App\Controllers\PublicoController;

use App\Controllers\Admin\RolController;

/* Inicialización del Router */
$router = new Router();

/* Autenticación administrativa */
$router->get('/', [LoginController::class, 'index']);
$router->get('/publico', [PublicoController::class, 'index']);
$router->post('/login', [LoginController::class, 'autenticar']);
$router->get('/logout', [LoginController::class, 'logout']);

/* Dashboard administrativo */
$router->get('/dashboard', [DashboardController::class, 'index']);

/* Módulo de usuarios */
$router->get('/usuarios', [UsuarioController::class, 'index']);
$router->get('/usuarios/crear', [UsuarioController::class, 'crear']);
$router->post('/usuarios/guardar', [UsuarioController::class, 'guardar']);
$router->get('/usuarios/editar', [UsuarioController::class, 'editar']);
$router->post('/usuarios/actualizar', [UsuarioController::class, 'actualizar']);
$router->post('/usuarios/cambiar-estado', [UsuarioController::class, 'cambiarEstado']);

/* Módulo de roles y permisos */
$router->get('/roles', [RolController::class, 'index']);
$router->get('/roles/permisos', [RolController::class, 'permisos']);
$router->post('/roles/permisos/guardar', [RolController::class, 'guardarPermisos']);
$router->get('/roles/crear', [RolController::class, 'crear']);
$router->post('/roles/guardar', [RolController::class, 'guardar']);
$router->get('/roles/editar', [RolController::class, 'editar']);
$router->post('/roles/actualizar', [RolController::class, 'actualizar']);
$router->post('/roles/cambiar-estado', [RolController::class, 'cambiarEstado']);

/* Autenticación del portal */
$router->get('/portal/login', [EstudianteAuthController::class, 'index']);
$router->post('/portal/login', [EstudianteAuthController::class, 'autenticar']);
$router->get('/portal/logout', [EstudianteAuthController::class, 'logout']);

/* Inicio del portal */
$router->get('/portal/inicio', [PortalController::class, 'inicio']);

/* Catálogo del portal */
$router->get('/portal/catalogo', [PortalController::class, 'catalogo']);
$router->get('/portal/catalogo/detalle', [PortalController::class, 'detalle']);
$router->post('/portal/reservar', [PortalController::class, 'reservar']);

/* Préstamos del portal */
$router->get('/portal/prestamos', [PortalController::class, 'prestamos']);
$router->post('/portal/prestamos/devolver', [PortalController::class, 'devolver']);

/* Solicitudes de libros */
$router->get('/portal/solicitudes', [PortalController::class, 'solicitudes']);
$router->post('/portal/solicitudes', [PortalController::class, 'guardarSolicitud']);

/* Perfil del usuario */
$router->get('/portal/perfil', [PortalController::class, 'perfil']);

/* Ejecución del Router */
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
