<?php

use App\Config\Config;

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

if (!isset($libro) || $libro === false) {
    ?>
    <main class="client-page">
        <div class="client-page-inner">
            <div class="client-empty-state client-not-found-state">
                <span><i class="fa-solid fa-book"></i></span>
                <h1>Libro no encontrado</h1>
                <p>El título solicitado no existe o ya no está disponible en el catálogo.</p>
                <a href="<?= Config::url('portal/catalogo') ?>" class="btn btn-primary">
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver al catálogo
                </a>
            </div>
        </div>
    </main>
    <?php
    require_once __DIR__ . '/../../Partials/footer.php';
    return;
}

$rutaImagen = trim(str_replace('\\', '/', (string) ($libro['imagen_ruta'] ?? $libro['thumbnail_ruta'] ?? '')), '/');
$urlImagen = $rutaImagen !== '' ? Config::asset($rutaImagen) : null;
$titulo = $libro['titulo'] ?? 'Libro sin título';
$autor = $libro['autor'] ?? 'Autor desconocido';
$categoria = $libro['categoria'] ?? 'Sin categoría';
$editorial = $libro['editorial'] ?? 'Sin editorial';
$anioPublicacion = $libro['anio_publicacion'] ?? 'No indicado';
$ubicacion = $libro['ubicacion'] ?? $libro['ubicacion_fisica'] ?? 'Sin ubicación';
$isbn = trim((string) ($libro['isbn'] ?? '')) ?: 'No registrado';
$temas = trim((string) ($libro['temas'] ?? '')) ?: 'Sin temas registrados';
$descripcion = trim((string) ($libro['descripcion'] ?? '')) ?: 'Este libro no tiene una descripción registrada.';
$existencias = (int) ($libro['existencias'] ?? $libro['existencias_disponibles'] ?? 0);
?>

<main class="client-page">
    <div class="client-page-inner">
        <nav class="client-breadcrumb" aria-label="Ruta de navegación">
            <a href="<?= Config::url('portal/inicio') ?>">Inicio</a>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="<?= Config::url('portal/catalogo') ?>">Catálogo</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span><?= $escapar($titulo) ?></span>
        </nav>

        <?php if (!empty($exitoReserva)): ?>
            <div class="alert alert-success client-alert">
                <i class="fa-solid fa-circle-check"></i>
                <?= $escapar($exitoReserva) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorReserva)): ?>
            <div class="alert alert-danger client-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= $escapar($errorReserva) ?>
            </div>
        <?php endif; ?>

        <section class="client-book-detail-card">
            <div class="client-book-detail-cover-wrap">
                <div class="client-book-detail-cover">
                    <?php if ($urlImagen !== null): ?>
                        <img
                            src="<?= $escapar($urlImagen) ?>"
                            alt="Portada de <?= $escapar($titulo) ?>"
                            onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                        >
                        <span class="client-book-detail-fallback" hidden><i class="fa-solid fa-book-open"></i></span>
                    <?php else: ?>
                        <span class="client-book-detail-fallback"><i class="fa-solid fa-book-open"></i></span>
                    <?php endif; ?>
                </div>

                <span class="client-detail-stock <?= $existencias > 0 ? 'is-available' : 'is-unavailable' ?>">
                    <i class="fa-solid <?= $existencias > 0 ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
                    <?= $existencias > 0
                        ? $existencias . ' ' . ($existencias === 1 ? 'unidad disponible' : 'unidades disponibles')
                        : 'Sin existencias disponibles' ?>
                </span>
            </div>

            <div class="client-book-detail-content">
                <div class="client-book-detail-heading">
                    <span class="client-book-category"><i class="fa-solid fa-tag"></i> <?= $escapar($categoria) ?></span>
                    <h1><?= $escapar($titulo) ?></h1>
                    <p class="client-book-detail-author">
                        <i class="fa-solid fa-feather-pointed"></i>
                        <?= $escapar($autor) ?>
                    </p>
                </div>

                <div class="client-book-meta-grid">
                    <article>
                        <span><i class="fa-solid fa-building"></i></span>
                        <div><small>Editorial</small><strong><?= $escapar($editorial) ?></strong></div>
                    </article>
                    <article>
                        <span><i class="fa-regular fa-calendar"></i></span>
                        <div><small>Año</small><strong><?= $escapar($anioPublicacion) ?></strong></div>
                    </article>
                    <article>
                        <span><i class="fa-solid fa-barcode"></i></span>
                        <div><small>ISBN</small><strong><?= $escapar($isbn) ?></strong></div>
                    </article>
                    <article>
                        <span><i class="fa-solid fa-location-dot"></i></span>
                        <div><small>Ubicación</small><strong><?= $escapar($ubicacion) ?></strong></div>
                    </article>
                </div>

                <div class="client-book-description">
                    <h2><i class="fa-solid fa-align-left"></i> Descripción</h2>
                    <p><?= nl2br($escapar($descripcion)) ?></p>
                </div>

                <div class="client-book-topics">
                    <h2><i class="fa-solid fa-hashtag"></i> Temas</h2>
                    <p><?= $escapar($temas) ?></p>
                </div>

                <div class="client-book-detail-actions">
                    <?php if ($existencias > 0): ?>
                        <form method="POST" action="<?= Config::url('portal/reservar') ?>">
                            <input type="hidden" name="id_libro" value="<?= (int) ($libro['id_libro'] ?? 0) ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa-solid fa-calendar-check"></i>
                                Reservar este libro
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?= Config::url('portal/solicitudes') ?>" class="btn btn-primary">
                            <i class="fa-solid fa-circle-plus"></i>
                            Solicitar este título
                        </a>
                    <?php endif; ?>

                    <a href="<?= Config::url('portal/catalogo') ?>" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i>
                        Volver al catálogo
                    </a>
                </div>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
