<?php
// Los datos llegan desde el controlador a través de $data
// Variables disponibles: $nombreEstudiante, $cipSesion, $carreraSesion,
// $stats, $categoriasDestacadas, $librosRecientes, $topLibrosPorPeriodo

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';
?>

<div class="container-fluid py-4 px-4">

    <div class="portal-hero mb-4">
        <div class="row align-items-center g-4">
            <!-- Columna izquierda: mensaje de bienvenida -->
            <div class="col-lg-8">
                <h2 class="mb-2">Hola, <?= htmlspecialchars(explode(' ', $nombreEstudiante ?? 'Usuario')[0]) ?></h2>
                <p class="mb-0"><?= htmlspecialchars($carreraSesion ?? 'Carrera no especificada') ?> · CIP <?= htmlspecialchars($cipSesion ?? '') ?></p>
                <p class="mb-0">Busca un libro, revisa tus préstamos activos o solicita un título que no encuentres.</p>
            </div>

            <!-- Columna derecha: formulario de búsqueda (estilo similar al catálogo) -->
            <div class="col-lg-4">
                <div class="card p-3">
                    <form action="<?= \App\Config\Config::url('portal/catalogo') ?>" method="GET" class="d-flex flex-column gap-2">
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
            <a href="<?= \App\Config\Config::url('portal/catalogo') ?>?categoria=<?= urlencode($cat['nombre']) ?>"
               class="btn btn-secondary btn-sm">
                <i class="<?= $cat['icono'] ?? 'fa-solid fa-tag' ?>"></i> <?= htmlspecialchars($cat['nombre']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- LIBROS RECIENTES -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0"><i class="fa-solid fa-clock-rotate-left"></i> Agregados recientemente</h5>
        <a href="<?= \App\Config\Config::url('portal/catalogo') ?>" class="fw-bold text-decoration-none">
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
                            <span class="badge badge-categoria"><?= htmlspecialchars($libro['categoria']) ?></span>
                            <?php if ($libro['existencias'] > 0): ?>
                                <span class="badge badge-existencias-ok"><?= $libro['existencias'] ?> disp.</span>
                            <?php else: ?>
                                <span class="badge badge-existencias-agotado">Agotado</span>
                            <?php endif; ?>
                        </div>
                        <a href="<?= \App\Config\Config::url('portal/catalogo/detalle') ?>?id=<?= $libro['id_libro'] ?>" class="btn btn-primary btn-sm w-100 mt-2">
                            Ver detalle
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ===== ESTADÍSTICAS: LIBROS MÁS USADOS POR PERIODO ===== -->
    <div class="mt-5 pt-3 mb-5 pb-2">
        <h5 class="mb-4"><i class="fa-solid fa-chart-simple"></i> Libros más usados por periodo</h5>
        <div class="row g-4">
            <?php foreach ($topLibrosPorPeriodo ?? [] as $index => $periodo): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm" style="border-radius: 20px; overflow: hidden; border: none;">

                        <!-- Encabezado oscuro (igual al hero) -->
                        <div class="card-header" style="background: linear-gradient(135deg, #382417, #6D3C1C); padding: 12px 20px; border-bottom: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span style="color: #FFF9F0; font-weight: 900;">
                                    <i class="fa-regular fa-calendar"></i> <?= $periodo['nombre'] ?>
                                </span>
                                <span class="badge rounded-pill" style="background-color: #E7C196; color: #2E2118; font-weight: 900;">
                                    <?= count($periodo['libros']) ?> libros
                                </span>
                            </div>
                        </div>

                        <!-- Cuerpo de la tarjeta -->
                        <div class="card-body" style="padding: 20px; background: #FFFFFF;">
                            <?php if (empty($periodo['libros'])): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fa-regular fa-face-frown fa-2x mb-2"></i>
                                    <p class="mb-0">Sin préstamos en este periodo.</p>
                                </div>
                            <?php else: ?>
                                <!-- Gráfico de pastel con tamaño fijo (120px) -->
                                <div class="text-center mb-3">
                                    <div style="display: inline-block; width: 120px; height: 120px;">
                                        <canvas id="chart-periodo-<?= $index ?>" style="width:120px; height:120px;"></canvas>
                                    </div>
                                </div>

                                <!-- Lista de libros con scroll si son muchos -->
                                <div class="mt-2" style="max-height: 180px; overflow-y: auto; padding-right: 5px;">
                                    <?php foreach ($periodo['libros'] as $libro): ?>
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                                            <div style="flex: 1; min-width: 0;">
                                                <span class="fw-bold small" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    <?= htmlspecialchars($libro['titulo']) ?>
                                                </span>
                                                <small class="text-muted"><?= htmlspecialchars($libro['autor']) ?></small>
                                            </div>
                                            <span class="badge rounded-pill ms-2" style="background-color: #E7C196; color: #2E2118; font-weight: 900; flex-shrink: 0;">
                                                <?= $libro['total_prestamos'] ?>
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
                <a href="<?= \App\Config\Config::url('portal/catalogo') ?>" class="btn btn-secondary mt-auto">Ir al catálogo</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 h-100">
                <i class="fa-solid fa-calendar-check mb-2" style="color:var(--caramel); font-size:26px;"></i>
                <h5>Mis préstamos</h5>
                <p>Consulta tus préstamos activos y tu historial de devoluciones.</p>
                <a href="<?= \App\Config\Config::url('portal/prestamos') ?>" class="btn btn-secondary mt-auto">Ver mis préstamos</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 h-100">
                <i class="fa-solid fa-circle-plus mb-2" style="color:var(--caramel); font-size:26px;"></i>
                <h5>¿No lo encontraste?</h5>
                <p>Solicita un libro que necesites y la administración revisará tu petición.</p>
                <a href="<?= \App\Config\Config::url('portal/solicitudes') ?>" class="btn btn-secondary mt-auto">Solicitar libro</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>

<!-- ===== SCRIPTS PARA GRÁFICOS ===== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ===== Tooltip externo en HTML =====
    // Los tooltips normales de Chart.js se dibujan DENTRO del canvas,
    // por lo que si el canvas es pequeño (120x120) el texto se recorta
    // sin importar el maxWidth configurado. Este handler crea un <div>
    // flotante fuera del canvas para mostrar el nombre completo del libro.
    function externalTooltipHandler(context) {
        const { chart, tooltip } = context;

        let tooltipEl = document.getElementById('chartjs-tooltip-flotante');
        if (!tooltipEl) {
            tooltipEl = document.createElement('div');
            tooltipEl.id = 'chartjs-tooltip-flotante';
            tooltipEl.style.position = 'absolute';
            tooltipEl.style.pointerEvents = 'none';
            tooltipEl.style.background = 'rgba(56, 36, 23, 0.95)';
            tooltipEl.style.color = '#FFF9F0';
            tooltipEl.style.borderRadius = '12px';
            tooltipEl.style.padding = '10px 16px';
            tooltipEl.style.fontSize = '14px';
            tooltipEl.style.lineHeight = '1.4';
            tooltipEl.style.maxWidth = '240px';
            tooltipEl.style.whiteSpace = 'normal';
            tooltipEl.style.wordWrap = 'break-word';
            tooltipEl.style.zIndex = '9999';
            tooltipEl.style.boxShadow = '0 4px 14px rgba(0,0,0,0.25)';
            tooltipEl.style.transition = 'opacity .1s ease';
            document.body.appendChild(tooltipEl);
        }

        // Ocultar si no hay tooltip activo
        if (tooltip.opacity === 0) {
            tooltipEl.style.opacity = 0;
            return;
        }

        // Construir el contenido (título en negrita + líneas del cuerpo)
        if (tooltip.body) {
            let innerHtml = '';
            (tooltip.title || []).forEach(title => {
                if (title && title.trim() !== '') {
                    innerHtml += `<div style="font-weight:bold; margin-bottom:4px;">${title}</div>`;
                }
            });
            tooltip.body.forEach(item => {
                item.lines.forEach(line => {
                    innerHtml += `<div>${line}</div>`;
                });
            });
            tooltipEl.innerHTML = innerHtml;
        }

        const canvasRect = chart.canvas.getBoundingClientRect();
        let left = canvasRect.left + window.scrollX + tooltip.caretX;
        let top = canvasRect.top + window.scrollY + tooltip.caretY;

        tooltipEl.style.opacity = 1;
        tooltipEl.style.left = left + 'px';
        tooltipEl.style.top = top + 'px';

        // Evitar que el tooltip se salga de la pantalla
        requestAnimationFrame(() => {
            const rect = tooltipEl.getBoundingClientRect();
            if (rect.right > window.innerWidth) {
                tooltipEl.style.left = (window.innerWidth - rect.width - 10 + window.scrollX) + 'px';
            }
            if (rect.left < 0) {
                tooltipEl.style.left = (10 + window.scrollX) + 'px';
            }
            if (rect.top < 0) {
                tooltipEl.style.top = (10 + window.scrollY) + 'px';
            }
        });
    }

    <?php foreach ($topLibrosPorPeriodo ?? [] as $index => $periodo): ?>
        <?php if (!empty($periodo['libros'])): ?>
            const ctx<?= $index ?> = document.getElementById('chart-periodo-<?= $index ?>');
            if (ctx<?= $index ?>) {
                const colors = [
                    '#4e79a7', '#f28e2b', '#e15759', '#59a14f', '#edc948',
                    '#b07aa1', '#ff9da7', '#9c755f', '#bab0ac', '#8cd17d'
                ];
                new Chart(ctx<?= $index ?>.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: <?= json_encode(array_column($periodo['libros'], 'titulo')) ?>,
                        datasets: [{
                            data: <?= json_encode(array_column($periodo['libros'], 'total_prestamos')) ?>,
                            backgroundColor: colors.slice(0, <?= count($periodo['libros']) ?>),
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false, // Se desactiva el tooltip nativo (se recorta dentro del canvas)
                                position: 'nearest',
                                external: externalTooltipHandler, // 👈 Tooltip en HTML, no se corta
                                callbacks: {
                                    title: function() {
                                        return ''; // Se oculta: el nombre ya va en el label de abajo
                                    },
                                    label: function(context) {
                                        // Muestra el nombre completo sin truncar
                                        return context.label + ': ' + context.parsed + ' préstamo(s)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        <?php endif; ?>
    <?php endforeach; ?>
});
</script>
</body>
</html>