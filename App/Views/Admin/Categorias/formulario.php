<?php

use App\Config\Config;
use App\Helpers\Session;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$modo = $modo ?? 'crear';
$esEdicion = $modo === 'editar';

$categoria = $categoria ?? [];

$error = Session::getFlash('error');
$old = Session::getFlash('old_categoria') ?? [];

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};

$valor = static function (string $campo, array $old, array $categoria): mixed {
    return array_key_exists($campo, $old) ? $old[$campo] : $categoria[$campo] ?? '';
};

$titulo = $esEdicion ? 'Editar categoría' : 'Nueva categoría';

$accion = $esEdicion ? Config::url('categorias/actualizar') : Config::url('categorias/guardar');
?>

<div class="container-fluid admin-page admin-page-categorias">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main">
            <div
                class="admin-page-header d-flex justify-content-between align-items-center gap-3"
            >
                <div>
                    <h2 class="mb-1">
                        <?= $escapar($titulo) ?>
                    </h2>

                    <p class="text-muted mb-0">
                        Organiza los libros según su categoría.
                    </p>
                </div>

                <a
                    href="<?= Config::url('categorias') ?>"
                    class="btn btn-secondary"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger admin-alert">
                    <?= $escapar($error) ?>
                </div>
            <?php endif; ?>

            <form
                method="POST"
                action="<?= $accion ?>"
            >
                <?php if ($esEdicion): ?>
                    <input
                        type="hidden"
                        name="id_categoria"
                        value="<?= (int) $categoria['id_categoria'] ?>"
                    >
                <?php endif; ?>

                <div class="card admin-form-card mb-4">
                    <div class="mb-3">
                        <label
                            for="nombre"
                            class="form-label"
                        >
                            Nombre
                        </label>

                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            class="form-control"
                            maxlength="100"
                            required
                            placeholder="Ejemplo: Programación"
                            value="<?= $escapar($valor('nombre', $old, $categoria)) ?>"
                        >
                    </div>

                    <div class="mb-3">
                        <label
                            for="descripcion"
                            class="form-label"
                        >
                            Descripción
                        </label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            class="form-control"
                            rows="5"
                            maxlength="255"
                            placeholder="Describe brevemente la categoría"
                        ><?= $escapar($valor('descripcion', $old, $categoria)) ?></textarea>
                    </div>
                </div>

                <div class="admin-form-actions d-flex justify-content-end gap-2">
                    <a
                        href="<?= Config::url('categorias') ?>"
                        class="btn btn-secondary"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>

                        <?= $esEdicion ? 'Actualizar categoría' : 'Guardar categoría' ?>
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
