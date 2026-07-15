<?php

use App\Config\Config;
use App\Helpers\Session;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$error = Session::getFlash('error');
$old = Session::getFlash('old_rol') ?? [];

$nombreActual = $old['nombre'] ?? $rol['nombre'];
$descripcionActual = $old['descripcion'] ?? ($rol['descripcion'] ?? '');
$estadoActual = (int) ($old['estado'] ?? $rol['estado']);
?>

<div class="container-fluid admin-page admin-page-roles">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main">
            <section class="admin-page-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h1 class="mb-1">Editar rol</h1>
                    <p class="mb-0">Actualiza la identidad y el estado del rol seleccionado.</p>
                </div>

                <a href="<?= Config::url('roles') ?>" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver
                </a>
            </section>

            <?php if ($error): ?>
                <div class="alert alert-danger admin-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= Config::url('roles/actualizar') ?>">
                <input type="hidden" name="id_rol" value="<?= (int) $rol['id_rol'] ?>">

                <section class="card admin-form-card mb-4">
                    <div class="admin-card-heading">
                        <div>
                            <h2 class="admin-card-title mb-1">
                                <i class="fa-solid fa-id-badge"></i>
                                Información del rol
                            </h2>
                            <p class="mb-0">Mantén el nombre, la descripción y el estado al día.</p>
                        </div>
                    </div>

                    <div class="admin-form-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del rol</label>
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
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea
                                name="descripcion"
                                id="descripcion"
                                class="form-control"
                                rows="4"
                                maxlength="255"
                            ><?= htmlspecialchars($descripcionActual, ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>

                        <div>
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="1" <?= $estadoActual === 1 ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= $estadoActual === 0 ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </section>

                <div class="admin-form-actions d-flex justify-content-end gap-2">
                    <a href="<?= Config::url('roles') ?>" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Actualizar rol
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
