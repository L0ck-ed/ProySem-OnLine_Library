<?php

use App\Config\Config; ?>

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
            content="Selecciona el tipo de acceso a la Biblioteca Online."
        >

        <title>Biblioteca Online | Seleccionar acceso</title>

        <link
            rel="stylesheet"
            href="<?= Config::assetsUrl() ?>/CSS/acceso-selector.css"
        >

        <link rel="stylesheet" href="<?= Config::assetsUrl() ?>/CSS/icon-pack.css?v=icon-pack-1">
    </head>

    <body>
        <main class="pagina-acceso">
            <header class="encabezado-acceso">
                <a
                    href="<?= Config::url('') ?>"
                    class="marca-biblioteca"
                >
                    <span class="marca-icono">
                        <span class="pack-icon pack-icon-libro" aria-hidden="true"></span>
                    </span>

                    <span>
                        Biblioteca Online
                    </span>
                </a>
            </header>

            <section class="presentacion-acceso">
                <div class="texto-presentacion">
                    <span class="etiqueta-superior">
                        Sistema de biblioteca digital
                    </span>

                    <h1>
                        Bienvenido a tu biblioteca
                    </h1>

                    <p>
                        Selecciona el tipo de cuenta con el que deseas
                        ingresar al sistema.
                    </p>
                </div>

                <div
                    class="contenedor-acceso"
                    id="selectorAcceso"
                >
                    <!-- ACCESO ADMINISTRATIVO -->
                    <section
                        class="
                            panel-acceso
                            panel-administrativo
                        "
                        aria-labelledby="tituloAdministrativo"
                    >
                        <div class="contenido-panel">
                            <div
                                class="
                                    icono-tipo-acceso
                                    icono-administrativo
                                "
                            >
                                <span class="pack-icon pack-icon-candado-cerrado" aria-hidden="true"></span>
                            </div>

                            <span class="tipo-cuenta">
                                Personal autorizado
                            </span>

                            <h2 id="tituloAdministrativo">
                                Acceso administrativo
                            </h2>

                            <p>
                                Ingresa para gestionar usuarios, roles,
                                estudiantes, profesores, libros, reservas,
                                solicitudes y reportes.
                            </p>

                            <ul class="lista-funciones">
                                <li>
                                    Gestión del catálogo e inventario
                                </li>

                                <li>
                                    Control de reservas y préstamos
                                </li>

                                <li>
                                    Administración de usuarios y permisos
                                </li>
                            </ul>

                            <a
                                href="<?= Config::url('admin/login') ?>"
                                class="
                                    boton-acceso
                                    boton-administrativo
                                "
                            >
                                <span>
                                    Entrar como administrativo
                                </span>

                                <span class="pack-icon pack-icon-flecha-derecha" aria-hidden="true"></span>
                            </a>
                        </div>
                    </section>

                    <!-- ACCESO REGULAR -->
                    <section
                        class="
                            panel-acceso
                            panel-regular
                        "
                        aria-labelledby="tituloRegular"
                    >
                        <div class="contenido-panel">
                            <div
                                class="
                                    icono-tipo-acceso
                                    icono-regular
                                "
                            >
                                <span class="pack-icon pack-icon-carreras" aria-hidden="true"></span>
                            </div>

                            <span class="tipo-cuenta">
                                Estudiantes y profesores
                            </span>

                            <h2 id="tituloRegular">
                                Acceso regular
                            </h2>

                            <p>
                                Ingresa al portal para consultar el catálogo, revisar préstamos,
                                reservar libros y realizar solicitudes según los permisos de tu cuenta.
                            </p>

                            <ul class="lista-funciones">
                                <li>
                                    Consulta de libros disponibles
                                </li>

                                <li>
                                    Reservas y seguimiento de préstamos
                                </li>

                                <li>
                                    Solicitudes de nuevos títulos
                                </li>
                            </ul>

                            <a
                                href="<?= Config::url('portal/login') ?>"
                                class="
                                    boton-acceso
                                    boton-regular
                                "
                            >
                                <span>
                                    Entrar como estudiante
                                </span>

                                <span class="pack-icon pack-icon-flecha-derecha" aria-hidden="true"></span>
                            </a>
                        </div>
                    </section>

                    <!-- PANEL ANIMADO -->
                    <div class="contenedor-toggle">
                        <div class="toggle">
                            <section
                                class="
                                    panel-toggle
                                    panel-toggle-izquierdo
                                "
                            >
                                <div class="icono-toggle">
                                    <span class="pack-icon pack-icon-libro" aria-hidden="true"></span>
                                </div>

                                <h2>
                                    ¿Eres administrativo?
                                </h2>

                                <p>
                                    Regresa al acceso del personal encargado
                                    de gestionar la biblioteca.
                                </p>

                                <button
                                    type="button"
                                    class="boton-cambiar"
                                    id="mostrarAdministrativo"
                                >
                                    Acceso administrativo
                                </button>
                            </section>

                            <section
                                class="
                                    panel-toggle
                                    panel-toggle-derecho
                                "
                            >
                                <div class="icono-toggle">
                                    <span class="pack-icon pack-icon-usuario" aria-hidden="true"></span>
                                </div>

                                <h2>
                                    ¿Eres usuario regular?
                                </h2>

                                <p>
                                    Ingresa al portal regular como estudiante o profesor 
                                    para consultar los servicios de la biblioteca.
                                </p>

                                <button
                                    type="button"
                                    class="boton-cambiar"
                                    id="mostrarRegular"
                                >
                                    Entrar como usuario regular
                                </button>
                            </section>
                        </div>
                    </div>
                </div>

                <p class="ayuda-acceso">
                    Selecciona la opción que corresponde a tu cuenta.
                    El acceso está protegido según tus roles y permisos.
                </p>
            </section>

            <div class="ayuda-acceso" style="text-align:center; margin-top:1.25rem;">
                <a href="<?= Config::url('publico') ?>" style="display:inline-flex; align-items:center; gap:.5rem;">
                    <span class="pack-icon pack-icon-libro" aria-hidden="true"></span>
                    Conoce el sistema, su tecnología y la importancia de las bibliotecas digitales
                </a>
            </div>
        </main>
        <script
            src="<?= Config::assetsUrl() ?>/JavaScript/acceso-selector.js"
            defer
        ></script>
        <?php require_once __DIR__ . '/../Partials/audio.php'; ?>
    </body>
</html>