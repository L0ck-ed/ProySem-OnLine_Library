<?php

/* Vista de solo interfaz. Los botones de acción no ejecutan lógica todavía. */

$nombreEstudiante = $nombreEstudiante ?? 'Anthony Castillo';
$cipSesion = $cipSesion ?? '8-1023-2265';

$prestamosActivos = $prestamosActivos ?? [
    ['titulo' => 'Clean Code',               'fecha_reserva' => '2026-07-01', 'estado' => 'Prestado'],
    ['titulo' => 'Cálculo de una Variable',   'fecha_reserva' => '2026-07-05', 'estado' => 'Prestado'],
];

$historial = $historial ?? [
    ['titulo' => 'Estructuras de Datos en Java', 'fecha_reserva' => '2026-05-10', 'fecha_devolucion' => '2026-05-24', 'estado' => 'Devuelto'],
    ['titulo' => 'Lógica Matemática',             'fecha_reserva' => '2026-04-02', 'fecha_devolucion' => '2026-04-16', 'estado' => 'Devuelto'],
    ['titulo' => 'Química General',               'fecha_reserva' => '2026-03-01', 'fecha_devolucion' => null,        'estado' => 'Cancelado'],
];

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

?>

<div class="container-fluid py-4 px-4">

    <h2><i class="fa-solid fa-calendar-check"></i> Mis préstamos</h2>

    <ul class="nav nav-tabs mb-3" id="prestamosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-activos" type="button">
                Préstamos activos <span class="badge bg-success ms-1"><?= count($prestamosActivos) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-historial" type="button">
                Historial
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-activos">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Libro</th>
                            <th>Fecha de préstamo</th>
                            <th>Estado</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($prestamosActivos)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No tienes préstamos activos.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($prestamosActivos as $p): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($p['titulo']) ?></td>
                                <td><?= htmlspecialchars($p['fecha_reserva']) ?></td>
                                <td><span class="badge bg-success"><?= htmlspecialchars($p['estado']) ?></span></td>
                                <td class="text-end">
                                    <button class="btn btn-secondary btn-sm" type="button" disabled>
                                        <i class="fa-solid fa-arrow-rotate-left"></i> Devolver
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-historial">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Libro</th>
                            <th>Fecha de préstamo</th>
                            <th>Fecha de devolución</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $h): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($h['titulo']) ?></td>
                                <td><?= htmlspecialchars($h['fecha_reserva']) ?></td>
                                <td><?= htmlspecialchars($h['fecha_devolucion'] ?? '—') ?></td>
                                <td>
                                    <?php if ($h['estado'] === 'Devuelto'): ?>
                                        <span class="badge bg-success">Devuelto</span>
                                    <?php else: ?>
                                        <span class="badge badge-existencias-agotado">Cancelado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>