<?php

use App\Config\Config;
use App\Helpers\Session;
use App\Middleware\Auth;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$estudiantes = $estudiantes ?? [];
$buscar = $buscar ?? '';
$pagina = $pagina ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$totalRegistros = $totalRegistros ?? 0;

$success = Session::getFlash('success');
$error = Session::getFlash('error');

$puedeCrear = Auth::tienePermiso('estudiantes.crear');

$puedeEditar = Auth::tienePermiso('estudiantes.editar');

$puedeCambiarEstado = Auth::tienePermiso('estudiantes.eliminar');

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};
?>

<div class="container-fluid admin-page admin-page-estudiantes">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main">
            <div class="admin-page-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="mb-1">Estudiantes</h2>

                    <p class="text-muted mb-0">
                        Total de registros:
                        <?= (int) $totalRegistros ?>
                    </p>
                </div>

                <?php if ($puedeCrear): ?>
                    <a
                        href="<?= Config::url('estudiantes/crear') ?>"
                        class="btn btn-primary"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Nuevo estudiante
                    </a>
                <?php endif; ?>
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
                            Directorio de estudiantes
                        </h2>
                        <p class="mb-0">Consulta la información académica y el estado de cada estudiante.</p>
                    </div>
                </div>
                <form
                    method="GET"
                    action="<?= Config::url('estudiantes') ?>"
                    class="row g-3 admin-filter-form"
                >
                    <div class="col-md-10">
                        <input
                            type="search"
                            name="buscar"
                            class="form-control"
                            placeholder="Buscar por CIP, nombre, usuario, facultad o carrera"
                            value="<?= $escapar($buscar) ?>"
                        >
                    </div>

                    <div class="col-md-2 d-grid">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="fa-solid fa-magnifying-glass"></i>
                            Buscar
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle admin-table">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>CIP</th>
                                <th>Estudiante</th>
                                <th>Usuario</th>
                                <th>Facultad</th>
                                <th>Carrera</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($estudiantes as $estudiante): ?>
                                <?php $nombreCompleto = trim(
                                    ($estudiante['primer_nombre'] ?? '') .
                                        ' ' .
                                        ($estudiante['segundo_nombre'] ?? '') .
                                        ' ' .
                                        ($estudiante['primer_apellido'] ?? '') .
                                        ' ' .
                                        ($estudiante['segundo_apellido'] ?? ''),
                                ); ?>

                                <tr>
                                    <td>
                                        <?= (int) $estudiante['id_estudiante'] ?>
                                    </td>

                                    <td>
                                        <?= $escapar($estudiante['cip']) ?>
                                    </td>

                                    <td>
                                        <?= $escapar($nombreCompleto) ?>
                                    </td>

                                    <td>
                                        <?= $escapar($estudiante['usuario']) ?>
                                    </td>

                                    <td>
                                        <?= $escapar($estudiante['facultad']) ?>
                                    </td>

                                    <td>
                                        <?= $escapar($estudiante['carrera']) ?>
                                    </td>

                                    <td>
                                        <?php if ((int) $estudiante['estado'] === 1): ?>
                                            <span class="badge bg-success">
                                                Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?php if ($puedeEditar): ?>
                                                <a
                                                    href="<?= Config::url(
                                                        'estudiantes/editar?id=' .
                                                            (int) $estudiante['id_estudiante'],
                                                    ) ?>"
                                                    class="btn btn-sm btn-warning"
                                                >
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                    Editar
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($puedeCambiarEstado): ?>
                                                <form
                                                    method="POST"
                                                    action="<?= Config::url(
                                                        'estudiantes/cambiar-estado',
                                                    ) ?>"
                                                    onsubmit="return confirm(
                                                        '¿Seguro que deseas <?= (int) $estudiante[
                                                            'estado'
                                                        ] === 1
                                                            ? 'desactivar'
                                                            : 'activar' ?> este estudiante?'
                                                    );"
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="id_estudiante"
                                                        value="<?= (int) $estudiante[
                                                            'id_estudiante'
                                                        ] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="estado"
                                                        value="<?= (int) $estudiante['estado'] === 1
                                                            ? 0
                                                            : 1 ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm <?= (int) $estudiante[
                                                            'estado'
                                                        ] === 1
                                                            ? 'btn-danger'
                                                            : 'btn-success' ?>"
                                                    >
                                                        <?php if (
                                                            (int) $estudiante['estado'] === 1
                                                        ): ?>
                                                            <i class="fa-solid fa-ban"></i>
                                                            Desactivar
                                                        <?php else: ?>
                                                            <i class="fa-solid fa-check"></i>
                                                            Activar
                                                        <?php endif; ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($estudiantes)): ?>
                                <tr>
                                    <td
                                        colspan="8"
                                        class="text-center"
                                    >
                                        No hay estudiantes registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación de estudiantes">
                        <ul class="pagination justify-content-center mb-0 admin-pagination">
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li
                                    class="page-item <?= $i === (int) $pagina ? 'active' : '' ?>"
                                >
                                    <a
                                        class="page-link"
                                        href="<?= Config::url(
                                            'estudiantes?pagina=' .
                                                $i .
                                                '&buscar=' .
                                                urlencode($buscar),
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
