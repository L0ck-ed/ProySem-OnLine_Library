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

$formatearFecha = static function (mixed $valor): array {
    if ($valor === null || $valor === '') {
        return ['fecha' => '—', 'hora' => ''];
    }

    try {
        $fecha = $valor instanceof DateTimeInterface
            ? $valor
            : new DateTime((string) $valor);

        return [
            'fecha' => $fecha->format('d/m/Y'),
            'hora' => $fecha->format('H:i'),
        ];
    } catch (Throwable) {
        return ['fecha' => (string) $valor, 'hora' => ''];
    }
};

$estadoClase = static fn (string $estado): string => match ($estado) {
    'Pendiente' => 'estado-pendiente',
    'Reservado' => 'estado-reservado',
    'Prestado' => 'estado-prestado',
    'Devuelto' => 'estado-devuelto',
    'Vencido' => 'estado-vencido',
    'Cancelado' => 'estado-cancelado',
    default => 'estado-otro',
};

$filtrosActivos = array_filter([
    $filtros['buscar'] ?? '',
    $filtros['estado'] ?? '',
    $filtros['tipo_usuario'] ?? '',
    $filtros['fecha_inicio'] ?? '',
    $filtros['fecha_fin'] ?? '',
], static fn (mixed $valor): bool => $valor !== null && $valor !== '');

$queryPaginacion = $filtros;
?>

