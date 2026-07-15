<?php

use App\Config\Config;
use App\Helpers\Session;
use App\Middleware\Auth;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$profesores = $profesores ?? [];
$buscar = $buscar ?? '';
$pagina = $pagina ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$totalRegistros = $totalRegistros ?? 0;

$success = Session::getFlash('success');
$error = Session::getFlash('error');

$puedeCrear = Auth::tienePermiso('profesores.crear');

$puedeEditar = Auth::tienePermiso('profesores.editar');

$puedeCambiarEstado = Auth::tienePermiso('profesores.eliminar');

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};
?>

<div class="container-fluid admin-page admin-page-profesores">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main">
            <div class="admin-page-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="mb-1">Profesores</h2>

                    <p class="text-muted mb-0">
                        Total de registros:
                        <?= (int) $totalRegistros ?>
                    </p>
                </div>

                <?php if ($puedeCrear): ?>
                    <a
                        href="<?= Config::url('profesores/crear') ?>"
                        class="btn btn-primary"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Nuevo profesor
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
                            Directorio de profesores
                        </h2>
                        <p class="mb-0">Consulta la información profesional y el estado de cada docente.</p>
                    </div>
                </div>
                <form
                    method="GET"
                    action="<?= Config::url('profesores') ?>"
                    class="row g-3 admin-filter-form"
                >
                    <div class="col-md-10">
                        <input
                            type="search"
                            name="buscar"
                            class="form-control"
                            placeholder="Buscar por CIP, nombre, usuario, departamento o especialidad"
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
                                <th>Profesor</th>
                                <th>Usuario</th>
                                <th>Departamento</th>
                                <th>Especialidad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($profesores as $profesor): ?>
                                <?php $nombreCompleto = trim(
                                    ($profesor['primer_nombre'] ?? '') .
                                        ' ' .
                                        ($profesor['segundo_nombre'] ?? '') .
                                        ' ' .
                                        ($profesor['primer_apellido'] ?? '') .
                                        ' ' .
                                        ($profesor['segundo_apellido'] ?? ''),
                                ); ?>

                                <tr>
                                    <td>
                                        <?= (int) $profesor['id_profesor'] ?>
                                    </td>

                                    <td>
                                        <?= $escapar($profesor['cip']) ?>
                                    </td>

                                    <td>
                                        <?= $escapar($nombreCompleto) ?>
                                    </td>

                                    <td>
                                        <?= $escapar($profesor['usuario']) ?>
                                    </td>

                                    <td>
                                        <?= $escapar(
                                            $profesor['departamento'] ?? 'Sin departamento',
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $escapar(
                                            $profesor['especialidad'] ?? 'Sin especialidad',
                                        ) ?>
                                    </td>

                                    <td>
                                        <?php if ((int) $profesor['estado'] === 1): ?>
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
                                                        'profesores/editar?id=' .
                                                            (int) $profesor['id_profesor'],
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
                                                        'profesores/cambiar-estado',
                                                    ) ?>"
                                                    onsubmit="return confirm(
                                                        '¿Seguro que deseas <?= (int) $profesor[
                                                            'estado'
                                                        ] === 1
                                                            ? 'desactivar'
                                                            : 'activar' ?> este profesor?'
                                                    );"
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="id_profesor"
                                                        value="<?= (int) $profesor[
                                                            'id_profesor'
                                                        ] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="estado"
                                                        value="<?= (int) $profesor['estado'] === 1
                                                            ? 0
                                                            : 1 ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm <?= (int) $profesor[
                                                            'estado'
                                                        ] === 1
                                                            ? 'btn-danger'
                                                            : 'btn-success' ?>"
                                                    >
                                                        <?php if (
                                                            (int) $profesor['estado'] === 1
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

                            <?php if (empty($profesores)): ?>
                                <tr>
                                    <td
                                        colspan="8"
                                        class="text-center"
                                    >
                                        No hay profesores registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación de profesores">
                        <ul class="pagination justify-content-center mb-0 admin-pagination">
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li
                                    class="page-item <?= $i === (int) $pagina ? 'active' : '' ?>"
                                >
                                    <a
                                        class="page-link"
                                        href="<?= Config::url(
                                            'profesores?pagina=' .
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
