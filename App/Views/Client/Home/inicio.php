<?php

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};
?>

<div class="container-fluid py-4 px-4">

    <div class="portal-hero mb-4">
        <div class="row align-items-center g-4">
            <!-- Columna izquierda: mensaje de bienvenida -->
            <div class="col-lg-8">
                <h2 class="mb-2">Hola, <?= htmlspecialchars(
                    explode(' ', $nombreEstudiante ?? 'Usuario')[0],
                ) ?></h2>
                <p class="mb-0"><?= htmlspecialchars(
                    $carreraSesion ?? 'Carrera no especificada',
                ) ?> · CIP <?= htmlspecialchars($cipSesion ?? '') ?></p>
                <p class="mb-0">Busca un libro, revisa tus préstamos activos o solicita un título que no encuentres.</p>
            </div>

            <!-- Columna derecha: formulario de búsqueda (estilo similar al catálogo) -->
            <div class="col-lg-4">
                <div class="card p-3">
                    <form action="<?= \App\Config\Config::url(
                        'portal/catalogo',
                    ) ?>" method="GET" class="d-flex flex-column gap-2">
                        <div>
                            <label for="buscar-home" class="form-label fw-semibold small">Buscar por título o autor</label>
                            <input 
                                type="text" 
                                name="buscar" 
                                id="buscar-home"
                                class="form-control" 
                                placeholder="Ej: Lógica Matemática, Brown..."
                                value="<?= htmlspecialchars($busqueda ?? '') ?>"
                            >
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-magnifying-glass"></i> Buscar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- STATS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-chip">
                <i class="fa-solid fa-book"></i>
                <div>
                    <div class="stat-value"><?= $stats['total_libros'] ?? 0 ?></div>
                    <div class="stat-label">Libros en catálogo</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-chip">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <div class="stat-value"><?= $stats['disponibles_ahora'] ?? 0 ?></div>
                    <div class="stat-label">Disponibles ahora</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-chip">
                <i class="fa-solid fa-tags"></i>
                <div>
                    <div class="stat-value"><?= $stats['categorias'] ?? 0 ?></div>
                    <div class="stat-label">Categorías</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-chip">
                <i class="fa-solid fa-calendar-check"></i>
                <div>
                    <div class="stat-value"><?= $stats['prestamos_activos'] ?? 0 ?></div>
                    <div class="stat-label">Tus préstamos activos</div>
                </div>
            </div>
        </div>
    </div>

    <!-- CATEGORÍAS DESTACADAS -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0"><i class="fa-solid fa-tags"></i> Categorías</h5>
    </div>
    <div class="d-flex flex-wrap gap-2 mb-4">
        <?php foreach ($categoriasDestacadas ?? [] as $cat): ?>
            <a href="<?= \App\Config\Config::url('portal/catalogo') ?>?categoria=<?= urlencode(
    $cat['nombre'],
) ?>"
               class="btn btn-secondary btn-sm">
                <i class="<?= $cat['icono'] ?? 'fa-solid fa-tag' ?>"></i> <?= htmlspecialchars(
    $cat['nombre'],
) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- LIBROS RECIENTES -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0"><i class="fa-solid fa-clock-rotate-left"></i> Agregados recientemente</h5>
        <a href="<?= \App\Config\Config::url(
            'portal/catalogo',
        ) ?>" class="fw-bold text-decoration-none">
            Ver catálogo completo <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="row g-4 mb-4">
        <?php foreach ($librosRecientes ?? [] as $libro): ?>
            <div class="col-md-6 col-lg-3">
                <div class="book-card">
                    <div class="book-cover"><i class="fa-solid fa-book"></i></div>
                    <div class="book-card-body">
                        <h5><?= htmlspecialchars($libro['titulo']) ?></h5>
                        <span class="book-autor"><?= htmlspecialchars($libro['autor']) ?></span>
                        <div class="book-card-footer">
                            <span class="badge badge-categoria"><?= htmlspecialchars(
                                $libro['categoria'],
                            ) ?></span>
                            <?php if ($libro['existencias'] > 0): ?>
                                <span class="badge badge-existencias-ok"><?= $libro[
                                    'existencias'
                                ] ?> disp.</span>
                            <?php else: ?>
                                <span class="badge badge-existencias-agotado">Agotado</span>
                            <?php endif; ?>
                        </div>
                        <a href="<?= \App\Config\Config::url(
                            'portal/catalogo/detalle',
                        ) ?>?id=<?= $libro[
    'id_libro'
] ?>" class="btn btn-primary btn-sm w-100 mt-2">
                            Ver detalle
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ===== ESTADÍSTICAS: LIBROS MÁS USADOS POR PERIODO ===== -->
    <div class="mt-5 pt-3 mb-5 pb-2">
        <h5 class="mb-4">
            <i class="fa-solid fa-chart-simple"></i>
            Libros más usados por periodo
        </h5>

        <div class="row g-4">
            <?php foreach ($topLibrosPorPeriodo ?? [] as $index => $periodo): ?>
                <?php
                $librosPeriodo = is_array($periodo['libros'] ?? null) ? $periodo['libros'] : [];

                $nombrePeriodo = $periodo['nombre'] ?? 'Periodo sin nombre';
                ?>

                <div class="col-md-4">
                    <div
                        class="card h-100 shadow-sm"
                        style="
                            border-radius: 20px;
                            overflow: hidden;
                            border: none;
                        "
                    >
                        <div
                            class="card-header"
                            style="
                                background: linear-gradient(
                                    135deg,
                                    #382417,
                                    #6d3c1c
                                );
                                padding: 12px 20px;
                                border-bottom: none;
                            "
                        >
                            <div
                                class="d-flex justify-content-between align-items-center"
                            >
                                <span
                                    style="
                                        color: #fff9f0;
                                        font-weight: 900;
                                    "
                                >
                                    <i class="fa-regular fa-calendar"></i>

                                    <?= $escapar($nombrePeriodo) ?>
                                </span>

                                <span
                                    class="badge rounded-pill"
                                    style="
                                        background-color: #e7c196;
                                        color: #2e2118;
                                        font-weight: 900;
                                    "
                                >
                                    <?= count($librosPeriodo) ?>

                                    <?= count($librosPeriodo) === 1 ? 'libro' : 'libros' ?>
                                </span>
                            </div>
                        </div>

                        <div
                            class="card-body"
                            style="
                                padding: 20px;
                                background: #ffffff;
                            "
                        >
                            <?php if (empty($librosPeriodo)): ?>
                                <div class="text-center text-muted py-4">
                                    <i
                                        class="fa-regular fa-face-frown fa-2x mb-2"
                                    ></i>

                                    <p class="mb-0">
                                        Sin préstamos en este periodo.
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="text-center mb-3">
                                    <div
                                        style="
                                            position: relative;
                                            width: 180px;
                                            height: 180px;
                                            margin: 0 auto;
                                        "
                                    >
                                        <canvas
                                            id="chart-periodo-<?= (int) $index ?>"
                                        ></canvas>
                                    </div>
                                </div>

                                <div
                                    class="mt-2"
                                    style="
                                        max-height: 180px;
                                        overflow-y: auto;
                                        padding-right: 5px;
                                    "
                                >
                                    <?php foreach ($librosPeriodo as $libro): ?>
                                        <?php
                                        $tituloLibro = $libro['titulo'] ?? 'Libro sin título';

                                        $autorLibro = trim((string) ($libro['autor'] ?? ''));

                                        if ($autorLibro === '') {
                                            $autorLibro = 'Autor desconocido';
                                        }

                                        $totalUsos =
                                            (int) ($libro['total_prestamos'] ??
                                                ($libro['total_reservas'] ??
                                                    ($libro['total_usos'] ?? 0)));
                                        ?>

                                        <div
                                            class="
                                                d-flex
                                                justify-content-between
                                                align-items-center
                                                py-2
                                                border-bottom
                                                border-light
                                            "
                                        >
                                            <div
                                                style="
                                                    flex: 1;
                                                    min-width: 0;
                                                "
                                            >
                                                <span
                                                    class="fw-bold small"
                                                    style="
                                                        display: block;
                                                        white-space: nowrap;
                                                        overflow: hidden;
                                                        text-overflow: ellipsis;
                                                    "
                                                    title="<?= $escapar($tituloLibro) ?>"
                                                >
                                                    <?= $escapar($tituloLibro) ?>
                                                </span>

                                                <small class="text-muted">
                                                    <?= $escapar($autorLibro) ?>
                                                </small>
                                            </div>

                                            <span
                                                class="badge rounded-pill ms-2"
                                                style="
                                                    background-color: #e7c196;
                                                    color: #2e2118;
                                                    font-weight: 900;
                                                    flex-shrink: 0;
                                                "
                                            >
                                                <?= $totalUsos ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ACCIONES RÁPIDAS -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card p-4 h-100">
                <i class="fa-solid fa-magnifying-glass mb-2" style="color:var(--caramel); font-size:26px;"></i>
                <h5>Explora el catálogo</h5>
                <p>Busca por título, autor o categoría y revisa la disponibilidad en tiempo real.</p>
                <a href="<?= \App\Config\Config::url(
                    'portal/catalogo',
                ) ?>" class="btn btn-secondary mt-auto">Ir al catálogo</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 h-100">
                <i class="fa-solid fa-calendar-check mb-2" style="color:var(--caramel); font-size:26px;"></i>
                <h5>Mis préstamos</h5>
                <p>Consulta tus préstamos activos y tu historial de devoluciones.</p>
                <a href="<?= \App\Config\Config::url(
                    'portal/prestamos',
                ) ?>" class="btn btn-secondary mt-auto">Ver mis préstamos</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 h-100">
                <i class="fa-solid fa-circle-plus mb-2" style="color:var(--caramel); font-size:26px;"></i>
                <h5>¿No lo encontraste?</h5>
                <p>Solicita un libro que necesites y la administración revisará tu petición.</p>
                <a href="<?= \App\Config\Config::url(
                    'portal/solicitudes',
                ) ?>" class="btn btn-secondary mt-auto">Solicitar libro</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    if (typeof Chart === "undefined") {
        console.error("Chart.js no pudo cargarse.");
        return;
    }

    const configuraciones = <?= json_encode(
        array_map(
            static function (array $periodo, int $index): array {
                $libros = is_array($periodo['libros'] ?? null) ? $periodo['libros'] : [];

                return [
                    'id' => 'chart-periodo-' . $index,

                    'labels' => array_map(
                        static fn(array $libro): string => (string) ($libro['titulo'] ??
                            'Libro sin título'),
                        $libros,
                    ),

                    'valores' => array_map(
                        static fn(array $libro): int => (int) ($libro['total_prestamos'] ??
                            ($libro['total_reservas'] ?? ($libro['total_usos'] ?? 0))),
                        $libros,
                    ),
                ];
            },
            $topLibrosPorPeriodo ?? [],
            array_keys($topLibrosPorPeriodo ?? []),
        ),
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT,
    ) ?>;

    const colores = [
        "#4e79a7",
        "#f28e2b",
        "#e15759",
        "#59a14f",
        "#edc948",
        "#b07aa1",
        "#ff9da7",
        "#9c755f",
        "#bab0ac",
        "#8cd17d",
    ];

    configuraciones.forEach((configuracion) => {
        const canvas = document.getElementById(
            configuracion.id
        );

        if (
            !canvas ||
            configuracion.valores.length === 0
        ) {
            return;
        }

        new Chart(canvas, {
            type: "pie",

            data: {
                labels: configuracion.labels,

                datasets: [
                    {
                        data: configuracion.valores,
                        backgroundColor: colores.slice(
                            0,
                            configuracion.valores.length
                        ),
                        borderWidth: 2,
                        borderColor: "#ffffff",
                    },
                ],
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        display: false,
                    },

                    tooltip: {
                        enabled: true,

                        callbacks: {
                            label(context) {
                                const titulo =
                                    context.label ??
                                    "Libro";

                                const cantidad =
                                    context.parsed ?? 0;

                                return `${titulo}: ${cantidad} uso(s)`;
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
