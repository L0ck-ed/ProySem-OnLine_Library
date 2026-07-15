<?php

use App\Config\Config;
use App\Helpers\Session;
use App\Middleware\Auth;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$reservas = $reservas ?? [];
$filtros = $filtros ?? [];
$estados = $estados ?? [];
$tiposUsuario = $tiposUsuario ?? [];
$pagina = $pagina ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$totalRegistros = $totalRegistros ?? 0;

$success = Session::getFlash('success');
$error = Session::getFlash('error');

$puedeAprobar = Auth::tienePermiso('reservas.aprobar');
$puedeDevolver = Auth::tienePermiso('reservas.devolver');
$puedeCancelar = Auth::tienePermiso('reservas.cancelar');

$escapar = static fn (mixed $valor): string =>
    htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$queryPaginacion = $filtros;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Reservas y préstamos</h2>
                    <p class="text-muted mb-0">
                        Total de registros: <?= (int) $totalRegistros ?>
                    </p>
                </div>

                <a
                    href="<?= Config::url('reservas/reporte') ?>"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-file-excel"></i>
                    Reportes
                </a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= $escapar($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= $escapar($error) ?>
                </div>
            <?php endif; ?>

            <div class="card p-4 mb-4">
                <form method="GET" action="<?= Config::url('reservas') ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="buscar" class="form-label">
                                Buscar
                            </label>
                            <input
                                type="search"
                                id="buscar"
                                name="buscar"
                                class="form-control"
                                placeholder="Libro, autor, persona o usuario"
                                value="<?= $escapar($filtros['buscar'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-md-2">
                            <label for="estado" class="form-label">
                                Estado
                            </label>
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
                            <label for="tipo_usuario" class="form-label">
                                Tipo de usuario
                            </label>
                            <select
                                id="tipo_usuario"
                                name="tipo_usuario"
                                class="form-select"
                            >
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
                            <label for="fecha_inicio" class="form-label">
                                Desde
                            </label>
                            <input
                                type="date"
                                id="fecha_inicio"
                                name="fecha_inicio"
                                class="form-control"
                                value="<?= $escapar($filtros['fecha_inicio'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-md-2">
                            <label for="fecha_fin" class="form-label">
                                Hasta
                            </label>
                            <input
                                type="date"
                                id="fecha_fin"
                                name="fecha_fin"
                                class="form-control"
                                value="<?= $escapar($filtros['fecha_fin'] ?? '') ?>"
                            >
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a
                            href="<?= Config::url('reservas') ?>"
                            class="btn btn-secondary"
                        >
                            Limpiar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <div class="card p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Tipo</th>
                                <th>Libro</th>
                                <th>Reserva</th>
                                <th>Vencimiento</th>
                                <th>Días</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <?php
                                $nombrePersona = trim(
                                    ($reserva['primer_nombre_persona'] ?? '') . ' ' .
                                    ($reserva['primer_apellido_persona'] ?? '')
                                );
                                $estadoReserva = (string) ($reserva['estado'] ?? '');
                                ?>
                                <tr>
                                    <td><?= (int) $reserva['id_reserva'] ?></td>
                                    <td>
                                        <strong><?= $escapar($nombrePersona) ?></strong>
                                        <small class="d-block text-muted">
                                            <?= $escapar($reserva['usuario'] ?? '') ?>
                                        </small>
                                    </td>
                                    <td><?= $escapar($reserva['tipo_usuario'] ?? '') ?></td>
                                    <td>
                                        <strong><?= $escapar($reserva['titulo'] ?? '') ?></strong>
                                        <small class="d-block text-muted">
                                            <?= $escapar($reserva['autor'] ?? '') ?>
                                        </small>
                                    </td>
                                    <td><?= $escapar($reserva['fecha_reserva'] ?? '') ?></td>
                                    <td><?= $escapar($reserva['fecha_vencimiento'] ?? '') ?></td>
                                    <td><?= (int) ($reserva['dias_reservado'] ?? 0) ?></td>
                                    <td>
                                        <span class="badge <?= match ($estadoReserva) {
                                            'Devuelto' => 'bg-success',
                                            'Cancelado' => 'bg-secondary',
                                            'Vencido' => 'bg-danger',
                                            'Prestado' => 'bg-primary',
                                            'Reservado' => 'bg-warning text-dark',
                                            default => 'bg-info text-dark',
                                        } ?>">
                                            <?= $escapar($estadoReserva) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?php if (
                                                $puedeAprobar &&
                                                $estadoReserva === 'Pendiente'
                                            ): ?>
                                                <form method="POST" action="<?= Config::url('reservas/aprobar') ?>">
                                                    <input type="hidden" name="id_reserva" value="<?= (int) $reserva['id_reserva'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        Aprobar
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (
                                                $puedeAprobar &&
                                                in_array($estadoReserva, ['Pendiente', 'Reservado'], true)
                                            ): ?>
                                                <form method="POST" action="<?= Config::url('reservas/prestar') ?>">
                                                    <input type="hidden" name="id_reserva" value="<?= (int) $reserva['id_reserva'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        Entregar
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (
                                                $puedeDevolver &&
                                                in_array($estadoReserva, ['Reservado', 'Prestado', 'Vencido'], true)
                                            ): ?>
                                                <form method="POST" action="<?= Config::url('reservas/devolver') ?>">
                                                    <input type="hidden" name="id_reserva" value="<?= (int) $reserva['id_reserva'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        Devolver
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (
                                                $puedeCancelar &&
                                                in_array($estadoReserva, ['Pendiente', 'Reservado'], true)
                                            ): ?>
                                                <form
                                                    method="POST"
                                                    action="<?= Config::url('reservas/cancelar') ?>"
                                                    onsubmit="return confirm('¿Cancelar esta reserva?');"
                                                >
                                                    <input type="hidden" name="id_reserva" value="<?= (int) $reserva['id_reserva'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        Cancelar
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($reservas)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        No hay reservas que coincidan con los filtros.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación de reservas">
                        <ul class="pagination justify-content-center mb-0">
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <?php
                                $queryPaginacion['pagina'] = $i;
                                $urlPagina = Config::url('reservas') . '?' .
                                    http_build_query($queryPaginacion);
                                ?>
                                <li class="page-item <?= $i === (int) $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $escapar($urlPagina) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
