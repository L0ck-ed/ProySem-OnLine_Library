<?php

use App\Config\Config;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$libros = is_array($libros ?? null) ? $libros : [];
$categorias = is_array($categorias ?? null) ? $categorias : [];
$busqueda = trim((string) ($busqueda ?? ''));
$categoriaSeleccionada = trim((string) ($categoriaSeleccionada ?? ''));
$paginaActual = max(1, (int) ($paginaActual ?? 1));
$totalPaginas = max(1, (int) ($totalPaginas ?? 1));
$hayFiltros = $busqueda !== '' || $categoriaSeleccionada !== '';
$queryBase = 'buscar=' . urlencode($busqueda) . '&categoria=' . urlencode($categoriaSeleccionada);

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$urlPortada = static function (array $libro): ?string {
    $ruta = trim(str_replace('\\', '/', (string) ($libro['thumbnail_ruta'] ?? $libro['imagen_ruta'] ?? '')), '/');
    return $ruta !== '' ? Config::asset($ruta) : null;
};
?>

<main class="client-page">
    <div class="client-page-inner">
        <header class="client-page-header">
            <div class="client-page-title-wrap">
                <span class="client-page-title-icon"><i class="fa-solid fa-book-open"></i></span>
                <div>
                    <span class="client-section-kicker">Biblioteca digital</span>
                    <h1>Catálogo de libros</h1>
                    <p>Explora los títulos disponibles y reserva el material que necesites.</p>
                </div>
            </div>

            <a href="<?= Config::url('portal/solicitudes') ?>" class="btn btn-secondary client-header-action">
                <i class="fa-solid fa-circle-plus"></i>
                Solicitar un libro
            </a>
        </header>

        <section class="client-filter-card">
            <div class="client-filter-card-heading">
                <div>
                    <h2><i class="fa-solid fa-sliders"></i> Buscar y filtrar</h2>
                    <p>Usa uno o ambos criterios para encontrar un título.</p>
                </div>
                <?php if ($hayFiltros): ?>
                    <span class="client-active-filter-badge">
                        <i class="fa-solid fa-filter"></i>
                        Filtros activos
                    </span>
                <?php endif; ?>
            </div>

            <form action="<?= Config::url('portal/catalogo') ?>" method="GET" class="client-filter-form">
                <div class="client-filter-field client-filter-search">
                    <label for="buscar">Título, autor o tema</label>
                    <div class="client-input-icon">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input
                            type="search"
                            name="buscar"
                            id="buscar"
                            class="form-control"
                            value="<?= $escapar($busqueda) ?>"
                            placeholder="Ej: Clean Code, Tolkien, programación..."
                        >
                    </div>
                </div>

                <div class="client-filter-field">
                    <label for="categoria">Categoría</label>
                    <div class="client-input-icon">
                        <i class="fa-solid fa-tag"></i>
                        <select name="categoria" id="categoria" class="form-select">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $escapar($cat) ?>" <?= $categoriaSeleccionada === $cat ? 'selected' : '' ?>>
                                    <?= $escapar($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="client-filter-actions">
                    <?php if ($hayFiltros): ?>
                        <a href="<?= Config::url('portal/catalogo') ?>" class="btn btn-secondary">
                            <i class="fa-solid fa-eraser"></i>
                            Limpiar
                        </a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Buscar
                    </button>
                </div>
            </form>
        </section>

        <section class="client-section client-catalog-results">
            <div class="client-section-heading">
                <div>
                    <span class="client-section-kicker">Resultados</span>
                    <h2>
                        <i class="fa-solid fa-border-all"></i>
                        <?= $hayFiltros ? 'Libros encontrados' : 'Todos los libros' ?>
                    </h2>
                    <p>
                        <?= count($libros) ?> <?= count($libros) === 1 ? 'resultado visible' : 'resultados visibles' ?>
                        <?php if ($totalPaginas > 1): ?>
                            · Página <?= $paginaActual ?> de <?= $totalPaginas ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <?php if (!empty($libros)): ?>
                <div class="client-books-grid">
                    <?php foreach ($libros as $libro): ?>
                        <?php $portada = $urlPortada($libro); ?>
                        <article class="client-book-card">
                            <a
                                class="client-book-cover"
                                href="<?= Config::url('portal/catalogo/detalle') ?>?id=<?= (int) ($libro['id_libro'] ?? 0) ?>"
                                aria-label="Ver <?= $escapar($libro['titulo'] ?? 'libro') ?>"
                            >
                                <?php if ($portada !== null): ?>
                                    <img
                                        src="<?= $escapar($portada) ?>"
                                        alt="Portada de <?= $escapar($libro['titulo'] ?? 'libro') ?>"
                                        loading="lazy"
                                        onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                                    >
                                    <span class="client-book-cover-fallback" hidden><i class="fa-solid fa-book-open"></i></span>
                                <?php else: ?>
                                    <span class="client-book-cover-fallback"><i class="fa-solid fa-book-open"></i></span>
                                <?php endif; ?>

                                <span class="client-book-availability <?= (int) ($libro['existencias'] ?? 0) > 0 ? 'is-available' : 'is-unavailable' ?>">
                                    <?= (int) ($libro['existencias'] ?? 0) > 0
                                        ? (int) $libro['existencias'] . ' disponible' . ((int) $libro['existencias'] === 1 ? '' : 's')
                                        : 'Agotado' ?>
                                </span>
                            </a>

                            <div class="client-book-body">
                                <span class="client-book-category"><?= $escapar($libro['categoria'] ?? 'Sin categoría') ?></span>
                                <h3><?= $escapar($libro['titulo'] ?? 'Libro sin título') ?></h3>
                                <p><i class="fa-solid fa-feather-pointed"></i> <?= $escapar($libro['autor'] ?? 'Autor desconocido') ?></p>

                                <?php if (!empty($libro['ubicacion'])): ?>
                                    <small class="client-book-location">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <?= $escapar($libro['ubicacion']) ?>
                                    </small>
                                <?php endif; ?>

                                <a
                                    href="<?= Config::url('portal/catalogo/detalle') ?>?id=<?= (int) ($libro['id_libro'] ?? 0) ?>"
                                    class="btn btn-primary client-book-action"
                                >
                                    Ver detalles
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="client-empty-state">
                    <span><i class="fa-solid fa-book-open-reader"></i></span>
                    <h3>No encontramos libros con esos criterios</h3>
                    <p>Prueba con otro título, autor o categoría. También puedes solicitar el material que necesitas.</p>
                    <div>
                        <a href="<?= Config::url('portal/catalogo') ?>" class="btn btn-secondary">
                            <i class="fa-solid fa-rotate-left"></i>
                            Ver todo el catálogo
                        </a>
                        <a href="<?= Config::url('portal/solicitudes') ?>" class="btn btn-primary">
                            <i class="fa-solid fa-circle-plus"></i>
                            Solicitar libro
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($totalPaginas > 1): ?>
                <nav class="client-pagination-wrap" aria-label="Paginación del catálogo">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= $queryBase ?>&pagina=<?= max(1, $paginaActual - 1) ?>" aria-label="Página anterior">
                                <i class="fa-solid fa-angle-left"></i>
                            </a>
                        </li>

                        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                            <li class="page-item <?= $p === $paginaActual ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= $queryBase ?>&pagina=<?= $p ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= $queryBase ?>&pagina=<?= min($totalPaginas, $paginaActual + 1) ?>" aria-label="Página siguiente">
                                <i class="fa-solid fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </section>

        <aside class="client-help-banner">
            <span><i class="fa-solid fa-lightbulb"></i></span>
            <div>
                <h2>¿No encontraste el libro que buscas?</h2>
                <p>Envíanos una solicitud y la administración evaluará su incorporación al catálogo.</p>
            </div>
            <a href="<?= Config::url('portal/solicitudes') ?>" class="btn client-btn-light">
                Solicitar libro
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </aside>
    </div>
</main>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
