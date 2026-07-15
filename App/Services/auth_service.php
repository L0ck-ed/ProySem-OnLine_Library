<?php

namespace App\Services;

use App\Config\Config;
use App\Helpers\Logger;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Models\Usuario;
use App\Middleware\Auth;

class AuthService
{
    private const MAX_INTENTOS = 3;

    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function login(): void
    {
        Session::start();

        // Evita reutilizar una sesión administrativa anterior en el mismo navegador.
        Auth::limpiarSesionAdministrativa();

        $usuario = trim($_POST['usuario'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        Session::flash('old_usuario', $usuario);

        if (!Validator::required($usuario) || !Validator::required($password)) {
            Logger::login(
                $usuario,
                'campos_vacios',
                null,
                'No se completaron todos los campos.',
            );

            Session::flash('error', 'Debe completar todos los campos.');
            $this->redirigirLogin();
        }

        if (mb_strlen($usuario) > 100 || mb_strlen($password) > 255) {
            Logger::login(
                $usuario,
                'datos_invalidos',
                null,
                'Los datos ingresados superan la longitud permitida.',
            );

            Session::flash('error', 'Los datos ingresados no son válidos.');
            $this->redirigirLogin();
        }

        $datos = $this->usuarioModel->buscarPorUsuario($usuario);

        if (!$datos) {
            Logger::login(
                $usuario,
                'usuario_no_existe',
                null,
                'El usuario ingresado no existe.',
            );

            Session::flash('error', 'Usuario o contraseña incorrectos.');
            $this->redirigirLogin();
        }

        $idUsuario = (int) $datos['id_usuario'];

        if ((int) $datos['estado'] !== 1) {
            Logger::login(
                $usuario,
                'usuario_inactivo',
                $idUsuario,
                'La cuenta se encuentra inactiva.',
            );

            Session::flash('error', 'Esta cuenta se encuentra inactiva.');
            $this->redirigirLogin();
        }

        if ((int) ($datos['bloqueado'] ?? 0) === 1) {
            Logger::login(
                $usuario,
                'usuario_bloqueado',
                $idUsuario,
                'La cuenta está bloqueada por intentos fallidos.',
            );

            Session::flash(
                'error',
                'Este usuario está bloqueado por 3 intentos fallidos. Debe ser desbloqueado por un administrador.',
            );

            $this->redirigirLogin();
        }

        if (!password_verify($password, (string) $datos['password'])) {
            $intentos = $this->usuarioModel->aumentarIntentos($idUsuario);

            Logger::login(
                $usuario,
                'password_incorrecta',
                $idUsuario,
                "Intento fallido número {$intentos}.",
            );

            if ($intentos >= self::MAX_INTENTOS) {
                $this->usuarioModel->bloquearUsuario($idUsuario);

                Logger::login(
                    $usuario,
                    'bloqueado_por_intentos',
                    $idUsuario,
                    'La cuenta fue bloqueada después de tres intentos.',
                );

                Session::flash(
                    'error',
                    'Usuario bloqueado por 3 intentos fallidos. Debe ser desbloqueado por un administrador.',
                );
            } else {
                $restantes = self::MAX_INTENTOS - $intentos;
                $textoIntento = $restantes === 1 ? 'intento' : 'intentos';

                Session::flash(
                    'error',
                    "Usuario o contraseña incorrectos. Intento {$intentos} de "
                    . self::MAX_INTENTOS
                    . ". Te quedan {$restantes} {$textoIntento}.",
                );
            }

            $this->redirigirLogin();
        }

        $roles = $this->usuarioModel->obtenerNombresRolesUsuario($idUsuario);

        if (empty($roles)) {
            Logger::login(
                $usuario,
                'sin_roles',
                $idUsuario,
                'El usuario no tiene roles activos asignados.',
            );

            Session::flash('error', 'La cuenta no tiene un rol activo asignado.');
            $this->redirigirLogin();
        }

        if (!Auth::rolesPermitenPanel($roles)) {
            Logger::login(
                $usuario,
                'acceso_admin_denegado',
                $idUsuario,
                'Credenciales válidas, pero la cuenta no posee un rol administrativo.',
            );

            Session::flash(
                'error',
                'Tu cuenta no tiene acceso al panel administrativo. Usa el acceso regular.',
            );

            $this->redirigirLogin();
        }

        $this->usuarioModel->actualizarLogin($idUsuario);

        Logger::login(
            $usuario,
            'correcto',
            $idUsuario,
            'Inicio de sesión administrativo exitoso.',
        );

        $this->limpiarSesionPortal();
        session_regenerate_id(true);

        $permisos = $this->usuarioModel->obtenerPermisosUsuario($idUsuario);

        Session::set('admin_autenticado', true);
        Session::set('tipo_sesion', 'admin');
        Session::set('id_usuario', $idUsuario);
        Session::set('usuario', $datos['usuario']);
        Session::set('nombre', $datos['nombre']);
        Session::set('roles', $roles);
        Session::set('permisos', $permisos);
        Session::set('rol', $roles[0]);

        Session::getFlash('old_usuario');

        header('Location: ' . Config::url('dashboard'));
        exit();
    }

    public function logout(): void
    {
        Session::start();

        Auth::limpiarSesionAdministrativa(true);

        header('Location: ' . Config::url());
        exit();
    }

    private function limpiarSesionPortal(): void
    {
        $clavesPortal = [
            'portal_autenticado',
            'portal_id_usuario',
            'portal_tipo_usuario',
            'portal_nombre',
            'portal_cip',
            'portal_permisos',
            'permisos_portal',
            'id_estudiante',
            'id_profesor',
            'nombre_estudiante',
            'cip',
            'error_permiso',
        ];

        foreach ($clavesPortal as $clave) {
            unset($_SESSION[$clave]);
        }

        if (($_SESSION['tipo_sesion'] ?? null) === 'portal') {
            unset($_SESSION['tipo_sesion']);
        }
    }

    private function redirigirLogin(): never
    {
        header('Location: ' . Config::url('admin/login'));
        exit();
    }
}
