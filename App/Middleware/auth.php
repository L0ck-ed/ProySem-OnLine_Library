<?php

namespace App\Middleware;

use App\Helpers\Session;
use App\Config\Config;
use App\Models\Usuario;

class Auth
{
    private static ?array $permisosActuales = null;

    public static function check(): void
    {
        Session::start();

        if (!Session::has('usuario')) {
            header('Location: ' . Config::url());
            exit();
        }
    }

    public static function tieneRol(string $rol): bool
    {
        self::check();

        $roles = Session::get('roles');

        /*
         * Compatibilidad temporal con sesiones antiguas
         * que solamente tengan la clave "rol".
         */
        if (!is_array($roles)) {
            $rolAnterior = Session::get('rol');

            $roles = $rolAnterior ? [$rolAnterior] : [];
        }

        return in_array($rol, $roles, true);
    }

    public static function tieneAlgunRol(array $rolesPermitidos): bool
    {
        self::check();

        $rolesUsuario = Session::get('roles');

        if (!is_array($rolesUsuario)) {
            $rolAnterior = Session::get('rol');

            $rolesUsuario = $rolAnterior ? [$rolAnterior] : [];
        }

        return !empty(array_intersect($rolesUsuario, $rolesPermitidos));
    }

    public static function exigirRoles(array $rolesPermitidos): void
    {
        if (!self::tieneAlgunRol($rolesPermitidos)) {
            http_response_code(403);

            Session::flash('error', 'No tienes permiso para acceder a este módulo.');

            header('Location: ' . Config::url('dashboard'));
            exit();
        }
    }

    private static function cargarPermisosActuales(): array
    {
        self::check();

        /*
         * Solo se consulta una vez durante cada petición.
         * En la siguiente página se vuelve a consultar la BD.
         */
        if (self::$permisosActuales !== null) {
            return self::$permisosActuales;
        }

        $idUsuario = (int) Session::get('id_usuario');

        if ($idUsuario <= 0) {
            self::$permisosActuales = [];

            return [];
        }

        $usuarioModel = new Usuario();

        self::$permisosActuales = $usuarioModel->obtenerPermisosUsuario($idUsuario);

        /*
         * También actualizamos la sesión para mantenerla sincronizada.
         */
        Session::set('permisos', self::$permisosActuales);

        return self::$permisosActuales;
    }

    public static function tienePermiso(string $permiso): bool
    {
        $permisos = self::cargarPermisosActuales();

        return in_array($permiso, $permisos, true);
    }

    public static function exigirPermiso(string $permiso): void
    {
        if (!self::tienePermiso($permiso)) {
            Session::flash('error', 'No tienes permiso para realizar esta acción.');

            header('Location: ' . Config::url('dashboard'));
            exit();
        }
    }
}
