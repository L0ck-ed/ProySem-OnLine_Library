<?php

/* Vista de solo interfaz. Datos de ejemplo hasta conectar con el modelo Estudiante. */

$estudiante = $estudiante ?? [
    'cip'               => '8-1023-2265',
    'primer_nombre'     => 'Anthony',
    'segundo_nombre'    => '',
    'primer_apellido'   => 'Castillo',
    'segundo_apellido'  => '',
    'fecha_nacimiento'  => '2002-01-02',
    'carrera'           => 'Licenciatura en Desarrollo y Gestión de Software',
    'estado'            => 'Activo',
];

$statsPerfil = $statsPerfil ?? [
    'prestamos_activos'  => 2,
    'prestamos_historial' => 5,
    'solicitudes'        => 1,
];

$nombreCompleto = trim($estudiante['primer_nombre'] . ' ' . $estudiante['segundo_nombre'] . ' ' . $estudiante['primer_apellido'] . ' ' . $estudiante['segundo_apellido']);

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

?>

<div class="container-fluid py-4 px-4">

    <h2><i class="fa-solid fa-id-card"></i> Mi perfil</h2>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <i class="fa-solid fa-user-graduate logo mx-auto"></i>
                <h4 class="mt-3 mb-0"><?= htmlspecialchars($nombreCompleto) ?></h4>
                <p class="mb-1">CIP <?= htmlspecialchars($estudiante['cip']) ?></p>
                <span class="badge bg-success mx-auto"><?= htmlspecialchars($estudiante['estado']) ?></span>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-4 mb-4">
                <h5 class="mb-3">Información académica</h5>
                <div class="row g-3">
                    <div class="col-6">
                        <small class="d-block text-muted">Carrera</small>
                        <span class="fw-bold"><?= htmlspecialchars($estudiante['carrera']) ?></span>
                    </div>
                    <div class="col-6">
                        <small class="d-block text-muted">Fecha de nacimiento</small>
                        <span class="fw-bold"><?= htmlspecialchars($estudiante['fecha_nacimiento']) ?></span>
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