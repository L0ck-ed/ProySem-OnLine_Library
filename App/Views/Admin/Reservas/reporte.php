<?php

use App\Config\Config;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$reservas = $reservas ?? [];
$filtros = $filtros ?? [];
$estados = $estados ?? [];
$tiposUsuario = $tiposUsuario ?? [];
$resumen = $resumen ?? [];

$escapar = static fn (mixed $valor): string =>
    htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$urlExcel = Config::url('reservas/reporte/excel') . '?' .
    http_build_query($filtros);
?>

<div class="container-fluid admin-page admin-page-reporte-reservas">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main">
            <div class="admin-page-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="mb-1">Reporte de reservas</h2>
                    <p class="text-muted mb-0">
                        Filtra por fechas, días y tipo de usuario.
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?= Config::url('reservas') ?>" class="btn btn-secondary">
                        Volver
                    </a>
                    <a href="<?= $escapar($urlExcel) ?>" class="btn btn-success">
                        <i class="fa-solid fa-file-excel"></i>
                        Exportar a Excel
                    </a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <?php foreach ([
                    'Total' => $resumen['total'] ?? 0,
                    'Estudiantes' => $resumen['estudiantes'] ?? 0,
                    'Docentes' => $resumen['docentes'] ?? 0,
                    'Administrativos' => $resumen['administrativos'] ?? 0,
                ] as $titulo => $valor): ?>
                    <div class="col-md-3">
                        <div class="card report-stat-card h-100">
                            <small class="text-muted"><?= $escapar($titulo) ?></small>
                            <strong class="fs-3"><?= (int) $valor ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card admin-form-card report-filter-card mb-4">
                <form method="GET" action="<?= Config::url('reservas/reporte') ?>">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="buscar" class="form-label">Buscar</label>
                            <input
                                type="search"
                                id="buscar"
                                name="buscar"
                                class="form-control"
                                value="<?= $escapar($filtros['buscar'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-md-2">
                            <label for="estado" class="form-label">Estado</label>
                            <select id="estado" name="estado" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($estados as $estado): ?>
                                    <option
                                        value="<?= $escapar($estado) ?>"
                                        <?= ($filtros['estado'] ?? '') === $estado
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= $escapar($estado) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="tipo_usuario" class="form-label">Tipo</label>
                            <select id="tipo_usuario" name="tipo_usuario" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($tiposUsuario as $tipo): ?>
                                    <option
                                        value="<?= $escapar($tipo) ?>"
                                        <?= ($filtros['tipo_usuario'] ?? '') === $tipo
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= $escapar($tipo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="fecha_inicio" class="form-label">Desde</label>
                            <input
                                type="date"
                                id="fecha_inicio"
                                name="fecha_inicio"
                                class="form-control"
                                value="<?= $escapar($filtros['fecha_inicio'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-md-2">
                            <label for="fecha_fin" class="form-label">Hasta</label>
                            <input
                                type="date"
                                id="fecha_fin"
                                name="fecha_fin"
                                class="form-control"
                                value="<?= $escapar($filtros['fecha_fin'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-md-1">
                            <label for="dias_minimos" class="form-label">Días</label>
                            <input
                                type="number"
                                id="dias_minimos"
                                name="dias_minimos"
                                class="form-control"
                                min="0"
                                value="<?= $escapar($filtros['dias_minimos'] ?? '') ?>"
                            >
                        </div>
                    </div>

                    <div class="admin-form-actions d-flex justify-content-end gap-2 mt-3">
                        <a href="<?= Config::url('reservas/reporte') ?>" class="btn btn-secondary">
                            Limpiar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Generar reporte
                        </button>
                    </div>
                </form>
            </div>

            <div class="card admin-list-card report-table-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle admin-table">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Persona</th>
                                <th>Tipo</th>
                                <th>Libro</th>
                                <th>Categoría</th>
                                <th>Días</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <?php
                                $nombre = trim(
                                    ($reserva['primer_nombre_persona'] ?? '') . ' ' .
                                    ($reserva['primer_apellido_persona'] ?? '')
                                );
                                ?>
                                <tr>
                                    <td><?= (int) $reserva['id_reserva'] ?></td>
                                    <td><?= $escapar($reserva['fecha_reserva'] ?? '') ?></td>
                                    <td><?= $escapar($nombre) ?></td>
                                    <td><?= $escapar($reserva['tipo_usuario'] ?? '') ?></td>
                                    <td>
                                        <?= $escapar($reserva['titulo'] ?? '') ?>
                                        <small class="d-block text-muted">
                                            <?= $escapar($reserva['autor'] ?? '') ?>
                                        </small>
                                    </td>
                                    <td><?= $escapar($reserva['categoria'] ?? '') ?></td>
                                    <td><?= (int) ($reserva['dias_reservado'] ?? 0) ?></td>
                                    <td><?= $escapar($reserva['estado'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($reservas)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        No hay datos para este reporte.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
