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
                <h2>Usuarios</h2>

                <a href="<?= Config::baseUrl() ?>/usuarios/crear" class="btn btn-primary">
                    <i class="fa-solid fa-user-plus"></i>
                    Nuevo Usuario
                </a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="card p-4">
                <form method="GET" action="<?= Config::baseUrl() ?>/usuarios" class="row mb-3">
                    <div class="col-md-10">
                        <input
                            type="text"
                            name="buscar"
                            class="form-control"
                            placeholder="Buscar por nombre o usuario"
                            value="<?= $buscar ?? '' ?>">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-dark w-100">
                            <i class="fa-solid fa-search"></i>
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
                    class="badge <?= $u['estado'] === 'Activo' ? 'bg-success' : 'bg-secondary' ?>"
                >
                    <?= htmlspecialchars($u['estado'], ENT_QUOTES, 'UTF-8') ?>
                </span>
            </td>

            <td>
                <?= htmlspecialchars($u['fecha_creacion'], ENT_QUOTES, 'UTF-8') ?>
            </td>

            <td>
                <a
                    href="<?= Config::url('usuarios/editar?id=' . (int) $u['id_usuario']) ?>"
                    class="btn btn-sm btn-warning"
                >
                    <i class="fa-solid fa-pen-to-square"></i>
                    Editar
                </a>
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

                <nav>
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= max(1, $paginas); $i++): ?>
                            <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="<?= Config::baseUrl() ?>/usuarios?buscar=<?= urlencode(
    $buscar,
) ?>&pagina=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
