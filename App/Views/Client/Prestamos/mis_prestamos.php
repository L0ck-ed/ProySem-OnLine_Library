<?php

use App\Config\Config;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$prestamosActivos = is_array($prestamosActivos ?? null) ? $prestamosActivos : [];
$historial = is_array($historial ?? null) ? $historial : [];
$exitoDevolucion = $exitoDevolucion ?? null;
$errorDevolucion = $errorDevolucion ?? null;

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$formatearFecha = static function (mixed $fecha): string {
    if (!$fecha) {
        return 'No registrada';
    }

    try {
        return (new DateTimeImmutable((string) $fecha))->format('d/m/Y');
    } catch (Throwable) {
        return (string) $fecha;
    }
};

$claseEstado = static function (string $estado): string {
    return match ($estado) {
        'Pendiente' => 'status-pending',
        'Reservado' => 'status-reserved',
        'Prestado' => 'status-loaned',
        'Vencido' => 'status-overdue',
        'Devuelto' => 'status-returned',
        'Cancelado' => 'status-cancelled',
        default => 'status-neutral',
    };
};

$iconoEstado = static function (string $estado): string {
    return match ($estado) {
        'Pendiente' => 'fa-solid fa-clock',
        'Reservado' => 'fa-solid fa-bookmark',
        'Prestado' => 'fa-solid fa-book-open-reader',
        'Vencido' => 'fa-solid fa-triangle-exclamation',
        'Devuelto' => 'fa-solid fa-circle-check',
        'Cancelado' => 'fa-solid fa-ban',
        default => 'fa-solid fa-circle-info',
    };
};
?>

