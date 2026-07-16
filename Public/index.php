<?php

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');
$logDirectory = __DIR__ . '/../App/Storage/logs';
if (!is_dir($logDirectory)) {
    @mkdir($logDirectory, 0775, true);
}
ini_set('error_log', $logDirectory . '/php-error.log');

require_once __DIR__ . '/../App/Helpers/polyfills.php';

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
            new RecursiveDirectoryIterator(
                $basePath,
                FilesystemIterator::SKIP_DOTS,
            ),
        );

        foreach ($iterator as $file) {
            if (
                !$file->isFile() ||
                strtolower($file->getExtension()) !== 'php'
            ) {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            if ($content === false) {
                continue;
            }

            $tieneNamespace = preg_match(
                '/namespace\s+([^;]+);/',
                $content,
                $namespaceMatch,
            );

            $tieneClase = preg_match(
                '/(?:class|interface|trait)\s+([A-Za-z_][A-Za-z0-9_]*)/',
                $content,
                $classMatch,
            );

            if (!$tieneNamespace || !$tieneClase) {
                continue;
            }

            $nombreCompletoClase =
                trim($namespaceMatch[1]) .
                '\\' .
                trim($classMatch[1]);

            $classMap[$nombreCompletoClase] = $file->getPathname();
        }
    }

    if (isset($classMap[$class])) {
        require_once $classMap[$class];
    }
});

/* Manejo centralizado de errores y excepciones. */
\App\Core\ErrorHandler::registrar();

