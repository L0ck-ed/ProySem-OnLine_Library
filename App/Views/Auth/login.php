<?php

use App\Config\Config;
use App\Helpers\Session;

$error = Session::getFlash('error');
$usuarioAnterior = Session::getFlash('old_usuario') ?? '';

$escapar = static function (mixed $valor): string {
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8',
    );
};
?>

<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >
        <meta
            name="description"
            content="Acceso administrativo de Biblioteca Online"
        >

        <title>Acceso administrativo | Biblioteca Online</title>

        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
        >
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin
        >
        <link
            href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap"
            rel="stylesheet"
        >
        <link
            rel="stylesheet"
            href="<?= Config::asset('CSS/admin-login.css') ?>"
        >

        <link rel="stylesheet" href="<?= Config::assetsUrl() ?>/CSS/icon-pack.css?v=icon-pack-1">
    </head>

    <body class="admin-login-body">
        <main class="admin-login-page">
            <a
                href="<?= Config::url() ?>"
                class="admin-login-back"
                aria-label="Volver al selector de acceso"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Volver al selector
            </a>

            <section class="admin-login-card">
                <div class="admin-login-decoration" aria-hidden="true"></div>

                <header class="admin-login-header">
                    <div class="admin-login-logo" aria-hidden="true">
                        <i class="fa-solid fa-book-open-reader"></i>
                    </div>

                    <span class="admin-login-eyebrow">
                        Personal autorizado
                    </span>

                    <h1>Acceso administrativo</h1>

                    <p>
                        Ingresa con tu usuario y contraseña para gestionar
                        la biblioteca.
                    </p>
                </header>

                <?php if ($error): ?>
                    <div
                        class="admin-login-alert"
                        role="alert"
                    >
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><?= $escapar($error) ?></span>
                    </div>
                <?php endif; ?>

                <form
                    method="POST"
                    action="<?= Config::url('login') ?>"
                    class="admin-login-form"
                    autocomplete="on"
                >
                    <div class="admin-login-field">
                        <label for="usuario">
                            Usuario
                        </label>

                        <div class="admin-login-input-wrap">
                            <i
                                class="fa-solid fa-user"
                                aria-hidden="true"
                            ></i>

                            <input
                                id="usuario"
                                type="text"
                                name="usuario"
                                value="<?= $escapar($usuarioAnterior) ?>"
                                placeholder="Escribe tu usuario"
                                maxlength="100"
                                autocomplete="username"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="admin-login-field">
                        <label for="password">
                            Contraseña
                        </label>

                        <div class="admin-login-input-wrap">
                            <i
                                class="fa-solid fa-lock"
                                aria-hidden="true"
                            ></i>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Escribe tu contraseña"
                                maxlength="255"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                type="button"
                                id="toggleAdminPassword"
                                class="admin-login-password-toggle"
                                aria-label="Mostrar contraseña"
                                aria-pressed="false"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="admin-login-submit"
                    >
                        <span>Iniciar sesión</span>
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    </button>
                </form>

                <footer class="admin-login-footer">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>
                        Acceso protegido mediante roles y permisos.
                    </span>
                </footer>
            </section>
        </main>

        <script
            src="<?= Config::asset('JavaScript/admin-login.js') ?>"
            defer
        ></script>
        <?php require_once __DIR__ . '/../Partials/audio.php'; ?>
    </body>
</html>
