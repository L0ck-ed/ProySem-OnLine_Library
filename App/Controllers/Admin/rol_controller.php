<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middleware\Auth;
use App\Models\Rol;
use App\Helpers\Session;
use App\Config\Config;

class RolController extends Controller
{
    public function index(): void
    {
        Auth::check();
        Auth::exigirPermiso('roles.gestionar');

        $rolModel = new Rol();
        $roles = $rolModel->listar();

        $this->view('Admin/Roles/listar', [
            'roles' => $roles,
        ]);
    }

    public function permisos(): void
    {
        Auth::check();
        Auth::exigirPermiso('roles.gestionar');

        $idRol = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$idRol) {
            \App\Helpers\Session::flash('error', 'El rol seleccionado no es válido.');

            header('Location: ' . \App\Config\Config::url('roles'));
            exit();
        }

        $rolModel = new Rol();

        $rol = $rolModel->buscarPorId((int) $idRol);

        if (!$rol) {
            \App\Helpers\Session::flash('error', 'El rol seleccionado no existe.');

            header('Location: ' . \App\Config\Config::url('roles'));
            exit();
        }

        $permisos = $rolModel->listarPermisos();

        $permisosAsignados = $rolModel->obtenerPermisosDelRol((int) $idRol);

        $this->view('Admin/Roles/permisos', [
            'rol' => $rol,
            'permisos' => $permisos,
            'permisosAsignados' => $permisosAsignados,
        ]);
    }

    public function guardarPermisos(): void
    {
        Auth::check();
        Auth::exigirPermiso('roles.gestionar');

        $idRol = filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT);

        $permisosRecibidos = $_POST['id_permisos'] ?? [];

        $idPermisos = array_values(
            array_unique(
                array_filter(
                    array_map('intval', is_array($permisosRecibidos) ? $permisosRecibidos : []),
                    fn(int $idPermiso): bool => $idPermiso > 0,
                ),
            ),
        );

        if (!$idRol) {
            Session::flash('error', 'El rol seleccionado no es válido.');

            header('Location: ' . Config::url('roles'));
            exit();
        }

        $rolModel = new Rol();

        $rol = $rolModel->buscarPorId((int) $idRol);

        if (!$rol) {
            Session::flash('error', 'El rol seleccionado no existe.');

            header('Location: ' . Config::url('roles'));
            exit();
        }

        /*
         * Comprueba que todos los permisos enviados
         * existan y estén activos.
         */
        $permisosDisponibles = $rolModel->listarPermisos();

        $idsDisponibles = array_map(
            fn(array $permiso): int => (int) $permiso['id_permiso'],
            $permisosDisponibles,
        );

        foreach ($idPermisos as $idPermiso) {
            if (!in_array($idPermiso, $idsDisponibles, true)) {
                Session::flash('error', 'Uno de los permisos seleccionados no es válido.');

                header('Location: ' . Config::url('roles/permisos?id=' . (int) $idRol));
                exit();
            }
        }

        try {
            $rolModel->actualizarPermisos((int) $idRol, $idPermisos);

            Session::flash('success', 'Permisos del rol actualizados correctamente.');

            header('Location: ' . Config::url('roles'));
            exit();
        } catch (\Throwable $e) {
            error_log('Error al actualizar permisos del rol: ' . $e->getMessage());

            Session::flash('error', 'No se pudieron actualizar los permisos del rol.');

            header('Location: ' . Config::url('roles/permisos?id=' . (int) $idRol));
            exit();
        }
    }

    public function crear(): void
    {
        Auth::check();
        Auth::exigirPermiso('roles.gestionar');

        $this->view('Admin/Roles/crear');
    }

    public function guardar(): void
    {
        Auth::check();
        Auth::exigirPermiso('roles.gestionar');

        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        $datosAnteriores = [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
        ];

        if ($nombre === '') {
            Session::flash('old_rol', $datosAnteriores);
            Session::flash('error', 'El nombre del rol es obligatorio.');

            header('Location: ' . Config::url('roles/crear'));
            exit();
        }

        if (mb_strlen($nombre) > 50) {
            Session::flash('old_rol', $datosAnteriores);
            Session::flash('error', 'El nombre del rol no puede superar los 50 caracteres.');

            header('Location: ' . Config::url('roles/crear'));
            exit();
        }

        if (mb_strlen($descripcion) > 255) {
            Session::flash('old_rol', $datosAnteriores);
            Session::flash('error', 'La descripción no puede superar los 255 caracteres.');

            header('Location: ' . Config::url('roles/crear'));
            exit();
        }

        $rolModel = new Rol();

        if ($rolModel->buscarPorNombre($nombre)) {
            Session::flash('old_rol', $datosAnteriores);
            Session::flash('error', 'Ya existe un rol con ese nombre.');

            header('Location: ' . Config::url('roles/crear'));
            exit();
        }

        try {
            $rolModel->crear([
                'nombre' => $nombre,
                'descripcion' => $descripcion !== '' ? $descripcion : null,
            ]);

            Session::flash('success', 'Rol creado correctamente.');

            header('Location: ' . Config::url('roles'));
            exit();
        } catch (\Throwable $e) {
            error_log('Error al crear rol: ' . $e->getMessage());

            Session::flash('old_rol', $datosAnteriores);
            Session::flash('error', 'No se pudo crear el rol.');

            header('Location: ' . Config::url('roles/crear'));
            exit();
        }
    }

    public function editar(): void
    {
        Auth::check();
        Auth::exigirPermiso('roles.gestionar');

        $idRol = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$idRol) {
            Session::flash('error', 'El rol seleccionado no es válido.');

            header('Location: ' . Config::url('roles'));
            exit();
        }

        $rolModel = new Rol();

        $rol = $rolModel->buscarPorId((int) $idRol);

        if (!$rol) {
            Session::flash('error', 'El rol seleccionado no existe.');

            header('Location: ' . Config::url('roles'));
            exit();
        }

        $this->view('Admin/Roles/editar', [
            'rol' => $rol,
        ]);
    }

    public function actualizar(): void
    {
        Auth::check();
        Auth::exigirPermiso('roles.gestionar');

        $idRol = filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT);

        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $estado = $_POST['estado'] ?? null;

        $datosAnteriores = [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'estado' => $estado,
        ];

        $urlEditar = Config::url('roles/editar?id=' . (int) $idRol);

        if (!$idRol || $nombre === '' || !in_array($estado, ['0', '1'], true)) {
            Session::flash('old_rol', $datosAnteriores);
            Session::flash('error', 'Debe completar correctamente todos los campos.');

            header('Location: ' . $urlEditar);
            exit();
        }

        if (mb_strlen($nombre) > 50) {
            Session::flash('old_rol', $datosAnteriores);
            Session::flash('error', 'El nombre del rol no puede superar los 50 caracteres.');

            header('Location: ' . $urlEditar);
            exit();
        }

        if (mb_strlen($descripcion) > 255) {
            Session::flash('old_rol', $datosAnteriores);
            Session::flash('error', 'La descripción no puede superar los 255 caracteres.');

            header('Location: ' . $urlEditar);
            exit();
        }

        $rolModel = new Rol();

        $rolActual = $rolModel->buscarPorId((int) $idRol);

        if (!$rolActual) {
            Session::flash('error', 'El rol seleccionado no existe.');

            header('Location: ' . Config::url('roles'));
            exit();
        }

        $rolConMismoNombre = $rolModel->buscarPorNombre($nombre);

        if ($rolConMismoNombre && (int) $rolConMismoNombre['id_rol'] !== (int) $idRol) {
            Session::flash('old_rol', $datosAnteriores);
            Session::flash('error', 'Ya existe otro rol con ese nombre.');

            header('Location: ' . $urlEditar);
            exit();
        }

        try {
            $rolModel->actualizar([
                'id_rol' => (int) $idRol,
                'nombre' => $nombre,
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'estado' => (int) $estado,
            ]);

            Session::flash('success', 'Rol actualizado correctamente.');

            header('Location: ' . Config::url('roles'));
            exit();
        } catch (\Throwable $e) {
            error_log('Error al actualizar rol: ' . $e->getMessage());

            Session::flash('old_rol', $datosAnteriores);
            Session::flash('error', 'No se pudo actualizar el rol.');

            header('Location: ' . $urlEditar);
            exit();
        }
    }

    public function cambiarEstado(): void
    {
        Auth::check();
        Auth::exigirPermiso('roles.gestionar');

        $idRol = filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT);

        $estadoRecibido = $_POST['estado'] ?? null;

        if (!$idRol || !in_array($estadoRecibido, ['0', '1'], true)) {
            Session::flash('error', 'Los datos del rol no son válidos.');

            header('Location: ' . Config::url('roles'));
            exit();
        }

        $rolModel = new Rol();
        $rol = $rolModel->buscarPorId((int) $idRol);

        if (!$rol) {
            Session::flash('error', 'El rol seleccionado no existe.');

            header('Location: ' . Config::url('roles'));
            exit();
        }

        if ($rol['nombre'] === 'Administrador' && $estadoRecibido === '0') {
            Session::flash('error', 'El rol Administrador no puede ser desactivado.');

            header('Location: ' . Config::url('roles'));
            exit();
        }

        try {
            $nuevoEstado = (int) $estadoRecibido;

            $rolModel->cambiarEstado((int) $idRol, $nuevoEstado);

            Session::flash(
                'success',
                $nuevoEstado === 1
                    ? 'Rol activado correctamente.'
                    : 'Rol desactivado correctamente.',
            );
        } catch (\Throwable $e) {
            error_log('Error al cambiar el estado del rol: ' . $e->getMessage());

            Session::flash('error', 'No se pudo cambiar el estado del rol.');
        }

        header('Location: ' . Config::url('roles'));
        exit();
    }
}
