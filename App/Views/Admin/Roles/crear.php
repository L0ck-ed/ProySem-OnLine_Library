<?php

use App\Helpers\Session;
use App\Config\Config;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$error = Session::getFlash('error');
$old = Session::getFlash('old_rol') ?? [];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <h2>Nuevo Rol</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger mt-3">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="card p-4 mt-3">
                <form
                    method="POST"
                    action="<?= Config::url('roles/guardar') ?>"
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
                            value="<?= htmlspecialchars(
                                $old['nombre'] ?? '',
                                ENT_QUOTES,
                                'UTF-8',
                            ) ?>"
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
                        ><?= htmlspecialchars(
                            $old['descripcion'] ?? '',
                            ENT_QUOTES,
                            'UTF-8',
                        ) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Guardar rol
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
