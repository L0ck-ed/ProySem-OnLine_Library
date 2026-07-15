<?php

use App\Helpers\Session;
use App\Config\Config;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$error = Session::getFlash('error');
$old = Session::getFlash('old_usuario') ?? [];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <h2>Nuevo Usuario</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger mt-3">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="card p-4 mt-3">
                <form
                    method="POST"
                    action="<?= Config::baseUrl() ?>/usuarios/guardar"
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
                            value="<?= htmlspecialchars(
                                $old['nombre'] ?? '',
                                ENT_QUOTES,
                                'UTF-8',
                            ) ?>"
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
                            value="<?= htmlspecialchars(
                                $old['usuario'] ?? '',
                                ENT_QUOTES,
                                'UTF-8',
                            ) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Contraseña
                        </label>

                        <div class="input-group">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                required
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                class="btn btn-outline-secondary"
                            >
                                Ver
                            </button>
                        </div>
                    </div>

                        <label class="form-label">
                            Roles
                        </label>

                        <div class="border rounded p-3">
                            <?php foreach ($roles as $rol): ?>
                                <?php $idRol = (int) $rol['id_rol']; ?>

                                <div class="form-check mb-2">
                                    <input
                                        type="checkbox"
                                        name="id_roles[]"
                                        id="rol_<?= $idRol ?>"
                                        value="<?= $idRol ?>"
                                        class="form-check-input"
                                        <?= in_array(
                                            $idRol,
                                            array_map('intval', (array) ($old['id_roles'] ?? [])),
                                            true,
                                        )
                                            ? 'checked'
                                            : '' ?>
                                    >

                                    <label
                                        for="rol_<?= $idRol ?>"
                                        class="form-check-label"
                                    >
                                        <?= htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <small class="text-muted">
                            Seleccione al menos un rol para el usuario.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-save"></i>
                        Guardar
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

<script src="/ProySem-OnLine_Library/Public/Assets/JavaScript/Functions/toggle-password-visibility.js?v=10"></script>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
