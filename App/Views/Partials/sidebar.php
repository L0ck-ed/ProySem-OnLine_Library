<?php

use App\Config\Config;

?>

<div class="sidebar">
    <h4 class="text-white mb-4">
        <i class="fa-solid fa-layer-group"></i>
        Menú
    </h4>

    <a href="<?= Config::BASE_URL ?>/dashboard">
        <i class="fa-solid fa-house"></i>
        Dashboard
    </a>

    <a href="<?= Config::BASE_URL ?>/usuarios">
        <i class="fa-solid fa-users"></i>
        Usuarios
    </a>

    <a href="#">
        <i class="fa-solid fa-user-graduate"></i>
        Estudiantes
    </a>

    <a href="#">
        <i class="fa-solid fa-building-columns"></i>
        Carreras
    </a>

    <a href="#">
        <i class="fa-solid fa-tags"></i>
        Categorías
    </a>

    <a href="#">
        <i class="fa-solid fa-book"></i>
        Libros
    </a>

    <a href="#">
        <i class="fa-solid fa-calendar-check"></i>
        Reservas
    </a>

    <a href="#">
        <i class="fa-solid fa-chart-column"></i>
        Estadísticas
    </a>
</div>
