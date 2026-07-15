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
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $limite = 5;
        $offset = ($pagina - 1) * $limite;

        $usuarioModel = new Usuario();

        $usuarios = $usuarioModel->listar($buscar, $limite, $offset);
        $total = $usuarioModel->contar($buscar);
        $paginas = (int) ceil($total / $limite);

        $this->view('Admin/User/listar', [
            'usuarios' => $usuarios,
            'buscar' => $buscar,
            'pagina' => $pagina,
            'paginas' => $paginas,
        ]);
    }

    public function crear(): void
    {
        Auth::check();

        $usuarioModel = new Usuario();
        $roles = $usuarioModel->listarRolesActivos();

        $this->view('Admin/User/crear', [
            'roles' => $roles,
        ]);
    }

    public function editar(): void
    {
        Auth::check();

        $idUsuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$idUsuario) {
            Session::flash('error', 'El usuario seleccionado no es válido.');

            header('Location: ' . Config::url('usuarios'));
            exit();
        }

        $usuarioModel = new Usuario();

        $usuario = $usuarioModel->buscarPorId((int) $idUsuario);

        if (!$usuario) {
            Session::flash('error', 'El usuario no existe.');

            header('Location: ' . Config::url('usuarios'));
            exit();
        }

        $roles = $usuarioModel->listarRolesActivos();

        $this->view('Admin/User/editar', [
            'usuario' => $usuario,
            'roles' => $roles,
        ]);
    }

    public function guardar(): void
    {
        Auth::check();

        $nombre = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';

        $idRol = filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT);

        // Datos que se conservarán si ocurre un error.
        // No guardamos la contraseña por seguridad.
        $datosAnteriores = [
            'nombre' => $nombre,
            'usuario' => $usuario,
            'id_rol' => $idRol ?: '',
        ];

        if ($nombre === '' || $usuario === '' || $password === '' || !$idRol) {
            Session::flash('old_usuario', $datosAnteriores);
            Session::flash('error', 'Debe completar todos los campos.');

            header('Location: ' . Config::url('usuarios/crear'));
            exit();
        }

        if (!Validator::min($password, 8)) {
            Session::flash('old_usuario', $datosAnteriores);
            Session::flash('error', 'La contraseña debe tener mínimo 8 caracteres.');

            header('Location: ' . Config::url('usuarios/crear'));
            exit();
        }

        $usuarioModel = new Usuario();

        if ($usuarioModel->buscarPorUsuario($usuario)) {
            Session::flash('old_usuario', $datosAnteriores);
            Session::flash('error', 'El nombre de usuario ya existe.');

            header('Location: ' . Config::url('usuarios/crear'));
            exit();
        }

        try {
            $usuarioModel->crear([
                'nombre' => $nombre,
                'usuario' => $usuario,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'correo' => null,
                'id_rol' => (int) $idRol,
            ]);

            Session::flash('success', 'Usuario creado correctamente.');

            header('Location: ' . Config::url('usuarios'));
            exit();
        } catch (\Throwable $e) {
            error_log('Error al crear usuario: ' . $e->getMessage());

            Session::flash('old_usuario', $datosAnteriores);
            Session::flash('error', 'No se pudo crear el usuario.');

            header('Location: ' . Config::url('usuarios/crear'));
            exit();
        }
    }

    public function actualizar(): void
    {
        Auth::check();

        $idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

        $idRol = filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT);

        $nombre = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        $estado = $_POST['estado'] ?? null;

        $datosAnteriores = [
            'nombre' => $nombre,
            'usuario' => $usuario,
            'id_rol' => $idRol ?: '',
            'estado' => $estado,
        ];

        if (
            !$idUsuario ||
            !$idRol ||
            $nombre === '' ||
            $usuario === '' ||
            !in_array($estado, ['0', '1'], true)
        ) {
            Session::flash('old_usuario', $datosAnteriores);
            Session::flash('error', 'Debe completar correctamente todos los campos.');

            header('Location: ' . Config::url('usuarios/editar?id=' . (int) $idUsuario));
            exit();
        }

        if ($password !== '' && !Validator::min($password, 8)) {
            Session::flash('old_usuario', $datosAnteriores);
            Session::flash('error', 'La nueva contraseña debe tener mínimo 8 caracteres.');

            header('Location: ' . Config::url('usuarios/editar?id=' . (int) $idUsuario));
            exit();
        }

        $usuarioModel = new Usuario();

        $usuarioExistente = $usuarioModel->buscarPorUsuario($usuario);

        if ($usuarioExistente && (int) $usuarioExistente['id_usuario'] !== (int) $idUsuario) {
            Session::flash('old_usuario', $datosAnteriores);
            Session::flash('error', 'El nombre de usuario ya pertenece a otra cuenta.');

            header('Location: ' . Config::url('usuarios/editar?id=' . (int) $idUsuario));
            exit();
        }

        try {
            $usuarioModel->actualizar([
                'id_usuario' => (int) $idUsuario,
                'nombre' => $nombre,
                'usuario' => $usuario,
                'estado' => (int) $estado,
                'id_rol' => (int) $idRol,
                'password_hash' =>
                    $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null,
            ]);

            Session::flash('success', 'Usuario actualizado correctamente.');

            header('Location: ' . Config::url('usuarios'));
            exit();
        } catch (\Throwable $e) {
            error_log('Error al actualizar usuario: ' . $e->getMessage());

            Session::flash('old_usuario', $datosAnteriores);
            Session::flash('error', 'No se pudo actualizar el usuario.');

            header('Location: ' . Config::url('usuarios/editar?id=' . (int) $idUsuario));
            exit();
        }
    }
}
