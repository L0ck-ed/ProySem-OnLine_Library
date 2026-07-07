<?php

use App\Helpers\Session;
use App\Config\Config;

$nombreSesion = Session::get('nombre') ?? 'Administrador';

?>

<nav class="navbar navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold">
            <i class="fa-solid fa-book-open-reader"></i>
            MyProjectBibliotecaV2
        </span>

        <div class="d-flex align-items-center gap-3 text-white">
            <span>
                <i class="fa-solid fa-user"></i>
                <?= $nombreSesion ?>
            </span>

            <a href="<?= Config::BASE_URL ?>/logout" class="btn btn-danger btn-sm">
                <i class="fa-solid fa-right-from-bracket"></i>
                Cerrar sesión
            </a>
        </div>
    </div>
</nav>
