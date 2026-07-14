<?php

/*
 * Vista de solo interfaz (sin lógica de datos real todavía).
 * Cuando exista el controlador de Portal, estos valores llegarán
 * vía $data desde Controller->view(), reemplazando este mock.
 */

$nombreEstudiante = $nombreEstudiante ?? 'Anthony Castillo';
$cipSesion = $cipSesion ?? '8-1023-2265';
$carreraSesion = $carreraSesion ?? 'Licenciatura en Desarrollo y Gestión de Software';

$stats = $stats ?? [
    'total_libros'      => 128,
    'categorias'        => 5,
    'prestamos_activos' => 2,
    'disponibles_ahora' => 96,
];

$categoriasDestacadas = $categoriasDestacadas ?? [
    ['nombre' => 'Sistemas',    'icono' => 'fa-solid fa-microchip'],
    ['nombre' => 'Matemática',  'icono' => 'fa-solid fa-square-root-variable'],
    ['nombre' => 'Química',     'icono' => 'fa-solid fa-flask'],
    ['nombre' => 'Lógica',      'icono' => 'fa-solid fa-diagram-project'],
    ['nombre' => 'Estadística', 'icono' => 'fa-solid fa-chart-line'],
];

$librosRecientes = $librosRecientes ?? [
    ['titulo' => 'Clean Code',                 'autor' => 'Robert C. Martin', 'categoria' => 'Sistemas',   'existencias' => 4],
    ['titulo' => 'Cálculo de una Variable',     'autor' => 'James Stewart',    'categoria' => 'Matemática', 'existencias' => 2],
    ['titulo' => 'Química General',             'autor' => 'Raymond Chang',    'categoria' => 'Química',    'existencias' => 0],
    ['titulo' => 'Estadística para Ingeniería', 'autor' => 'Montgomery',       'categoria' => 'Estadística','existencias' => 6],
];

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

?>

<div class="container-fluid py-4 px-4">

    <div class="portal-hero mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h2 class="mb-2">Hola, <?= htmlspecialchars(explode(' ', $nombreEstudiante)[0]) ?></h2>
                <p class="mb-0"><?= htmlspecialchars($carreraSesion) ?> · CIP <?= htmlspecialchars($cipSesion) ?></p>
                <p class="mb-0">Busca un libro, revisa tus préstamos activos o solicita un título que no encuentres.</p>
            </div>
            <div class="col-lg-4">
                <form action="<?= App\Config\Config::url('portal/catalogo') ?>" method="GET" class="d-flex gap-2">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar un libro...">
                    <button class="btn btn-dark px-3">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-chip">
                <i class="fa-solid fa-book"></i>
                <div>
                    <div class="stat-value"><?= $stats['total_libros'] ?></div>
                    <div class="stat-label">Libros en catálogo</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-chip">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <div class="stat-value"><?= $stats['disponibles_ahora'] ?></div>
                    <div class="stat-label">Disponibles ahora</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-chip">
                <i class="fa-solid fa-tags"></i>
                <div>
                    <div class="stat-value"><?= $stats['categorias'] ?></div>
                    <div class="stat-label">Categorías</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-chip">
                <i class="fa-solid fa-calendar-check"></i>
                <div>
                    <div class="stat-value"><?= $stats['prestamos_activos'] ?></div>
                    <div class="stat-label">Tus préstamos activos</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0"><i class="fa-solid fa-tags"></i> Categorías</h5>
    </div>
    <div class="d-flex flex-wrap gap-2 mb-4">
        <?php foreach ($categoriasDestacadas as $cat): ?>
            <a href="<?= App\Config\Config::url('portal/catalogo') ?>?categoria=<?= urlencode($cat['nombre']) ?>"
               class="btn btn-secondary btn-sm">
                <i class="<?= $cat['icono'] ?>"></i> <?= htmlspecialchars($cat['nombre']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0"><i class="fa-solid fa-clock-rotate-left"></i> Agregados recientemente</h5>
        <a href="<?= App\Config\Config::url('portal/catalogo') ?>" class="fw-bold text-decoration-none">
            Ver catálogo completo <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="row g-4 mb-4">
        <?php foreach ($librosRecientes as $libro): ?>
            <div class="col-md-6 col-lg-3">
                <div class="book-card">
                    <div class="book-cover"><i class="fa-solid fa-book"></i></div>
                    <div class="book-card-body">
                        <h5><?= htmlspecialchars($libro['titulo']) ?></h5>
                        <span class="book-autor"><?= htmlspecialchars($libro['autor']) ?></span>
                        <div class="book-card-footer">
                            <span class="badge badge-categoria"><?= htmlspecialchars($libro['categoria']) ?></span>
                            <?php if ($libro['existencias'] > 0): ?>
                                <span class="badge badge-existencias-ok"><?= $libro['existencias'] ?> disp.</span>
                            <?php else: ?>
                                <span class="badge badge-existencias-agotado">Agotado</span>
                            <?php endif; ?>
                        </div>
                        <a href="<?= App\Config\Config::url('portal/catalogo/detalle') ?>?id=<?= $libro['id_libro'] ?>" class="btn btn-primary btn-sm w-100 mt-2">
                            Ver detalle
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card p-4 h-100">
                <i class="fa-solid fa-magnifying-glass mb-2" style="color:var(--caramel); font-size:26px;"></i>
                <h5>Explora el catálogo</h5>
                <p>Busca por título, autor o categoría y revisa la disponibilidad en tiempo real.</p>
                <a href="<?= App\Config\Config::url('portal/catalogo') ?>" class="btn btn-secondary mt-auto">Ir al catálogo</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 h-100">
                <i class="fa-solid fa-calendar-check mb-2" style="color:var(--caramel); font-size:26px;"></i>
                <h5>Mis préstamos</h5>
                <p>Consulta tus préstamos activos y tu historial de devoluciones.</p>
                <a href="<?= App\Config\Config::url('portal/prestamos') ?>" class="btn btn-secondary mt-auto">Ver mis préstamos</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 h-100">
                <i class="fa-solid fa-circle-plus mb-2" style="color:var(--caramel); font-size:26px;"></i>
                <h5>¿No lo encontraste?</h5>
                <p>Solicita un libro que necesites y la administración revisará tu petición.</p>
                <a href="<?= App\Config\Config::url('portal/solicitudes') ?>" class="btn btn-secondary mt-auto">Solicitar libro</a>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>