<?php

$bondades = $bondades ?? [];
$fortalezas = $fortalezas ?? [];
$desarrolladores = $desarrolladores ?? [];

require_once __DIR__ . '/../Partials/header.php';

?>

<nav class="navbar navbar-expand-lg client-navbar">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold">
            <i class="fa-solid fa-book-open-reader"></i>
            Biblioteca Online
        </span>
        <div class="d-flex gap-2">
            <a href="<?= App\Config\Config::url('portal/login') ?>" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-user-graduate"></i> Portal del estudiante
            </a>
            <a href="<?= App\Config\Config::url('/') ?>" class="btn btn-dark btn-sm">
                <i class="fa-solid fa-lock"></i> Administración
            </a>
        </div>
    </div>
</nav>

<div class="portal-hero m-4 text-center">
    <h2 class="mb-2" style="font-size:2rem;">Biblioteca Online</h2>
    <p class="mb-0" style="font-size:1.1rem;">
        La forma más simple de buscar, reservar y solicitar libros de tu institución, desde cualquier lugar.
    </p>
</div>

<div class="container-fluid px-4 pb-5">

    <h3 class="text-center mb-4">¿Qué puedes hacer aquí?</h3>
    <div class="row g-4 mb-5">
        <?php foreach ($bondades as $b): ?>
            <div class="col-md-4">
                <div class="card p-4 h-100 text-center">
                    <i class="<?= $b['icono'] ?> mb-3" style="color:var(--caramel); font-size:32px;"></i>
                    <h5><?= htmlspecialchars($b['titulo']) ?></h5>
                    <p class="mb-0"><?= htmlspecialchars($b['texto']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h3 class="text-center mb-4">Fortalezas del sistema</h3>
    <div class="row g-3 mb-5">
        <?php foreach ($fortalezas as $f): ?>
            <div class="col-md-6 col-lg-4">
                <div class="stat-chip">
                    <i class="<?= $f['icono'] ?>"></i>
                    <span class="fw-bold"><?= htmlspecialchars($f['texto']) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h3 class="text-center mb-4">El equipo detrás del proyecto</h3>
    <div class="row g-4 mb-3">
        <?php foreach ($desarrolladores as $dev): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card p-4 text-center h-100">
                    <i class="fa-solid fa-user-graduate mx-auto mb-2" style="color:var(--caramel); font-size:38px;"></i>
                    <h6 class="mb-1"><?= htmlspecialchars($dev['nombre']) ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($dev['rol']) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../Partials/footer.php'; ?>