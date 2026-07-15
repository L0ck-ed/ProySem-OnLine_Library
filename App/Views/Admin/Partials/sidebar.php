<?php

use App\Config\Config;
use App\Middleware\Auth;

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$route = str_replace(Config::baseUrl(), '', $currentPath);
$route = '/' . trim($route, '/');

if ($route === '/') {
    $route = '/dashboard';
}

if (!function_exists('activeSidebar')) {
    function activeSidebar(string $path, string $route): string
    {
        return str_starts_with($route, $path) ? 'active' : '';
    }
}
?>

<nav class="sidebar" aria-label="Menú administrativo">
    <h4 class="mb-4">
        <i class="fa-solid fa-layer-group"></i>
        Menú
    </h4>

    <a href="<?= Config::url('dashboard') ?>" class="<?= activeSidebar('/dashboard', $route) ?>">
        <i class="fa-solid fa-house"></i>
        Dashboard
    </a>

    <?php if (Auth::tienePermiso('usuarios.ver')): ?>
        <a
            href="<?= Config::url('usuarios') ?>"
            class="<?= $route === '/usuarios' || str_starts_with($route, '/usuarios/crear') || str_starts_with($route, '/usuarios/editar') ? 'active' : '' ?>"
        >
            <i class="fa-solid fa-users"></i>
            Usuarios
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('usuarios.editar')): ?>
        <a
            href="<?= Config::url('usuarios/bloqueados') ?>"
            class="<?= activeSidebar('/usuarios/bloqueados', $route) ?>"
        >
            <i class="fa-solid fa-user-lock"></i>
            Cuentas bloqueadas
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('roles.gestionar')): ?>
        <a href="<?= Config::url('roles') ?>" class="<?= activeSidebar('/roles', $route) ?>">
            <i class="fa-solid fa-user-shield"></i>
            Roles y permisos
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('profesores.ver')): ?>
        <a href="<?= Config::url('profesores') ?>" class="<?= activeSidebar('/profesores', $route) ?>">
            <i class="fa-solid fa-chalkboard-user"></i>
            Profesores
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('estudiantes.ver')): ?>
        <a href="<?= Config::url('estudiantes') ?>" class="<?= activeSidebar('/estudiantes', $route) ?>">
            <i class="fa-solid fa-user-graduate"></i>
            Estudiantes
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('estructura.ver')): ?>
        <a
            href="<?= Config::url('estructura-academica') ?>"
            class="<?= activeSidebar('/estructura-academica', $route) ?>"
        >
            <i class="fa-solid fa-building-columns"></i>
            Estructura académica
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('categorias.ver')): ?>
        <a href="<?= Config::url('categorias') ?>" class="<?= activeSidebar('/categorias', $route) ?>">
            <i class="fa-solid fa-tags"></i>
            Categorías
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('libros.ver')): ?>
        <a href="<?= Config::url('libros') ?>" class="<?= activeSidebar('/libros', $route) ?>">
            <i class="fa-solid fa-book"></i>
            Libros
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('reservas.ver')): ?>
        <a href="<?= Config::url('reservas') ?>" class="<?= activeSidebar('/reservas', $route) ?>">
            <i class="fa-solid fa-calendar-check"></i>
            Reservas
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('solicitudes.ver')): ?>
        <a href="<?= Config::url('solicitudes') ?>" class="<?= activeSidebar('/solicitudes', $route) ?>">
            <i class="fa-solid fa-book-circle-plus"></i>
            Solicitudes
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('reportes.ver')): ?>
        <a href="<?= Config::url('estadisticas') ?>" class="<?= activeSidebar('/estadisticas', $route) ?>">
            <i class="fa-solid fa-chart-column"></i>
            Estadísticas
        </a>
    <?php endif; ?>
</nav>
