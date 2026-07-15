<?php

namespace App\Services;

use App\Config\Config;
use App\Helpers\Logger;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Models\Usuario;

class AuthService
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function login(): void
    {
        Session::start();

        $usuario = trim($_POST['usuario'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        Session::flash('old_usuario', $usuario);

        if (
            !Validator::required($usuario) ||
            !Validator::required($password)
        ) {
            Logger::login(
                $usuario,
                'campos_vacios',
                null,
                'No se completaron todos los campos.',
            );

            Session::flash(
                'error',
                'Debe completar todos los campos.',
            );

            $this->redirigirLogin();
        }

        if (
            mb_strlen($usuario) > 100 ||
            mb_strlen($password) > 255
        ) {
            Logger::login(
                $usuario,
                'datos_invalidos',
                null,
                'Los datos ingresados superan la longitud permitida.',
            );

            Session::flash(
                'error',
                'Los datos ingresados no son válidos.',
            );

            $this->redirigirLogin();
        }

        $datos = $this->usuarioModel->buscarPorUsuario(
            $usuario,
        );

        if (!$datos) {
            Logger::login(
                $usuario,
                'usuario_no_existe',
                null,
                'El usuario ingresado no existe.',
            );

            Session::flash(
                'error',
                'Usuario o contraseña incorrectos.',
            );

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

            Session::flash(
                'error',
                'Esta cuenta se encuentra inactiva.',
            );

            $this->redirigirLogin();
        }

        if ((int) $datos['bloqueado'] === 1) {
            Logger::login(
                $usuario,
                'usuario_bloqueado',
                $idUsuario,
                'La cuenta está bloqueada por intentos fallidos.',
            );

            Session::flash(
                'error',
                'Este usuario está bloqueado por intentos fallidos.',
            );

            $this->redirigirLogin();
        }

        if (
            !password_verify(
                $password,
                (string) $datos['password'],
            )
        ) {
            $this->usuarioModel->aumentarIntentos(
                $idUsuario,
            );

            $intentos =
                (int) $datos['intentos_fallidos'] + 1;

            Logger::login(
                $usuario,
                'password_incorrecta',
                $idUsuario,
                "Intento fallido número {$intentos}.",
            );

            if ($intentos >= 3) {
                $this->usuarioModel->bloquearUsuario(
                    $idUsuario,
                );

                Logger::login(
                    $usuario,
                    'bloqueado_por_intentos',
                    $idUsuario,
                    'La cuenta fue bloqueada después de tres intentos.',
                );

                Session::flash(
                    'error',
                    'Usuario bloqueado por 3 intentos fallidos.',
                );
            } else {
                Session::flash(
                    'error',
                    "Usuario o contraseña incorrectos. Intento {$intentos} de 3.",
                );
            }

            $this->redirigirLogin();
        }

        $this->usuarioModel->actualizarLogin(
            $idUsuario,
        );

        Logger::login(
            $usuario,
            'correcto',
            $idUsuario,
            'Inicio de sesión exitoso.',
        );

        session_regenerate_id(true);

        $roles = $this->usuarioModel
            ->obtenerNombresRolesUsuario(
                $idUsuario,
            );

        if (empty($roles)) {
            Logger::login(
                $usuario,
                'sin_roles',
                $idUsuario,
                'El usuario no tiene roles activos asignados.',
            );

            Session::flash(
                'error',
                'La cuenta no tiene un rol activo asignado.',
            );

            $this->redirigirLogin();
        }

        $permisos = $this->usuarioModel
            ->obtenerPermisosUsuario(
                $idUsuario,
            );

        Session::set('id_usuario', $idUsuario);
        Session::set('usuario', $datos['usuario']);
        Session::set('nombre', $datos['nombre']);
        Session::set('roles', $roles);
        Session::set('permisos', $permisos);
        Session::set('rol', $roles[0]);

        Session::getFlash('old_usuario');

        header(
            'Location: ' .
            Config::url('dashboard'),
        );

        exit();
    }

    public function logout(): void
    {
        Session::start();

        $clavesAdministrativas = [
            'id_usuario',
            'usuario',
            'nombre',
            'roles',
            'permisos',
            'rol',
            'old_usuario',
        ];

        foreach ($clavesAdministrativas as $clave) {
            unset($_SESSION[$clave]);
        }

        session_regenerate_id(true);

        header(
            'Location: ' . Config::url(),
        );

        exit();
    }

    private function redirigirLogin(): never
    {
        header(
            'Location: ' .
            Config::url('admin/login'),
        );

        exit();
    }
}
