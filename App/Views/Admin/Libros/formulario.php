<?php

use App\Config\Config;
use App\Helpers\Session;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$modo = $modo ?? 'crear';
$esEdicion = $modo === 'editar';
$libro = $libro ?? [];
$categorias = $categorias ?? [];
$temas = $temas ?? [];
$temasSeleccionados = $temasSeleccionados ?? [];

$error = Session::getFlash('error');
$old = Session::getFlash('old_libro') ?? [];

if (isset($old['id_temas']) && is_array($old['id_temas'])) {
    $temasSeleccionados = array_map('intval', $old['id_temas']);
}

$escapar = static function (mixed $valor): string {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

$valor = static function (
    string $campo,
    array $old,
    array $libro
): mixed {
    return array_key_exists($campo, $old)
        ? $old[$campo]
        : ($libro[$campo] ?? '');
};

$tituloPagina = $esEdicion
    ? 'Editar libro'
    : 'Nuevo libro';

$accion = $esEdicion
    ? Config::url('libros/actualizar')
    : Config::url('libros/guardar');
?>

<div class="container-fluid admin-page admin-page-libros">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main">
            <div class="admin-page-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="mb-1">
                        <?= $escapar($tituloPagina) ?>
                    </h2>

                    <p class="text-muted mb-0">
                        Registra la información, existencias, temas e imagen del libro.
                    </p>
                </div>

                <a
                    href="<?= Config::url('libros') ?>"
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
                enctype="multipart/form-data"
            >
                <?php if ($esEdicion): ?>
                    <input
                        type="hidden"
                        name="id_libro"
                        value="<?= (int) $libro['id_libro'] ?>"
                    >
                <?php endif; ?>

                <div class="card admin-form-card mb-4">
                    <h5 class="mb-3">Información bibliográfica</h5>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="titulo" class="form-label">
                                Título
                            </label>

                            <input
                                type="text"
                                id="titulo"
                                name="titulo"
                                class="form-control"
                                maxlength="250"
                                required
                                value="<?= $escapar(
                                    $valor('titulo', $old, $libro)
                                ) ?>"
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="isbn" class="form-label">
                                ISBN
                            </label>

                            <input
                                type="text"
                                id="isbn"
                                name="isbn"
                                class="form-control"
                                maxlength="30"
                                value="<?= $escapar(
                                    $valor('isbn', $old, $libro)
                                ) ?>"
                            >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="autor" class="form-label">
                                Autor
                            </label>

                            <input
                                type="text"
                                id="autor"
                                name="autor"
                                class="form-control"
                                maxlength="200"
                                required
                                value="<?= $escapar(
                                    $valor('autor', $old, $libro)
                                ) ?>"
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="editorial" class="form-label">
                                Editorial
                            </label>

                            <input
                                type="text"
                                id="editorial"
                                name="editorial"
                                class="form-control"
                                maxlength="150"
                                value="<?= $escapar(
                                    $valor('editorial', $old, $libro)
                                ) ?>"
                            >
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="anio_publicacion" class="form-label">
                                Año
                            </label>

                            <input
                                type="number"
                                id="anio_publicacion"
                                name="anio_publicacion"
                                class="form-control"
                                min="1000"
                                max="2100"
                                value="<?= $escapar(
                                    $valor('anio_publicacion', $old, $libro)
                                ) ?>"
                            >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_categoria" class="form-label">
                                Categoría
                            </label>

                            <select
                                id="id_categoria"
                                name="id_categoria"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    Seleccione una categoría
                                </option>

                                <?php foreach ($categorias as $categoria): ?>
                                    <option
                                        value="<?= (int) $categoria['id_categoria'] ?>"
                                        <?= (int) $valor(
                                            'id_categoria',
                                            $old,
                                            $libro
                                        ) ===
                                        (int) $categoria['id_categoria']
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= $escapar($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="costo" class="form-label">
                                Costo
                            </label>

                            <input
                                type="number"
                                id="costo"
                                name="costo"
                                class="form-control"
                                min="0"
                                step="0.01"
                                required
                                value="<?= $escapar(
                                    $valor('costo', $old, $libro) !== ''
                                        ? $valor('costo', $old, $libro)
                                        : '0.00'
                                ) ?>"
                            >
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="ubicacion_fisica" class="form-label">
                                Ubicación física
                            </label>

                            <input
                                type="text"
                                id="ubicacion_fisica"
                                name="ubicacion_fisica"
                                class="form-control"
                                maxlength="200"
                                placeholder="Ejemplo: Estante A-12"
                                value="<?= $escapar(
                                    $valor('ubicacion_fisica', $old, $libro)
                                ) ?>"
                            >
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            Descripción
                        </label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            class="form-control"
                            rows="5"
                        ><?= $escapar(
                            $valor('descripcion', $old, $libro)
                        ) ?></textarea>
                    </div>
                </div>

                <div class="card admin-form-card mb-4">
                    <h5 class="mb-3">Existencias y temas</h5>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label
                                for="existencias_totales"
                                class="form-label"
                            >
                                Existencias totales
                            </label>

                            <input
                                type="number"
                                id="existencias_totales"
                                name="existencias_totales"
                                class="form-control"
                                min="0"
                                required
                                value="<?= $escapar(
                                    $valor(
                                        'existencias_totales',
                                        $old,
                                        $libro
                                    ) !== ''
                                        ? $valor(
                                            'existencias_totales',
                                            $old,
                                            $libro
                                        )
                                        : 0
                                ) ?>"
                            >
                        </div>

                        <div class="col-md-3 mb-3">
                            <label
                                for="existencias_disponibles"
                                class="form-label"
                            >
                                Disponibles
                            </label>

                            <input
                                type="number"
                                id="existencias_disponibles"
                                name="existencias_disponibles"
                                class="form-control"
                                min="0"
                                required
                                value="<?= $escapar(
                                    $valor(
                                        'existencias_disponibles',
                                        $old,
                                        $libro
                                    ) !== ''
                                        ? $valor(
                                            'existencias_disponibles',
                                            $old,
                                            $libro
                                        )
                                        : 0
                                ) ?>"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block">
                                Temas
                            </label>

                            <div class="row g-2">
                                <?php foreach ($temas as $tema): ?>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input
                                                type="checkbox"
                                                id="tema_<?= (int) $tema['id_tema'] ?>"
                                                name="id_temas[]"
                                                value="<?= (int) $tema['id_tema'] ?>"
                                                class="form-check-input"
                                                <?= in_array(
                                                    (int) $tema['id_tema'],
                                                    $temasSeleccionados,
                                                    true
                                                )
                                                    ? 'checked'
                                                    : '' ?>
                                            >

                                            <label
                                                for="tema_<?= (int) $tema['id_tema'] ?>"
                                                class="form-check-label"
                                            >
                                                <?= $escapar($tema['nombre']) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (empty($temas)): ?>
                                <div class="alert alert-warning mb-0">
                                    No hay temas activos registrados.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card admin-form-card mb-4">
                    <h5 class="mb-3">Portada del libro</h5>

                    <div class="row align-items-center">
                        <?php if (
                            $esEdicion &&
                            !empty($libro['thumbnail_ruta'])
                        ): ?>
                            <div class="col-md-3 mb-3">
                                <img
                                    src="<?= $escapar(
                                        Config::asset(
                                            $libro['thumbnail_ruta']
                                        )
                                    ) ?>"
                                    alt="Portada actual"
                                    class="img-fluid rounded shadow-sm"
                                    style="max-height: 240px;"
                                >
                            </div>
                        <?php endif; ?>

                        <div class="<?= $esEdicion && !empty($libro['thumbnail_ruta'])
                            ? 'col-md-9'
                            : 'col-md-12' ?> mb-3">
                            <label for="imagen" class="form-label">
                                <?= $esEdicion
                                    ? 'Reemplazar imagen'
                                    : 'Imagen de portada' ?>
                            </label>

                            <input
                                type="file"
                                id="imagen"
                                name="imagen"
                                class="form-control"
                                accept="image/jpeg,image/png,image/webp"
                            >

                            <div class="form-text">
                                JPG, PNG o WEBP. Máximo 5 MB. El sistema
                                creará la imagen redimensionada y su miniatura.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin-form-actions d-flex justify-content-end gap-2">
                    <a
                        href="<?= Config::url('libros') ?>"
                        class="btn btn-secondary"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        <?= empty($categorias) ? 'disabled' : '' ?>
                    >
                        <i class="fa-solid fa-floppy-disk"></i>

                        <?= $esEdicion
                            ? 'Actualizar libro'
                            : 'Guardar libro' ?>
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
