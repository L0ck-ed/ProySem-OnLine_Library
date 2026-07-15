<?php

use App\Config\Config;
use App\Middleware\Auth;

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$route = str_replace(Config::baseUrl(), '', $currentPath);
$route = '/' . trim($route, '/');

if ($route === '/') {
    $route = '/dashboard';
}

function activeSidebar(string $path, string $route): string
{
    return str_starts_with($route, $path) ? 'active' : '';
}
?>

<div class="sidebar">
    <h4 class="mb-4">
        <i class="fa-solid fa-layer-group"></i>
        Menú
    </h4>

    <a href="<?= Config::baseUrl() ?>/dashboard" class="<?= activeSidebar('/dashboard', $route) ?>">
        <i class="fa-solid fa-house"></i>
        Dashboard
    </a>

    <?php if (Auth::tienePermiso('usuarios.ver')): ?>
        <a href="<?= Config::url('usuarios') ?>">
            <i class="fa-solid fa-users"></i>
            Usuarios
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('roles.gestionar')): ?>
        <a
            href="<?= Config::url('roles') ?>"
            class="nav-link"
        >
            <i class="fa-solid fa-user-shield"></i>
            Roles y permisos
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('profesores.ver')): ?>
        <a
            href="<?= Config::url('profesores') ?>"
            class="list-group-item list-group-item-action"
        >
            <i class="fa-solid fa-chalkboard-user me-2"></i>
            Profesores
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('estudiantes.ver')): ?>
        <a
            href="<?= Config::url('estudiantes') ?>"
            class="list-group-item list-group-item-action"
        >
            <i class="fa-solid fa-user-graduate me-2"></i>
            Estudiantes
        </a>
    <?php endif; ?>

    <a href="#">
        <i class="fa-solid fa-building-columns"></i>
        Carreras
    </a>

    <?php if (Auth::tienePermiso('categorias.ver')): ?>
        <a
            href="<?= Config::url('categorias') ?>"
            class="list-group-item list-group-item-action"
        >
            <i class="fa-solid fa-tags me-2"></i>
            Categorías
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('libros.ver')): ?>
        <a
            href="<?= Config::url('libros') ?>"
            class="list-group-item list-group-item-action"
        >
            <i class="fa-solid fa-book me-2"></i>
            Libros
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('reservas.ver')): ?>
        <a
            href="<?= Config::url('reservas') ?>"
            class="list-group-item list-group-item-action"
        >
            <i class="fa-solid fa-calendar-check me-2"></i>
            Reservas
        </a>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('solicitudes.ver')): ?>
        <a
            href="<?= Config::url('solicitudes') ?>"
            class="list-group-item list-group-item-action"
        >
            <i
                class="fa-solid fa-book-circle-plus me-2"
            ></i>
            Solicitudes
        </a>
    <?php endif; ?>

    <a href="#">
        <i class="fa-solid fa-chart-column"></i>
        Estadísticas
    </a>
</div>
