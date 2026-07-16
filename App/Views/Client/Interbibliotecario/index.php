<?php
use App\Config\Config;
$catalogo=$catalogo??[]; $misSolicitudes=$misSolicitudes??[]; $buscar=$buscar??'';
$esc=static fn(mixed $v):string=>htmlspecialchars((string)($v??''),ENT_QUOTES,'UTF-8');
require_once __DIR__.'/../../Partials/header.php';
require_once __DIR__.'/../Partials/navbar.php';
?>
<main class="client-page container py-4">
    <section class="client-page-heading mb-4">
        <div><span class="client-eyebrow">Catálogo aliado</span><h1>Préstamo interbibliotecario</h1><p>Solicita libros disponibles en otras instituciones desde tu portal académico.</p></div>
    </section>

    <?php if(!empty($exitoInterbibliotecario)):?><div class="alert alert-success"><?=$esc($exitoInterbibliotecario)?></div><?php endif;?>
    <?php if(!empty($errorInterbibliotecario)):?><div class="alert alert-danger"><?=$esc($errorInterbibliotecario)?></div><?php endif;?>

    <section class="client-panel mb-4">
        <form method="GET" action="<?=Config::url('portal/interbibliotecario')?>" class="row g-3 align-items-end">
            <div class="col-md-9"><label class="form-label">Buscar por título, autor, ISBN o institución</label><input class="form-control" name="buscar" value="<?=$esc($buscar)?>" placeholder="Ej.: Cálculo, Stewart, Universidad..."></div>
            <div class="col-md-3 d-grid"><button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button></div>
        </form>
    </section>

    <section class="mb-5">
        <div class="client-section-heading"><div><span class="client-eyebrow">Disponibles</span><h2>Libros de otras bibliotecas</h2></div></div>
        <div class="row g-4">
            <?php foreach($catalogo as $libro):?>
                <div class="col-md-6 col-xl-4"><article class="client-book-card h-100"><div class="client-book-cover"><i class="fa-solid fa-building-columns"></i></div><div class="client-book-body">
                    <span class="client-book-category"><?=$esc($libro['institucion'])?></span>
                    <h3><?=$esc($libro['titulo'])?></h3><p class="client-book-author"><?=$esc($libro['autor'])?></p>
                    <?php if(!empty($libro['descripcion'])):?><p class="small text-muted"><?=$esc($libro['descripcion'])?></p><?php endif;?>
                    <?php if(!empty($libro['url_catalogo'])):?><a href="<?=$esc($libro['url_catalogo'])?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm mb-2">Ver catálogo externo</a><?php endif;?>
                    <form method="POST" action="<?=Config::url('portal/interbibliotecario/solicitar')?>" class="mt-auto"><input type="hidden" name="id_libro_externo" value="<?=(int)$libro['id_libro_externo']?>"><textarea class="form-control mb-2" name="observacion" rows="2" maxlength="1000" placeholder="Materia o motivo de interés (opcional)"></textarea><button class="btn btn-primary w-100"><i class="fa-solid fa-paper-plane"></i> Solicitar préstamo</button></form>
                </div></article></div>
            <?php endforeach;?>
            <?php if(!$catalogo):?><div class="col-12"><div class="client-empty-state"><i class="fa-solid fa-book-open"></i><h3>No hay resultados</h3><p>Prueba con otro título, autor o institución.</p></div></div><?php endif;?>
        </div>
    </section>

    <section class="client-panel">
        <div class="client-section-heading"><div><span class="client-eyebrow">Seguimiento</span><h2>Mis solicitudes interbibliotecarias</h2></div></div>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Libro</th><th>Institución</th><th>Solicitud</th><th>Vencimiento</th><th>Estado</th><th></th></tr></thead><tbody>
        <?php foreach($misSolicitudes as $s):?><tr><td><strong><?=$esc($s['titulo'])?></strong><div class="small text-muted"><?=$esc($s['autor'])?></div></td><td><?=$esc($s['institucion'])?></td><td><?=$esc(substr((string)$s['fecha_solicitud'],0,10))?></td><td><?=$esc($s['fecha_vencimiento']?:'Por definir')?></td><td><span class="badge bg-<?=in_array($s['estado'],['Aprobado','Recibido'],true)?'success':(in_array($s['estado'],['Rechazado','Cancelado'],true)?'danger':'warning')?>"><?=$esc($s['estado'])?></span></td><td><?php if($s['estado']==='Solicitado'):?><form method="POST" action="<?=Config::url('portal/interbibliotecario/cancelar')?>"><input type="hidden" name="id_solicitud" value="<?=(int)$s['id_prestamo_interbibliotecario']?>"><button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Cancelar esta solicitud?')">Cancelar</button></form><?php endif;?></td></tr><?php endforeach;?>
        <?php if(!$misSolicitudes):?><tr><td colspan="6" class="text-center py-4">Todavía no has solicitado préstamos interbibliotecarios.</td></tr><?php endif;?>
        </tbody></table></div>
    </section>
</main>
<?php require_once __DIR__.'/../../Partials/footer.php'; ?>
