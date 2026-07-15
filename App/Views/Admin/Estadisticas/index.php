<?php

use App\Config\Config;
use App\Middleware\Auth;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$filtros = $filtros ?? [];
$datos = $datos ?? [];
$advertencia = $advertencia ?? '';
$periodos = $periodos ?? [];
$tiposUsuario = $tiposUsuario ?? [];
$metricas = $metricas ?? [];
$limites = $limites ?? [];
$intervalos = $intervalos ?? [];
$facultades = $facultades ?? [];
$carreras = $carreras ?? [];
$resumen = $datos['resumen'] ?? [];
$resumenAcademico = $datos['resumen_academico'] ?? [];
$ranking = $datos['ranking'] ?? [];
$detalleAcademico = $datos['detalle_academico'] ?? [];

$escapar = static fn (mixed $valor): string =>
    htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$etiquetasPeriodo = [
    'mes_actual' => 'Mes actual',
    'ultimos_30' => 'Últimos 30 días',
    'semestre_actual' => 'Semestre actual',
    'anio_actual' => 'Año actual',
    'personalizado' => 'Personalizado',
];

$etiquetasIntervalo = [
    'auto' => 'Automático',
    'dia' => 'Diario',
    'semana' => 'Semanal',
    'mes' => 'Mensual',
    'anio' => 'Anual',
];

$etiquetasMetrica = [
    'uso' => 'Uso confirmado',
    'solicitudes' => 'Demanda solicitada',
];

$tipoSeleccionado = (string) ($filtros['tipo_usuario'] ?? 'Todos');
$metricaSeleccionada = (string) ($filtros['metrica'] ?? 'uso');
$unidad = (string) ($resumen['unidad'] ?? 'usos');
$unidadCapitalizada = ucfirst($unidad);
$queryExcel = http_build_query($filtros);
$urlExcel = Config::url('estadisticas/excel') . '?' . $queryExcel;
$hayDatos = (int) ($resumen['total_general'] ?? 0) > 0;
$hayDatosAcademicos = !empty($detalleAcademico);
$graficoJson = json_encode(
    $datos['grafico'] ?? [],
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES |
    JSON_HEX_TAG |
    JSON_HEX_AMP |
    JSON_HEX_APOS |
    JSON_HEX_QUOT,
);
?>

