<?php

use App\Config\Config;
use App\Helpers\Session;
use App\Middleware\Auth;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$nombreSesion = Session::get('nombre') ?? 'Administrador';

$modulos = [
    [
        'permiso' => 'usuarios.ver',
        'ruta' => 'usuarios',
        'icono' => 'fa-users',
        'titulo' => 'Usuarios',
        'descripcion' => 'Cuentas, roles y estados de acceso.',
    ],
    [
        'permiso' => 'estudiantes.ver',
        'ruta' => 'estudiantes',
        'icono' => 'fa-user-graduate',
        'titulo' => 'Estudiantes',
        'descripcion' => 'Registros académicos y carreras.',
    ],
    [
        'permiso' => 'profesores.ver',
        'ruta' => 'profesores',
        'icono' => 'fa-chalkboard-user',
        'titulo' => 'Profesores',
        'descripcion' => 'Datos docentes y especialidades.',
    ],
    [
        'permiso' => 'estructura.ver',
        'ruta' => 'estructura-academica',
        'icono' => 'fa-building-columns',
        'titulo' => 'Estructura académica',
        'descripcion' => 'Facultades, departamentos y carreras.',
    ],
    [
        'permiso' => 'categorias.ver',
        'ruta' => 'categorias',
        'icono' => 'fa-tags',
        'titulo' => 'Categorías',
        'descripcion' => 'Organización temática del catálogo.',
    ],
    [
        'permiso' => 'libros.ver',
        'ruta' => 'libros',
        'icono' => 'fa-book-open',
        'titulo' => 'Libros',
        'descripcion' => 'Inventario, existencias y reportes.',
    ],
    [
        'permiso' => 'reservas.ver',
        'ruta' => 'reservas',
        'icono' => 'fa-calendar-check',
        'titulo' => 'Reservas',
        'descripcion' => 'Entregas, préstamos y devoluciones.',
    ],
    [
        'permiso' => 'solicitudes.ver',
        'ruta' => 'solicitudes',
        'icono' => 'fa-book-circle-plus',
        'titulo' => 'Solicitudes',
        'descripcion' => 'Peticiones de nuevos libros.',
    ],
    [
        'permiso' => 'roles.gestionar',
        'ruta' => 'roles',
        'icono' => 'fa-user-shield',
        'titulo' => 'Roles y permisos',
        'descripcion' => 'Alcance de acceso por cada rol.',
    ],
    [
        'permiso' => 'reportes.ver',
        'ruta' => 'estadisticas',
        'icono' => 'fa-chart-column',
        'titulo' => 'Estadísticas',
        'descripcion' => 'Libros más usados por período y población.',
    ],
];

$modulosDisponibles = array_values(array_filter(
    $modulos,
    static fn (array $modulo): bool => Auth::tienePermiso($modulo['permiso']),
));
?>

<div class="container-fluid admin-page admin-page-dashboard">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main dashboard-main">
            <section class="dashboard-hero">
                <div class="dashboard-hero-copy">
                    <span class="dashboard-eyebrow">
                        <i class="fa-solid fa-sparkles"></i>
                        Panel administrativo
                    </span>
                    <h1>Hola, <?= htmlspecialchars((string) $nombreSesion, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p>
                        Gestiona la biblioteca desde un solo lugar y entra rápidamente
                        a los módulos habilitados para tu cuenta.
                    </p>
                </div>

                <div class="dashboard-hero-mark" aria-hidden="true">
                    <i class="fa-solid fa-book-open-reader"></i>
                </div>
            </section>

            <section class="dashboard-section">
                <div class="dashboard-section-heading">
                    <div>
                        <h2 class="mb-1">Accesos rápidos</h2>
                        <p class="mb-0">
                            <?= count($modulosDisponibles) ?>
                            <?= count($modulosDisponibles) === 1 ? 'módulo disponible' : 'módulos disponibles' ?>
                        </p>
                    </div>
                </div>

                <div class="row g-4">
                    <?php foreach ($modulosDisponibles as $modulo): ?>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <a
                                href="<?= Config::url($modulo['ruta']) ?>"
                                class="dashboard-module-card"
                            >
                                <span class="dashboard-module-icon">
                                    <i class="fa-solid <?= htmlspecialchars($modulo['icono'], ENT_QUOTES, 'UTF-8') ?>"></i>
                                </span>
                                <span class="dashboard-module-copy">
                                    <strong><?= htmlspecialchars($modulo['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <small><?= htmlspecialchars($modulo['descripcion'], ENT_QUOTES, 'UTF-8') ?></small>
                                </span>
                                <i class="fa-solid fa-arrow-right dashboard-module-arrow" aria-hidden="true"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($modulosDisponibles)): ?>
                        <div class="col-12">
                            <div class="admin-empty-state">
                                <span class="admin-empty-icon">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </span>
                                <h3>No hay módulos habilitados</h3>
                                <p>Solicita a un administrador que revise los permisos de tu rol.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="card dashboard-help-card">
                <div class="dashboard-help-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div>
                    <h2 class="admin-card-title mb-1">Todo centralizado</h2>
                    <p class="mb-0">
                        Los cambios de usuarios, catálogo, reservas y solicitudes se realizan
                        desde los módulos anteriores según los permisos asignados a tu rol.
                    </p>
                </div>
            </section>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
