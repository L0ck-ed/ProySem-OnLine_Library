<?php

/* Vista de solo interfaz. El botón "Reservar" no ejecuta lógica todavía. */

$nombreEstudiante = $nombreEstudiante ?? 'Anthony Castillo';
$cipSesion = $cipSesion ?? '8-1023-2265';

$libro = $libro ?? [
    'titulo'           => 'Clean Code',
    'autor'            => 'Robert C. Martin',
    'editorial'        => 'Prentice Hall',
    'anio_publicacion' => 2008,
    'categoria'        => 'Sistemas',
    'existencias'      => 4,
    'ubicacion'        => 'Estante S-12',
    'descripcion'      => 'Un manual sobre buenas prácticas para escribir código legible, mantenible y bien estructurado, con ejemplos aplicables a distintos lenguajes de programación.',
];

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

?>

<div class="container-fluid py-4 px-4">

    <a href="<?= App\Config\Config::url('portal/catalogo') ?>" class="fw-bold text-decoration-none d-inline-block mb-3">
        <i class="fa-solid fa-arrow-left"></i> Volver al catálogo
    </a>

    <div class="card p-4">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="book-detalle-cover">
                    <i class="fa-solid fa-book"></i>
                </div>
            </div>

            <div class="col-md-8">
                <span class="badge badge-categoria mb-2"><?= htmlspecialchars($libro['categoria']) ?></span>
                <h2 class="mb-1"><?= htmlspecialchars($libro['titulo']) ?></h2>
                <p class="fw-bold" style="color:var(--muted)"><?= htmlspecialchars($libro['autor']) ?></p>

                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-4">
                        <small class="d-block text-muted">Editorial</small>
                        <span class="fw-bold"><?= htmlspecialchars($libro['editorial']) ?></span>
                    </div>
                    <div class="col-6 col-md-4">
                        <small class="d-block text-muted">Año</small>
                        <span class="fw-bold"><?= htmlspecialchars((string) $libro['anio_publicacion']) ?></span>
                    </div>
                    <div class="col-6 col-md-4">
                        <small class="d-block text-muted">Ubicación</small>
                        <span class="fw-bold"><?= htmlspecialchars($libro['ubicacion']) ?></span>
                    </div>
                </div>

                <p><?= htmlspecialchars($libro['descripcion']) ?></p>

                <?php if ($libro['existencias'] > 0): ?>
                    <span class="badge badge-existencias-ok mb-3"><?= $libro['existencias'] ?> unidades disponibles</span>
                    <div>
                        <button class="btn btn-primary" type="button">
                            <i class="fa-solid fa-calendar-check"></i> Reservar este libro
                        </button>
                    </div>
                <?php else: ?>
                    <span class="badge badge-existencias-agotado mb-3">Sin existencias disponibles</span>
                    <div class="alert alert-danger">
                        Este libro no tiene unidades disponibles en este momento.
                        <a href="<?= App\Config\Config::url('portal/solicitudes') ?>" class="fw-bold">
                            Puedes solicitar su compra aquí.
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>