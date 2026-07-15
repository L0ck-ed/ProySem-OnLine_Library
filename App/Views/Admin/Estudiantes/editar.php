<?php

use App\Config\Config;
use App\Helpers\Session;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$estudiante = $estudiante ?? [];
$facultades = $facultades ?? [];

$error = Session::getFlash('error');
$old = Session::getFlash('old_estudiante') ?? [];

$valor = static function (string $campo, array $old, array $estudiante): mixed {
    return array_key_exists($campo, $old) ? $old[$campo] : $estudiante[$campo] ?? '';
};

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Editar estudiante</h2>

                    <p class="text-muted mb-0">
                        Cuenta:
                        <?= $escapar(
                            $estudiante['nombre_usuario'] . ' — ' . $estudiante['usuario'],
                        ) ?>
                    </p>
                </div>

                <a
                    href="<?= Config::url('estudiantes') ?>"
                    class="btn btn-secondary"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= $escapar($error) ?>
                </div>
            <?php endif; ?>

            <form
                method="POST"
                action="<?= Config::url('estudiantes/actualizar') ?>"
            >
                <input
                    type="hidden"
                    name="id_estudiante"
                    value="<?= (int) $estudiante['id_estudiante'] ?>"
                >

                <div class="card p-4 mb-4">
                    <h5 class="mb-3">
                        Información académica
                    </h5>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label
                                for="cip"
                                class="form-label"
                            >
                                CIP
                            </label>

                            <input
                                type="text"
                                id="cip"
                                name="cip"
                                class="form-control"
                                maxlength="30"
                                required
                                value="<?= $escapar($valor('cip', $old, $estudiante)) ?>"
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label
                                for="id_facultad"
                                class="form-label"
                            >
                                Facultad
                            </label>

                            <select
                                id="id_facultad"
                                name="id_facultad"
                                class="form-select"
                                data-url-carreras="<?= $escapar(
                                    Config::url('estudiantes/carreras-por-facultad'),
                                ) ?>"
                                required
                            >
                                <option value="">
                                    Seleccione una facultad
                                </option>

                                <?php foreach ($facultades as $facultad): ?>
                                    <option
                                        value="<?= (int) $facultad['id_facultad'] ?>"
                                        <?= (int) $valor('id_facultad', $old, $estudiante) ===
                                        (int) $facultad['id_facultad']
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= $escapar($facultad['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label
                                for="id_carrera"
                                class="form-label"
                            >
                                Carrera
                            </label>

                            <select
                                id="id_carrera"
                                name="id_carrera"
                                class="form-select"
                                data-carrera-seleccionada="<?= (int) $valor(
                                    'id_carrera',
                                    $old,
                                    $estudiante,
                                ) ?>"
                                required
                                disabled
                            >
                                <option value="">
                                    Cargando carrera...
                                </option>
                            </select>

                            <div
                                id="estadoCarreras"
                                class="form-text"
                            >
                                Las carreras se cargarán según
                                la facultad.
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label
                                for="primer_nombre"
                                class="form-label"
                            >
                                Primer nombre
                            </label>

                            <input
                                type="text"
                                id="primer_nombre"
                                name="primer_nombre"
                                class="form-control"
                                maxlength="50"
                                required
                                value="<?= $escapar($valor('primer_nombre', $old, $estudiante)) ?>"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label
                                for="segundo_nombre"
                                class="form-label"
                            >
                                Segundo nombre
                            </label>

                            <input
                                type="text"
                                id="segundo_nombre"
                                name="segundo_nombre"
                                class="form-control"
                                maxlength="50"
                                value="<?= $escapar($valor('segundo_nombre', $old, $estudiante)) ?>"
                            >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label
                                for="primer_apellido"
                                class="form-label"
                            >
                                Primer apellido
                            </label>

                            <input
                                type="text"
                                id="primer_apellido"
                                name="primer_apellido"
                                class="form-control"
                                maxlength="50"
                                required
                                value="<?= $escapar(
                                    $valor('primer_apellido', $old, $estudiante),
                                ) ?>"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label
                                for="segundo_apellido"
                                class="form-label"
                            >
                                Segundo apellido
                            </label>

                            <input
                                type="text"
                                id="segundo_apellido"
                                name="segundo_apellido"
                                class="form-control"
                                maxlength="50"
                                value="<?= $escapar(
                                    $valor('segundo_apellido', $old, $estudiante),
                                ) ?>"
                            >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label
                                for="fecha_nacimiento"
                                class="form-label"
                            >
                                Fecha de nacimiento
                            </label>

                            <input
                                type="date"
                                id="fecha_nacimiento"
                                name="fecha_nacimiento"
                                class="form-control"
                                max="<?= date('Y-m-d') ?>"
                                required
                                value="<?= $escapar(
                                    $valor('fecha_nacimiento', $old, $estudiante),
                                ) ?>"
                            >
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a
                        href="<?= Config::url('estudiantes') ?>"
                        class="btn btn-secondary"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        Actualizar estudiante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script
    src="<?= Config::url('Assets/JavaScript/estudiantes-crear.js') ?>"
    defer
></script>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
