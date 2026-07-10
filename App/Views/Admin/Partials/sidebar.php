<?php

use App\Config\Config;

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

    <a href="<?= Config::baseUrl() ?>/usuarios" class="<?= activeSidebar('/usuarios', $route) ?>">
        <i class="fa-solid fa-users"></i>
        Usuarios
    </a>

    <a href="#">
        <i class="fa-solid fa-user-graduate"></i>
        Estudiantes
    </a>

    <a href="#">
        <i class="fa-solid fa-building-columns"></i>
        Carreras
    </a>

    <a href="#">
        <i class="fa-solid fa-tags"></i>
        Categorías
    </a>

    <a href="#">
        <i class="fa-solid fa-book"></i>
        Libros
    </a>

    <a href="#">
        <i class="fa-solid fa-calendar-check"></i>
        Reservas
    </a>

    <a href="#">
        <i class="fa-solid fa-chart-column"></i>
        Estadísticas
    </a>
</div>
