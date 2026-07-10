<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\Auth;
use App\Models\Usuario;
use App\Helpers\Sanitizer;
use App\Helpers\Validator;
use App\Helpers\Session;
use App\Config\Config;

class UsuarioController extends Controller
{
    public function index(): void
    {
        Auth::check();

        $buscar = Sanitizer::text($_GET['buscar'] ?? '');
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $limite = 5;
        $offset = ($pagina - 1) * $limite;

        $usuarioModel = new Usuario();

        $usuarios = $usuarioModel->listar($buscar, $limite, $offset);
        $total = $usuarioModel->contar($buscar);
        $paginas = (int)ceil($total / $limite);

        $this->view('Admin/User/listar', [
            'usuarios' => $usuarios,
            'buscar' => $buscar,
            'pagina' => $pagina,
            'paginas' => $paginas
        ]);
    }

    public function crear(): void
    {
        Auth::check();
        $this->view('Admin/User/crear');
    }

    public function guardar(): void
    {
        Auth::check();

        $nombre = Sanitizer::text($_POST['nombre'] ?? '');
        $usuario = Sanitizer::text($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        $rol = Sanitizer::text($_POST['rol'] ?? 'Bibliotecario');

        if (!Validator::required($nombre) || !Validator::required($usuario) || !Validator::required($password)) {
            Session::flash('error', 'Debe completar todos los campos.');
            header('Location: ' . Config::url('usuarios/crear'));
            exit;
        }

        if (!Validator::min($password, 6)) {
            Session::flash('error', 'La contraseña debe tener mínimo 6 caracteres.');
            header('Location: ' . Config::url('usuarios/crear'));
            exit;
        }

        $usuarioModel = new Usuario();

        if ($usuarioModel->buscarPorUsuario($usuario)) {
            Session::flash('error', 'El usuario ya existe.');
            header('Location: ' . Config::url('usuarios/crear'));
            exit;
        }

        $usuarioModel->crear([
            'nombre' => $nombre,
            'usuario' => $usuario,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'rol' => $rol
        ]);

        Session::flash('success', 'Usuario creado correctamente.');
        header('Location: ' . Config::url('usuarios'));
        exit;
    }
}
