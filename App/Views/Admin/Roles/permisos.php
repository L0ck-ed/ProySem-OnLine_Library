<?php

use App\Helpers\Session;
use App\Config\Config;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$error = Session::getFlash('error');

$permisosPorModulo = [];

foreach ($permisos as $permiso) {
    $modulo = $permiso['modulo'];
    $permisosPorModulo[$modulo][] = $permiso;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Gestionar permisos</h2>

                    <p class="text-muted mb-0">
                        Rol:
                        <strong>
                            <?= htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </strong>
                    </p>
                </div>

                <a
                    href="<?= Config::url('roles') ?>"
                    class="btn btn-secondary"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form
                method="POST"
                action="<?= Config::url('roles/permisos/guardar') ?>"
            >
                <input
                    type="hidden"
                    name="id_rol"
                    value="<?= (int) $rol['id_rol'] ?>"
                >

                <?php foreach ($permisosPorModulo as $modulo => $permisosModulo): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <?= htmlspecialchars(ucfirst($modulo), ENT_QUOTES, 'UTF-8') ?>
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($permisosModulo as $permiso): ?>
                                    <?php
                                    $idPermiso = (int) $permiso['id_permiso'];
                                    $estaAsignado = in_array($idPermiso, $permisosAsignados, true);
                                    ?>

                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="form-check border rounded p-3 h-100">
                                            <input
                                                type="checkbox"
                                                name="id_permisos[]"
                                                id="permiso_<?= $idPermiso ?>"
                                                value="<?= $idPermiso ?>"
                                                class="form-check-input ms-0 me-2"
                                                <?= $estaAsignado ? 'checked' : '' ?>
                                            >

                                            <label
                                                for="permiso_<?= $idPermiso ?>"
                                                class="form-check-label"
                                            >
                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $permiso['accion'],
                                                        ENT_QUOTES,
                                                        'UTF-8',
                                                    ) ?>
                                                </strong>

                                                <br>

                                                <small class="text-muted">
                                                    <?= htmlspecialchars(
                                                        $permiso['descripcion'] ??
                                                            $permiso['codigo'],
                                                        ENT_QUOTES,
                                                        'UTF-8',
                                                    ) ?>
                                                </small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Guardar permisos
                    </button>

                    <a
                        href="<?= Config::url('roles') ?>"
                        class="btn btn-secondary"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