<div class="container-fluid admin-page admin-page-estadisticas">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main estadisticas-main">
            <header class="admin-page-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="mb-1">Estadísticas de uso y demanda</h2>
                    <p>
                        Analiza los libros más utilizados y la demanda de cada facultad, carrera y departamento.
                    </p>
                </div>

                <?php if (Auth::tienePermiso('reportes.exportar_excel')): ?>
                    <a href="<?= $escapar($urlExcel) ?>" class="btn btn-success">
                        <i class="fa-solid fa-file-excel"></i>
                        Exportar a Excel
                    </a>
                <?php endif; ?>
            </header>

            <?php if ($advertencia !== ''): ?>
                <div class="alert alert-warning admin-alert" role="alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <?= $escapar($advertencia) ?>
                </div>
            <?php endif; ?>

            <section class="card admin-form-card estadisticas-filter-card">
                <div class="admin-card-heading">
                    <div>
                        <h2 class="admin-card-title mb-1">
                            <i class="fa-solid fa-filter"></i>
                            Período y población de análisis
                        </h2>
                        <p class="mb-0">
                            Uso confirmado cuenta Prestado, Devuelto y Vencido; demanda solicitada también incluye Pendiente y Reservado.
                        </p>
                    </div>
                    <span class="estadisticas-period-badge">
                        <i class="fa-solid fa-calendar"></i>
                        <?= $escapar($filtros['fecha_inicio'] ?? '') ?>
                        —
                        <?= $escapar($filtros['fecha_fin'] ?? '') ?>
                    </span>
                </div>

                <form method="GET" action="<?= Config::url('estadisticas') ?>" id="estadisticasFiltros">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-3">
                            <label for="periodo" class="form-label">Período</label>
                            <select id="periodo" name="periodo" class="form-select">
                                <?php foreach ($periodos as $periodo): ?>
                                    <option value="<?= $escapar($periodo) ?>" <?= ($filtros['periodo'] ?? '') === $periodo ? 'selected' : '' ?>>
                                        <?= $escapar($etiquetasPeriodo[$periodo] ?? $periodo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-6 col-md-3 col-xl-2 estadisticas-custom-date">
                            <label for="fecha_inicio" class="form-label">Desde</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= $escapar($filtros['fecha_inicio'] ?? '') ?>">
                        </div>

                        <div class="col-6 col-md-3 col-xl-2 estadisticas-custom-date">
                            <label for="fecha_fin" class="form-label">Hasta</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= $escapar($filtros['fecha_fin'] ?? '') ?>">
                        </div>

                        <div class="col-12 col-md-6 col-xl-2">
                            <label for="metrica" class="form-label">Métrica</label>
                            <select id="metrica" name="metrica" class="form-select">
                                <?php foreach ($metricas as $metrica): ?>
                                    <option value="<?= $escapar($metrica) ?>" <?= $metricaSeleccionada === $metrica ? 'selected' : '' ?>>
                                        <?= $escapar($etiquetasMetrica[$metrica] ?? $metrica) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-2">
                            <label for="tipo_usuario" class="form-label">Población</label>
                            <select id="tipo_usuario" name="tipo_usuario" class="form-select">
                                <?php foreach ($tiposUsuario as $tipo): ?>
                                    <option value="<?= $escapar($tipo) ?>" <?= ($filtros['tipo_usuario'] ?? '') === $tipo ? 'selected' : '' ?>>
                                        <?= $tipo === 'Todos' ? 'Estudiantes y docentes' : $escapar($tipo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-6 col-md-3 col-xl-2">
                            <label for="intervalo" class="form-label">Intervalo</label>
                            <select id="intervalo" name="intervalo" class="form-select">
                                <?php foreach ($intervalos as $intervalo): ?>
                                    <option value="<?= $escapar($intervalo) ?>" <?= ($filtros['intervalo'] ?? 'auto') === $intervalo ? 'selected' : '' ?>>
                                        <?= $escapar($etiquetasIntervalo[$intervalo] ?? $intervalo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-6 col-md-3 col-xl-1">
                            <label for="limite" class="form-label">Top</label>
                            <select id="limite" name="limite" class="form-select">
                                <?php foreach ($limites as $limite): ?>
                                    <option value="<?= (int) $limite ?>" <?= (int) ($filtros['limite'] ?? 10) === (int) $limite ? 'selected' : '' ?>>
                                        <?= (int) $limite ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-5">
                            <label for="id_facultad" class="form-label">Facultad</label>
                            <select id="id_facultad" name="id_facultad" class="form-select">
                                <option value="0">Todas las facultades</option>
                                <?php foreach ($facultades as $facultad): ?>
                                    <option value="<?= (int) $facultad['id_facultad'] ?>" <?= (int) ($filtros['id_facultad'] ?? 0) === (int) $facultad['id_facultad'] ? 'selected' : '' ?>>
                                        <?= $escapar($facultad['nombre'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-5">
                            <label for="id_carrera" class="form-label">Carrera estudiantil</label>
                            <select id="id_carrera" name="id_carrera" class="form-select">
                                <option value="0">Todas las carreras</option>
                                <?php foreach ($carreras as $carrera): ?>
                                    <option
                                        value="<?= (int) $carrera['id_carrera'] ?>"
                                        data-facultad="<?= (int) ($carrera['id_facultad'] ?? 0) ?>"
                                        <?= (int) ($filtros['id_carrera'] ?? 0) === (int) $carrera['id_carrera'] ? 'selected' : '' ?>
                                    >
                                        <?= $escapar($carrera['nombre'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Al elegir una carrera se analizan únicamente sus estudiantes.</div>
                        </div>

                        <div class="col-12 col-xl-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa-solid fa-chart-column"></i>
                                Generar
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <section class="estadisticas-summary-grid" aria-label="Resumen del período">
                <article class="estadisticas-summary-card">
                    <span class="estadisticas-summary-icon"><i class="fa-solid fa-chart-column"></i></span>
                    <div>
                        <small><?= $escapar($unidadCapitalizada) ?> según filtro</small>
                        <strong><?= (int) ($resumen['total_filtrado'] ?? 0) ?></strong>
                        <span><?= $escapar($tipoSeleccionado) ?></span>
                    </div>
                </article>
                <article class="estadisticas-summary-card">
                    <span class="estadisticas-summary-icon"><i class="fa-solid fa-user-graduate"></i></span>
                    <div>
                        <small><?= $escapar($unidadCapitalizada) ?> de estudiantes</small>
                        <strong><?= (int) ($resumen['estudiantes'] ?? 0) ?></strong>
                        <span><?= $escapar($resumen['metrica_label'] ?? '') ?></span>
                    </div>
                </article>
                <article class="estadisticas-summary-card">
                    <span class="estadisticas-summary-icon"><i class="fa-solid fa-chalkboard-user"></i></span>
                    <div>
                        <small><?= $escapar($unidadCapitalizada) ?> de docentes</small>
                        <strong><?= (int) ($resumen['docentes'] ?? 0) ?></strong>
                        <span><?= $escapar($resumen['metrica_label'] ?? '') ?></span>
                    </div>
                </article>
                <article class="estadisticas-summary-card">
                    <span class="estadisticas-summary-icon"><i class="fa-solid fa-book"></i></span>
                    <div>
                        <small>Libros diferentes</small>
                        <strong><?= (int) ($resumen['libros_distintos'] ?? 0) ?></strong>
                        <span title="<?= $escapar($resumen['libro_mas_usado'] ?? '') ?>">Más usado: <?= $escapar($resumen['libro_mas_usado'] ?? 'Sin datos') ?></span>
                    </div>
                </article>
            </section>

            <?php if ($hayDatos): ?>
                <section class="estadisticas-chart-grid">
                    <article class="card estadisticas-chart-card estadisticas-chart-card-wide">
                        <div class="estadisticas-card-heading">
                            <div>
                                <span class="estadisticas-card-kicker">Ranking general</span>
                                <h2>Libros más utilizados</h2>
                                <p>Resultado para: <?= $escapar($tipoSeleccionado) ?>.</p>
                            </div>
                            <span class="estadisticas-chart-total"><?= (int) ($resumen['total_filtrado'] ?? 0) ?> <?= $escapar($unidad) ?></span>
                        </div>
                        <div class="estadisticas-canvas-wrap estadisticas-canvas-ranking">
                            <canvas id="graficoRanking" aria-label="Ranking de libros más utilizados"></canvas>
                        </div>
                    </article>

                    <article class="card estadisticas-chart-card">
                        <div class="estadisticas-card-heading">
                            <div>
                                <span class="estadisticas-card-kicker">Comparación</span>
                                <h2>Distribución por población</h2>
                                <p>Participación de estudiantes y docentes en la métrica elegida.</p>
                            </div>
                        </div>
                        <div class="estadisticas-canvas-wrap estadisticas-canvas-donut">
                            <canvas id="graficoDistribucion" aria-label="Distribución por población"></canvas>
                        </div>
                    </article>

                    <article class="card estadisticas-chart-card estadisticas-chart-card-wide">
                        <div class="estadisticas-card-heading">
                            <div>
                                <span class="estadisticas-card-kicker">Comportamiento</span>
                                <h2>Evolución durante el período</h2>
                                <p>Serie comparativa en intervalo <?= $escapar($resumenAcademico['intervalo'] ?? '') ?>.</p>
                            </div>
                        </div>
                        <div class="estadisticas-canvas-wrap estadisticas-canvas-trend">
                            <canvas id="graficoTendencia" aria-label="Evolución de uso de libros"></canvas>
                        </div>
                    </article>
                </section>

                <section class="row g-4 estadisticas-population-row">
                    <div class="col-12 col-xl-6">
                        <article class="card estadisticas-chart-card h-100">
                            <div class="estadisticas-card-heading"><div><span class="estadisticas-card-kicker">Estudiantes</span><h2>Top 5 estudiantil</h2><p>Libros preferidos por los estudiantes.</p></div></div>
                            <div class="estadisticas-canvas-wrap estadisticas-canvas-small"><canvas id="graficoEstudiantes"></canvas></div>
                        </article>
                    </div>
                    <div class="col-12 col-xl-6">
                        <article class="card estadisticas-chart-card h-100">
                            <div class="estadisticas-card-heading"><div><span class="estadisticas-card-kicker">Docentes</span><h2>Top 5 docente</h2><p>Libros preferidos por los profesores.</p></div></div>
                            <div class="estadisticas-canvas-wrap estadisticas-canvas-small"><canvas id="graficoDocentes"></canvas></div>
                        </article>
                    </div>
                </section>

                <section class="card admin-list-card estadisticas-table-card">
                    <div class="admin-card-heading">
                        <div><h2 class="admin-card-title mb-1"><i class="fa-solid fa-list-check"></i> Detalle del ranking</h2><p class="mb-0">Comparación directa entre estudiantes y docentes.</p></div>
                        <span class="estadisticas-result-count"><?= count($ranking) ?> resultados</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle admin-table">
                            <thead><tr><th>Posición</th><th>Libro</th><th>Categoría</th><th class="text-center">Estudiantes</th><th class="text-center">Docentes</th><th class="text-center">Total</th><th>Participación</th></tr></thead>
                            <tbody>
                                <?php foreach ($ranking as $indice => $libro): ?>
                                    <tr>
                                        <td><span class="estadisticas-rank-number <?= $indice < 3 ? 'is-top' : '' ?>"><?= $indice + 1 ?></span></td>
                                        <td><div class="estadisticas-book-cell"><span class="estadisticas-book-icon"><i class="fa-solid fa-book"></i></span><div><strong><?= $escapar($libro['titulo'] ?? '') ?></strong><small><?= $escapar($libro['autor'] ?? '') ?></small></div></div></td>
                                        <td><span class="estadisticas-category-pill"><?= $escapar($libro['categoria'] ?? '') ?></span></td>
                                        <td class="text-center"><strong><?= (int) ($libro['usos_estudiantes'] ?? 0) ?></strong></td>
                                        <td class="text-center"><strong><?= (int) ($libro['usos_docentes'] ?? 0) ?></strong></td>
                                        <td class="text-center"><span class="estadisticas-total-pill"><?= (int) ($libro['total_usos'] ?? 0) ?></span></td>
                                        <td><div class="estadisticas-progress-row"><div class="progress estadisticas-progress"><div class="progress-bar" style="width: <?= min(100, max(0, (float) ($libro['porcentaje'] ?? 0))) ?>%"></div></div><strong><?= number_format((float) ($libro['porcentaje'] ?? 0), 1) ?>%</strong></div></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="estadisticas-academic-heading">
                    <div>
                        <span class="estadisticas-card-kicker">Inteligencia académica</span>
                        <h2>¿Qué facultades y carreras demandan cada libro?</h2>
                        <p>Relaciona el catálogo con la estructura académica, la métrica y el intervalo seleccionados.</p>
                    </div>
                    <a href="<?= Config::url('estructura-academica') ?>" class="btn btn-secondary">
                        <i class="fa-solid fa-building-columns"></i>
                        Gestionar estructura
                    </a>
                </section>

                <section class="estadisticas-academic-summary">
                    <article><small>Facultad con mayor demanda</small><strong><?= $escapar($resumenAcademico['facultad_mayor_demanda'] ?? 'Sin datos') ?></strong></article>
                    <article><small>Carrera o departamento líder</small><strong><?= $escapar($resumenAcademico['unidad_mayor_demanda'] ?? 'Sin datos') ?></strong></article>
                    <article><small>Intervalo aplicado</small><strong><?= $escapar($etiquetasIntervalo[$resumenAcademico['intervalo'] ?? 'auto'] ?? ($resumenAcademico['intervalo'] ?? '')) ?></strong></article>
                </section>

                <?php if ($hayDatosAcademicos): ?>
                    <section class="estadisticas-academic-grid">
                        <article class="card estadisticas-chart-card">
                            <div class="estadisticas-card-heading"><div><span class="estadisticas-card-kicker">Facultades</span><h2>Demanda total por facultad</h2><p>Movimientos contabilizados según la métrica seleccionada.</p></div></div>
                            <div class="estadisticas-canvas-wrap estadisticas-canvas-academic"><canvas id="graficoFacultades"></canvas></div>
                        </article>

                        <article class="card estadisticas-chart-card estadisticas-academic-wide">
                            <div class="estadisticas-card-heading"><div><span class="estadisticas-card-kicker">Afinidad temática</span><h2>Libros solicitados por carrera o departamento</h2><p>Cada color representa un libro y cada barra una unidad académica.</p></div></div>
                            <div class="estadisticas-canvas-wrap estadisticas-canvas-matrix"><canvas id="graficoUnidadesLibros"></canvas></div>
                        </article>

                        <article class="card estadisticas-chart-card estadisticas-academic-full">
                            <div class="estadisticas-card-heading"><div><span class="estadisticas-card-kicker">Intervalos</span><h2>Interés académico a través del tiempo</h2><p>Permite detectar días, semanas, meses o años de mayor demanda por carrera o departamento.</p></div></div>
                            <div class="estadisticas-canvas-wrap estadisticas-canvas-academic-trend"><canvas id="graficoTendenciaAcademica"></canvas></div>
                        </article>
                    </section>

                    <section class="card admin-list-card estadisticas-table-card">
                        <div class="admin-card-heading">
                            <div><h2 class="admin-card-title mb-1"><i class="fa-solid fa-building-columns"></i> Detalle académico de la demanda</h2><p class="mb-0">Facultad, carrera o departamento, libro y período de mayor interés.</p></div>
                            <span class="estadisticas-result-count"><?= count($detalleAcademico) ?> relaciones</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle admin-table">
                                <thead><tr><th>Facultad</th><th>Carrera / Departamento</th><th>Libro</th><th class="text-center">Est.</th><th class="text-center">Doc.</th><th class="text-center">Total</th><th>Pico de demanda</th></tr></thead>
                                <tbody>
                                    <?php foreach ($detalleAcademico as $fila): ?>
                                        <tr>
                                            <td><span class="estructura-parent-pill"><?= $escapar($fila['facultad'] ?? '') ?></span></td>
                                            <td><div class="estadisticas-unit-cell"><strong><?= $escapar($fila['unidad'] ?? '') ?></strong><small><?= $escapar($fila['tipo_unidad'] ?? '') ?></small></div></td>
                                            <td><div class="estadisticas-book-cell"><span class="estadisticas-book-icon"><i class="fa-solid fa-book"></i></span><div><strong><?= $escapar($fila['titulo'] ?? '') ?></strong><small><?= $escapar($fila['categoria'] ?? '') ?></small></div></div></td>
                                            <td class="text-center"><?= (int) ($fila['usos_estudiantes'] ?? 0) ?></td>
                                            <td class="text-center"><?= (int) ($fila['usos_docentes'] ?? 0) ?></td>
                                            <td class="text-center"><span class="estadisticas-total-pill"><?= (int) ($fila['total_usos'] ?? 0) ?></span></td>
                                            <td><span class="estadisticas-peak-pill"><i class="fa-solid fa-clock"></i><?= $escapar($fila['periodo_pico'] ?? '') ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php else: ?>
                    <section class="admin-empty-state estadisticas-empty-state">
                        <span class="admin-empty-icon"><i class="fa-solid fa-building-columns"></i></span>
                        <h3>No hay relaciones académicas para mostrar</h3>
                        <p>Asigna carreras a los estudiantes y departamentos a los docentes para habilitar este análisis.</p>
                    </section>
                <?php endif; ?>
            <?php else: ?>
                <section class="admin-empty-state estadisticas-empty-state">
                    <span class="admin-empty-icon"><i class="fa-solid fa-chart-column"></i></span>
                    <h3>No hay <?= $escapar($unidad) ?> registrados en este período</h3>
                    <p>Selecciona otro rango, cambia la métrica o registra movimientos para estudiantes y docentes.</p>
                    <a href="<?= Config::url('reservas') ?>" class="btn btn-primary mt-3"><i class="fa-solid fa-calendar-check"></i>Ver reservas y préstamos</a>
                </section>
            <?php endif; ?>
        </main>
    </div>
</div>

<script type="application/json" id="estadisticasData"><?= $graficoJson ?: '{}' ?></script>
<script src="<?= Config::assetsUrl() ?>/JavaScript/estadisticas.js?v=3"></script>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
