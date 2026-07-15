<?php

namespace App\Services;

use App\Models\Usuario;
use App\Helpers\Session;
use App\Helpers\Sanitizer;
use App\Helpers\Validator;
use App\Helpers\Logger;
use App\Config\Config;

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
    $password = $_POST['password'] ?? '';

    if (
        !Validator::required($usuario) ||
        !Validator::required($password)
    ) {
        Logger::login(
            $usuario,
            'campos_vacios',
            null,
            'No se completaron todos los campos.'
        );

        Session::flash(
            'error',
            'Debe completar todos los campos.'
        );

        header('Location: ' . Config::url());
        exit;
    }

    $datos = $this->usuarioModel->buscarPorUsuario($usuario);

    if (!$datos) {
        Logger::login(
            $usuario,
            'usuario_no_existe',
            null,
            'El usuario ingresado no existe.'
        );

        Session::flash(
            'error',
            'Usuario o contraseña incorrectos.'
        );

        header('Location: ' . Config::url());
        exit;
    }

    $idUsuario = (int) $datos['id_usuario'];

    if ((int) $datos['estado'] !== 1) {
        Logger::login(
            $usuario,
            'usuario_inactivo',
            $idUsuario,
            'La cuenta se encuentra inactiva.'
        );

        Session::flash(
            'error',
            'Esta cuenta se encuentra inactiva.'
        );

        header('Location: ' . Config::url());
        exit;
    }

    if ((int) $datos['bloqueado'] === 1) {
        Logger::login(
            $usuario,
            'usuario_bloqueado',
            $idUsuario,
            'La cuenta está bloqueada por intentos fallidos.'
        );

        Session::flash(
            'error',
            'Este usuario está bloqueado por intentos fallidos.'
        );

        header('Location: ' . Config::url());
        exit;
    }

    if (!password_verify($password, $datos['password'])) {
        $this->usuarioModel->aumentarIntentos($idUsuario);

        $intentos = (int) $datos['intentos_fallidos'] + 1;

        Logger::login(
            $usuario,
            'password_incorrecta',
            $idUsuario,
            "Intento fallido número {$intentos}."
        );

        if ($intentos >= 3) {
            $this->usuarioModel->bloquearUsuario($idUsuario);

            Logger::login(
                $usuario,
                'bloqueado_por_intentos',
                $idUsuario,
                'La cuenta fue bloqueada después de tres intentos.'
            );

            Session::flash(
                'error',
                'Usuario bloqueado por 3 intentos fallidos.'
            );
        } else {
            Session::flash(
                'error',
                "Usuario o contraseña incorrectos. Intento {$intentos} de 3."
            );
        }

        header('Location: ' . Config::url());
        exit;
    }

    $this->usuarioModel->actualizarLogin($idUsuario);

    Logger::login(
        $usuario,
        'correcto',
        $idUsuario,
        'Inicio de sesión exitoso.'
    );

    session_regenerate_id(true);

    Session::set('id_usuario', $idUsuario);
    Session::set('usuario', $datos['usuario']);
    Session::set('nombre', $datos['nombre']);
    Session::set('rol', $datos['rol']);

    header('Location: ' . Config::url('dashboard'));
    exit;
}

    public function logout(): void
    {
        Session::destroy();
        header('Location: ' . Config::url());
        exit;
    }
}