/* Encabezados defensivos recomendados por OWASP. */
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    if ($esHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/* ==================================================
   IMPORTACIÓN DE CLASES
   ================================================== */

use App\Core\Router;

use App\Controllers\AccesoController;
use App\Controllers\DashboardController;
use App\Controllers\LoginController;
use App\Controllers\PortalController;
use App\Controllers\PublicoController;
use App\Controllers\UsuarioController;
use App\Controllers\UsuarioRegularAuthController;

use App\Controllers\Admin\CategoriaController;
use App\Controllers\Admin\EstadisticaController;
use App\Controllers\Admin\EstudianteController;
use App\Controllers\Admin\InterbibliotecarioController;
use App\Controllers\Admin\EstructuraAcademicaController;
use App\Controllers\Admin\LibroController;
use App\Controllers\Admin\ProfesorController;
use App\Controllers\Admin\ReservaController;
use App\Controllers\Admin\RolController;
use App\Controllers\Admin\SolicitudController;

/* ==================================================
   INICIALIZACIÓN DEL ROUTER
   ================================================== */

$router = new Router();

/* ==================================================
   PÁGINA INICIAL Y PÁGINA PÚBLICA
   ================================================== */

$router->get('/', [AccesoController::class, 'index']);
$router->get('/publico', [PublicoController::class, 'index']);
$router->post('/publico/contacto', [PublicoController::class, 'contactar']);

/* ==================================================
   AUTENTICACIÓN ADMINISTRATIVA
   ================================================== */

$router->get('/admin/login', [LoginController::class, 'index']);
$router->post('/login', [LoginController::class, 'autenticar']);
$router->post('/logout', [LoginController::class, 'logout']);

/* ==================================================
   DASHBOARD ADMINISTRATIVO
   ================================================== */

$router->get('/dashboard', [DashboardController::class, 'index']);

/* ==================================================
   ESTADÍSTICAS ADMINISTRATIVAS
   ================================================== */

$router->get('/estadisticas', [EstadisticaController::class, 'index']);
$router->get('/estadisticas/excel', [EstadisticaController::class, 'exportarExcel']);

/* ==================================================
   ESTRUCTURA ACADÉMICA
   ================================================== */

$router->get('/estructura-academica', [EstructuraAcademicaController::class, 'index']);
$router->get('/estructura-academica/crear', [EstructuraAcademicaController::class, 'crear']);
$router->post('/estructura-academica/guardar', [EstructuraAcademicaController::class, 'guardar']);
$router->get('/estructura-academica/editar', [EstructuraAcademicaController::class, 'editar']);
$router->post('/estructura-academica/actualizar', [EstructuraAcademicaController::class, 'actualizar']);
$router->post('/estructura-academica/cambiar-estado', [EstructuraAcademicaController::class, 'cambiarEstado']);
$router->post('/estructura-academica/eliminar', [EstructuraAcademicaController::class, 'eliminar']);
$router->get('/estructura-academica/departamentos-por-facultad', [EstructuraAcademicaController::class, 'departamentosPorFacultad']);

/* ==================================================
   USUARIOS
   ================================================== */

$router->get('/usuarios', [UsuarioController::class, 'index']);
$router->get('/usuarios/crear', [UsuarioController::class, 'crear']);
$router->post('/usuarios/guardar', [UsuarioController::class, 'guardar']);
$router->get('/usuarios/editar', [UsuarioController::class, 'editar']);
$router->post('/usuarios/actualizar', [UsuarioController::class, 'actualizar']);
$router->post('/usuarios/cambiar-estado', [UsuarioController::class, 'cambiarEstado']);
$router->get('/usuarios/bloqueados', [UsuarioController::class, 'bloqueados']);
$router->post('/usuarios/desbloquear', [UsuarioController::class, 'desbloquear']);

/* ==================================================
   ROLES Y PERMISOS
   ================================================== */

$router->get('/roles', [RolController::class, 'index']);
$router->get('/roles/crear', [RolController::class, 'crear']);
$router->post('/roles/guardar', [RolController::class, 'guardar']);
$router->get('/roles/editar', [RolController::class, 'editar']);
$router->post('/roles/actualizar', [RolController::class, 'actualizar']);
$router->post('/roles/cambiar-estado', [RolController::class, 'cambiarEstado']);
$router->get('/roles/permisos', [RolController::class, 'permisos']);
$router->post('/roles/permisos/guardar', [RolController::class, 'guardarPermisos']);

/* ==================================================
   ESTUDIANTES
   ================================================== */

$router->get('/estudiantes', [EstudianteController::class, 'index']);
$router->get('/estudiantes/crear', [EstudianteController::class, 'crear']);
$router->post('/estudiantes/guardar', [EstudianteController::class, 'guardar']);
$router->get('/estudiantes/editar', [EstudianteController::class, 'editar']);
$router->post('/estudiantes/actualizar', [EstudianteController::class, 'actualizar']);
$router->post('/estudiantes/cambiar-estado', [EstudianteController::class, 'cambiarEstado']);
$router->get(
    '/estudiantes/carreras-por-facultad',
    [EstudianteController::class, 'carrerasPorFacultad'],
);

/* ==================================================
   PROFESORES
   ================================================== */

$router->get('/profesores', [ProfesorController::class, 'index']);
$router->get('/profesores/crear', [ProfesorController::class, 'crear']);
$router->post('/profesores/guardar', [ProfesorController::class, 'guardar']);
$router->get('/profesores/editar', [ProfesorController::class, 'editar']);
$router->post('/profesores/actualizar', [ProfesorController::class, 'actualizar']);
$router->post('/profesores/cambiar-estado', [ProfesorController::class, 'cambiarEstado']);
$router->get(
    '/profesores/departamentos-por-facultad',
    [ProfesorController::class, 'departamentosPorFacultad'],
);

/* ==================================================
   CATEGORÍAS
   ================================================== */

$router->get('/categorias', [CategoriaController::class, 'index']);
$router->get('/categorias/crear', [CategoriaController::class, 'crear']);
$router->post('/categorias/guardar', [CategoriaController::class, 'guardar']);
$router->get('/categorias/editar', [CategoriaController::class, 'editar']);
$router->post('/categorias/actualizar', [CategoriaController::class, 'actualizar']);
$router->post('/categorias/cambiar-estado', [CategoriaController::class, 'cambiarEstado']);

/* ==================================================
   LIBROS
   ================================================== */

$router->get('/libros', [LibroController::class, 'index']);
$router->get('/libros/crear', [LibroController::class, 'crear']);
$router->post('/libros/guardar', [LibroController::class, 'guardar']);
$router->get('/libros/editar', [LibroController::class, 'editar']);
$router->post('/libros/actualizar', [LibroController::class, 'actualizar']);
$router->post('/libros/cambiar-estado', [LibroController::class, 'cambiarEstado']);
$router->get('/libros/excel', [LibroController::class, 'exportarExcel']);

/* ==================================================
   RESERVAS Y PRÉSTAMOS
   ================================================== */

$router->get('/reservas', [ReservaController::class, 'index']);
$router->post('/reservas/aprobar', [ReservaController::class, 'aprobar']);
$router->post('/reservas/prestar', [ReservaController::class, 'prestar']);
$router->post('/reservas/devolver', [ReservaController::class, 'devolver']);
$router->post('/reservas/cancelar', [ReservaController::class, 'cancelar']);
$router->get('/reservas/reporte', [ReservaController::class, 'reporte']);
$router->get('/reservas/reporte/excel', [ReservaController::class, 'exportarExcel']);

/* ==================================================
   SOLICITUDES ADMINISTRATIVAS
   ================================================== */

$router->get('/solicitudes', [SolicitudController::class, 'index']);
$router->get('/solicitudes/gestionar', [SolicitudController::class, 'gestionar']);
$router->post('/solicitudes/actualizar', [SolicitudController::class, 'actualizar']);

/* ==================================================
   PRÉSTAMO INTERBIBLIOTECARIO ADMINISTRATIVO
   ================================================== */
$router->get('/interbibliotecario', [InterbibliotecarioController::class, 'index']);
$router->post('/interbibliotecario/institucion/guardar', [InterbibliotecarioController::class, 'guardarInstitucion']);
$router->post('/interbibliotecario/institucion/estado', [InterbibliotecarioController::class, 'cambiarEstadoInstitucion']);
$router->post('/interbibliotecario/libro/guardar', [InterbibliotecarioController::class, 'guardarLibro']);
$router->post('/interbibliotecario/libro/disponibilidad', [InterbibliotecarioController::class, 'cambiarDisponibilidadLibro']);
$router->post('/interbibliotecario/solicitud/actualizar', [InterbibliotecarioController::class, 'actualizarSolicitud']);


/* ==================================================
   AUTENTICACIÓN DE USUARIOS REGULARES
   ================================================== */

$router->get('/portal/login', [UsuarioRegularAuthController::class, 'index']);
$router->post('/portal/login', [UsuarioRegularAuthController::class, 'autenticar']);
$router->post('/portal/logout', [UsuarioRegularAuthController::class, 'logout']);

/* ==================================================
   PORTAL REGULAR
   ================================================== */

$router->get('/portal/inicio', [PortalController::class, 'inicio']);
$router->get('/portal/catalogo', [PortalController::class, 'catalogo']);
$router->get('/portal/catalogo/detalle', [PortalController::class, 'detalle']);
$router->post('/portal/reservar', [PortalController::class, 'reservar']);
$router->get('/portal/prestamos', [PortalController::class, 'prestamos']);
$router->post('/portal/prestamos/devolver', [PortalController::class, 'devolver']);
$router->get('/portal/solicitudes', [PortalController::class, 'solicitudes']);
$router->post('/portal/solicitudes', [PortalController::class, 'guardarSolicitud']);
$router->get('/portal/perfil', [PortalController::class, 'perfil']);

$router->get('/portal/interbibliotecario', [PortalController::class, 'interbibliotecario']);
$router->post('/portal/interbibliotecario/solicitar', [PortalController::class, 'solicitarInterbibliotecario']);
$router->post('/portal/interbibliotecario/cancelar', [PortalController::class, 'cancelarInterbibliotecario']);


/* ==================================================
   EJECUCIÓN DEL ROUTER
   ================================================== */

$router->dispatch(
    $_SERVER['REQUEST_URI'],
    $_SERVER['REQUEST_METHOD'],
);
