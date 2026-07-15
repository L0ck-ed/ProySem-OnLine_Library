<?php

use App\Config\Config;

$error = $error ?? null;
$credencialAnterior = $credencialAnterior ?? '';

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

        <title>Portal regular | Biblioteca Online</title>

        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        >

        <link
            rel="stylesheet"
            href="<?= Config::assetsUrl() ?>/CSS/portal-login.css"
        >
    </head>

    <body>
        <main class="pagina-login-regular">
            <a
                href="<?= Config::url('') ?>"
                class="volver-selector"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Volver al selector de acceso
            </a>

            <section class="tarjeta-login-regular">
                <div class="panel-presentacion-login">
                    <div class="marca-login">
                        <span class="marca-login-icono">
                            <i class="fa-solid fa-book-open-reader"></i>
                        </span>

                        <span>Biblioteca Online</span>
                    </div>

                    <div class="presentacion-contenido">
                        <span class="etiqueta-login">
                            Acceso regular
                        </span>

                        <h1>
                            Estudiantes y profesores
                        </h1>

                        <p>
                            Consulta el catálogo, reserva libros, revisa tus
                            préstamos y envía solicitudes según los permisos
                            de tu cuenta.
                        </p>

                        <ul>
                            <li>
                                <i class="fa-solid fa-circle-check"></i>
                                Catálogo y disponibilidad en tiempo real
                            </li>

                            <li>
                                <i class="fa-solid fa-circle-check"></i>
                                Reservas, préstamos e historial
                            </li>

                            <li>
                                <i class="fa-solid fa-circle-check"></i>
                                Solicitudes de nuevos títulos
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="panel-formulario-login">
                    <div class="formulario-encabezado">
                        <span class="icono-usuario-regular">
                            <i class="fa-solid fa-users"></i>
                        </span>

                        <h2>Iniciar sesión</h2>

                        <p>
                            Usa tu usuario, correo institucional o CIP.
                        </p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alerta-login">
                            <i class="fa-solid fa-circle-exclamation"></i>

                            <span>
                                <?= $escapar($error) ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <form
                        method="POST"
                        action="<?= Config::url('portal/login') ?>"
                        class="formulario-login-regular"
                    >
                        <div class="campo-login">
                            <label for="credencial">
                                Usuario, correo o CIP
                            </label>

                            <div class="control-con-icono">
                                <i class="fa-solid fa-id-card"></i>

                                <input
                                    type="text"
                                    id="credencial"
                                    name="credencial"
                                    maxlength="150"
                                    autocomplete="username"
                                    placeholder="Ejemplo: Jayro o 8-123-456"
                                    value="<?= $escapar($credencialAnterior) ?>"
                                    required
                                    autofocus
                                >
                            </div>
                        </div>

                        <div class="campo-login">
                            <label for="clave">
                                Contraseña
                            </label>

                            <div class="control-con-icono">
                                <i class="fa-solid fa-lock"></i>

                                <input
                                    type="password"
                                    id="clave"
                                    name="clave"
                                    maxlength="255"
                                    autocomplete="current-password"
                                    placeholder="Escribe tu contraseña"
                                    required
                                >

                                <button
                                    type="button"
                                    class="mostrar-clave"
                                    id="mostrarClave"
                                    aria-label="Mostrar contraseña"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="boton-login-regular"
                        >
                            <span>Entrar al portal</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>

                    <p class="nota-login">
                        El portal verificará automáticamente si tu cuenta es
                        de estudiante o profesor.
                    </p>
                </div>
            </section>
        </main>

        <script
            src="<?= Config::assetsUrl() ?>/JavaScript/portal-login.js"
            defer
        ></script>
    </body>
</html>
