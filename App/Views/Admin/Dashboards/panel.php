<?php

require_once __DIR__ . '/../../Partials/header.php';
require_once __DIR__ . '/../Partials/navbar.php';

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once __DIR__ . '/../Partials/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Dashboard</h2>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <i class="fa-solid fa-users"></i>
                        <h4>Usuarios</h4>
                        <p>Módulo administrativo</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="dashboard-card">
                        <i class="fa-solid fa-user-graduate"></i>
                        <h4>Estudiantes</h4>
                        <p>Registro académico</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="dashboard-card">
                        <i class="fa-solid fa-book"></i>
                        <h4>Libros</h4>
                        <p>Inventario bibliotecario</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="dashboard-card">
                        <i class="fa-solid fa-calendar-check"></i>
                        <h4>Reservas</h4>
                        <p>Préstamos y devoluciones</p>
                    </div>
                </div>
            </div>

            <div class="card p-4 mt-4">
                <h5>Bienvenido al sistema</h5>
                <p class="mb-0">
                    Desde este panel se administrarán usuarios, estudiantes, carreras,
                    categorías, libros, reservas, solicitudes y estadísticas.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../Partials/footer.php'; ?>
