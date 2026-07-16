<?php

use App\Config\Config;
use App\Helpers\Session;
use App\Middleware\Auth;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$libros = $libros ?? [];
$buscar = $buscar ?? '';
$pagina = $pagina ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$totalRegistros = $totalRegistros ?? 0;

$success = Session::getFlash('success');
$error = Session::getFlash('error');

$puedeCrear = Auth::tienePermiso('libros.crear');
$puedeEditar = Auth::tienePermiso('libros.editar');
$puedeCambiarEstado = Auth::tienePermiso('libros.eliminar');
$puedeExportar = Auth::tienePermiso('reportes.exportar_excel');

$escapar = static function (mixed $valor): string {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};
?>

<div class="container-fluid admin-page admin-page-libros">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main">
            <div class="admin-page-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="mb-1">Libros</h2>

                    <p class="text-muted mb-0">
                        Total de registros:
                        <?= (int) $totalRegistros ?>
                    </p>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($puedeExportar): ?>
                        <a
                            href="<?= Config::url('libros/excel?buscar=' . urlencode($buscar)) ?>"
                            class="btn btn-success"
                        >
                            <i class="fa-solid fa-file-excel"></i>
                            Exportar Excel
                        </a>
                    <?php endif; ?>
                    <?php if ($puedeCrear): ?>
                        <a
                            href="<?= Config::url('libros/crear') ?>"
                            class="btn btn-primary"
                        >
                            <i class="fa-solid fa-plus"></i>
                            Nuevo libro
                        </a>
                    <?php endif; ?>
                </div>
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
                            Inventario de libros
                        </h2>
                        <p class="mb-0">Busca títulos, revisa existencias y administra el catálogo.</p>
                    </div>
                </div>
                <form
                    method="GET"
                    action="<?= Config::url('libros') ?>"
                    class="row g-3 admin-filter-form"
                >
                    <div class="col-md-10">
                        <input
                            type="search"
                            name="buscar"
                            class="form-control"
                            placeholder="Buscar por título, autor, ISBN, categoría o tema"
                            value="<?= $escapar($buscar) ?>"
                        >
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            Buscar
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle admin-table">
                        <thead class="table-primary">
                            <tr>
                                <th>Portada</th>
                                <th>Libro</th>
                                <th>Categoría y temas</th>
                                <th>ISBN</th>
                                <th>Costo</th>
                                <th>Existencias</th>
                                <th>Estado</th>
                                <th>Integridad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($libros as $libro): ?>
                                <tr>
                                    <td style="width: 90px;">
                                        <?php if (!empty($libro['thumbnail_ruta'])): ?>
                                            <img
                                                src="<?= $escapar(
                                                    Config::asset(
                                                        $libro['thumbnail_ruta']
                                                    )
                                                ) ?>"
                                                alt="Portada"
                                                class="rounded shadow-sm"
                                                style="width: 58px; height: 78px; object-fit: cover;"
                                            >
                                        <?php else: ?>
                                            <div
                                                class="d-flex align-items-center justify-content-center bg-light rounded"
                                                style="width: 58px; height: 78px;"
                                            >
                                                <i class="fa-solid fa-book fa-2x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= $escapar($libro['titulo']) ?>
                                        </strong>
                                        <div class="text-muted small">
                                            <?= $escapar($libro['autor']) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?= $escapar(
                                                $libro['editorial'] ??
                                                'Sin editorial'
                                            ) ?>
                                            <?php if (!empty($libro['anio_publicacion'])): ?>
                                                · <?= (int) $libro['anio_publicacion'] ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge bg-dark mb-1">
                                            <?= $escapar($libro['categoria']) ?>
                                        </span>
                                        <div class="small text-muted">
                                            <?= $escapar($libro['temas']) ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?= $escapar(
                                            $libro['isbn'] ?? 'Sin ISBN'
                                        ) ?>
                                    </td>

                                    <td>
                                        B/.
                                        <?= number_format(
                                            (float) $libro['costo'],
                                            2
                                        ) ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= (int) $libro['existencias_disponibles'] ?>
                                        </strong>
                                        disponibles de
                                        <?= (int) $libro['existencias_totales'] ?>

                                        <div class="mt-1">
                                            <?php if (
                                                (int) $libro['existencias_disponibles'] > 0
                                            ): ?>
                                                <span class="badge bg-success">
                                                    Disponible
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    Agotado
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?php if ((int) $libro['estado'] === 1): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (($libro['integridad'] ?? null) === true): ?>
                                            <span class="badge bg-success" title="Firma OpenSSL verificada">
                                                <i class="fa-solid fa-shield-halved"></i> Íntegro
                                            </span>
                                        <?php elseif (($libro['integridad'] ?? null) === false): ?>
                                            <span class="badge bg-danger" title="Los datos no coinciden con la firma digital">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Alterado
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary" title="Se firmará al editar el registro">
                                                Sin firma
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?php if ($puedeEditar): ?>
                                                <a
                                                    href="<?= Config::url(
                                                        'libros/editar?id=' .
                                                        (int) $libro['id_libro']
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
                                                        'libros/cambiar-estado'
                                                    ) ?>"
                                                    onsubmit="return confirm(
                                                        '¿Seguro que deseas <?= (int) $libro['estado'] === 1
                                                            ? 'desactivar'
                                                            : 'activar' ?> este libro?'
                                                    );"
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="id_libro"
                                                        value="<?= (int) $libro['id_libro'] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="estado"
                                                        value="<?= (int) $libro['estado'] === 1
                                                            ? 0
                                                            : 1 ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm <?= (int) $libro['estado'] === 1
                                                            ? 'btn-danger'
                                                            : 'btn-success' ?>"
                                                    >
                                                        <?php if ((int) $libro['estado'] === 1): ?>
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

                            <?php if (empty($libros)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        No hay libros registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación de libros">
                        <ul class="pagination justify-content-center mb-0 admin-pagination">
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li
                                    class="page-item <?= $i === (int) $pagina
                                        ? 'active'
                                        : '' ?>"
                                >
                                    <a
                                        class="page-link"
                                        href="<?= Config::url(
                                            'libros?pagina=' .
                                            $i .
                                            '&buscar=' .
                                            urlencode($buscar)
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
