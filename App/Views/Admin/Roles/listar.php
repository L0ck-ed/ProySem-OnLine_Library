<?php

use App\Helpers\Session;
use App\Config\Config;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$success = Session::getFlash('success');
$error = Session::getFlash('error');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Roles y permisos</h2>

                <a
                    href="<?= Config::url('roles/crear') ?>"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-plus"></i>
                    Nuevo rol
                </a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="card p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Rol</th>
                                <th>Descripción</th>
                                <th>Permisos asignados</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($roles as $rol): ?>
                                <tr>
                                    <td>
                                        <?= (int) $rol['id_rol'] ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $rol['descripcion'] ?? 'Sin descripción',
                                            ENT_QUOTES,
                                            'UTF-8',
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-dark text-white px-3 py-2">
                                            <?= (int) $rol['total_permisos'] ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if ((int) $rol['estado'] === 1): ?>
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
                                            <a
                                                href="<?= Config::url(
                                                    'roles/editar?id=' . (int) $rol['id_rol'],
                                                ) ?>"
                                                class="btn btn-sm btn-warning"
                                            >
                                                <i class="fa-solid fa-pen-to-square"></i>
                                                Editar
                                            </a>

                                            <a
                                                href="<?= Config::url(
                                                    'roles/permisos?id=' . (int) $rol['id_rol'],
                                                ) ?>"
                                                class="btn btn-sm btn-primary"
                                            >
                                                <i class="fa-solid fa-key"></i>
                                                Gestionar permisos
                                            </a>

                                            <?php if ($rol['nombre'] !== 'Administrador'): ?>
                                                <form
                                                    method="POST"
                                                    action="<?= Config::url(
                                                        'roles/cambiar-estado',
                                                    ) ?>"
                                                    onsubmit="return confirm(
                                                        '¿Seguro que deseas <?= (int) $rol[
                                                            'estado'
                                                        ] === 1
                                                            ? 'desactivar'
                                                            : 'activar' ?> este rol?'
                                                    );"
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="id_rol"
                                                        value="<?= (int) $rol['id_rol'] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="estado"
                                                        value="<?= (int) $rol['estado'] === 1
                                                            ? 0
                                                            : 1 ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm <?= (int) $rol[
                                                            'estado'
                                                        ] === 1
                                                            ? 'btn-danger'
                                                            : 'btn-success' ?>"
                                                    >
                                                        <?php if ((int) $rol['estado'] === 1): ?>
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

                            <?php if (empty($roles)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        No hay roles registrados.
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
