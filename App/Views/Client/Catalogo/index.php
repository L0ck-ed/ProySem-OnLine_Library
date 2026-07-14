<?php
/* Vista de solo interfaz. Búsqueda, filtro y paginación son visuales por ahora. */

$nombreEstudiante = $nombreEstudiante ?? 'Anthony Castillo';
$cipSesion = $cipSesion ?? '8-1023-2265';

$categorias = $categorias ?? ['Química', 'Sistemas', 'Lógica', 'Matemática', 'Estadística'];
$busqueda = $_GET['buscar'] ?? '';
$categoriaSeleccionada = $_GET['categoria'] ?? '';

$libros = $libros ?? [
    ['titulo' => 'Clean Code',                  'autor' => 'Robert C. Martin',  'categoria' => 'Sistemas',    'existencias' => 4],
    ['titulo' => 'Introducción a Algoritmos',    'autor' => 'Cormen',            'categoria' => 'Sistemas',    'existencias' => 1],
    ['titulo' => 'Cálculo de una Variable',      'autor' => 'James Stewart',     'categoria' => 'Matemática',  'existencias' => 2],
    ['titulo' => 'Química General',              'autor' => 'Raymond Chang',     'categoria' => 'Química',     'existencias' => 0],
    ['titulo' => 'Lógica Matemática',            'autor' => 'Suppes',            'categoria' => 'Lógica',      'existencias' => 3],
    ['titulo' => 'Estadística para Ingeniería',  'autor' => 'Montgomery',        'categoria' => 'Estadística', 'existencias' => 6],
];

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

?>

<div class="container-fluid py-4 px-4">

    <h2><i class="fa-solid fa-magnifying-glass"></i> Catálogo de libros</h2>

    <div class="card p-3 mb-4">
        <form action="<?= App\Config\Config::url('portal/catalogo') ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label>Buscar por título o autor</label>
                <input type="text" name="buscar" class="form-control" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Ej: Clean Code, Stewart...">
            </div>
            <div class="col-md-4">
                <label>Categoría</label>
                <select name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $categoriaSeleccionada === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Buscar
                </button>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <?php foreach ($libros as $libro): ?>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="book-card">
                    <div class="book-cover"><i class="fa-solid fa-book"></i></div>
                    <div class="book-card-body">
                        <h5><?= htmlspecialchars($libro['titulo']) ?></h5>
                        <span class="book-autor"><?= htmlspecialchars($libro['autor']) ?></span>
                        <div class="book-card-footer">
                            <span class="badge badge-categoria"><?= htmlspecialchars($libro['categoria']) ?></span>
                            <?php if ($libro['existencias'] > 0): ?>
                                <span class="badge badge-existencias-ok"><?= $libro['existencias'] ?> disp.</span>
                            <?php else: ?>
                                <span class="badge badge-existencias-agotado">Agotado</span>
                            <?php endif; ?>
                        </div>
                        <a href="<?= App\Config\Config::url('portal/catalogo/detalle') ?>?id=<?= $libro['id_libro'] ?>" class="btn btn-primary btn-sm w-100 mt-2">
                            Ver detalle
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php
        $paginaActual = $paginaActual ?? 1;
        $totalPaginas = $totalPaginas ?? 1;
        $queryBase = 'buscar=' . urlencode($busqueda) . '&categoria=' . urlencode($categoriaSeleccionada);
    ?>
    <?php if ($totalPaginas > 1): ?>
    <nav class="mt-2">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= $queryBase ?>&pagina=<?= max(1, $paginaActual - 1) ?>"><i class="fa-solid fa-angle-left"></i></a>
            </li>
            <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                <li class="page-item <?= $p === $paginaActual ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= $queryBase ?>&pagina=<?= $p ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= $queryBase ?>&pagina=<?= min($totalPaginas, $paginaActual + 1) ?>"><i class="fa-solid fa-angle-right"></i></a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

    <?php if (empty($libros)): ?>
        <div class="alert alert-info text-center">No se encontraron libros con esos criterios.</div>
    <?php endif; ?>

    <div class="alert alert-success text-center">
        <i class="fa-solid fa-circle-info"></i>
        ¿No encontraste el libro que buscas?
        <a href="<?= App\Config\Config::url('portal/solicitudes') ?>" class="fw-bold">Solicítalo aquí</a>.
    </div>

</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>