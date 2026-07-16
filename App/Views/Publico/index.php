<?php
use App\Config\Config;
require_once __DIR__.'/../Partials/header.php';
$mensajeExito=$mensajeExito??null; $mensajeError=$mensajeError??null; $datosAnteriores=$datosAnteriores??[];
$esc=static fn(mixed $v):string=>htmlspecialchars((string)($v??''),ENT_QUOTES,'UTF-8');
?>
<nav class="navbar navbar-expand-lg client-navbar sticky-top"><div class="container-fluid px-4"><a class="navbar-brand fw-bold" href="<?=Config::url('publico')?>"><i class="fa-solid fa-book-open-reader"></i> Biblioteca Online</a><div class="d-flex gap-2"><a href="#importancia" class="btn btn-outline-secondary btn-sm">Importancia</a><a href="#stack" class="btn btn-outline-secondary btn-sm">Stack</a><a href="#contacto" class="btn btn-outline-secondary btn-sm">Contáctenos</a><a href="<?=Config::url('portal/login')?>" class="btn btn-primary btn-sm">Entrar al portal</a></div></div></nav>

<header class="portal-hero m-4 text-center py-5"><span class="badge bg-light text-dark mb-3">Biblioteca digital universitaria</span><h1 class="display-5 fw-bold">Conocimiento disponible desde cualquier lugar</h1><p class="lead mx-auto" style="max-width:850px">Consulta el catálogo, reserva libros, solicita títulos inexistentes y accede a materiales de instituciones aliadas mediante préstamo interbibliotecario.</p><div class="d-flex justify-content-center gap-3 flex-wrap"><a href="<?=Config::url('portal/login')?>" class="btn btn-light btn-lg">Acceso de estudiantes y docentes</a><a href="<?=Config::url('admin/login')?>" class="btn btn-outline-light btn-lg">Modo administrativo</a></div></header>

<main class="container pb-5">
<section class="py-4"><div class="text-center mb-4"><span class="text-uppercase fw-bold text-muted">Servicios</span><h2>Todo el ciclo bibliotecario en una sola plataforma</h2></div><div class="row g-4">
<?php foreach([
['fa-magnifying-glass','Catálogo inteligente','Búsquedas por título, autor, categoría y tema, con existencias actualizadas.'],
['fa-calendar-check','Reservas y préstamos','Seguimiento de reservas, entregas, vencimientos, devoluciones e historial.'],
['fa-building-columns','Préstamo interbibliotecario','Consulta títulos externos y solicita materiales de otras instituciones.'],
['fa-chart-column','Estadísticas académicas','Analiza uso y demanda por período, estudiantes, docentes, facultades y carreras.'],
['fa-file-excel','Reportes en Excel','Exporta inventario, reservas y estadísticas conservando los filtros aplicados.'],
['fa-shield-halved','Seguridad por roles','Permisos por módulo, bloqueo de intentos, CSRF, hashing y firmas digitales.']
] as $item):?><div class="col-md-6 col-lg-4"><article class="card p-4 h-100"><i class="fa-solid <?=$item[0]?> mb-3" style="font-size:2rem;color:var(--caramel)"></i><h3 class="h5"><?=$esc($item[1])?></h3><p class="mb-0 text-muted"><?=$esc($item[2])?></p></article></div><?php endforeach;?>
</div></section>

