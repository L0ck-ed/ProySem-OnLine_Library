<?php

use App\Config\Config;
use App\Helpers\Session;
use App\Middleware\Auth;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$solicitudes = $solicitudes ?? [];
$filtros = $filtros ?? [];
$estados = $estados ?? [];
$pagina = $pagina ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$totalRegistros = $totalRegistros ?? 0;

$success = Session::getFlash('success');
$error = Session::getFlash('error');

$puedeGestionar = Auth::tienePermiso('solicitudes.gestionar');

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$claseEstado = static function (string $estado): string {
    return match ($estado) {
        'Pendiente' => 'bg-secondary',
        'En revisión' => 'bg-warning text-dark',
        'Aprobada' => 'bg-primary',
        'Rechazada' => 'bg-danger',
        'Adquirida' => 'bg-success',
        default => 'bg-dark',
    };
};
?>

<div class="container-fluid admin-page admin-page-solicitudes">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main">
            <div class="admin-page-header admin-page-header-simple">
                <h2 class="mb-1">
                    Solicitudes de libros
                </h2>

                <p class="text-muted mb-0">
                    Total de solicitudes:
                    <?= (int) $totalRegistros ?>
                </p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success admin-alert">
                    <?= $escapar($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger admin-alert">
                    <?= $escapar($error) ?>
                </div>
            <?php endif; ?>

            <div class="card admin-list-card">
                <div class="admin-card-heading">
                    <div>
                        <h2 class="admin-card-title mb-1">
                            <i class="fa-solid fa-list-check"></i>
                            Bandeja de solicitudes
                        </h2>
                        <p class="mb-0">Revisa y gestiona las solicitudes de libros enviadas por los usuarios.</p>
                    </div>
                </div>
                <form
                    method="GET"
                    action="<?= Config::url('solicitudes') ?>"
                    class="row g-3 admin-filter-form"
                >
                    <div class="col-lg-6">
                        <input
                            type="search"
                            name="buscar"
                            class="form-control"
                            placeholder="Título, área, usuario o CIP"
                            value="<?= $escapar($filtros['buscar'] ?? '') ?>"
                        >
                    </div>

                    <div class="col-lg-2">
                        <select
                            name="estado"
                            class="form-select"
                        >
                            <option value="">
                                Todos los estados
                            </option>

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

                    <div class="col-lg-2">
                        <select
                            name="tipo_usuario"
                            class="form-select"
                        >
                            <option value="">
                                Todos los usuarios
                            </option>

                            <?php foreach (['Estudiante', 'Profesor', 'Usuario'] as $tipo): ?>
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

                    <div class="col-lg-2 d-grid">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="fa-solid fa-filter"></i>
                            Filtrar
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle admin-table">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Solicitante</th>
                                <th>Libro solicitado</th>
                                <th>Área</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($solicitudes as $solicitud): ?>
                                <?php $nombrePersona = trim(
                                    ($solicitud['estudiante_nombre'] ??
                                        ($solicitud['profesor_nombre'] ??
                                            ($solicitud['nombre_usuario'] ?? ''))) .
                                        ' ' .
                                        ($solicitud['estudiante_apellido'] ??
                                            ($solicitud['profesor_apellido'] ?? '')),
                                ); ?>

                                <tr>
                                    <td>
                                        <?= (int) $solicitud['id_solicitud'] ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= $escapar($nombrePersona) ?>
                                        </strong>

                                        <small class="d-block text-muted">
                                            <?= $escapar($solicitud['tipo_usuario']) ?>

                                            ·

                                            <?= $escapar($solicitud['usuario']) ?>
                                        </small>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= $escapar($solicitud['titulo_libro']) ?>
                                        </strong>

                                        <?php if (!empty($solicitud['autor'])): ?>
                                            <small class="d-block text-muted">
                                                Autor:
                                                <?= $escapar($solicitud['autor']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= $escapar($solicitud['materia']) ?>
                                    </td>

                                    <td>
                                        <?= $escapar($solicitud['fecha_solicitud']) ?>
                                    </td>

                                    <td>
                                        <span
                                            class="badge <?= $claseEstado(
                                                (string) $solicitud['estado'],
                                            ) ?>"
                                        >
                                            <?= $escapar($solicitud['estado']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if ($puedeGestionar): ?>
                                            <a
                                                href="<?= Config::url(
                                                    'solicitudes/gestionar?id=' .
                                                        (int) $solicitud['id_solicitud'],
                                                ) ?>"
                                                class="btn btn-sm btn-primary"
                                            >
                                                <i
                                                    class="fa-solid fa-clipboard-check"
                                                ></i>
                                                Gestionar
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                Solo lectura
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($solicitudes)): ?>
                                <tr>
                                    <td
                                        colspan="7"
                                        class="text-center"
                                    >
                                        No hay solicitudes que coincidan
                                        con los filtros.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <nav class="mt-3">
                        <ul
                            class="pagination justify-content-center mb-0 admin-pagination"
                        >
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <?php $parametros = [
                                    'pagina' => $i,
                                    'buscar' => $filtros['buscar'] ?? '',
                                    'estado' => $filtros['estado'] ?? '',
                                    'tipo_usuario' => $filtros['tipo_usuario'] ?? '',
                                ]; ?>

                                <li
                                    class="page-item <?= $i === (int) $pagina ? 'active' : '' ?>"
                                >
                                    <a
                                        class="page-link"
                                        href="<?= Config::url(
                                            'solicitudes?' . http_build_query($parametros),
                                        ) ?>"
                                    >
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
