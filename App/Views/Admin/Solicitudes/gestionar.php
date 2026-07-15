<?php

use App\Config\Config;
use App\Helpers\Session;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$solicitud = $solicitud ?? [];
$estados = $estados ?? [];

$error = Session::getFlash('error');

$old = Session::getFlash('old_solicitud_gestion') ?? [];

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$estadoActual = $old['estado'] ?? ($solicitud['estado'] ?? 'Pendiente');

$respuestaActual = $old['respuesta'] ?? ($solicitud['respuesta'] ?? '');

$nombreSolicitante = trim(
    ($solicitud['estudiante_nombre'] ??
        ($solicitud['profesor_nombre'] ?? ($solicitud['nombre_usuario'] ?? ''))) .
        ' ' .
        ($solicitud['estudiante_segundo_nombre'] ?? ($solicitud['profesor_segundo_nombre'] ?? '')) .
        ' ' .
        ($solicitud['estudiante_apellido'] ?? ($solicitud['profesor_apellido'] ?? '')) .
        ' ' .
        ($solicitud['estudiante_segundo_apellido'] ??
            ($solicitud['profesor_segundo_apellido'] ?? '')),
);

if ($nombreSolicitante === '') {
    $nombreSolicitante = 'Usuario sin nombre';
}

$cipSolicitante = $solicitud['cip_estudiante'] ?? ($solicitud['cip_profesor'] ?? 'No registrado');

$tipoUsuario = $solicitud['tipo_usuario'] ?? 'Usuario';
?>

<div class="container-fluid admin-page admin-page-solicitudes">
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
                        Gestionar solicitud
                    </h2>

                    <p class="text-muted mb-0">
                        Solicitud #
                        <?= (int) ($solicitud['id_solicitud'] ?? 0) ?>
                    </p>
                </div>

                <a
                    href="<?= Config::url('solicitudes') ?>"
                    class="btn btn-secondary"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger admin-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?= $escapar($error) ?>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card admin-form-card h-100">
                        <h5 class="mb-4">
                            <i class="fa-solid fa-book-open-reader"></i>
                            Información de la solicitud
                        </h5>

                        <div class="mb-3">
                            <small class="text-muted d-block">
                                Solicitante
                            </small>

                            <strong>
                                <?= $escapar($nombreSolicitante) ?>
                            </strong>

                            <div class="text-muted">
                                <?= $escapar($tipoUsuario) ?>

                                · Usuario:

                                <?= $escapar($solicitud['usuario'] ?? 'No registrado') ?>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    CIP
                                </small>

                                <strong>
                                    <?= $escapar($cipSolicitante) ?>
                                </strong>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    Correo
                                </small>

                                <strong>
                                    <?= $escapar($solicitud['correo'] ?? 'No registrado') ?>
                                </strong>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <small class="text-muted d-block">
                                Título solicitado
                            </small>

                            <strong>
                                <?= $escapar($solicitud['titulo_libro'] ?? 'Sin título') ?>
                            </strong>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    Autor
                                </small>

                                <strong>
                                    <?= $escapar($solicitud['autor'] ?? 'No especificado') ?>
                                </strong>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    Área
                                </small>

                                <strong>
                                    <?= $escapar($solicitud['materia'] ?? 'No especificada') ?>
                                </strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">
                                Fecha de solicitud
                            </small>

                            <strong>
                                <?= $escapar($solicitud['fecha_solicitud'] ?? 'No registrada') ?>
                            </strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">
                                Motivo o descripción
                            </small>

                            <div class="border rounded p-3 bg-light">
                                <?= nl2br(
                                    $escapar($solicitud['motivo_interes'] ?? 'Sin descripción.'),
                                ) ?>
                            </div>
                        </div>

                        <?php if (!empty($solicitud['nombre_responde'])): ?>
                            <hr>

                            <div class="mb-2">
                                <small class="text-muted d-block">
                                    Última gestión realizada por
                                </small>

                                <strong>
                                    <?= $escapar($solicitud['nombre_responde']) ?>
                                </strong>

                                <?php if (!empty($solicitud['usuario_responde'])): ?>
                                    <span class="text-muted">
                                        — <?= $escapar($solicitud['usuario_responde']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($solicitud['fecha_respuesta'])): ?>
                                <div>
                                    <small class="text-muted">
                                        Fecha:
                                        <?= $escapar($solicitud['fecha_respuesta']) ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-6">
                    <form
                        method="POST"
                        action="<?= Config::url('solicitudes/actualizar') ?>"
                        class="card admin-form-card h-100"
                    >
                        <input
                            type="hidden"
                            name="id_solicitud"
                            value="<?= (int) ($solicitud['id_solicitud'] ?? 0) ?>"
                        >

                        <h5 class="mb-4">
                            <i class="fa-solid fa-clipboard-check"></i>
                            Respuesta administrativa
                        </h5>

                        <div class="mb-3">
                            <label
                                for="estado"
                                class="form-label"
                            >
                                Estado
                            </label>

                            <select
                                id="estado"
                                name="estado"
                                class="form-select"
                                required
                            >
                                <?php foreach ($estados as $estado): ?>
                                    <option
                                        value="<?= $escapar($estado) ?>"
                                        <?= $estadoActual === $estado ? 'selected' : '' ?>
                                    >
                                        <?= $escapar($estado) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="form-text">
                                Selecciona el estado actual de la solicitud.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label
                                for="respuesta"
                                class="form-label"
                            >
                                Respuesta para el solicitante
                            </label>

                            <textarea
                                id="respuesta"
                                name="respuesta"
                                class="form-control"
                                rows="10"
                                maxlength="1000"
                                placeholder="Escribe el resultado de la revisión o los pasos que se tomarán."
                            ><?= $escapar($respuestaActual) ?></textarea>

                            <div class="form-text">
                                La respuesta será visible desde el portal
                                del estudiante.
                            </div>
                        </div>

                        <div class="alert alert-info admin-alert">
                            <strong>Estados disponibles:</strong>

                            <div class="mt-2">
                                <div>
                                    <strong>Pendiente:</strong>
                                    todavía no se ha revisado.
                                </div>

                                <div>
                                    <strong>En revisión:</strong>
                                    se está buscando disponibilidad.
                                </div>

                                <div>
                                    <strong>Aprobada:</strong>
                                    la administración autorizó la solicitud.
                                </div>

                                <div>
                                    <strong>Rechazada:</strong>
                                    la solicitud no será procesada.
                                </div>

                                <div>
                                    <strong>Adquirida:</strong>
                                    el libro ya fue conseguido.
                                </div>
                            </div>
                        </div>

                        <div
                            class="admin-form-actions d-flex justify-content-end gap-2 mt-auto"
                        >
                            <a
                                href="<?= Config::url('solicitudes') ?>"
                                class="btn btn-secondary"
                            >
                                Cancelar
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="fa-solid fa-floppy-disk"></i>
                                Guardar gestión
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