<main class="client-page">
    <div class="client-page-inner">
        <header class="client-page-header">
            <div class="client-page-title-wrap">
                <span class="client-page-title-icon"><i class="fa-solid fa-calendar-check"></i></span>
                <div>
                    <span class="client-section-kicker">Tu actividad</span>
                    <h1>Mis préstamos</h1>
                    <p>Consulta tus reservas activas, fechas de vencimiento y movimientos anteriores.</p>
                </div>
            </div>

            <a href="<?= Config::url('portal/catalogo') ?>" class="btn btn-secondary client-header-action">
                <i class="fa-solid fa-magnifying-glass"></i>
                Buscar libros
            </a>
        </header>

        <?php if ($exitoDevolucion): ?>
            <div class="alert alert-success client-alert">
                <i class="fa-solid fa-circle-check"></i>
                <?= $escapar($exitoDevolucion) ?>
            </div>
        <?php endif; ?>

        <?php if ($errorDevolucion): ?>
            <div class="alert alert-danger client-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= $escapar($errorDevolucion) ?>
            </div>
        <?php endif; ?>

        <section class="client-loan-summary-grid">
            <article>
                <span><i class="fa-solid fa-bookmark"></i></span>
                <div><strong><?= count($prestamosActivos) ?></strong><small>Reservas y préstamos activos</small></div>
            </article>
            <article>
                <span><i class="fa-solid fa-clock-rotate-left"></i></span>
                <div><strong><?= count($historial) ?></strong><small>Movimientos en el historial</small></div>
            </article>
            <article>
                <span><i class="fa-solid fa-circle-info"></i></span>
                <div><strong>7 días</strong><small>Periodo regular de préstamo</small></div>
            </article>
        </section>

        <section class="client-tabs-card">
            <ul class="nav client-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#prestamos-activos"
                        type="button"
                        role="tab"
                    >
                        <i class="fa-solid fa-book-open-reader"></i>
                        Activos
                        <span><?= count($prestamosActivos) ?></span>
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
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Historial
                        <span><?= count($historial) ?></span>
                    </button>
                </li>
            </ul>

            <div class="tab-content client-tab-content">
                <div class="tab-pane fade show active" id="prestamos-activos" role="tabpanel">
                    <?php if (!empty($prestamosActivos)): ?>
                        <div class="client-loans-list">
                            <?php foreach ($prestamosActivos as $prestamo): ?>
                                <?php
                                $estado = (string) ($prestamo['estado'] ?? '');
                                $esReserva = in_array($estado, ['Pendiente', 'Reservado'], true);
                                ?>
                                <article class="client-loan-card">
                                    <div class="client-loan-book-icon"><i class="fa-solid fa-book-open"></i></div>

                                    <div class="client-loan-main">
                                        <div class="client-loan-heading">
                                            <div>
                                                <h2><?= $escapar($prestamo['titulo'] ?? 'Libro sin título') ?></h2>
                                                <p>
                                                    <i class="fa-solid fa-feather-pointed"></i>
                                                    <?= $escapar($prestamo['autor'] ?? 'Autor desconocido') ?>
                                                </p>
                                            </div>
                                            <span class="client-status-pill <?= $claseEstado($estado) ?>">
                                                <i class="<?= $iconoEstado($estado) ?>"></i>
                                                <?= $escapar($estado ?: 'Sin estado') ?>
                                            </span>
                                        </div>

                                        <div class="client-loan-dates">
                                            <div>
                                                <small>Fecha de reserva</small>
                                                <strong><i class="fa-regular fa-calendar"></i> <?= $formatearFecha($prestamo['fecha_reserva'] ?? null) ?></strong>
                                            </div>
                                            <div>
                                                <small>Vencimiento</small>
                                                <strong><i class="fa-regular fa-calendar-xmark"></i> <?= $formatearFecha($prestamo['fecha_vencimiento'] ?? null) ?></strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="client-loan-action">
                                        <form
                                            method="POST"
                                            action="<?= Config::url('portal/prestamos/devolver') ?>"
                                            onsubmit="return confirm('¿Seguro que deseas <?= $esReserva ? 'cancelar esta reserva' : 'registrar la devolución' ?>?');"
                                        >
                                            <input type="hidden" name="id_reserva" value="<?= (int) ($prestamo['id_reserva'] ?? 0) ?>">
                                            <button type="submit" class="btn <?= $esReserva ? 'btn-danger' : 'btn-primary' ?>">
                                                <i class="fa-solid <?= $esReserva ? 'fa-ban' : 'fa-rotate-left' ?>"></i>
                                                <?= $esReserva ? 'Cancelar reserva' : 'Registrar devolución' ?>
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="client-empty-state compact">
                            <span><i class="fa-solid fa-book-open-reader"></i></span>
                            <h2>No tienes préstamos activos</h2>
                            <p>Explora el catálogo y reserva el material que necesites.</p>
                            <a href="<?= Config::url('portal/catalogo') ?>" class="btn btn-primary">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                Ir al catálogo
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="historial-prestamos" role="tabpanel">
                    <?php if (!empty($historial)): ?>
                        <div class="table-responsive client-history-table-wrap">
                            <table class="table align-middle client-history-table">
                                <thead>
                                    <tr>
                                        <th>Libro</th>
                                        <th>Reserva</th>
                                        <th>Cierre</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial as $prestamo): ?>
                                        <?php $estado = (string) ($prestamo['estado'] ?? ''); ?>
                                        <tr>
                                            <td data-label="Libro">
                                                <div class="client-table-book">
                                                    <span><i class="fa-solid fa-book"></i></span>
                                                    <div>
                                                        <strong><?= $escapar($prestamo['titulo'] ?? 'Libro sin título') ?></strong>
                                                        <?php if (!empty($prestamo['autor'])): ?>
                                                            <small><?= $escapar($prestamo['autor']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Reserva"><?= $formatearFecha($prestamo['fecha_reserva'] ?? null) ?></td>
                                            <td data-label="Cierre"><?= $formatearFecha($prestamo['fecha_devolucion'] ?? null) ?></td>
                                            <td data-label="Estado">
                                                <span class="client-status-pill <?= $claseEstado($estado) ?>">
                                                    <i class="<?= $iconoEstado($estado) ?>"></i>
                                                    <?= $escapar($estado ?: 'Sin estado') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="client-empty-state compact">
                            <span><i class="fa-solid fa-clock-rotate-left"></i></span>
                            <h2>Tu historial está vacío</h2>
                            <p>Los préstamos cerrados y reservas canceladas aparecerán aquí.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
