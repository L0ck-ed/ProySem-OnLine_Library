<?php

$nombreEstudiante = $nombreEstudiante ?? 'Estudiante';
$cipSesion = $cipSesion ?? '';

$areas = $areas ?? [];
$misSolicitudes = $misSolicitudes ?? [];

$errorSolicitud = $errorSolicitud ?? null;
$exitoSolicitud = $exitoSolicitud ?? null;
$tituloAnterior = $tituloAnterior ?? '';
$areaAnterior = $areaAnterior ?? '';
$descripcionAnterior = $descripcionAnterior ?? '';

function badgeEstadoSolicitud(string $estado): string
{
    return match ($estado) {
        'Aprobado'  => 'bg-success',
        'Rechazado' => 'badge-existencias-agotado',
        'Revisado'  => 'badge-categoria',
        default     => 'bg-secondary',
    };
}

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

?>

<div class="container-fluid py-4 px-4">

    <h2><i class="fa-solid fa-circle-plus"></i> Solicitar un libro</h2>
    <p class="mb-4">¿No encontraste el libro que necesitas en el catálogo? Cuéntanos qué buscas y la administración revisará tu solicitud.</p>

    <?php if ($errorSolicitud): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorSolicitud) ?></div>
    <?php endif; ?>

    <?php if ($exitoSolicitud): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($exitoSolicitud) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card p-4">
                <form action="<?= App\Config\Config::url('portal/solicitudes') ?>" method="POST">
                    <div class="mb-3">
                        <label>Título del libro</label>
                        <input type="text" name="titulo_libro" class="form-control" value="<?= htmlspecialchars($tituloAnterior) ?>" placeholder="Ej: Introduction to Algorithms" required>
                    </div>

                    <div class="mb-3">
                        <label>Área</label>
                        <select name="area" class="form-select" required>
                            <option value="">Selecciona un área</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= htmlspecialchars($area) ?>" <?= $areaAnterior === $area ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($area) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Descripción o motivo (opcional)</label>
                        <textarea name="descripcion" class="form-control" rows="4" placeholder="Cuéntanos para qué lo necesitas o dónde lo viste"><?= htmlspecialchars($descripcionAnterior) ?></textarea>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">
                        <i class="fa-solid fa-paper-plane"></i> Enviar solicitud
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <h5 class="mb-3">Mis solicitudes</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Área</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($misSolicitudes)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Aún no has enviado solicitudes.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($misSolicitudes as $s): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($s['titulo']) ?></td>
                                <td><?= htmlspecialchars($s['area']) ?></td>
                                <td><?= htmlspecialchars($s['fecha']) ?></td>
                                <td><span class="badge <?= badgeEstadoSolicitud($s['estado']) ?>"><?= htmlspecialchars($s['estado']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>