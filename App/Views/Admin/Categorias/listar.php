<?php

use App\Config\Config;
use App\Helpers\Session;
use App\Middleware\Auth;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$categorias = $categorias ?? [];
$buscar = $buscar ?? '';
$pagina = $pagina ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$totalRegistros = $totalRegistros ?? 0;

$success = Session::getFlash('success');
$error = Session::getFlash('error');

$puedeCrear = Auth::tienePermiso('categorias.crear');

$puedeEditar = Auth::tienePermiso('categorias.editar');

$puedeCambiarEstado = Auth::tienePermiso('categorias.eliminar');

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <div
                class="d-flex justify-content-between align-items-center mb-4"
            >
                <div>
                    <h2 class="mb-1">Categorías</h2>

                    <p class="text-muted mb-0">
                        Total de registros:
                        <?= (int) $totalRegistros ?>
                    </p>
                </div>

                <?php if ($puedeCrear): ?>
                    <a
                        href="<?= Config::url('categorias/crear') ?>"
                        class="btn btn-primary"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Nueva categoría
                    </a>
                <?php endif; ?>
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

            <div class="card p-4">
                <form
                    method="GET"
                    action="<?= Config::url('categorias') ?>"
                    class="row g-2 mb-4"
                >
                    <div class="col-md-10">
                        <input
                            type="search"
                            name="buscar"
                            class="form-control"
                            placeholder="Buscar por nombre o descripción"
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
                    <table class="table table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($categorias as $categoria): ?>
                                <tr>
                                    <td>
                                        <?= (int) $categoria['id_categoria'] ?>
                                    </td>

                                    <td>
                                        <?= $escapar($categoria['nombre']) ?>
                                    </td>

                                    <td>
                                        <?= $escapar(
                                            $categoria['descripcion'] ?? 'Sin descripción',
                                        ) ?>
                                    </td>

                                    <td>
                                        <?php if ((int) $categoria['estado'] === 1): ?>
                                            <span class="badge bg-success">
                                                Activa
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                Inactiva
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?php if ($puedeEditar): ?>
                                                <a
                                                    href="<?= Config::url(
                                                        'categorias/editar?id=' .
                                                            (int) $categoria['id_categoria'],
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
                                                        'categorias/cambiar-estado',
                                                    ) ?>"
                                                    onsubmit="return confirm(
                                                        '¿Seguro que deseas <?= (int) $categoria[
                                                            'estado'
                                                        ] === 1
                                                            ? 'desactivar'
                                                            : 'activar' ?> esta categoría?'
                                                    );"
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="id_categoria"
                                                        value="<?= (int) $categoria[
                                                            'id_categoria'
                                                        ] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="estado"
                                                        value="<?= (int) $categoria['estado'] === 1
                                                            ? 0
                                                            : 1 ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm <?= (int) $categoria[
                                                            'estado'
                                                        ] === 1
                                                            ? 'btn-danger'
                                                            : 'btn-success' ?>"
                                                    >
                                                        <?php if (
                                                            (int) $categoria['estado'] === 1
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

                            <?php if (empty($categorias)): ?>
                                <tr>
                                    <td
                                        colspan="5"
                                        class="text-center"
                                    >
                                        No hay categorías registradas.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación de categorías">
                        <ul class="pagination justify-content-center mb-0">
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li
                                    class="page-item <?= $i === (int) $pagina ? 'active' : '' ?>"
                                >
                                    <a
                                        class="page-link"
                                        href="<?= Config::url(
                                            'categorias?pagina=' .
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
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
