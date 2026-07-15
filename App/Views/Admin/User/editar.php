<?php

use App\Helpers\Session;
use App\Config\Config;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$error = Session::getFlash('error');
$old = Session::getFlash('old_usuario') ?? [];

$nombreActual = $old['nombre'] ?? $usuario['nombre'];
$usuarioActual = $old['usuario'] ?? $usuario['usuario'];
$idRolActual = (int) ($old['id_rol'] ?? $usuario['id_rol']);
$estadoActual = (int) ($old['estado'] ?? $usuario['estado']);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <h2>Editar Usuario</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger mt-3">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="card p-4 mt-3">
                <form
                    method="POST"
                    action="<?= Config::baseUrl() ?>/usuarios/actualizar"
                >
                    <input
                        type="hidden"
                        name="id_usuario"
                        value="<?= (int) $usuario['id_usuario'] ?>"
                    >

                    <div class="mb-3">
                        <label for="nombre" class="form-label">
                            Nombre completo
                        </label>

                        <input
                            type="text"
                            name="nombre"
                            id="nombre"
                            class="form-control"
                            value="<?= htmlspecialchars($nombreActual, ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="usuario" class="form-label">
                            Nombre de usuario
                        </label>

                        <input
                            type="text"
                            name="usuario"
                            id="usuario"
                            class="form-control"
                            value="<?= htmlspecialchars($usuarioActual, ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Nueva contraseña
                        </label>

                        <div class="input-group">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                minlength="8"
                                placeholder="Déjela vacía para conservar la actual"
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                class="btn btn-outline-secondary"
                            >
                                Ver
                            </button>
                        </div>

                        <small class="text-muted">
                            Solo complete este campo para cambiar la contraseña.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="id_rol" class="form-label">
                            Rol
                        </label>

                        <select
                            name="id_rol"
                            id="id_rol"
                            class="form-select"
                            required
                        >
                            <option value="">
                                Seleccione un rol
                            </option>

                            <?php foreach ($roles as $rol): ?>
                                <option
                                    value="<?= (int) $rol['id_rol'] ?>"
                                    <?= $idRolActual === (int) $rol['id_rol'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label">
                            Estado
                        </label>

                        <select
                            name="estado"
                            id="estado"
                            class="form-select"
                            required
                        >
                            <option
                                value="1"
                                <?= $estadoActual === 1 ? 'selected' : '' ?>
                            >
                                Activo
                            </option>

                            <option
                                value="0"
                                <?= $estadoActual === 0 ? 'selected' : '' ?>
                            >
                                Inactivo
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-save"></i>
                        Actualizar
                    </button>

                    <a
                        href="<?= Config::baseUrl() ?>/usuarios"
                        class="btn btn-secondary"
                    >
                        Cancelar
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= Config::baseUrl() ?>/Public/Assets/JavaScript/Functions/toggle-password-visibility.js?v=10"></script>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
