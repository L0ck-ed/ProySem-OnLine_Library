<?php

use App\Helpers\Session;
use App\Config\Config;
use App\Middleware\Auth;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$success = Session::getFlash('success');
$error = Session::getFlash('error');

$puedeCrear = Auth::tienePermiso('usuarios.crear');
$puedeEditar = Auth::tienePermiso('usuarios.editar');
$puedeCambiarEstado = Auth::tienePermiso('usuarios.eliminar');
?>

<div class="container-fluid admin-page admin-page-usuarios">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main">
            <div class="admin-page-header d-flex justify-content-between align-items-center gap-3">
                <h2>Usuarios</h2>

                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($puedeEditar): ?>
                        <a
                            href="<?= Config::url('usuarios/bloqueados') ?>"
                            class="btn btn-outline-danger"
                        >
                            <i class="fa-solid fa-lock-open"></i>
                            Cuentas bloqueadas
                        </a>
                    <?php endif; ?>

                    <?php if ($puedeCrear): ?>
                        <a
                            href="<?= Config::url('usuarios/crear') ?>"
                            class="btn btn-primary"
                        >
                            <i class="fa-solid fa-user-plus"></i>
                            Nuevo Usuario
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success admin-alert">
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger admin-alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="card admin-list-card">
                <div class="admin-card-heading">
                    <div>
                        <h2 class="admin-card-title mb-1">
                            <i class="fa-solid fa-list-check"></i>
                            Directorio de usuarios
                        </h2>
                        <p class="mb-0">Busca usuarios, revisa sus roles y administra su estado.</p>
                    </div>
                </div>
                <form
                    method="GET"
                    action="<?= Config::baseUrl() ?>/usuarios"
                    class="row g-3 admin-filter-form"
                >
                    <div class="col-md-10">
                        <input
                            type="text"
                            name="buscar"
                            class="form-control"
                            placeholder="Buscar por nombre o usuario"
                            value="<?= htmlspecialchars($buscar ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark w-100">
                            <i class="fa-solid fa-search"></i>
                            Buscar
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle admin-table">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?= (int) $u['id_usuario'] ?></td>

                                    <td>
                                        <?= htmlspecialchars($u['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($u['usuario'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($u['rol'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td>
                                        <span
                                            class="badge <?= $u['estado'] === 'Activo'
                                                ? 'bg-success'
                                                : 'bg-secondary' ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $u['estado'],
                                                ENT_QUOTES,
                                                'UTF-8',
                                            ) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $u['fecha_creacion'],
                                            ENT_QUOTES,
                                            'UTF-8',
                                        ) ?>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?php if ($puedeEditar): ?>
                                                <a
                                                    href="<?= Config::url(
                                                        'usuarios/editar?id=' .
                                                            (int) $u['id_usuario'],
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
                                                        'usuarios/cambiar-estado',
                                                    ) ?>"
                                                    onsubmit="return confirm(
                                                        '¿Seguro que deseas <?= $u['estado'] ===
                                                        'Activo'
                                                            ? 'desactivar'
                                                            : 'activar' ?> este usuario?'
                                                    );"
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="id_usuario"
                                                        value="<?= (int) $u['id_usuario'] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="estado"
                                                        value="<?= $u['estado'] === 'Activo'
                                                            ? 0
                                                            : 1 ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm <?= $u['estado'] ===
                                                        'Activo'
                                                            ? 'btn-danger'
                                                            : 'btn-success' ?>"
                                                    >
                                                        <?php if ($u['estado'] === 'Activo'): ?>
                                                            <i class="fa-solid fa-user-slash"></i>
                                                            Desactivar
                                                        <?php else: ?>
                                                            <i class="fa-solid fa-user-check"></i>
                                                            Activar
                                                        <?php endif; ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (!$puedeEditar && !$puedeCambiarEstado): ?>
                                                <span class="text-muted">
                                                    Sin acciones disponibles
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        No hay usuarios registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Paginación de usuarios">
                    <ul class="pagination justify-content-center mb-0 admin-pagination">
                        <?php for ($i = 1; $i <= max(1, $paginas); $i++): ?>
                            <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                <a
                                    class="page-link"
                                    href="<?= Config::baseUrl() ?>/usuarios?buscar=<?= urlencode(
    $buscar,
) ?>&pagina=<?= $i ?>"
                                >
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