<section id="importancia" class="py-5"><div class="row align-items-center g-5"><div class="col-lg-6"><span class="text-uppercase fw-bold text-muted">Importancia</span><h2>¿Por qué una biblioteca digital es esencial?</h2><p>Una biblioteca digital elimina barreras de horario y ubicación, permite localizar recursos con rapidez y ayuda a que estudiantes y docentes planifiquen mejor sus actividades académicas.</p><p>Además, los datos de reservas y solicitudes permiten identificar qué áreas necesitan más ejemplares. La institución puede detectar, por ejemplo, si una facultad aumenta su demanda de libros de cálculo durante un semestre y tomar decisiones de compra con evidencia.</p></div><div class="col-lg-6"><div class="card p-4"><ul class="list-unstyled mb-0 d-grid gap-3"><li><i class="fa-solid fa-check text-success"></i> Acceso equitativo al conocimiento.</li><li><i class="fa-solid fa-check text-success"></i> Reducción del tiempo de búsqueda y espera.</li><li><i class="fa-solid fa-check text-success"></i> Conservación y organización del catálogo.</li><li><i class="fa-solid fa-check text-success"></i> Decisiones de adquisición basadas en estadísticas.</li><li><i class="fa-solid fa-check text-success"></i> Cooperación con bibliotecas externas.</li></ul></div></div></div></section>

<section id="stack" class="py-5"><div class="text-center mb-4"><span class="text-uppercase fw-bold text-muted">Stack del sistema</span><h2>Tecnologías y arquitectura</h2></div><div class="row g-3 text-center">
<?php foreach([
['PHP 8+','Lógica del servidor y orientación a objetos'],['MVC','Separación de controladores, modelos y vistas'],['PDO','Conexión mediante clase y consultas preparadas'],['SQL Server / MySQL','Persistencia compatible con ambos motores'],['Bootstrap 5','Interfaz adaptable a computadoras y celulares'],['JavaScript','Interactividad, gráficas, música y efectos'],['Chart.js','Visualización de estadísticas por períodos'],['OpenSSL','Firma digital RSA-SHA256 de registros']
] as $tech):?><div class="col-6 col-md-3"><div class="card p-3 h-100"><strong><?=$esc($tech[0])?></strong><small class="text-muted mt-1"><?=$esc($tech[1])?></small></div></div><?php endforeach;?>
</div></section>

<section id="contacto" class="py-5"><div class="row g-5"><div class="col-lg-5"><span class="text-uppercase fw-bold text-muted">Contáctenos</span><h2>¿Tienes una consulta o recomendación?</h2><p>Envía un mensaje al equipo de la biblioteca. Tu solicitud quedará registrada para seguimiento administrativo.</p><div class="card p-4"><p><i class="fa-solid fa-envelope"></i> biblioteca@universidad.edu</p><p><i class="fa-solid fa-phone"></i> +507 000-0000</p><p class="mb-0"><i class="fa-solid fa-location-dot"></i> Campus universitario</p></div></div><div class="col-lg-7"><div class="card p-4">
<?php if($mensajeExito):?><div class="alert alert-success"><?=$esc($mensajeExito)?></div><?php endif;?><?php if($mensajeError):?><div class="alert alert-danger"><?=$esc($mensajeError)?></div><?php endif;?>
<form method="POST" action="<?=Config::url('publico/contacto')?>" class="row g-3"><div class="col-md-6"><label class="form-label">Nombre</label><input required maxlength="150" class="form-control" name="nombre" value="<?=$esc($datosAnteriores['nombre']??'')?>"></div><div class="col-md-6"><label class="form-label">Correo</label><input required type="email" maxlength="150" class="form-control" name="correo" value="<?=$esc($datosAnteriores['correo']??'')?>"></div><div class="col-12"><label class="form-label">Asunto</label><input required maxlength="200" class="form-control" name="asunto" value="<?=$esc($datosAnteriores['asunto']??'')?>"></div><div class="col-12"><label class="form-label">Mensaje</label><textarea required minlength="10" maxlength="2000" rows="5" class="form-control" name="mensaje"><?=$esc($datosAnteriores['mensaje']??'')?></textarea></div><div class="col-12 d-grid"><button class="btn btn-primary btn-lg"><i class="fa-solid fa-paper-plane"></i> Enviar mensaje</button></div></form>
</div></div></div></section>
</main>
<?php require_once __DIR__.'/../Partials/footer.php'; ?>
