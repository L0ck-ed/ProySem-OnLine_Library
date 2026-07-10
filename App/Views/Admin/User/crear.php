<?php

use App\Helpers\Session;
use App\Config\Config;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$error = Session::getFlash('error');

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <h2>Nuevo Usuario</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger mt-3"><?= $error ?></div>
            <?php endif; ?>

            <div class="card p-4 mt-3">
                <form method="POST" action="<?= Config::baseUrl() ?>/usuarios/guardar">
                    <div class="mb-3">
                        <label>Nombre completo</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Usuario</label>
                        <input type="text" name="usuario" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Rol</label>
                        <select name="rol" class="form-control">
                            <option value="Administrador">Administrador</option>
                            <option value="Bibliotecario" selected>Bibliotecario</option>
                        </select>
                    </div>

                    <button class="btn btn-success">
                        <i class="fa-solid fa-save"></i>
                        Guardar
                    </button>

                    <a href="<?= Config::baseUrl() ?>/usuarios" class="btn btn-secondary">
                        Cancelar
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