<div class="container-fluid reservas-page">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 reservas-main">
            <section class="reservas-encabezado">
                <div class="reservas-titulo-wrap">
                    <div class="reservas-titulo-icono" aria-hidden="true">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h1 class="reservas-titulo mb-0">Reservas y préstamos</h1>
                            <span class="reservas-total">
                                <?= (int) $totalRegistros ?>
                                <?= (int) $totalRegistros === 1 ? 'registro' : 'registros' ?>
                            </span>
                        </div>
                        <p class="reservas-subtitulo mb-0">
                            Consulta reservas, entrega libros y registra devoluciones.
                        </p>
                    </div>
                </div>

                <a
                    href="<?= Config::url('reservas/reporte') ?>"
                    class="btn btn-primary reservas-reporte-btn"
                >
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Generar reporte</span>
                </a>
            </section>

            <?php if ($success): ?>
                <div class="alert alert-success reservas-alerta" role="alert">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?= $escapar($success) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger reservas-alerta" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= $escapar($error) ?></span>
                </div>
            <?php endif; ?>

            <section class="card reservas-filtros-card">
                <div class="reservas-card-heading">
                    <div>
                        <h2 class="reservas-card-title mb-1">
                            <i class="fa-solid fa-sliders"></i>
                            Filtros de búsqueda
                        </h2>
                        <p class="mb-0">Encuentra rápidamente una reserva específica.</p>
                    </div>

                    <?php if (!empty($filtrosActivos)): ?>
                        <span class="filtros-activos-badge">
                            <?= count($filtrosActivos) ?>
                            <?= count($filtrosActivos) === 1 ? 'filtro activo' : 'filtros activos' ?>
                        </span>
                    <?php endif; ?>
                </div>

                <form method="GET" action="<?= Config::url('reservas') ?>" class="reservas-filtros-form">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label for="buscar" class="form-label">Buscar</label>
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input
                                    type="search"
                                    id="buscar"
                                    name="buscar"
                                    class="form-control input-con-icono"
                                    placeholder="Libro, autor, nombre o usuario"
                                    value="<?= $escapar($filtros['buscar'] ?? '') ?>"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label for="estado" class="form-label">Estado</label>
                            <select id="estado" name="estado" class="form-select">
                                <option value="">Todos los estados</option>
                                <?php foreach ($estados as $estado): ?>
                                    <option
                                        value="<?= $escapar($estado) ?>"
                                        <?= ($filtros['estado'] ?? '') === $estado ? 'selected' : '' ?>
                                    >
                                        <?= $escapar($estado) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label for="tipo_usuario" class="form-label">Tipo de usuario</label>
                            <select id="tipo_usuario" name="tipo_usuario" class="form-select">
                                <option value="">Todos los tipos</option>
                                <?php foreach ($tiposUsuario as $tipo): ?>
                                    <option
                                        value="<?= $escapar($tipo) ?>"
                                        <?= ($filtros['tipo_usuario'] ?? '') === $tipo ? 'selected' : '' ?>
                                    >
                                        <?= $escapar($tipo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label for="fecha_inicio" class="form-label">Desde</label>
                            <input
                                type="date"
                                id="fecha_inicio"
                                name="fecha_inicio"
                                class="form-control"
                                value="<?= $escapar($filtros['fecha_inicio'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label for="fecha_fin" class="form-label">Hasta</label>
                            <input
                                type="date"
                                id="fecha_fin"
                                name="fecha_fin"
                                class="form-control"
                                value="<?= $escapar($filtros['fecha_fin'] ?? '') ?>"
                            >
                        </div>
                    </div>

                    <div class="reservas-filtros-acciones">
                        <?php if (!empty($filtrosActivos)): ?>
                            <a href="<?= Config::url('reservas') ?>" class="btn btn-secondary">
                                <i class="fa-solid fa-rotate-left"></i>
                                Limpiar filtros
                            </a>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-filter"></i>
                            Aplicar filtros
                        </button>
                    </div>
                </form>
            </section>

            <section class="card reservas-tabla-card">
                <div class="reservas-card-heading reservas-tabla-heading">
                    <div>
                        <h2 class="reservas-card-title mb-1">
                            <i class="fa-solid fa-list-check"></i>
                            Historial de reservas
                        </h2>
                        <p class="mb-0">
                            Página <?= (int) $pagina ?> de <?= max(1, (int) $totalPaginas) ?>
                        </p>
                    </div>

                    <div class="reservas-leyenda" aria-label="Leyenda de estados">
                        <span><i class="leyenda-punto punto-reservado"></i> Reservado</span>
                        <span><i class="leyenda-punto punto-prestado"></i> Prestado</span>
                        <span><i class="leyenda-punto punto-vencido"></i> Vencido</span>
                    </div>
                </div>

                <div class="table-responsive reservas-tabla-wrap">
                    <table class="table table-hover align-middle reservas-tabla">
                        <thead>
                            <tr>
                                <th scope="col" class="col-id">ID</th>
                                <th scope="col">Usuario</th>
                                <th scope="col">Tipo</th>
                                <th scope="col" class="col-libro">Libro</th>
                                <th scope="col">Reserva</th>
                                <th scope="col">Vencimiento</th>
                                <th scope="col" class="text-center">Días</th>
                                <th scope="col">Estado</th>
                                <th scope="col" class="col-acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <?php
                                $nombrePersona = trim(
                                    ($reserva['primer_nombre_persona'] ?? '') . ' ' .
                                    ($reserva['primer_apellido_persona'] ?? '')
                                );
                                $usuario = (string) ($reserva['usuario'] ?? '');
                                $estadoReserva = (string) ($reserva['estado'] ?? '');
                                $fechaReserva = $formatearFecha($reserva['fecha_reserva'] ?? null);
                                $fechaVencimiento = $formatearFecha($reserva['fecha_vencimiento'] ?? null);
                                $diasReservado = (int) ($reserva['dias_reservado'] ?? 0);
                                $textoInicial = $nombrePersona !== '' ? $nombrePersona : $usuario;
                                $inicial = strtoupper(substr($textoInicial, 0, 1));
                                $autor = trim((string) ($reserva['autor'] ?? ''));
                                ?>
                                <tr>
                                    <td class="col-id">
                                        <span class="reserva-id">#<?= (int) $reserva['id_reserva'] ?></span>
                                    </td>
                                    <td>
                                        <div class="usuario-celda">
                                            <span class="usuario-avatar" aria-hidden="true">
                                                <?= $escapar($inicial !== '' ? $inicial : 'U') ?>
                                            </span>
                                            <div class="usuario-info">
                                                <strong><?= $escapar($nombrePersona !== '' ? $nombrePersona : 'Usuario sin nombre') ?></strong>
                                                <small>@<?= $escapar($usuario !== '' ? $usuario : 'sin-usuario') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="tipo-usuario-badge">
                                            <i class="fa-solid <?= ($reserva['tipo_usuario'] ?? '') === 'Profesor' ? 'fa-chalkboard-user' : 'fa-user-graduate' ?>"></i>
                                            <?= $escapar($reserva['tipo_usuario'] ?? 'Sin tipo') ?>
                                        </span>
                                    </td>
                                    <td class="col-libro">
                                        <div class="libro-celda">
                                            <span class="libro-icono" aria-hidden="true">
                                                <i class="fa-solid fa-book-open"></i>
                                            </span>
                                            <div>
                                                <strong><?= $escapar($reserva['titulo'] ?? 'Libro sin título') ?></strong>
                                                <small><?= $escapar($autor !== '' ? $autor : 'Autor no registrado') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fecha-celda">
                                            <strong><?= $escapar($fechaReserva['fecha']) ?></strong>
                                            <?php if ($fechaReserva['hora'] !== ''): ?>
                                                <small><i class="fa-regular fa-clock"></i> <?= $escapar($fechaReserva['hora']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fecha-celda">
                                            <strong><?= $escapar($fechaVencimiento['fecha']) ?></strong>
                                            <?php if ($fechaVencimiento['hora'] !== ''): ?>
                                                <small><i class="fa-regular fa-clock"></i> <?= $escapar($fechaVencimiento['hora']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="dias-badge <?= $diasReservado > 7 ? 'dias-alerta' : '' ?>">
                                            <?= $diasReservado === 0 ? 'Hoy' : $diasReservado ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="estado-badge <?= $estadoClase($estadoReserva) ?>">
                                            <i class="fa-solid fa-circle"></i>
                                            <?= $escapar($estadoReserva !== '' ? $estadoReserva : 'Sin estado') ?>
                                        </span>
                                    </td>
                                    <td class="col-acciones">
                                        <div class="reservas-acciones">
                                            <?php if ($puedeAprobar && $estadoReserva === 'Pendiente'): ?>
                                                <form method="POST" action="<?= Config::url('reservas/aprobar') ?>">
                                                    <input type="hidden" name="id_reserva" value="<?= (int) $reserva['id_reserva'] ?>">
                                                    <button
                                                        type="submit"
                                                        class="btn-accion btn-aprobar"
                                                        title="Aprobar reserva"
                                                    >
                                                        <i class="fa-solid fa-check"></i>
                                                        <span>Aprobar</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (
                                                $puedeAprobar &&
                                                $estadoReserva === 'Reservado'
                                            ): ?>
                                                <form
                                                    method="POST"
                                                    action="<?= Config::url('reservas/prestar') ?>"
                                                    onsubmit="return confirm('¿Confirmar la entrega de este libro?');"
                                                >
                                                    <input type="hidden" name="id_reserva" value="<?= (int) $reserva['id_reserva'] ?>">
                                                    <button
                                                        type="submit"
                                                        class="btn-accion btn-entregar"
                                                        title="Entregar libro"
                                                    >
                                                        <i class="fa-solid fa-hand-holding"></i>
                                                        <span>Entregar</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (
                                                $puedeDevolver &&
                                                in_array($estadoReserva, ['Prestado', 'Vencido'], true)
                                            ): ?>
                                                <form
                                                    method="POST"
                                                    action="<?= Config::url('reservas/devolver') ?>"
                                                    onsubmit="return confirm('¿Registrar la devolución de este libro?');"
                                                >
                                                    <input type="hidden" name="id_reserva" value="<?= (int) $reserva['id_reserva'] ?>">
                                                    <button
                                                        type="submit"
                                                        class="btn-accion btn-devolver"
                                                        title="Registrar devolución"
                                                    >
                                                        <i class="fa-solid fa-arrow-rotate-left"></i>
                                                        <span>Devolver</span>
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
                                                    <button
                                                        type="submit"
                                                        class="btn-accion btn-cancelar"
                                                        title="Cancelar reserva"
                                                    >
                                                        <i class="fa-solid fa-xmark"></i>
                                                        <span>Cancelar</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (
                                                !(
                                                    ($puedeAprobar && in_array($estadoReserva, ['Pendiente', 'Reservado'], true)) ||
                                                    ($puedeDevolver && in_array($estadoReserva, ['Prestado', 'Vencido'], true)) ||
                                                    ($puedeCancelar && in_array($estadoReserva, ['Pendiente', 'Reservado'], true))
                                                )
                                            ): ?>
                                                <span class="sin-acciones">
                                                    <i class="fa-solid fa-lock"></i>
                                                    Sin acciones
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($reservas)): ?>
                                <tr>
                                    <td colspan="9">
                                        <div class="reservas-vacio">
                                            <span class="reservas-vacio-icono">
                                                <i class="fa-solid fa-book-circle-xmark"></i>
                                            </span>
                                            <h3>No se encontraron reservas</h3>
                                            <p>Prueba cambiando o limpiando los filtros de búsqueda.</p>
                                            <?php if (!empty($filtrosActivos)): ?>
                                                <a href="<?= Config::url('reservas') ?>" class="btn btn-secondary">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                    Limpiar filtros
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <nav class="reservas-paginacion" aria-label="Paginación de reservas">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ((int) $pagina > 1): ?>
                                <?php
                                $queryPaginacion['pagina'] = (int) $pagina - 1;
                                $urlAnterior = Config::url('reservas') . '?' . http_build_query($queryPaginacion);
                                ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $escapar($urlAnterior) ?>" aria-label="Página anterior">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <?php
                                $queryPaginacion['pagina'] = $i;
                                $urlPagina = Config::url('reservas') . '?' . http_build_query($queryPaginacion);
                                ?>
                                <li class="page-item <?= $i === (int) $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $escapar($urlPagina) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ((int) $pagina < (int) $totalPaginas): ?>
                                <?php
                                $queryPaginacion['pagina'] = (int) $pagina + 1;
                                $urlSiguiente = Config::url('reservas') . '?' . http_build_query($queryPaginacion);
                                ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $escapar($urlSiguiente) ?>" aria-label="Página siguiente">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
