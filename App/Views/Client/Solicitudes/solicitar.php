<?php

use App\Config\Config;

$areas = is_array($areas ?? null) ? $areas : [];
$misSolicitudes = is_array($misSolicitudes ?? null) ? $misSolicitudes : [];
$errorSolicitud = $errorSolicitud ?? null;
$exitoSolicitud = $exitoSolicitud ?? null;
$tituloAnterior = $tituloAnterior ?? '';
$areaAnterior = $areaAnterior ?? '';
$descripcionAnterior = $descripcionAnterior ?? '';

$escapar = static function (mixed $valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$claseEstadoSolicitud = static function (string $estado): string {
    return match ($estado) {
        'Aprobado' => 'status-returned',
        'Rechazado' => 'status-overdue',
        'Revisado' => 'status-reserved',
        'Pendiente' => 'status-pending',
        default => 'status-neutral',
    };
};

$iconoEstadoSolicitud = static function (string $estado): string {
    return match ($estado) {
        'Aprobado' => 'fa-solid fa-circle-check',
        'Rechazado' => 'fa-solid fa-circle-xmark',
        'Revisado' => 'fa-solid fa-eye',
        'Pendiente' => 'fa-solid fa-clock',
        default => 'fa-solid fa-circle-info',
    };
};

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';
?>

<main class="client-page">
    <div class="client-page-inner">
        <header class="client-page-header">
            <div class="client-page-title-wrap">
                <span class="client-page-title-icon"><i class="fa-solid fa-circle-plus"></i></span>
                <div>
                    <span class="client-section-kicker">Ayúdanos a crecer</span>
                    <h1>Solicitar un libro</h1>
                    <p>Cuéntanos qué material necesitas y la administración revisará tu petición.</p>
                </div>
            </div>

            <a href="<?= Config::url('portal/catalogo') ?>" class="btn btn-secondary client-header-action">
                <i class="fa-solid fa-book-open"></i>
                Revisar catálogo
            </a>
        </header>

        <?php if ($errorSolicitud): ?>
            <div class="alert alert-danger client-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= $escapar($errorSolicitud) ?>
            </div>
        <?php endif; ?>

        <?php if ($exitoSolicitud): ?>
            <div class="alert alert-success client-alert">
                <i class="fa-solid fa-circle-check"></i>
                <?= $escapar($exitoSolicitud) ?>
            </div>
        <?php endif; ?>

        <div class="client-request-layout">
            <section class="client-request-form-card">
                <div class="client-card-heading">
                    <span><i class="fa-solid fa-paper-plane"></i></span>
                    <div>
                        <h2>Nueva solicitud</h2>
                        <p>Completa los datos principales del libro.</p>
                    </div>
                </div>

                <form action="<?= Config::url('portal/solicitudes') ?>" method="POST" class="client-request-form">
                    <div>
                        <label for="titulo_libro">Título del libro</label>
                        <div class="client-input-icon">
                            <i class="fa-solid fa-book"></i>
                            <input
                                type="text"
                                name="titulo_libro"
                                id="titulo_libro"
                                class="form-control"
                                value="<?= $escapar($tituloAnterior) ?>"
                                placeholder="Ej: Introduction to Algorithms"
                                minlength="3"
                                required
                            >
                        </div>
                        <small>Escribe el nombre más completo que conozcas.</small>
                    </div>

                    <div>
                        <label for="area">Área académica</label>
                        <div class="client-input-icon">
                            <i class="fa-solid fa-shapes"></i>
                            <select name="area" id="area" class="form-select" required>
                                <option value="">Selecciona un área</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= $escapar($area) ?>" <?= $areaAnterior === $area ? 'selected' : '' ?>>
                                        <?= $escapar($area) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="descripcion">Descripción o motivo <span>(opcional)</span></label>
                        <textarea
                            name="descripcion"
                            id="descripcion"
                            class="form-control client-textarea"
                            rows="5"
                            placeholder="Cuéntanos para qué lo necesitas, autor, edición o dónde lo viste..."
                        ><?= $escapar($descripcionAnterior) ?></textarea>
                    </div>

                    <button class="btn btn-primary client-request-submit" type="submit">
                        <i class="fa-solid fa-paper-plane"></i>
                        Enviar solicitud
                    </button>
                </form>

                <div class="client-request-tip">
                    <i class="fa-solid fa-lightbulb"></i>
                    <p><strong>Consejo:</strong> agregar autor, edición o ISBN ayuda a identificar el libro con mayor precisión.</p>
                </div>
            </section>

            <section class="client-request-history-card">
                <div class="client-card-heading client-card-heading-between">
                    <div class="client-card-heading-copy">
                        <span><i class="fa-solid fa-list-check"></i></span>
                        <div>
                            <h2>Mis solicitudes</h2>
                            <p>Seguimiento de las peticiones que has enviado.</p>
                        </div>
                    </div>
                    <strong class="client-count-badge"><?= count($misSolicitudes) ?></strong>
                </div>

                <?php if (!empty($misSolicitudes)): ?>
                    <div class="client-request-list">
                        <?php foreach ($misSolicitudes as $solicitud): ?>
                            <?php $estado = (string) ($solicitud['estado'] ?? 'Pendiente'); ?>
                            <article class="client-request-item">
                                <span class="client-request-item-icon"><i class="fa-solid fa-book"></i></span>
                                <div class="client-request-item-main">
                                    <div>
                                        <h3><?= $escapar($solicitud['titulo'] ?? 'Libro sin título') ?></h3>
                                        <p><i class="fa-solid fa-tag"></i> <?= $escapar($solicitud['area'] ?? 'Sin área') ?></p>
                                    </div>
                                    <span class="client-status-pill <?= $claseEstadoSolicitud($estado) ?>">
                                        <i class="<?= $iconoEstadoSolicitud($estado) ?>"></i>
                                        <?= $escapar($estado) ?>
                                    </span>
                                </div>
                                <time>
                                    <i class="fa-regular fa-calendar"></i>
                                    <?= $escapar($solicitud['fecha'] ?? 'Fecha no registrada') ?>
                                </time>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="client-empty-state compact">
                        <span><i class="fa-solid fa-inbox"></i></span>
                        <h2>Aún no has enviado solicitudes</h2>
                        <p>Completa el formulario y podrás seguir su estado desde aquí.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
