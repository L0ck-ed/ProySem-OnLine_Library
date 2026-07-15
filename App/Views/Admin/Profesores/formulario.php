<?php

use App\Config\Config;
use App\Helpers\Session;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$modo = $modo ?? 'crear';
$esEdicion = $modo === 'editar';

$profesor = $profesor ?? [];
$usuariosDisponibles = $usuariosDisponibles ?? [];
$facultades = $facultades ?? [];

$error = Session::getFlash('error');
$old = Session::getFlash('old_profesor') ?? [];

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};

$valor = static function (string $campo, array $old, array $profesor): mixed {
    return array_key_exists($campo, $old) ? $old[$campo] : $profesor[$campo] ?? '';
};

$titulo = $esEdicion ? 'Editar profesor' : 'Nuevo profesor';

$accion = $esEdicion ? Config::url('profesores/actualizar') : Config::url('profesores/guardar');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <div
                class="d-flex justify-content-between align-items-center mb-4"
            >
                <div>
                    <h2 class="mb-1">
                        <?= $escapar($titulo) ?>
                    </h2>

                    <p class="text-muted mb-0">
                        <?= $esEdicion
                            ? 'Actualiza la información profesional.'
                            : 'Vincula una cuenta con rol Profesor.' ?>
                    </p>
                </div>

                <a
                    href="<?= Config::url('profesores') ?>"
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
                action="<?= $accion ?>"
            >
                <?php if ($esEdicion): ?>
                    <input
                        type="hidden"
                        name="id_profesor"
                        value="<?= (int) $profesor['id_profesor'] ?>"
                    >

                    <div class="card p-4 mb-4">
                        <h5 class="mb-3">
                            Cuenta de usuario
                        </h5>

                        <p class="mb-0">
                            <strong>
                                <?= $escapar($profesor['nombre_usuario'] ?? '') ?>
                            </strong>

                            — Usuario:

                            <?= $escapar($profesor['usuario'] ?? '') ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="card p-4 mb-4">
                        <h5 class="mb-3">
                            Cuenta de usuario
                        </h5>

                        <div class="mb-3">
                            <label
                                for="id_usuario"
                                class="form-label"
                            >
                                Usuario con rol Profesor
                            </label>

                            <select
                                id="id_usuario"
                                name="id_usuario"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    Seleccione una cuenta
                                </option>

                                <?php foreach ($usuariosDisponibles as $usuario): ?>
                                    <option
                                        value="<?= (int) $usuario['id_usuario'] ?>"
                                        <?= (int) ($old['id_usuario'] ?? 0) ===
                                        (int) $usuario['id_usuario']
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= $escapar(
                                            $usuario['nombre'] .
                                                ' — Usuario: ' .
                                                $usuario['usuario'],
                                        ) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (empty($usuariosDisponibles)): ?>
                            <div class="alert alert-warning mb-0">
                                No hay cuentas disponibles. Primero crea
                                un usuario con el rol Profesor.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="card p-4 mb-4">
                    <h5 class="mb-3">
                        Información profesional
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
                                placeholder="Ejemplo: 8-123-456"
                                required
                                value="<?= $escapar($valor('cip', $old, $profesor)) ?>"
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
                                data-url-departamentos="<?= $escapar(
                                    Config::url('profesores/departamentos-por-facultad'),
                                ) ?>"
                                required
                            >
                                <option value="">
                                    Seleccione una facultad
                                </option>

                                <?php foreach ($facultades as $facultad): ?>
                                    <option
                                        value="<?= (int) $facultad['id_facultad'] ?>"
                                        <?= (int) $valor('id_facultad', $old, $profesor) ===
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
                                for="id_departamento"
                                class="form-label"
                            >
                                Departamento
                            </label>

                            <select
                                id="id_departamento"
                                name="id_departamento"
                                class="form-select"
                                data-departamento-seleccionado="<?= (int) $valor(
                                    'id_departamento',
                                    $old,
                                    $profesor,
                                ) ?>"
                                required
                                disabled
                            >
                                <option value="">
                                    Primero seleccione una facultad
                                </option>
                            </select>

                            <div
                                id="estadoDepartamentos"
                                class="form-text"
                            >
                                Los departamentos se cargarán según la facultad.
                            </div>
                        </div>
                    </div>

                    <?php if (empty($facultades)): ?>
                        <div class="alert alert-warning">
                            No hay facultades activas disponibles.
                        </div>
                    <?php endif; ?>

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
                                maxlength="60"
                                required
                                value="<?= $escapar($valor('primer_nombre', $old, $profesor)) ?>"
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
                                maxlength="60"
                                value="<?= $escapar($valor('segundo_nombre', $old, $profesor)) ?>"
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
                                maxlength="60"
                                required
                                value="<?= $escapar($valor('primer_apellido', $old, $profesor)) ?>"
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
                                maxlength="60"
                                value="<?= $escapar($valor('segundo_apellido', $old, $profesor)) ?>"
                            >
                        </div>
                    </div>

                    <div class="mb-3">
                        <label
                            for="especialidad"
                            class="form-label"
                        >
                            Especialidad
                        </label>

                        <input
                            type="text"
                            id="especialidad"
                            name="especialidad"
                            class="form-control"
                            maxlength="150"
                            placeholder="Ejemplo: Desarrollo de Software"
                            value="<?= $escapar($valor('especialidad', $old, $profesor)) ?>"
                        >
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a
                        href="<?= Config::url('profesores') ?>"
                        class="btn btn-secondary"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        <?= !$esEdicion && (empty($usuariosDisponibles) || empty($facultades))
                            ? 'disabled'
                            : '' ?>
                    >
                        <i class="fa-solid fa-floppy-disk"></i>

                        <?= $esEdicion ? 'Actualizar profesor' : 'Guardar profesor' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script
    src="<?= Config::url('Public/Assets/JavaScript/profesores-formulario.js') ?>"
></script>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>

