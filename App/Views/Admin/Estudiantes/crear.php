<?php

use App\Config\Config;
use App\Helpers\Session;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$facultades = $facultades ?? [];
$usuariosDisponibles = $usuariosDisponibles ?? [];

$error = Session::getFlash('error');
$old = Session::getFlash('old_estudiante') ?? [];

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
                    <h2 class="mb-1">Nuevo estudiante</h2>
                    <p class="text-muted mb-0">
                        Registra la información académica de una cuenta
                        con el rol Estudiante.
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
                action="<?= Config::url('estudiantes/guardar') ?>"
            >
                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Cuenta de usuario</h5>

                    <div class="mb-3">
                        <label for="id_usuario" class="form-label">
                            Usuario con rol Estudiante
                        </label>

                        <select
                            id="id_usuario"
                            name="id_usuario"
                            class="form-select"
                            required
                        >
                            <option value="">Seleccione una cuenta</option>

                            <?php foreach ($usuariosDisponibles as $usuario): ?>
                                <option
                                    value="<?= (int) $usuario['id_usuario'] ?>"
                                    <?= (int) ($old['id_usuario'] ?? 0) ===
                                    (int) $usuario['id_usuario']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= $escapar(
                                        $usuario['nombre'] . ' — Usuario: ' . $usuario['usuario'],
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div class="form-text">
                            Solo aparecen usuarios activos con el rol
                            Estudiante que todavía no tienen información
                            académica registrada.
                        </div>
                    </div>

                    <?php if (empty($usuariosDisponibles)): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            No hay cuentas disponibles. Primero crea un
                            usuario y asígnale el rol Estudiante.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Información académica</h5>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="cip" class="form-label">CIP</label>

                            <input
                                type="text"
                                id="cip"
                                name="cip"
                                class="form-control"
                                maxlength="30"
                                placeholder="Ejemplo: 8-123-456"
                                required
                                value="<?= $escapar($old['cip'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="id_facultad" class="form-label">
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
                                <option value="">Seleccione una facultad</option>

                                <?php foreach ($facultades as $facultad): ?>
                                    <option
                                        value="<?= (int) $facultad['id_facultad'] ?>"
                                        <?= (int) ($old['id_facultad'] ?? 0) ===
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
                            <label for="id_carrera" class="form-label">
                                Carrera
                            </label>

                            <select
                                id="id_carrera"
                                name="id_carrera"
                                class="form-select"
                                data-carrera-seleccionada="<?= (int) ($old['id_carrera'] ?? 0) ?>"
                                required
                                disabled
                            >
                                <option value="">
                                    Primero seleccione una facultad
                                </option>
                            </select>

                            <div id="estadoCarreras" class="form-text">
                                Las carreras se cargarán según la facultad.
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
                            <label for="primer_nombre" class="form-label">
                                Primer nombre
                            </label>

                            <input
                                type="text"
                                id="primer_nombre"
                                name="primer_nombre"
                                class="form-control"
                                maxlength="50"
                                required
                                value="<?= $escapar($old['primer_nombre'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="segundo_nombre" class="form-label">
                                Segundo nombre
                            </label>

                            <input
                                type="text"
                                id="segundo_nombre"
                                name="segundo_nombre"
                                class="form-control"
                                maxlength="50"
                                value="<?= $escapar($old['segundo_nombre'] ?? '') ?>"
                            >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="primer_apellido" class="form-label">
                                Primer apellido
                            </label>

                            <input
                                type="text"
                                id="primer_apellido"
                                name="primer_apellido"
                                class="form-control"
                                maxlength="50"
                                required
                                value="<?= $escapar($old['primer_apellido'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="segundo_apellido" class="form-label">
                                Segundo apellido
                            </label>

                            <input
                                type="text"
                                id="segundo_apellido"
                                name="segundo_apellido"
                                class="form-control"
                                maxlength="50"
                                value="<?= $escapar($old['segundo_apellido'] ?? '') ?>"
                            >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_nacimiento" class="form-label">
                                Fecha de nacimiento
                            </label>

                            <input
                                type="date"
                                id="fecha_nacimiento"
                                name="fecha_nacimiento"
                                class="form-control"
                                max="<?= date('Y-m-d') ?>"
                                required
                                value="<?= $escapar($old['fecha_nacimiento'] ?? '') ?>"
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
                        <?= empty($usuariosDisponibles) || empty($facultades) ? 'disabled' : '' ?>
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        Guardar estudiante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script
    src="<?= Config::url('Public/Assets/JavaScript/estudiantes-crear.js') ?>"
></script>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
