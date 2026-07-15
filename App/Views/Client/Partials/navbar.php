<?php

use App\Config\Config;

$nombreEstudiante = $nombreEstudiante ?? 'Estudiante';
$cipSesion = $cipSesion ?? '0-000-0000';

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$route = str_replace(Config::baseUrl(), '', $currentPath);
$route = '/' . trim($route, '/');

if ($route === '/') {
    $route = '/portal/inicio';
}

function activeClientNav(string $path, string $route): string
{
    return str_starts_with($route, $path) ? 'active' : '';
}

?>

<nav class="navbar navbar-expand-lg client-navbar">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold">
            <i class="fa-solid fa-book-open-reader"></i>
            Biblioteca Online
        </span>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuClienteCentral">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="menuClienteCentral">
            <ul class="navbar-nav mx-auto client-menu-central">
                <li class="nav-item">
                    <a class="nav-link <?= activeClientNav('/portal/inicio', $route) ?>" href="<?= Config::baseUrl() ?>/portal/inicio">
                        <i class="fa-solid fa-house"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= activeClientNav('/portal/catalogo', $route) ?>" href="<?= Config::baseUrl() ?>/portal/catalogo">
                        <i class="fa-solid fa-magnifying-glass"></i> Catálogo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= activeClientNav('/portal/prestamos', $route) ?>" href="<?= Config::baseUrl() ?>/portal/prestamos">
                        <i class="fa-solid fa-calendar-check"></i> Mis préstamos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= activeClientNav('/portal/solicitudes', $route) ?>" href="<?= Config::baseUrl() ?>/portal/solicitudes">
                        <i class="fa-solid fa-circle-plus"></i> Solicitar libro
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= activeClientNav('/portal/perfil', $route) ?>" href="<?= Config::baseUrl() ?>/portal/perfil">
                        <i class="fa-solid fa-id-card"></i> Mi perfil
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3 client-navbar-user">
                <span class="client-user-chip">
                    <i class="fa-solid fa-user-graduate"></i>
                    <?= htmlspecialchars($nombreEstudiante) ?>
                    <small class="d-block"><?= htmlspecialchars($cipSesion) ?></small>
                </span>

                <a href="<?= Config::baseUrl() ?>/portal/logout" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </div>
</nav>