<?php

use App\Helpers\Session;
use App\Config\Config;

$error = Session::getFlash('error');

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Estudiante - MyProjectBibliotecaV2</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= Config::assetsUrl() ?>/CSS/style.css?v=modern-library-1">

    <link rel="stylesheet" href="<?= Config::assetsUrl() ?>/CSS/icon-pack.css?v=icon-pack-1">
</head>

<body class="login-body">

<div class="login-container">
    <div class="card-login">
        <div class="text-center mb-4">
            <i class="fa-solid fa-user-graduate logo"></i>
            <h2>Portal del Estudiante</h2>
            <p>Reserva y consulta libros de la biblioteca</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= Config::url('portal/login') ?>">
            <div class="mb-3">
                <label>Cédula / CIP</label>
                <input type="text" name="cip" class="form-control" required autofocus>
            </div>

            <div class="mb-4">
                <label>PIN</label>
                <input type="password" name="pin" class="form-control" inputmode="numeric" required>
            </div>

            <button class="btn btn-primary w-100">
                <i class="fa-solid fa-right-to-bracket"></i>
                Ingresar
            </button>
        </form>

        <small class="d-block text-center mt-3 text-muted">
            ¿Olvidaste tu PIN? Solicítalo en administración de la biblioteca.
        </small>
    </div>
</div>

        <?php require_once __DIR__ . '/../../Partials/audio.php'; ?>
    </body>
</html>