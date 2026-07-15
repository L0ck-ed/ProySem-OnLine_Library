<?php

use App\Config\Config;
use App\Helpers\Session;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$tipo = $tipo ?? 'facultad';
$modo = $modo ?? 'crear';
$registro = $registro ?? [];
$facultades = $facultades ?? [];
$departamentos = $departamentos ?? [];
$old = Session::getFlash('old_estructura') ?? [];
$error = Session::getFlash('error');
$esEdicion = $modo === 'editar';

$escapar = static fn (mixed $valor): string =>
    htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$valor = static function (string $campo) use ($old, $registro): mixed {
    return array_key_exists($campo, $old) ? $old[$campo] : ($registro[$campo] ?? '');
};

$etiquetas = [
    'facultad' => [
        'singular' => 'facultad',
        'titulo' => 'Facultad',
        'icono' => 'fa-building-columns',
        'ayuda' => 'Unidad académica principal que agrupa departamentos y carreras.',
        'ejemplo' => 'Ejemplo: Facultad de Ingeniería Mecánica',
    ],
    'departamento' => [
        'singular' => 'departamento',
        'titulo' => 'Departamento',
        'icono' => 'fa-building',
        'ayuda' => 'Área académica o docente perteneciente a una facultad.',
        'ejemplo' => 'Ejemplo: Departamento de Energía y Ambiente',
    ],
    'carrera' => [
        'singular' => 'carrera',
        'titulo' => 'Carrera',
        'icono' => 'fa-graduation-cap',
        'ayuda' => 'Programa académico asignado a los estudiantes.',
        'ejemplo' => 'Ejemplo: Licenciatura en Ingeniería Mecánica',
    ],
];

$meta = $etiquetas[$tipo] ?? $etiquetas['facultad'];
$titulo = ($esEdicion ? 'Editar ' : 'Nueva ') . $meta['singular'];
$accion = $esEdicion
    ? Config::url('estructura-academica/actualizar')
    : Config::url('estructura-academica/guardar');
$idRegistro = (int) ($registro['id_' . $tipo] ?? $old['id_registro'] ?? 0);
$idFacultadSeleccionada = (int) $valor('id_facultad');
$idDepartamentoSeleccionado = (int) $valor('id_departamento');
?>

<div class="container-fluid admin-page admin-page-estructura">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main estructura-form-main">
            <header class="admin-page-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="mb-1"><?= $escapar($titulo) ?></h2>
                    <p><?= $escapar($meta['ayuda']) ?></p>
                </div>
                <a href="<?= Config::url('estructura-academica') ?>" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver
                </a>
            </header>

            <?php if ($error): ?>
                <div class="alert alert-danger admin-alert" role="alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <?= $escapar($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($tipo !== 'facultad' && empty($facultades)): ?>
                <div class="alert alert-warning admin-alert" role="alert">
                    <i class="fa-solid fa-circle-info"></i>
                    Primero debes registrar y activar al menos una facultad.
                </div>
            <?php endif; ?>

            <form
                method="POST"
                action="<?= $accion ?>"
                class="estructura-form"
                data-departamentos-url="<?= Config::url('estructura-academica/departamentos-por-facultad') ?>"
                data-departamento-seleccionado="<?= $idDepartamentoSeleccionado ?>"
            >
                <input type="hidden" name="tipo" value="<?= $escapar($tipo) ?>">
                <?php if ($esEdicion): ?>
                    <input type="hidden" name="id_registro" value="<?= $idRegistro ?>">
                <?php endif; ?>

                <section class="card admin-form-card estructura-form-card">
                    <div class="estructura-form-intro">
                        <span class="estructura-form-icon"><i class="fa-solid <?= $escapar($meta['icono']) ?>"></i></span>
                        <div>
                            <h3>Información de la <?= $escapar($meta['singular']) ?></h3>
                            <p>Los campos marcados como obligatorios deben completarse antes de guardar.</p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <?php if (in_array($tipo, ['departamento', 'carrera'], true)): ?>
                            <div class="col-12 <?= $tipo === 'carrera' ? 'col-lg-6' : '' ?>">
                                <label for="id_facultad" class="form-label">Facultad <span class="text-danger">*</span></label>
                                <select id="id_facultad" name="id_facultad" class="form-select" required>
                                    <option value="">Selecciona una facultad</option>
                                    <?php foreach ($facultades as $facultad): ?>
                                        <option
                                            value="<?= (int) $facultad['id_facultad'] ?>"
                                            <?= $idFacultadSeleccionada === (int) $facultad['id_facultad'] ? 'selected' : '' ?>
                                        >
                                            <?= $escapar($facultad['nombre'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if ($tipo === 'carrera'): ?>
                            <div class="col-12 col-lg-6">
                                <label for="id_departamento" class="form-label">Departamento relacionado</label>
                                <select id="id_departamento" name="id_departamento" class="form-select">
                                    <option value="">Sin departamento específico</option>
                                    <?php foreach ($departamentos as $departamento): ?>
                                        <option
                                            value="<?= (int) $departamento['id_departamento'] ?>"
                                            <?= $idDepartamentoSeleccionado === (int) $departamento['id_departamento'] ? 'selected' : '' ?>
                                        >
                                            <?= $escapar($departamento['nombre'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">El departamento es opcional, pero debe pertenecer a la facultad seleccionada.</div>
                            </div>
                        <?php endif; ?>

                        <div class="col-12">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                class="form-control"
                                maxlength="150"
                                required
                                placeholder="<?= $escapar($meta['ejemplo']) ?>"
                                value="<?= $escapar($valor('nombre')) ?>"
                            >
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea
                                id="descripcion"
                                name="descripcion"
                                class="form-control"
                                rows="5"
                                maxlength="500"
                                placeholder="Añade información que ayude a identificar este registro"
                            ><?= $escapar($valor('descripcion')) ?></textarea>
                            <div class="form-text">Máximo 500 caracteres.</div>
                        </div>
                    </div>
                </section>

                <div class="admin-form-actions d-flex justify-content-end gap-2">
                    <a href="<?= Config::url('estructura-academica') ?>" class="btn btn-secondary">Cancelar</a>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        <?= $tipo !== 'facultad' && empty($facultades) ? 'disabled' : '' ?>
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        <?= $esEdicion ? 'Actualizar' : 'Guardar' ?> <?= $escapar($meta['singular']) ?>
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<script src="<?= Config::assetsUrl() ?>/JavaScript/estructura-academica.js?v=2"></script>
<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
