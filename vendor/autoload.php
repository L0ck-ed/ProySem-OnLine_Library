<?php

/**
 * Autoloader simple para el proyecto.
 * No depende de Composer; carga solamente las clases del MVC.
 */

$root = dirname(__DIR__);

$files = [
    '/App/Configs/general_config.php',
    '/App/Configs/database_config.php',

    '/App/Core/controller.php',
    '/App/Core/model.php',
    '/App/Core/router.php',

    '/App/Helpers/session.php',
    '/App/Helpers/sanitizer.php',
    '/App/Helpers/validator.php',
    '/App/Helpers/logger.php',

    '/App/Middleware/auth.php',

    '/App/Models/usuario.php',

    '/App/Services/auth_service.php',

    '/App/Controllers/login_controller.php',
    '/App/Controllers/dashboard_controller.php',
    '/App/Controllers/usuario_controller.php',
];

foreach ($files as $file) {
    $path = $root . $file;
    if (is_file($path)) {
        require_once $path;
    }
}
