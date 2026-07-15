<?php

use App\Config\Config;
use App\Helpers\Session;
use App\Middleware\Auth;

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

$buscar = $buscar ?? '';
$facultades = $facultades ?? [];
$departamentos = $departamentos ?? [];
$carreras = $carreras ?? [];
$errorEstructura = $errorEstructura ?? '';
$success = Session::getFlash('success');
$error = Session::getFlash('error');

$escapar = static fn (mixed $valor): string =>
    htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$estadoBadge = static fn (mixed $estado): string => (int) $estado === 1
    ? '<span class="badge admin-status-badge status-active">Activa</span>'
    : '<span class="badge admin-status-badge status-inactive">Inactiva</span>';
?>

<div class="container-fluid admin-page admin-page-estructura">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <main class="col-md-10 admin-main estructura-main">
            <header class="admin-page-header estructura-page-header">
                <div>
                    <h2 class="mb-1">Estructura académica</h2>
                    <p>
                        Administra las facultades, departamentos y carreras disponibles en la Universidad.
                    </p>
                </div>

                <?php if (Auth::tienePermiso('estructura.crear')): ?>
                    <div class="estructura-header-actions">
                        <a href="<?= Config::url('estructura-academica/crear?tipo=facultad') ?>" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i>
                            Facultad
                        </a>
                        <a href="<?= Config::url('estructura-academica/crear?tipo=departamento') ?>" class="btn btn-secondary">
                            <i class="fa-solid fa-plus"></i>
                            Departamento
                        </a>
                        <a href="<?= Config::url('estructura-academica/crear?tipo=carrera') ?>" class="btn btn-success">
                            <i class="fa-solid fa-plus"></i>
                            Carrera
                        </a>
                    </div>
                <?php endif; ?>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success admin-alert" role="alert">
                    <i class="fa-solid fa-check"></i>
                    <?= $escapar($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error || $errorEstructura): ?>
                <div class="alert alert-danger admin-alert" role="alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <?= $escapar($error ?: $errorEstructura) ?>
                </div>
            <?php endif; ?>

            <section class="estructura-summary-grid" aria-label="Resumen de estructura académica">
                <a href="#facultades" class="estructura-summary-card">
                    <span class="estructura-summary-icon"><i class="fa-solid fa-building-columns"></i></span>
                    <div>
                        <small>Facultades</small>
                        <strong><?= count($facultades) ?></strong>
                        <span>Unidades académicas principales</span>
                    </div>
                </a>
                <a href="#departamentos" class="estructura-summary-card">
                    <span class="estructura-summary-icon"><i class="fa-solid fa-building"></i></span>
                    <div>
                        <small>Departamentos</small>
                        <strong><?= count($departamentos) ?></strong>
                        <span>Áreas docentes y académicas</span>
                    </div>
                </a>
                <a href="#carreras" class="estructura-summary-card">
                    <span class="estructura-summary-icon"><i class="fa-solid fa-graduation-cap"></i></span>
                    <div>
                        <small>Carreras</small>
                        <strong><?= count($carreras) ?></strong>
                        <span>Programas disponibles</span>
                    </div>
                </a>
            </section>

            <section class="card admin-form-card estructura-search-card">
                <form method="GET" action="<?= Config::url('estructura-academica') ?>" class="row g-3 align-items-end">
                    <div class="col-12 col-lg-9">
                        <label for="buscar" class="form-label">Buscar en toda la estructura</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input
                                type="search"
                                id="buscar"
                                name="buscar"
                                class="form-control"
                                value="<?= $escapar($buscar) ?>"
                                placeholder="Facultad, departamento, carrera o descripción"
                            >
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-filter"></i>
                            Filtrar
                        </button>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-1">
                        <a href="<?= Config::url('estructura-academica') ?>" class="btn btn-secondary w-100" title="Limpiar filtro">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    </div>
                </form>
            </section>

            <section class="card admin-list-card estructura-list-card" id="facultades">
                <div class="admin-card-heading estructura-card-heading">
                    <div>
                        <h2 class="admin-card-title mb-1">
                            <i class="fa-solid fa-building-columns"></i>
                            Facultades
                        </h2>
                        <p class="mb-0">Cada facultad agrupa departamentos, carreras y usuarios.</p>
                    </div>
                    <span class="estructura-count-pill"><?= count($facultades) ?> registros</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle admin-table">
                        <thead>
                            <tr>
                                <th>Facultad</th>
                                <th>Descripción</th>
                                <th class="text-center">Departamentos</th>
                                <th class="text-center">Carreras</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($facultades as $facultad): ?>
                                <tr>
                                    <td>
                                        <div class="estructura-name-cell">
                                            <span><i class="fa-solid fa-building-columns"></i></span>
                                            <strong><?= $escapar($facultad['nombre'] ?? '') ?></strong>
                                        </div>
                                    </td>
                                    <td class="estructura-description-cell">
                                        <?= $escapar($facultad['descripcion'] ?: 'Sin descripción') ?>
                                    </td>
                                    <td class="text-center"><span class="estructura-number-pill"><?= (int) ($facultad['total_departamentos'] ?? 0) ?></span></td>
                                    <td class="text-center"><span class="estructura-number-pill"><?= (int) ($facultad['total_carreras'] ?? 0) ?></span></td>
                                    <td><?= $estadoBadge($facultad['estado'] ?? 0) ?></td>
                                    <td>
                                        <?php $tipoFila = 'facultad'; $fila = $facultad; require __DIR__ . '/acciones.php'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($facultades)): ?>
                                <tr><td colspan="6"><div class="estructura-empty-row">No hay facultades que coincidan con la búsqueda.</div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card admin-list-card estructura-list-card" id="departamentos">
                <div class="admin-card-heading estructura-card-heading">
                    <div>
                        <h2 class="admin-card-title mb-1">
                            <i class="fa-solid fa-building"></i>
                            Departamentos
                        </h2>
                        <p class="mb-0">Relaciona a los docentes y organiza las áreas de cada facultad.</p>
                    </div>
                    <span class="estructura-count-pill"><?= count($departamentos) ?> registros</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle admin-table">
                        <thead>
                            <tr>
                                <th>Departamento</th>
                                <th>Facultad</th>
                                <th class="text-center">Profesores</th>
                                <th class="text-center">Carreras</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departamentos as $departamento): ?>
                                <tr>
                                    <td>
                                        <div class="estructura-name-cell">
                                            <span><i class="fa-solid fa-building"></i></span>
                                            <div>
                                                <strong><?= $escapar($departamento['nombre'] ?? '') ?></strong>
                                                <small><?= $escapar($departamento['descripcion'] ?: 'Sin descripción') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="estructura-parent-pill"><?= $escapar($departamento['facultad'] ?? '') ?></span></td>
                                    <td class="text-center"><span class="estructura-number-pill"><?= (int) ($departamento['total_profesores'] ?? 0) ?></span></td>
                                    <td class="text-center"><span class="estructura-number-pill"><?= (int) ($departamento['total_carreras'] ?? 0) ?></span></td>
                                    <td><?= $estadoBadge($departamento['estado'] ?? 0) ?></td>
                                    <td>
                                        <?php $tipoFila = 'departamento'; $fila = $departamento; require __DIR__ . '/acciones.php'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($departamentos)): ?>
                                <tr><td colspan="6"><div class="estructura-empty-row">No hay departamentos que coincidan con la búsqueda.</div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card admin-list-card estructura-list-card" id="carreras">
                <div class="admin-card-heading estructura-card-heading">
                    <div>
                        <h2 class="admin-card-title mb-1">
                            <i class="fa-solid fa-graduation-cap"></i>
                            Carreras
                        </h2>
                        <p class="mb-0">Programas utilizados en el registro académico de los estudiantes.</p>
                    </div>
                    <span class="estructura-count-pill"><?= count($carreras) ?> registros</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle admin-table">
                        <thead>
                            <tr>
                                <th>Carrera</th>
                                <th>Facultad</th>
                                <th>Departamento</th>
                                <th class="text-center">Estudiantes</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carreras as $carrera): ?>
                                <tr>
                                    <td>
                                        <div class="estructura-name-cell">
                                            <span><i class="fa-solid fa-graduation-cap"></i></span>
                                            <div>
                                                <strong><?= $escapar($carrera['nombre'] ?? '') ?></strong>
                                                <small><?= $escapar($carrera['descripcion'] ?: 'Sin descripción') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="estructura-parent-pill"><?= $escapar($carrera['facultad'] ?? 'Sin facultad') ?></span></td>
                                    <td><?= $escapar($carrera['departamento'] ?? 'Sin departamento asignado') ?></td>
                                    <td class="text-center"><span class="estructura-number-pill"><?= (int) ($carrera['total_estudiantes'] ?? 0) ?></span></td>
                                    <td><?= $estadoBadge($carrera['estado'] ?? 0) ?></td>
                                    <td>
                                        <?php $tipoFila = 'carrera'; $fila = $carrera; require __DIR__ . '/acciones.php'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($carreras)): ?>
                                <tr><td colspan="6"><div class="estructura-empty-row">No hay carreras que coincidan con la búsqueda.</div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
