<?php
use App\Helpers\Session;

// Los datos vienen del controlador
$estudiante = $estudiante ?? [];
$statsPerfil = $statsPerfil ?? [
    'prestamos_activos' => 0,
    'prestamos_historial' => 0,
    'solicitudes' => 0,
];

// Construir nombre completo
$nombreCompleto = trim(
    ($estudiante['primer_nombre'] ?? '') . ' ' .
    ($estudiante['segundo_nombre'] ?? '') . ' ' .
    ($estudiante['primer_apellido'] ?? '') . ' ' .
    ($estudiante['segundo_apellido'] ?? '')
);

// Si no hay nombre, usar el de sesión
if (empty($nombreCompleto)) {
    $nombreCompleto = $nombreEstudiante ?? 'Estudiante';
}

$cip = $estudiante['cip'] ?? $cipSesion ?? '000-0000-0000';
$carrera = $estudiante['carrera'] ?? $carreraSesion ?? 'Carrera no especificada';
$fechaNacimiento = isset($estudiante['fecha_nacimiento']) ? date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])) : 'No registrada';
$estado = $estudiante['estado'] ?? 'Activo';

// Mensajes flash
$error = Session::getFlash('error_perfil');
$exito = Session::getFlash('exito_perfil');

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';
?>

<div class="container-fluid py-4 px-4">

    <h2><i class="fa-solid fa-id-card"></i> Mi perfil</h2>

    <?php if ($exito): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($exito) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <i class="fa-solid fa-user-graduate logo mx-auto"></i>
                <h4 class="mt-3 mb-0"><?= htmlspecialchars($nombreCompleto) ?></h4>
                <p class="mb-1">CIP <?= htmlspecialchars($cip) ?></p>
                <span class="badge bg-<?= $estado === 'Activo' ? 'success' : 'secondary' ?> mx-auto">
                    <?= htmlspecialchars($estado) ?>
                </span>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-4 mb-4">
                <h5 class="mb-3">Información académica</h5>
                <div class="row g-3">
                    <div class="col-6">
                        <small class="d-block text-muted">Carrera</small>
                        <span class="fw-bold"><?= htmlspecialchars($carrera) ?></span>
                    </div>
                    <div class="col-6">
                        <small class="d-block text-muted">Fecha de nacimiento</small>
                        <span class="fw-bold"><?= htmlspecialchars($fechaNacimiento) ?></span>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div class="stat-chip">
                        <i class="fa-solid fa-calendar-check"></i>
                        <div>
                            <div class="stat-value"><?= $statsPerfil['prestamos_activos'] ?></div>
                            <div class="stat-label">Préstamos activos</div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-chip">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <div>
                            <div class="stat-value"><?= $statsPerfil['prestamos_historial'] ?></div>
                            <div class="stat-label">En historial</div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-chip">
                        <i class="fa-solid fa-circle-plus"></i>
                        <div>
                            <div class="stat-value"><?= $statsPerfil['solicitudes'] ?></div>
                            <div class="stat-label">Solicitudes</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <h5 class="mb-2">Seguridad</h5>
                <p class="mb-3">Puedes solicitar el cambio de tu PIN de acceso en administración de la biblioteca.</p>
                <button class="btn btn-secondary" type="button" disabled>
                    <i class="fa-solid fa-key"></i> Cambiar PIN (próximamente)
                </button>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>