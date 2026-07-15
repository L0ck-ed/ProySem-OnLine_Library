<?php

use App\Config\Config;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$primerNombre = explode(' ', trim((string) ($nombreEstudiante ?? 'Usuario')))[0] ?: 'Usuario';
$tipoUsuarioSesion = $tipoUsuarioSesion ?? 'Usuario';
$librosRecientes = is_array($librosRecientes ?? null) ? $librosRecientes : [];
$categoriasDestacadas = is_array($categoriasDestacadas ?? null) ? $categoriasDestacadas : [];
$topLibrosPorPeriodo = is_array($topLibrosPorPeriodo ?? null) ? $topLibrosPorPeriodo : [];

$urlPortada = static function (array $libro): ?string {
    $ruta = trim(str_replace('\\', '/', (string) ($libro['thumbnail_ruta'] ?? '')), '/');
    return $ruta !== '' ? Config::asset($ruta) : null;
};
?>

<main class="client-page client-home-page">
    <div class="client-page-inner">
        <?php if (!empty($errorPermiso)): ?>
            <div class="alert alert-danger client-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= $escapar($errorPermiso) ?>
            </div>
        <?php endif; ?>

        <section class="client-home-hero">
            <div class="client-home-hero-copy">
                <span class="client-eyebrow">
                    <i class="fa-solid <?= $tipoUsuarioSesion === 'Profesor' ? 'fa-chalkboard-user' : 'fa-user-graduate' ?>"></i>
                    Portal de <?= $escapar($tipoUsuarioSesion) ?>
                </span>

                <h1>Hola, <?= $escapar($primerNombre) ?></h1>

                <p class="client-home-profile-line">
                    <i class="fa-solid fa-building-columns"></i>
                    <?= $escapar($carreraSesion ?? 'Perfil no especificado') ?>
                    <?php if (!empty($cipSesion)): ?>
                        <span>·</span>
                        CIP <?= $escapar($cipSesion) ?>
                    <?php endif; ?>
                </p>

                <p class="client-home-hero-description">
                    Encuentra material académico, administra tus préstamos y solicita nuevos títulos desde un solo lugar.
                </p>

                <div class="client-home-hero-actions">
                    <?php if ($puedeVerLibros ?? false): ?>
                        <a href="<?= Config::url('portal/catalogo') ?>" class="btn client-btn-light">
                            <i class="fa-solid fa-book-open"></i>
                            Explorar catálogo
                        </a>
                    <?php endif; ?>

                    <a href="<?= Config::url('portal/prestamos') ?>" class="btn client-btn-ghost-light">
                        <i class="fa-solid fa-calendar-check"></i>
                        Ver mis préstamos
                    </a>
                </div>
            </div>

            <?php if ($puedeVerLibros ?? false): ?>
                <div class="client-hero-search-card">
                    <div class="client-hero-search-icon">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <div>
                        <h2>¿Qué libro necesitas?</h2>
                        <p>Busca por título, autor o tema.</p>
                    </div>

                    <form action="<?= Config::url('portal/catalogo') ?>" method="GET" class="client-hero-search-form">
                        <label for="buscar-home" class="visually-hidden">Buscar por título o autor</label>
                        <div class="client-search-field">
                            <i class="fa-solid fa-book-open"></i>
                            <input
                                type="search"
                                name="buscar"
                                id="buscar-home"
                                class="form-control"
                                placeholder="Ej: Bases de datos, Brown..."
                                value="<?= $escapar($busqueda ?? '') ?>"
                            >
                        </div>
                        <button type="submit" class="btn btn-primary">
                            Buscar
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </section>

        <section class="client-stats-grid" aria-label="Resumen del portal">
            <article class="client-stat-card">
                <span class="client-stat-icon"><i class="fa-solid fa-book"></i></span>
                <span class="client-stat-copy">
                    <strong><?= (int) ($stats['total_libros'] ?? 0) ?></strong>
                    <small>Libros en catálogo</small>
                </span>
            </article>

            <article class="client-stat-card">
                <span class="client-stat-icon"><i class="fa-solid fa-circle-check"></i></span>
                <span class="client-stat-copy">
                    <strong><?= (int) ($stats['disponibles_ahora'] ?? 0) ?></strong>
                    <small>Disponibles ahora</small>
                </span>
            </article>

            <article class="client-stat-card">
                <span class="client-stat-icon"><i class="fa-solid fa-tags"></i></span>
                <span class="client-stat-copy">
                    <strong><?= (int) ($stats['categorias'] ?? 0) ?></strong>
                    <small>Categorías</small>
                </span>
            </article>

            <article class="client-stat-card">
                <span class="client-stat-icon"><i class="fa-solid fa-calendar-day"></i></span>
                <span class="client-stat-copy">
                    <strong><?= (int) ($stats['prestamos_activos'] ?? 0) ?></strong>
                    <small>Préstamos activos</small>
                </span>
            </article>
        </section>

        <?php if (($puedeVerLibros ?? false) && !empty($categoriasDestacadas)): ?>
            <section class="client-section client-category-section">
                <div class="client-section-heading">
                    <div>
                        <span class="client-section-kicker">Explora por área</span>
                        <h2><i class="fa-solid fa-shapes"></i> Categorías destacadas</h2>
                    </div>
                    <a href="<?= Config::url('portal/catalogo') ?>" class="client-text-link">
                        Ver todas
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div class="client-category-list">
                    <?php foreach ($categoriasDestacadas as $cat): ?>
                        <a
                            href="<?= Config::url('portal/catalogo') ?>?categoria=<?= urlencode((string) ($cat['nombre'] ?? '')) ?>"
                            class="client-category-chip"
                        >
                            <i class="<?= $escapar($cat['icono'] ?? 'fa-solid fa-tag') ?>"></i>
                            <span><?= $escapar($cat['nombre'] ?? 'Categoría') ?></span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($puedeVerLibros ?? false): ?>
            <section class="client-section">
                <div class="client-section-heading">
                    <div>
                        <span class="client-section-kicker">Novedades</span>
                        <h2><i class="fa-solid fa-clock-rotate-left"></i> Agregados recientemente</h2>
                    </div>
                    <a href="<?= Config::url('portal/catalogo') ?>" class="client-text-link">
                        Ver catálogo completo
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <?php if (!empty($librosRecientes)): ?>
                    <div class="client-books-grid client-books-grid-home">
                        <?php foreach ($librosRecientes as $libro): ?>
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
                    <div class="client-empty-state compact">
                        <span><i class="fa-solid fa-book-open"></i></span>
                        <h3>Aún no hay libros recientes</h3>
                        <p>Los nuevos títulos aparecerán aquí cuando sean registrados.</p>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (!empty($topLibrosPorPeriodo)): ?>
            <section class="client-section">
                <div class="client-section-heading">
                    <div>
                        <span class="client-section-kicker">Actividad del catálogo</span>
                        <h2><i class="fa-solid fa-chart-pie"></i> Libros más usados por periodo</h2>
                    </div>
                </div>

                <div class="client-period-grid">
                    <?php foreach ($topLibrosPorPeriodo as $index => $periodo): ?>
                        <?php
                        $librosPeriodo = is_array($periodo['libros'] ?? null) ? $periodo['libros'] : [];
                        $nombrePeriodo = $periodo['nombre'] ?? 'Periodo';
                        ?>
                        <article class="client-period-card">
                            <header>
                                <span>
                                    <i class="fa-regular fa-calendar"></i>
                                    <?= $escapar($nombrePeriodo) ?>
                                </span>
                                <strong><?= count($librosPeriodo) ?> <?= count($librosPeriodo) === 1 ? 'libro' : 'libros' ?></strong>
                            </header>

                            <?php if (empty($librosPeriodo)): ?>
                                <div class="client-period-empty">
                                    <i class="fa-solid fa-chart-simple"></i>
                                    <p>Sin movimientos en este periodo.</p>
                                </div>
                            <?php else: ?>
                                <div class="client-period-chart">
                                    <canvas id="chart-periodo-<?= (int) $index ?>"></canvas>
                                </div>

                                <div class="client-period-list">
                                    <?php foreach ($librosPeriodo as $libro): ?>
                                        <?php
                                        $totalUsos = (int) ($libro['total_prestamos'] ?? $libro['total_reservas'] ?? $libro['total_usos'] ?? 0);
                                        ?>
                                        <div class="client-period-item">
                                            <span>
                                                <strong title="<?= $escapar($libro['titulo'] ?? '') ?>"><?= $escapar($libro['titulo'] ?? 'Libro sin título') ?></strong>
                                                <small><?= $escapar($libro['autor'] ?? 'Autor desconocido') ?></small>
                                            </span>
                                            <b><?= $totalUsos ?></b>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="client-section">
            <div class="client-section-heading">
                <div>
                    <span class="client-section-kicker">Atajos</span>
                    <h2><i class="fa-solid fa-bolt"></i> Acciones rápidas</h2>
                </div>
            </div>

            <div class="client-quick-grid">
                <?php if ($puedeVerLibros ?? false): ?>
                    <a href="<?= Config::url('portal/catalogo') ?>" class="client-quick-card">
                        <span><i class="fa-solid fa-magnifying-glass"></i></span>
                        <div>
                            <h3>Explorar catálogo</h3>
                            <p>Busca libros por título, autor o categoría.</p>
                        </div>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                <?php endif; ?>

                <a href="<?= Config::url('portal/prestamos') ?>" class="client-quick-card">
                    <span><i class="fa-solid fa-calendar-check"></i></span>
                    <div>
                        <h3>Mis préstamos</h3>
                        <p>Revisa reservas, vencimientos e historial.</p>
                    </div>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

                <a href="<?= Config::url('portal/solicitudes') ?>" class="client-quick-card">
                    <span><i class="fa-solid fa-circle-plus"></i></span>
                    <div>
                        <h3>Solicitar un libro</h3>
                        <p>Envía una petición cuando no encuentres un título.</p>
                    </div>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') {
        return;
    }

    const configuraciones = <?= json_encode(
        array_map(
            static function (array $periodo, int $index): array {
                $libros = is_array($periodo['libros'] ?? null) ? $periodo['libros'] : [];

                return [
                    'id' => 'chart-periodo-' . $index,
                    'labels' => array_map(
                        static fn(array $libro): string => (string) ($libro['titulo'] ?? 'Libro'),
                        $libros,
                    ),
                    'valores' => array_map(
                        static fn(array $libro): int => (int) ($libro['total_prestamos'] ?? $libro['total_reservas'] ?? $libro['total_usos'] ?? 0),
                        $libros,
                    ),
                ];
            },
            $topLibrosPorPeriodo,
            array_keys($topLibrosPorPeriodo),
        ),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
    ) ?>;

    const colores = ['#6D3C1C', '#C57938', '#E7C196', '#7E9B76', '#A96B43'];

    configuraciones.forEach((configuracion) => {
        const canvas = document.getElementById(configuracion.id);
        if (!canvas || configuracion.valores.length === 0) {
            return;
        }

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: configuracion.labels,
                datasets: [{
                    data: configuracion.valores,
                    backgroundColor: colores.slice(0, configuracion.valores.length),
                    borderWidth: 3,
                    borderColor: '#fffaf3',
                    hoverOffset: 5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return `${context.label ?? 'Libro'}: ${context.parsed ?? 0} uso(s)`;
                            },
                        },
                    },
                },
            },
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
