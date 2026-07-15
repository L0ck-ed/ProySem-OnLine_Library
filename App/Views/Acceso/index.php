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
    </head>

    <body>
        <main class="pagina-acceso">
            <header class="encabezado-acceso">
                <a
                    href="<?= Config::url('') ?>"
                    class="marca-biblioteca"
                >
                    <span class="marca-icono">
                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11a3 3 0 0 1 3 3v13a3 3 0 0 0-3-3H4V5.5Z"
                            ></path>

                            <path
                                d="M20 5.5A2.5 2.5 0 0 0 17.5 3H14v16a3 3 0 0 1 3-3h3V5.5Z"
                            ></path>
                        </svg>
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
                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M12 2a5 5 0 1 0 0 10 5 5 0 0 0 0-10Z"
                                    ></path>

                                    <path
                                        d="M4 21a8 8 0 0 1 16 0H4Z"
                                    ></path>

                                    <path
                                        d="M18.5 7.5 20 9l3-3"
                                    ></path>
                                </svg>
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

                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="m9 18 6-6-6-6"
                                    ></path>
                                </svg>
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
                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M22 10 12 5 2 10l10 5 10-5Z"
                                    ></path>

                                    <path
                                        d="M6 12.5V17c3.5 2.6 8.5 2.6 12 0v-4.5"
                                    ></path>

                                    <path
                                        d="M22 10v6"
                                    ></path>
                                </svg>
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

                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="m9 18 6-6-6-6"
                                    ></path>
                                </svg>
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
                                    <svg
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"
                                        ></path>

                                        <path
                                            d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"
                                        ></path>
                                    </svg>
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
                                    <svg
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M12 2a5 5 0 1 0 0 10 5 5 0 0 0 0-10Z"
                                        ></path>

                                        <path
                                            d="M4 21a8 8 0 0 1 16 0"
                                        ></path>
                                    </svg>
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
        </main>
        <script
            src="<?= Config::assetsUrl() ?>/JavaScript/acceso-selector.js"
            defer
        ></script>
    </body>
</html>