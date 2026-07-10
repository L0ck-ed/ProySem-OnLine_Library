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

        $usuario = Sanitizer::text($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!Validator::required($usuario) || !Validator::required($password)) {
            Logger::login($usuario, 'campos_vacios');
            Session::flash('error', 'Debe completar todos los campos.');
            header('Location: ' . Config::url());
            exit;
        }

        $datos = $this->usuarioModel->buscarPorUsuario($usuario);

        if (!$datos) {
            Logger::login($usuario, 'usuario_no_existe');
            Session::flash('error', 'Usuario o contraseña incorrectos.');
            header('Location: ' . Config::url());
            exit;
        }

        if (($datos['bloqueado'] ?? 0) == 1) {
            Logger::login($usuario, 'bloqueado');
            Session::flash('error', 'Este usuario está bloqueado por intentos fallidos.');
            header('Location: ' . Config::url());
            exit;
        }

        if (!password_verify($password, $datos['password'])) {
            $this->usuarioModel->aumentarIntentos((int)$datos['id_usuario']);
            $intentos = (int)$datos['intentos_fallidos'] + 1;

            Logger::login($usuario, 'password_incorrecta');

            if ($intentos >= 3) {
                $this->usuarioModel->bloquearUsuario((int)$datos['id_usuario']);
                Logger::login($usuario, 'bloqueado_por_intentos');
                Session::flash('error', 'Usuario bloqueado por 3 intentos fallidos.');
            } else {
                Session::flash('error', "Usuario o contraseña incorrectos. Intento {$intentos} de 3.");
            }

            header('Location: ' . Config::url());
            exit;
        }

        $this->usuarioModel->actualizarLogin((int)$datos['id_usuario']);
        Logger::login($usuario, 'correcto');

        Session::set('id_usuario', $datos['id_usuario']);
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
