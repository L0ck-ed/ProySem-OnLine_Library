<?php

/* Vista de solo interfaz. El formulario todavía no envía datos a un controlador. */

$nombreEstudiante = $nombreEstudiante ?? 'Anthony Castillo';
$cipSesion = $cipSesion ?? '8-1023-2265';

$areas = $areas ?? ['Matemáticas', 'Ciencias', 'Tecnologías', 'Deporte', 'Salud', 'Revistas Científicas'];

$misSolicitudes = $misSolicitudes ?? [
    ['titulo' => 'Introduction to Algorithms (4ta ed.)', 'area' => 'Tecnologías', 'fecha' => '2026-06-20', 'estado' => 'Pendiente'],
    ['titulo' => 'Anatomía y Fisiología Humana',          'area' => 'Salud',       'fecha' => '2026-05-14', 'estado' => 'Aprobado'],
];

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

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card p-4">
                <form>
                    <div class="mb-3">
                        <label>Título del libro</label>
                        <input type="text" class="form-control" placeholder="Ej: Introduction to Algorithms">
                    </div>

                    <div class="mb-3">
                        <label>Área</label>
                        <select class="form-select">
                            <option value="">Selecciona un área</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= htmlspecialchars($area) ?>"><?= htmlspecialchars($area) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Descripción o motivo (opcional)</label>
                        <textarea class="form-control" rows="4" placeholder="Cuéntanos para qué lo necesitas o dónde lo viste"></textarea>
                    </div>

                    <button class="btn btn-primary w-100" type="button" disabled>
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