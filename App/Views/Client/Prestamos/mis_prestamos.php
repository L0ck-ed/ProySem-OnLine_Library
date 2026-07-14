<?php
// Datos que vienen del controlador (PortalController->prestamos())
// $prestamosActivos, $historial, $nombreEstudiante, $cipSesion, etc.

use App\Helpers\Session;
use App\Config\Config;

// Obtener mensajes flash (si existen)
$exitoDevolucion = Session::getFlash('exito_devolucion');
$errorDevolucion = Session::getFlash('error_devolucion');

// Incluir partials
require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

?>

<div class="container-fluid py-4 px-4">

    <h2><i class="fa-solid fa-calendar-check"></i> Mis préstamos</h2>

    <!-- Mostrar mensajes flash -->
    <?php if ($exitoDevolucion): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($exitoDevolucion) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if ($errorDevolucion): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-exclamation-triangle"></i> <?= htmlspecialchars($errorDevolucion) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-3" id="prestamosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-activos-btn" data-bs-toggle="tab" data-bs-target="#tab-activos" type="button" role="tab">
                Préstamos activos
                <span class="badge bg-success ms-1"><?= count($prestamosActivos ?? []) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-historial-btn" data-bs-toggle="tab" data-bs-target="#tab-historial" type="button" role="tab">
                Historial
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- TAB: Préstamos activos -->
        <div class="tab-pane fade show active" id="tab-activos" role="tabpanel">
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
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-circle-info"></i> No tienes préstamos activos.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($prestamosActivos as $p): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($p['titulo'] ?? 'Título no disponible') ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['fecha_reserva'])) ?></td>
                                    <td><span class="badge bg-success"><?= htmlspecialchars($p['estado'] ?? 'Prestado') ?></span></td>
                                    <td class="text-end">
                                        <form action="<?= Config::url('portal/prestamos/devolver') ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="id_reserva" value="<?= (int) $p['id_reserva'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fa-solid fa-arrow-rotate-left"></i> Devolver
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: Historial -->
        <div class="tab-pane fade" id="tab-historial" role="tabpanel">
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
                        <?php if (empty($historial)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-circle-info"></i> No hay registros en tu historial.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historial as $h): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($h['titulo'] ?? 'Título no disponible') ?></td>
                                    <td><?= date('d/m/Y', strtotime($h['fecha_reserva'])) ?></td>
                                    <td>
                                        <?php if (!empty($h['fecha_devolucion'])): ?>
                                            <?= date('d/m/Y', strtotime($h['fecha_devolucion'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (($h['estado'] ?? '') === 'Devuelto'): ?>
                                            <span class="badge bg-success">Devuelto</span>
                                        <?php elseif (($h['estado'] ?? '') === 'Cancelado'): ?>
                                            <span class="badge bg-danger">Cancelado</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($h['estado'] ?? 'Desconocido') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>