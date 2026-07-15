<?php

use App\Config\Config;

$nombreEstudiante = $nombreEstudiante ?? '';
$cipSesion = $cipSesion ?? '';

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

if (!isset($libro) || $libro === false) {

    require_once __DIR__ . '/../../Partials/header.php';
    require_once __DIR__ . '/../Partials/navbar.php';
    ?>

    <div class="container-fluid py-4 px-4">
        <div class="alert alert-danger">
            Libro no encontrado.

            <a href="<?= Config::url('portal/catalogo') ?>">
                Volver al catálogo
            </a>
        </div>
    </div>

    <?php
    require_once __DIR__ . '/../../Partials/footer.php';
    return;

}

$rutaImagen = $libro['imagen_ruta'] ?? ($libro['thumbnail_ruta'] ?? '');

$rutaImagen = trim(str_replace('\\', '/', (string) $rutaImagen), '/');

$urlImagen = $rutaImagen !== '' ? Config::asset($rutaImagen) : null;

$titulo = $libro['titulo'] ?? 'Libro sin título';

$autor = $libro['autor'] ?? 'Autor desconocido';

$categoria = $libro['categoria'] ?? 'Sin categoría';

$editorial = $libro['editorial'] ?? 'Sin editorial';

$anioPublicacion = $libro['anio_publicacion'] ?? 'No indicado';

$ubicacion = $libro['ubicacion'] ?? ($libro['ubicacion_fisica'] ?? 'Sin ubicación');

$descripcion = trim((string) ($libro['descripcion'] ?? ''));

if ($descripcion === '') {
    $descripcion = 'Este libro no tiene una descripción registrada.';
}

$existencias = (int) ($libro['existencias'] ?? ($libro['existencias_disponibles'] ?? 0));

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';
?>

<div class="container-fluid py-4 px-4">
    <a
        href="<?= Config::url('portal/catalogo') ?>"
        class="fw-bold text-decoration-none d-inline-block mb-3"
    >
        <i class="fa-solid fa-arrow-left"></i>
        Volver al catálogo
    </a>

    <div class="card p-4">
        <div class="row g-4">
            <div class="col-md-4">
                <div
                    class="book-detalle-cover position-relative overflow-hidden"
                >
                    <?php if ($urlImagen !== null): ?>
                        <img
                            src="<?= $escapar($urlImagen) ?>"
                            alt="Portada de <?= $escapar($titulo) ?>"
                            class="w-100 h-100"
                            style="object-fit: cover; min-height: 320px;"
                            onerror="
                                this.style.display = 'none';
                                this.nextElementSibling.style.display = 'flex';
                            "
                        >

                        <div
                            class="w-100 h-100 align-items-center justify-content-center"
                            style="display: none; min-height: 320px;"
                        >
                            <i class="fa-solid fa-book"></i>
                        </div>
                    <?php else: ?>
                        <i class="fa-solid fa-book"></i>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-8">
                <span class="badge badge-categoria mb-2">
                    <?= $escapar($categoria) ?>
                </span>

                <h2 class="mb-1">
                    <?= $escapar($titulo) ?>
                </h2>

                <p
                    class="fw-bold"
                    style="color: var(--muted);"
                >
                    <?= $escapar($autor) ?>
                </p>

                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-4">
                        <small class="d-block text-muted">
                            Editorial
                        </small>

                        <span class="fw-bold">
                            <?= $escapar($editorial) ?>
                        </span>
                    </div>

                    <div class="col-6 col-md-4">
                        <small class="d-block text-muted">
                            Año
                        </small>

                        <span class="fw-bold">
                            <?= $escapar($anioPublicacion) ?>
                        </span>
                    </div>

                    <div class="col-6 col-md-4">
                        <small class="d-block text-muted">
                            Ubicación
                        </small>

                        <span class="fw-bold">
                            <?= $escapar($ubicacion) ?>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="d-block text-muted mb-1">
                        Descripción
                    </small>

                    <p class="mb-0">
                        <?= nl2br($escapar($descripcion)) ?>
                    </p>
                </div>

                <?php if ($existencias > 0): ?>
                    <span class="badge badge-existencias-ok mb-3">
                        <?= $existencias ?>
                        <?= $existencias === 1 ? 'unidad disponible' : 'unidades disponibles' ?>
                    </span>

                    <?php if (!empty($exitoReserva)): ?>
                        <div class="alert alert-success">
                            <?= $escapar($exitoReserva) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorReserva)): ?>
                        <div class="alert alert-danger">
                            <?= $escapar($errorReserva) ?>
                        </div>
                    <?php endif; ?>

                    <form
                        method="POST"
                        action="<?= Config::url('portal/reservar') ?>"
                    >
                        <input
                            type="hidden"
                            name="id_libro"
                            value="<?= (int) $libro['id_libro'] ?>"
                        >

                        <button
                            class="btn btn-primary"
                            type="submit"
                        >
                            <i class="fa-solid fa-calendar-check"></i>
                            Reservar este libro
                        </button>
                    </form>
                <?php else: ?>
                    <span class="badge badge-existencias-agotado mb-3">
                        Sin existencias disponibles
                    </span>

                    <div class="alert alert-danger">
                        Este libro no tiene unidades disponibles
                        en este momento.

                        <a
                            href="<?= Config::url('portal/solicitudes') ?>"
                            class="fw-bold"
                        >
                            Puedes solicitar su compra aquí.
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
