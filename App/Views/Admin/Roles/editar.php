<?php

use App\Helpers\Session;
use App\Config\Config;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$error = Session::getFlash('error');
$old = Session::getFlash('old_rol') ?? [];

$nombreActual = $old['nombre'] ?? $rol['nombre'];
$descripcionActual = $old['descripcion'] ?? ($rol['descripcion'] ?? '');
$estadoActual = (int) ($old['estado'] ?? $rol['estado']);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <h2>Editar Rol</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger mt-3">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="card p-4 mt-3">
                <form
                    method="POST"
                    action="<?= Config::url('roles/actualizar') ?>"
                >
                    <input
                        type="hidden"
                        name="id_rol"
                        value="<?= (int) $rol['id_rol'] ?>"
                    >

                    <div class="mb-3">
                        <label for="nombre" class="form-label">
                            Nombre del rol
                        </label>

                        <input
                            type="text"
                            name="nombre"
                            id="nombre"
                            class="form-control"
                            value="<?= htmlspecialchars($nombreActual, ENT_QUOTES, 'UTF-8') ?>"
                            maxlength="50"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            Descripción
                        </label>

                        <textarea
                            name="descripcion"
                            id="descripcion"
                            class="form-control"
                            rows="4"
                            maxlength="255"
                        ><?= htmlspecialchars($descripcionActual, ENT_QUOTES, 'UTF-8') ?></textarea>
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
                        <i class="fa-solid fa-floppy-disk"></i>
                        Actualizar rol
                    </button>

                    <a
                        href="<?= Config::url('roles') ?>"
                        class="btn btn-secondary"
                    >
                        Cancelar
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
