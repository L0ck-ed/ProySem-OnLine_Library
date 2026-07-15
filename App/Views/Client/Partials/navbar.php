<?php

use App\Config\Config;

$nombreEstudiante = $nombreEstudiante ?? 'Usuario';
$cipSesion = $cipSesion ?? '';
$tipoUsuarioSesion = $tipoUsuarioSesion ?? 'Usuario';
$puedeVerLibros = $puedeVerLibros ?? false;

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$route = str_replace(Config::baseUrl(), '', $currentPath);
$route = '/' . trim($route, '/');

if ($route === '/') {
    $route = '/portal/inicio';
}

$activeClientNav = static function (string $path) use ($route): string {
    return str_starts_with($route, $path) ? 'active' : '';
};

$escaparNavbar = static function (mixed $valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$iconoUsuario = $tipoUsuarioSesion === 'Profesor'
    ? 'fa-solid fa-chalkboard-user'
    : 'fa-solid fa-user-graduate';
?>

<nav class="navbar navbar-expand-xl client-navbar sticky-top">
    <div class="container-fluid client-navbar-container">
        <a class="navbar-brand client-brand" href="<?= Config::url('portal/inicio') ?>">
            <span class="client-brand-icon">
                <i class="fa-solid fa-book-open-reader"></i>
            </span>
            <span>
                Biblioteca <strong>Online</strong>
                <small>Portal académico</small>
            </span>
        </a>

        <button
            class="navbar-toggler client-navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#menuClienteCentral"
            aria-controls="menuClienteCentral"
            aria-expanded="false"
            aria-label="Abrir menú de navegación"
        >
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="menuClienteCentral">
            <ul class="navbar-nav mx-auto client-menu-central">
                <li class="nav-item">
                    <a
                        class="nav-link <?= $activeClientNav('/portal/inicio') ?>"
                        href="<?= Config::url('portal/inicio') ?>"
                    >
                        <i class="fa-solid fa-house"></i>
                        <span>Inicio</span>
                    </a>
                </li>

                <?php if ($puedeVerLibros): ?>
                    <li class="nav-item">
                        <a
                            class="nav-link <?= $activeClientNav('/portal/catalogo') ?>"
                            href="<?= Config::url('portal/catalogo') ?>"
                        >
                            <i class="fa-solid fa-book-open"></i>
                            <span>Catálogo</span>
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a
                        class="nav-link <?= $activeClientNav('/portal/prestamos') ?>"
                        href="<?= Config::url('portal/prestamos') ?>"
                    >
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Mis préstamos</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link <?= $activeClientNav('/portal/solicitudes') ?>"
                        href="<?= Config::url('portal/solicitudes') ?>"
                    >
                        <i class="fa-solid fa-circle-plus"></i>
                        <span>Solicitar libro</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link <?= $activeClientNav('/portal/perfil') ?>"
                        href="<?= Config::url('portal/perfil') ?>"
                    >
                        <i class="fa-solid fa-id-card"></i>
                        <span>Mi perfil</span>
                    </a>
                </li>
            </ul>

            <div class="client-navbar-account">
                <a class="client-account-card" href="<?= Config::url('portal/perfil') ?>">
                    <span class="client-account-avatar">
                        <i class="<?= $iconoUsuario ?>"></i>
                    </span>
                    <span class="client-account-copy">
                        <strong><?= $escaparNavbar($nombreEstudiante) ?></strong>
                        <small>
                            <?= $escaparNavbar($tipoUsuarioSesion) ?>
                            <?php if ($cipSesion !== ''): ?>
                                · <?= $escaparNavbar($cipSesion) ?>
                            <?php endif; ?>
                        </small>
                    </span>
                </a>

                <a
                    href="<?= Config::url('portal/logout') ?>"
                    class="client-logout-btn"
                    title="Cerrar sesión"
                    aria-label="Cerrar sesión"
                >
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </div>
</nav>
