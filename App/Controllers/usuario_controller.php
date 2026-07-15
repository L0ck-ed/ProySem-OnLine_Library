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
        Auth::exigirPermiso('usuarios.ver');

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
        Auth::exigirPermiso('usuarios.editar');

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

        $rolesUsuario = $usuarioModel->obtenerRolesUsuario((int) $idUsuario);

        $this->view('Admin/User/editar', [
            'usuario' => $usuario,
            'roles' => $roles,
            'rolesUsuario' => $rolesUsuario,
        ]);
    }

    public function guardar(): void
    {
        Auth::check();
        Auth::exigirPermiso('usuarios.crear');

        $nombre = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';

        $rolesRecibidos = $_POST['id_roles'] ?? [];

        $idRoles = array_values(
            array_unique(
                array_filter(
                    array_map('intval', is_array($rolesRecibidos) ? $rolesRecibidos : []),
                    fn(int $idRol): bool => $idRol > 0,
                ),
            ),
        );

        $datosAnteriores = [
            'nombre' => $nombre,
            'usuario' => $usuario,
            'id_roles' => $idRoles,
        ];

        if ($nombre === '' || $usuario === '' || $password === '' || empty($idRoles)) {
            Session::flash('old_usuario', $datosAnteriores);

            Session::flash(
                'error',
                'Debe completar todos los campos y seleccionar al menos un rol.',
            );

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

        $rolesActivos = $usuarioModel->listarRolesActivos();

        $idsRolesActivos = array_map(fn(array $rol): int => (int) $rol['id_rol'], $rolesActivos);

        foreach ($idRoles as $idRol) {
            if (!in_array($idRol, $idsRolesActivos, true)) {
                Session::flash('old_usuario', $datosAnteriores);

                Session::flash('error', 'Uno de los roles seleccionados no es válido.');

                header('Location: ' . Config::url('usuarios/crear'));
                exit();
            }
        }

        try {
            $usuarioModel->crear([
                'nombre' => $nombre,
                'usuario' => $usuario,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'correo' => null,
                'id_roles' => $idRoles,
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
        Auth::exigirPermiso('usuarios.editar');

        $idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
        $nombre = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        $estado = $_POST['estado'] ?? null;

        $rolesRecibidos = $_POST['id_roles'] ?? [];

        $idRoles = array_values(
            array_unique(
                array_filter(
                    array_map('intval', is_array($rolesRecibidos) ? $rolesRecibidos : []),
                    fn(int $idRol): bool => $idRol > 0,
                ),
            ),
        );

        $datosAnteriores = [
            'nombre' => $nombre,
            'usuario' => $usuario,
            'id_roles' => $idRoles,
            'estado' => $estado,
        ];

        $urlEditar = Config::url('usuarios/editar?id=' . (int) $idUsuario);

        if (
            !$idUsuario ||
            $nombre === '' ||
            $usuario === '' ||
            empty($idRoles) ||
            !in_array($estado, ['0', '1'], true)
        ) {
            Session::flash('old_usuario', $datosAnteriores);
            Session::flash(
                'error',
                'Debe completar correctamente todos los campos y seleccionar al menos un rol.',
            );

            header('Location: ' . $urlEditar);
            exit();
        }

        $idUsuarioSesion = (int) Session::get('id_usuario');

        if ((int) $idUsuario === $idUsuarioSesion && $estado === '0') {
            Session::flash('old_usuario', $datosAnteriores);
            Session::flash(
                'error',
                'No puedes desactivar tu propia cuenta mientras tienes la sesión iniciada.',
            );

            header('Location: ' . $urlEditar);
            exit();
        }

        if ($password !== '' && !Validator::min($password, 8)) {
            Session::flash('old_usuario', $datosAnteriores);
            Session::flash('error', 'La nueva contraseña debe tener mínimo 8 caracteres.');

            header('Location: ' . $urlEditar);
            exit();
        }

        $usuarioModel = new Usuario();

        $usuarioExistente = $usuarioModel->buscarPorUsuario($usuario);

        if ($usuarioExistente && (int) $usuarioExistente['id_usuario'] !== (int) $idUsuario) {
            Session::flash('old_usuario', $datosAnteriores);
            Session::flash('error', 'El nombre de usuario ya pertenece a otra cuenta.');

            header('Location: ' . $urlEditar);
            exit();
        }

        $rolesActivos = $usuarioModel->listarRolesActivos();

        $idsRolesActivos = array_map(fn(array $rol): int => (int) $rol['id_rol'], $rolesActivos);

        foreach ($idRoles as $idRol) {
            if (!in_array($idRol, $idsRolesActivos, true)) {
                Session::flash('old_usuario', $datosAnteriores);
                Session::flash('error', 'Uno de los roles seleccionados no es válido.');

                header('Location: ' . $urlEditar);
                exit();
            }
        }

        try {
            $usuarioModel->actualizar([
                'id_usuario' => (int) $idUsuario,
                'nombre' => $nombre,
                'usuario' => $usuario,
                'estado' => (int) $estado,
                'id_roles' => $idRoles,
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

            header('Location: ' . $urlEditar);
            exit();
        }
    }

    public function cambiarEstado(): void
    {
        Auth::check();
        Auth::exigirPermiso('usuarios.eliminar');

        $idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

        $estadoRecibido = $_POST['estado'] ?? null;

        if (!$idUsuario || !in_array($estadoRecibido, ['0', '1'], true)) {
            Session::flash('error', 'Los datos del usuario no son válidos.');

            header('Location: ' . Config::url('usuarios'));
            exit();
        }

        $idUsuarioSesion = (int) Session::get('id_usuario');

        if ($idUsuario === $idUsuarioSesion && $estadoRecibido === '0') {
            Session::flash(
                'error',
                'No puedes desactivar tu propia cuenta mientras tienes la sesión iniciada.',
            );

            header('Location: ' . Config::url('usuarios'));
            exit();
        }

        $usuarioModel = new Usuario();

        $usuario = $usuarioModel->buscarPorId((int) $idUsuario);

        if (!$usuario) {
            Session::flash('error', 'El usuario seleccionado no existe.');

            header('Location: ' . Config::url('usuarios'));
            exit();
        }

        try {
            $nuevoEstado = (int) $estadoRecibido;

            $estadoActualizado = $usuarioModel->cambiarEstado((int) $idUsuario, $nuevoEstado);

            if (!$estadoActualizado) {
                throw new \RuntimeException('La base de datos no modificó el estado del usuario.');
            }
            Session::flash(
                'success',
                $nuevoEstado === 1
                    ? 'Usuario activado correctamente.'
                    : 'Usuario desactivado correctamente.',
            );
        } catch (\Throwable $e) {
            error_log('Error al cambiar estado del usuario: ' . $e->getMessage());

            Session::flash('error', 'No se pudo cambiar el estado del usuario.');
        }

        header('Location: ' . Config::url('usuarios'));
        exit();
    }
}
