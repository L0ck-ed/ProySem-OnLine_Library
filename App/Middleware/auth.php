<?php

namespace App\Middleware;

use App\Config\Config;
use App\Helpers\Session;
use App\Models\Usuario;

class Auth
{
    /**
     * Roles que pueden iniciar sesión en el panel administrativo.
     *
     * El rol "Administrativo" no se incluye porque pertenece al portal
     * regular, igual que Estudiante y Profesor.
     */
    private const ROLES_PANEL = [
        'Administrador',
        'Bibliotecario',
    ];

    private const CLAVES_SESION_ADMIN = [
        'admin_autenticado',
        'id_usuario',
        'usuario',
        'nombre',
        'roles',
        'permisos',
        'rol',
        'old_usuario',
    ];

    private static ?array $permisosActuales = null;

    public static function check(): void
    {
        Session::start();

        $idUsuario = (int) Session::get('id_usuario');
        $sesionAdministrativa = Session::get('admin_autenticado') === true;
        $tipoSesion = Session::get('tipo_sesion');

        if (
            !$sesionAdministrativa ||
            $tipoSesion !== 'admin' ||
            $idUsuario <= 0 ||
            !Session::has('usuario')
        ) {
            self::redirigirLogin();
        }

        try {
            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->buscarPorId($idUsuario);
            $roles = $usuarioModel->obtenerNombresRolesUsuario($idUsuario);

            if (
                !$usuario ||
                (int) ($usuario['estado'] ?? 0) !== 1 ||
                !self::rolesPermitenPanel($roles)
            ) {
                self::limpiarSesionAdministrativa();

                Session::flash(
                    'error',
                    'Tu cuenta no tiene acceso al panel administrativo. Usa el acceso regular.',
                );

                self::redirigirLogin();
            }

            /*
             * Los roles se vuelven a consultar en cada petición para que
             * una revocación hecha por otro administrador tenga efecto
             * inmediatamente y no dependa de cerrar el navegador.
             */
            Session::set('roles', $roles);
            Session::set('rol', $roles[0] ?? null);
        } catch (\Throwable $e) {
            error_log('Error al validar la sesión administrativa: ' . $e->getMessage());

            self::limpiarSesionAdministrativa();
            Session::flash(
                'error',
                'No se pudo validar tu acceso administrativo. Inicia sesión nuevamente.',
            );

            self::redirigirLogin();
        }
    }

    /**
     * @param array<int, mixed> $roles
     */
    public static function rolesPermitenPanel(array $roles): bool
    {
        $rolesNormalizados = array_map(
            static fn (mixed $rol): string => strtolower(trim((string) $rol)),
            $roles,
        );

        $permitidosNormalizados = array_map(
            static fn (string $rol): string => strtolower($rol),
            self::ROLES_PANEL,
        );

        return !empty(array_intersect($rolesNormalizados, $permitidosNormalizados));
    }

    public static function limpiarSesionAdministrativa(bool $regenerarId = false): void
    {
        Session::start();

        foreach (self::CLAVES_SESION_ADMIN as $clave) {
            unset($_SESSION[$clave]);
        }

        if (($_SESSION['tipo_sesion'] ?? null) === 'admin') {
            unset($_SESSION['tipo_sesion']);
        }

        self::$permisosActuales = null;

        if ($regenerarId && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function tieneRol(string $rol): bool
    {
        self::check();

        $roles = Session::get('roles');

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

    private static function redirigirLogin(): never
    {
        header('Location: ' . Config::url('admin/login'));
        exit();
    }
}
