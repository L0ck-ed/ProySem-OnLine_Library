<?php

use App\Config\Config;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$prestamosActivos = $prestamosActivos ?? [];
$historial = $historial ?? [];
$exitoDevolucion = $exitoDevolucion ?? null;
$errorDevolucion = $errorDevolucion ?? null;

$escapar = static function (mixed $valor): string {
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8',
    );
};

$formatearFecha = static function (mixed $fecha): string {
    if (!$fecha) {
        return 'No registrada';
    }

    try {
        $objetoFecha = new DateTimeImmutable((string) $fecha);
        return $objetoFecha->format('d/m/Y');
    } catch (Throwable) {
        return (string) $fecha;
    }
};

$claseEstado = static function (string $estado): string {
    return match ($estado) {
        'Reservado', 'Pendiente' => 'bg-success',
        'Prestado' => 'bg-primary',
        'Vencido' => 'bg-danger',
        'Devuelto' => 'bg-secondary',
        'Cancelado' => 'bg-dark',
        default => 'bg-secondary',
    };
};
?>

<div class="container-fluid py-4 px-4">
    <h2 class="mb-4">
        <i class="fa-solid fa-calendar-check"></i>
        Mis préstamos
    </h2>

    <?php if ($exitoDevolucion): ?>
        <div class="alert alert-success">
            <?= $escapar($exitoDevolucion) ?>
        </div>
    <?php endif; ?>

    <?php if ($errorDevolucion): ?>
        <div class="alert alert-danger">
            <?= $escapar($errorDevolucion) ?>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button
                class="nav-link active"
                data-bs-toggle="tab"
                data-bs-target="#prestamos-activos"
                type="button"
                role="tab"
            >
                Préstamos activos
                <span class="badge bg-success ms-1">
                    <?= count($prestamosActivos) ?>
                </span>
            </button>
        </li>

        <li class="nav-item" role="presentation">
            <button
                class="nav-link"
                data-bs-toggle="tab"
                data-bs-target="#historial-prestamos"
                type="button"
                role="tab"
            >
                Historial
                <span class="badge bg-secondary ms-1">
                    <?= count($historial) ?>
                </span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div
            class="tab-pane fade show active"
            id="prestamos-activos"
            role="tabpanel"
        >
            <div class="card p-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Libro</th>
                                <th>Fecha de reserva</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($prestamosActivos as $prestamo): ?>
                                <?php
                                $estado = (string) ($prestamo['estado'] ?? '');
                                $esReserva = in_array($estado, ['Pendiente', 'Reservado'], true);
                                ?>

                                <tr>
                                    <td>
                                        <strong>
                                            <?= $escapar($prestamo['titulo'] ?? 'Libro sin título') ?>
                                        </strong>

                                        <?php if (!empty($prestamo['autor'])): ?>
                                            <small class="d-block text-muted">
                                                <?= $escapar($prestamo['autor']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= $formatearFecha($prestamo['fecha_reserva'] ?? null) ?>
                                    </td>

                                    <td>
                                        <?= $formatearFecha($prestamo['fecha_vencimiento'] ?? null) ?>
                                    </td>

                                    <td>
                                        <span class="badge <?= $claseEstado($estado) ?>">
                                            <?= $escapar($estado) ?>
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <form
                                            method="POST"
                                            action="<?= Config::url('portal/prestamos/devolver') ?>"
                                            class="d-inline"
                                            onsubmit="return confirm('¿Seguro que deseas <?= $esReserva ? 'cancelar esta reserva' : 'registrar la devolución' ?>?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="id_reserva"
                                                value="<?= (int) $prestamo['id_reserva'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm <?= $esReserva ? 'btn-danger' : 'btn-primary' ?>"
                                            >
                                                <i class="fa-solid <?= $esReserva ? 'fa-ban' : 'fa-rotate-left' ?>"></i>

                                                <?= $esReserva
                                                    ? 'Cancelar reserva'
                                                    : 'Devolver' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($prestamosActivos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No tienes reservas o préstamos activos.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div
            class="tab-pane fade"
            id="historial-prestamos"
            role="tabpanel"
        >
            <div class="card p-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Libro</th>
                                <th>Fecha de reserva</th>
                                <th>Fecha de cierre</th>
                                <th>Estado</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($historial as $prestamo): ?>
                                <?php $estado = (string) ($prestamo['estado'] ?? ''); ?>

                                <tr>
                                    <td>
                                        <strong>
                                            <?= $escapar($prestamo['titulo'] ?? 'Libro sin título') ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= $formatearFecha($prestamo['fecha_reserva'] ?? null) ?>
                                    </td>

                                    <td>
                                        <?= $formatearFecha($prestamo['fecha_devolucion'] ?? null) ?>
                                    </td>

                                    <td>
                                        <span class="badge <?= $claseEstado($estado) ?>">
                                            <?= $escapar($estado) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($historial)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Todavía no tienes movimientos en el historial.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
