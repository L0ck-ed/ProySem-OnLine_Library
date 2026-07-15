<?php

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$perfil = $perfil ?? $estudiante ?? [];
$statsPerfil = $statsPerfil ?? [];

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$nombreCompleto = $perfil['nombre_portal'] ?? trim(
    ($perfil['primer_nombre'] ?? '') . ' ' .
    ($perfil['segundo_nombre'] ?? '') . ' ' .
    ($perfil['primer_apellido'] ?? '') . ' ' .
    ($perfil['segundo_apellido'] ?? ''),
);

$tipoUsuario = $perfil['tipo_usuario'] ?? 'Usuario regular';
$detallePerfil = $perfil['detalle_perfil'] ?? $perfil['carrera'] ?? 'No especificado';
$esProfesor = $tipoUsuario === 'Profesor';
$iniciales = '';
foreach (array_slice(array_filter(explode(' ', trim($nombreCompleto))), 0, 2) as $parteNombre) {
    $iniciales .= strtoupper(substr($parteNombre, 0, 1));
}
$iniciales = $iniciales !== '' ? $iniciales : 'U';

$camposPerfil = [
    ['icono' => 'fa-solid fa-user-tag', 'etiqueta' => 'Tipo de usuario', 'valor' => $tipoUsuario],
    ['icono' => 'fa-solid fa-id-card', 'etiqueta' => 'CIP', 'valor' => $perfil['cip'] ?? 'No registrado'],
    ['icono' => 'fa-solid fa-at', 'etiqueta' => 'Usuario', 'valor' => $perfil['usuario'] ?? 'No registrado'],
    ['icono' => 'fa-solid fa-envelope', 'etiqueta' => 'Correo', 'valor' => $perfil['correo'] ?? 'No registrado'],
    ['icono' => $esProfesor ? 'fa-solid fa-building' : 'fa-solid fa-graduation-cap', 'etiqueta' => $esProfesor ? 'Departamento' : 'Carrera', 'valor' => $detallePerfil],
    ['icono' => 'fa-solid fa-building-columns', 'etiqueta' => 'Facultad', 'valor' => $perfil['facultad'] ?? 'No especificada'],
];

if ($esProfesor) {
    $camposPerfil[] = ['icono' => 'fa-solid fa-award', 'etiqueta' => 'Especialidad', 'valor' => $perfil['especialidad'] ?? 'No especificada'];
} elseif (!empty($perfil['fecha_nacimiento'])) {
    $camposPerfil[] = ['icono' => 'fa-solid fa-cake-candles', 'etiqueta' => 'Fecha de nacimiento', 'valor' => $perfil['fecha_nacimiento']];
}
?>

<main class="client-page">
    <div class="client-page-inner">
        <header class="client-page-header">
            <div class="client-page-title-wrap">
                <span class="client-page-title-icon"><i class="fa-solid fa-id-card"></i></span>
                <div>
                    <span class="client-section-kicker">Tu cuenta</span>
                    <h1>Mi perfil</h1>
                    <p>Consulta la información vinculada a tu acceso a la biblioteca.</p>
                </div>
            </div>
        </header>

        <section class="client-profile-hero">
            <div class="client-profile-avatar"><?= $escapar($iniciales) ?></div>
            <div class="client-profile-main-copy">
                <span class="client-profile-role">
                    <i class="fa-solid <?= $esProfesor ? 'fa-chalkboard-user' : 'fa-user-graduate' ?>"></i>
                    <?= $escapar($tipoUsuario) ?>
                </span>
                <h2><?= $escapar($nombreCompleto ?: 'Usuario') ?></h2>
                <p><?= $escapar($detallePerfil) ?></p>
            </div>
            <div class="client-profile-status">
                <span><i class="fa-solid fa-circle-check"></i> Cuenta activa</span>
                <small>CIP <?= $escapar($perfil['cip'] ?? 'No registrado') ?></small>
            </div>
        </section>

        <section class="client-profile-stats">
            <article>
                <span><i class="fa-solid fa-calendar-check"></i></span>
                <div><strong><?= (int) ($statsPerfil['prestamos_activos'] ?? 0) ?></strong><small>Préstamos activos</small></div>
            </article>
            <article>
                <span><i class="fa-solid fa-clock-rotate-left"></i></span>
                <div><strong><?= (int) ($statsPerfil['prestamos_historial'] ?? 0) ?></strong><small>Movimientos históricos</small></div>
            </article>
            <article>
                <span><i class="fa-solid fa-circle-plus"></i></span>
                <div><strong><?= (int) ($statsPerfil['solicitudes'] ?? 0) ?></strong><small>Solicitudes enviadas</small></div>
            </article>
        </section>

        <div class="client-profile-layout">
            <section class="client-profile-info-card">
                <div class="client-card-heading">
                    <span><i class="fa-solid fa-address-card"></i></span>
                    <div>
                        <h2>Información personal y académica</h2>
                        <p>Datos registrados para identificar tu cuenta.</p>
                    </div>
                </div>

                <div class="client-profile-info-grid">
                    <?php foreach ($camposPerfil as $campo): ?>
                        <article>
                            <span><i class="<?= $escapar($campo['icono']) ?>"></i></span>
                            <div>
                                <small><?= $escapar($campo['etiqueta']) ?></small>
                                <strong><?= $escapar($campo['valor']) ?></strong>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="client-profile-side-card">
                <span class="client-profile-side-icon"><i class="fa-solid fa-shield-halved"></i></span>
                <h2>Información protegida</h2>
                <p>Estos datos son administrados por la institución y se utilizan para validar tu acceso al servicio bibliotecario.</p>
                <div class="client-profile-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>Para corregir algún dato, comunícate con la administración de la biblioteca.</p>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
