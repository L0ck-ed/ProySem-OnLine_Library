<?php

use App\Config\Config;
use App\Helpers\Session;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$success = Session::getFlash('success');
$error = Session::getFlash('error');
?>

<div class="container-fluid admin-page admin-page-bloqueados">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main">
            <div class="admin-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-1">
                        <i class="fa-solid fa-user-lock text-danger"></i>
                        Cuentas bloqueadas
                    </h2>
                    <p class="text-muted mb-0">
                        Usuarios que alcanzaron el límite de tres intentos incorrectos.
                    </p>
                </div>

                <a href="<?= Config::url('usuarios') ?>" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver a usuarios
                </a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success admin-alert alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check"></i>
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Cerrar"
                    ></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger admin-alert alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Cerrar"
                    ></button>
                </div>
            <?php endif; ?>

            <div class="alert alert-info admin-alert">
                <i class="fa-solid fa-circle-info"></i>
                Al desbloquear una cuenta, su contador vuelve a cero. Su contraseña,
                roles y estado activo o inactivo no se modifican.
            </div>

            <div class="card admin-list-card">
                <div class="admin-card-heading">
                    <div>
                        <h2 class="admin-card-title mb-1">
                            <i class="fa-solid fa-list-check"></i>
                            Cuentas con acceso bloqueado
                        </h2>
                        <p class="mb-0">Reinicia los intentos fallidos de las cuentas que lo necesiten.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 admin-table">
                        <thead class="table-danger">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Intentos</th>
                                <th>Último intento</th>
                                <th>Bloqueado hasta</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($usuariosBloqueados as $usuario): ?>
                                <tr>
                                    <td><?= (int) $usuario['id_usuario'] ?></td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $usuario['nombre'],
                                            ENT_QUOTES,
                                            'UTF-8',
                                        ) ?>
                                    </td>

                                    <td>
                                        <code><?= htmlspecialchars(
                                            (string) $usuario['usuario'],
                                            ENT_QUOTES,
                                            'UTF-8',
                                        ) ?></code>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $usuario['rol'],
                                            ENT_QUOTES,
                                            'UTF-8',
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-danger fs-6">
                                            <?= (int) $usuario['intentos_fallidos'] ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= !empty($usuario['ultimo_intento'])
                                            ? htmlspecialchars(
                                                (string) $usuario['ultimo_intento'],
                                                ENT_QUOTES,
                                                'UTF-8',
                                            )
                                            : '<span class="text-muted">Sin registro</span>' ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($usuario['bloqueado_hasta'])): ?>
                                            <span class="badge text-bg-warning">
                                                <?= htmlspecialchars(
                                                    (string) $usuario['bloqueado_hasta'],
                                                    ENT_QUOTES,
                                                    'UTF-8',
                                                ) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge text-bg-dark">
                                                Desbloqueo manual
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span
                                            class="badge <?= (int) $usuario['estado'] === 1
                                                ? 'bg-success'
                                                : 'bg-secondary' ?>"
                                        >
                                            <?= (int) $usuario['estado'] === 1
                                                ? 'Activo'
                                                : 'Inactivo' ?>
                                        </span>
                                    </td>

                                    <td>
                                        <form
                                            method="POST"
                                            action="<?= Config::url('usuarios/desbloquear') ?>"
                                            onsubmit="return confirm('¿Deseas desbloquear esta cuenta y reiniciar sus intentos fallidos?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="id_usuario"
                                                value="<?= (int) $usuario['id_usuario'] ?>"
                                            >

                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fa-solid fa-lock-open"></i>
                                                Desbloquear
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($usuariosBloqueados)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fa-solid fa-shield-halved fa-3x text-success mb-3"></i>
                                        <h5>No hay cuentas bloqueadas</h5>
                                        <p class="text-muted mb-0">
                                            Todas las cuentas pueden intentar iniciar sesión normalmente.
                                        </p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
