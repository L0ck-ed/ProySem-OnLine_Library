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
    <title>Login - MyProjectBibliotecaV2</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= Config::BASE_URL ?>/assets/css/style.css">
</head>

<body class="login-body">

<div class="login-container">
    <div class="card-login">
        <div class="text-center mb-4">
            <i class="fa-solid fa-book-open-reader logo"></i>
            <h2>MyProjectBiblioteca</h2>
            <p>Sistema de Gestión Bibliotecaria</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= Config::BASE_URL ?>/login">
            <div class="mb-3">
                <label>Usuario</label>
                <input type="text" name="usuario" class="form-control" required>
            </div>

            <div class="mb-4">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">
                <i class="fa-solid fa-right-to-bracket"></i>
                Ingresar
            </button>
        </form>

        <small class="d-block text-center mt-3 text-muted">
            Usuario inicial: admin / root2514
        </small>
    </div>
</div>

</body>
</html>
