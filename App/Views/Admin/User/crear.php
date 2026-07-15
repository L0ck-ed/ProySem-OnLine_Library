<?php

use App\Config\Config;
use App\Helpers\Session;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$error = Session::getFlash('error');
$old = Session::getFlash('old_usuario') ?? [];
$rolesAnteriores = array_map('intval', (array) ($old['id_roles'] ?? []));
?>

<div class="container-fluid admin-page admin-page-usuarios">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main">
            <section class="admin-page-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h1 class="mb-1">Nuevo usuario</h1>
                    <p class="mb-0">Registra una cuenta administrativa y asigna sus roles.</p>
                </div>

                <a href="<?= Config::url('usuarios') ?>" class="btn btn-secondary">
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

            <form method="POST" action="<?= Config::url('usuarios/guardar') ?>">
                <section class="card admin-form-card mb-4">
                    <div class="admin-card-heading">
                        <div>
                            <h2 class="admin-card-title mb-1">
                                <i class="fa-solid fa-user-gear"></i>
                                Datos de acceso
                            </h2>
                            <p class="mb-0">Completa la identidad, las credenciales y los roles.</p>
                        </div>
                    </div>

                    <div class="admin-form-body">
                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <label for="nombre" class="form-label">Nombre completo</label>
                                <input
                                    type="text"
                                    name="nombre"
                                    id="nombre"
                                    class="form-control"
                                    value="<?= htmlspecialchars($old['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="Nombre y apellido"
                                    required
                                >
                            </div>

                            <div class="col-12 col-lg-6">
                                <label for="usuario" class="form-label">Nombre de usuario</label>
                                <input
                                    type="text"
                                    name="usuario"
                                    id="usuario"
                                    class="form-control"
                                    value="<?= htmlspecialchars($old['usuario'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="Usuario para iniciar sesión"
                                    required
                                >
                            </div>

                            <div class="col-12">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group admin-input-group">
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control"
                                        minlength="8"
                                        placeholder="Mínimo 8 caracteres"
                                        required
                                    >
                                    <button type="button" id="togglePassword" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-eye"></i>
                                        Ver
                                    </button>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Roles</label>
                                <div class="admin-check-grid">
                                    <?php foreach ($roles as $rol): ?>
                                        <?php $idRol = (int) $rol['id_rol']; ?>
                                        <label class="admin-check-card" for="rol_<?= $idRol ?>">
                                            <input
                                                type="checkbox"
                                                name="id_roles[]"
                                                id="rol_<?= $idRol ?>"
                                                value="<?= $idRol ?>"
                                                class="form-check-input"
                                                <?= in_array($idRol, $rolesAnteriores, true) ? 'checked' : '' ?>
                                            >
                                            <span>
                                                <strong><?= htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <small>Asignar este alcance al usuario</small>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <small class="form-help">Selecciona al menos un rol.</small>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="admin-form-actions d-flex justify-content-end gap-2">
                    <a href="<?= Config::url('usuarios') ?>" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-user-plus"></i>
                        Guardar usuario
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<script src="<?= Config::baseUrl() ?>/Public/Assets/JavaScript/Functions/toggle-password-visibility.js?v=10"></script>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
