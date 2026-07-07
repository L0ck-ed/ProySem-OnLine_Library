<?php

use App\Helpers\Session;
use App\Config\Config;

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

$error = Session::getFlash('error');

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <h2>Nuevo Usuario</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger mt-3"><?= $error ?></div>
            <?php endif; ?>

            <div class="card p-4 mt-3">
                <form method="POST" action="<?= Config::BASE_URL ?>/usuarios/guardar">
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

                    <a href="<?= Config::BASE_URL ?>/usuarios" class="btn btn-secondary">
                        Cancelar
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
