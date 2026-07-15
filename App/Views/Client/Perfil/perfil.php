<?php

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$perfil = $perfil ?? $estudiante ?? [];
$statsPerfil = $statsPerfil ?? [];

$escapar = static function (mixed $valor): string {
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8',
    );
};

$nombreCompleto = $perfil['nombre_portal']
    ?? trim(
        ($perfil['primer_nombre'] ?? '') . ' ' .
        ($perfil['segundo_nombre'] ?? '') . ' ' .
        ($perfil['primer_apellido'] ?? '') . ' ' .
        ($perfil['segundo_apellido'] ?? ''),
    );

$tipoUsuario = $perfil['tipo_usuario'] ?? 'Usuario regular';
$detallePerfil = $perfil['detalle_perfil']
    ?? $perfil['carrera']
    ?? 'No especificado';
?>

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fa-solid fa-id-card"></i>
                Mi perfil
            </h2>

            <p class="text-muted mb-0">
                Información asociada a tu cuenta regular.
            </p>
        </div>

        <span class="badge bg-primary px-3 py-2">
            <?= $escapar($tipoUsuario) ?>
        </span>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div
                        class="d-grid place-items-center rounded-circle bg-primary text-white"
                        style="width:72px; height:72px; display:grid; place-items:center; font-size:28px;"
                    >
                        <i class="fa-solid <?= $tipoUsuario === 'Profesor' ? 'fa-chalkboard-user' : 'fa-user-graduate' ?>"></i>
                    </div>

                    <div>
                        <h4 class="mb-1">
                            <?= $escapar($nombreCompleto ?: 'Usuario') ?>
                        </h4>

                        <p class="text-muted mb-0">
                            <?= $escapar($detallePerfil) ?>
                        </p>
                    </div>
                </div>

                <dl class="row mb-0">
                    <dt class="col-sm-4">Tipo de usuario</dt>
                    <dd class="col-sm-8"><?= $escapar($tipoUsuario) ?></dd>

                    <dt class="col-sm-4">CIP</dt>
                    <dd class="col-sm-8"><?= $escapar($perfil['cip'] ?? 'No registrado') ?></dd>

                    <dt class="col-sm-4">Usuario</dt>
                    <dd class="col-sm-8"><?= $escapar($perfil['usuario'] ?? 'No registrado') ?></dd>

                    <dt class="col-sm-4">Correo</dt>
                    <dd class="col-sm-8"><?= $escapar($perfil['correo'] ?? 'No registrado') ?></dd>

                    <dt class="col-sm-4">
                        <?= $tipoUsuario === 'Profesor' ? 'Departamento' : 'Carrera' ?>
                    </dt>
                    <dd class="col-sm-8"><?= $escapar($detallePerfil) ?></dd>

                    <dt class="col-sm-4">Facultad</dt>
                    <dd class="col-sm-8"><?= $escapar($perfil['facultad'] ?? 'No especificada') ?></dd>

                    <?php if ($tipoUsuario === 'Profesor'): ?>
                        <dt class="col-sm-4">Especialidad</dt>
                        <dd class="col-sm-8"><?= $escapar($perfil['especialidad'] ?? 'No especificada') ?></dd>
                    <?php elseif (!empty($perfil['fecha_nacimiento'])): ?>
                        <dt class="col-sm-4">Fecha de nacimiento</dt>
                        <dd class="col-sm-8"><?= $escapar($perfil['fecha_nacimiento']) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="row g-3">
                <div class="col-12">
                    <div class="stat-chip">
                        <i class="fa-solid fa-calendar-check"></i>
                        <div>
                            <div class="stat-value">
                                <?= (int) ($statsPerfil['prestamos_activos'] ?? 0) ?>
                            </div>
                            <div class="stat-label">Préstamos activos</div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="stat-chip">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <div>
                            <div class="stat-value">
                                <?= (int) ($statsPerfil['prestamos_historial'] ?? 0) ?>
                            </div>
                            <div class="stat-label">Movimientos históricos</div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="stat-chip">
                        <i class="fa-solid fa-book-circle-plus"></i>
                        <div>
                            <div class="stat-value">
                                <?= (int) ($statsPerfil['solicitudes'] ?? 0) ?>
                            </div>
                            <div class="stat-label">Solicitudes enviadas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
