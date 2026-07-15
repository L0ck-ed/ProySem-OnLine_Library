<?php

namespace App\Controllers\Admin;

use App\Models\Departamento;
use App\Models\Facultad;

use App\Config\Config;
use App\Core\Controller;
use App\Helpers\Session;
use App\Middleware\Auth;
use App\Models\Profesor;
use App\Models\Usuario;

class ProfesorController extends Controller
{
    public function index(): void
    {
        Auth::check();
        Auth::exigirPermiso('profesores.ver');

        $buscar = trim($_GET['buscar'] ?? '');
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));

        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $profesorModel = new Profesor();

        $profesores = $profesorModel->listar($buscar, $porPagina, $offset);

        $totalRegistros = $profesorModel->contar($buscar);

        $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));

        $this->view('Admin/Profesores/listar', [
            'profesores' => $profesores,
            'buscar' => $buscar,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros,
        ]);
    }

    public function crear(): void
    {
        Auth::check();
        Auth::exigirPermiso('profesores.crear');

        $usuarioModel = new Usuario();
        $facultadModel = new Facultad();

        $usuariosDisponibles = $usuarioModel->listarDisponiblesParaProfesor();

        $facultades = $facultadModel->listarActivas();

        $this->view('Admin/Profesores/formulario', [
            'modo' => 'crear',
            'profesor' => [],
            'usuariosDisponibles' => $usuariosDisponibles,
            'facultades' => $facultades,
        ]);
    }

    public function guardar(): void
    {
        Auth::check();
        Auth::exigirPermiso('profesores.crear');

        $datos = $this->leerDatosFormulario();

        $error = $this->validarDatos($datos);

        if ($error !== null) {
            $this->regresarConError($error, $datos, 'profesores/crear');
        }

        $profesorModel = new Profesor();
        $usuarioModel = new Usuario();
        $departamentoModel = new Departamento();

        if ($profesorModel->buscarPorCip($datos['cip'])) {
            $this->regresarConError(
                'Ya existe un profesor con ese CIP.',
                $datos,
                'profesores/crear',
            );
        }

        if ($profesorModel->buscarPorUsuario((int) $datos['id_usuario'])) {
            $this->regresarConError(
                'La cuenta seleccionada ya está vinculada a un profesor.',
                $datos,
                'profesores/crear',
            );
        }

        $usuario = $usuarioModel->buscarPorId((int) $datos['id_usuario']);

        if (!$usuario || (int) $usuario['estado'] !== 1) {
            $this->regresarConError(
                'La cuenta seleccionada no existe o está inactiva.',
                $datos,
                'profesores/crear',
            );
        }

        $rolesUsuario = $usuarioModel->obtenerNombresRolesUsuario((int) $datos['id_usuario']);

        if (!in_array('Profesor', $rolesUsuario, true)) {
            $this->regresarConError(
                'La cuenta seleccionada no tiene el rol Profesor.',
                $datos,
                'profesores/crear',
            );
        }

        $departamento = $departamentoModel->buscarActivoPorFacultad(
            (int) $datos['id_departamento'],
            (int) $datos['id_facultad'],
        );

        if (!$departamento) {
            $this->regresarConError(
                'El departamento seleccionado no pertenece a la facultad indicada.',
                $datos,
                'profesores/crear',
            );
        }

        try {
            $profesorModel->crear([
                'id_usuario' => (int) $datos['id_usuario'],

                'id_departamento' => (int) $datos['id_departamento'],

                'cip' => $datos['cip'],

                'primer_nombre' => $datos['primer_nombre'],

                'segundo_nombre' => $datos['segundo_nombre'],

                'primer_apellido' => $datos['primer_apellido'],

                'segundo_apellido' => $datos['segundo_apellido'],

                'departamento' => $departamento['nombre'],

                'especialidad' => $datos['especialidad'],
            ]);

            Session::flash('success', 'Profesor registrado correctamente.');

            header('Location: ' . Config::url('profesores'));

            exit();
        } catch (\Throwable $e) {
            error_log('Error al registrar profesor: ' . $e->getMessage());

            $this->regresarConError(
                'No se pudo registrar el profesor.',
                $datos,
                'profesores/crear',
            );
        }
    }

    public function editar(): void
    {
        Auth::check();
        Auth::exigirPermiso('profesores.editar');

        $facultadModel = new Facultad();

        $facultades = $facultadModel->listarActivas();

        $idProfesor = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$idProfesor) {
            Session::flash('error', 'El profesor seleccionado no es válido.');

            header('Location: ' . Config::url('profesores'));

            exit();
        }

        $profesorModel = new Profesor();

        $profesor = $profesorModel->buscarPorId((int) $idProfesor);

        if (!$profesor) {
            Session::flash('error', 'El profesor seleccionado no existe.');

            header('Location: ' . Config::url('profesores'));

            exit();
        }

        $this->view('Admin/Profesores/formulario', [
            'modo' => 'editar',
            'profesor' => $profesor,
            'usuariosDisponibles' => [],
        ]);
    }

    public function actualizar(): void
    {
        Auth::check();
        Auth::exigirPermiso('profesores.editar');

        $datos = $this->leerDatosFormulario();

        $datos['id_profesor'] = filter_input(INPUT_POST, 'id_profesor', FILTER_VALIDATE_INT);

        if (!$datos['id_profesor']) {
            Session::flash('error', 'El profesor seleccionado no es válido.');

            header('Location: ' . Config::url('profesores'));

            exit();
        }

        $rutaEditar = 'profesores/editar?id=' . (int) $datos['id_profesor'];

        $error = $this->validarDatos($datos, false);

        if ($error !== null) {
            $this->regresarConError($error, $datos, $rutaEditar);
        }

        $profesorModel = new Profesor();
        $departamentoModel = new Departamento();

        $profesorActual = $profesorModel->buscarPorId((int) $datos['id_profesor']);

        if (!$profesorActual) {
            Session::flash('error', 'El profesor seleccionado no existe.');

            header('Location: ' . Config::url('profesores'));

            exit();
        }

        $profesorConCip = $profesorModel->buscarPorCip($datos['cip']);

        if (
            $profesorConCip &&
            (int) $profesorConCip['id_profesor'] !== (int) $datos['id_profesor']
        ) {
            $this->regresarConError('Ya existe otro profesor con ese CIP.', $datos, $rutaEditar);
        }

        $departamento = $departamentoModel->buscarActivoPorFacultad(
            (int) $datos['id_departamento'],
            (int) $datos['id_facultad'],
        );

        if (!$departamento) {
            $this->regresarConError(
                'El departamento seleccionado no pertenece a la facultad indicada.',
                $datos,
                $rutaEditar,
            );
        }

        try {
            $profesorModel->actualizar([
                'id_profesor' => (int) $datos['id_profesor'],

                'id_departamento' => (int) $datos['id_departamento'],

                'cip' => $datos['cip'],

                'primer_nombre' => $datos['primer_nombre'],

                'segundo_nombre' => $datos['segundo_nombre'],

                'primer_apellido' => $datos['primer_apellido'],

                'segundo_apellido' => $datos['segundo_apellido'],

                'departamento' => $departamento['nombre'],

                'especialidad' => $datos['especialidad'],
            ]);

            Session::flash('success', 'Profesor actualizado correctamente.');

            header('Location: ' . Config::url('profesores'));

            exit();
        } catch (\Throwable $e) {
            error_log('Error al actualizar profesor: ' . $e->getMessage());

            $this->regresarConError('No se pudo actualizar el profesor.', $datos, $rutaEditar);
        }
    }

    public function cambiarEstado(): void
    {
        Auth::check();
        Auth::exigirPermiso('profesores.eliminar');

        $idProfesor = filter_input(INPUT_POST, 'id_profesor', FILTER_VALIDATE_INT);

        $estado = $_POST['estado'] ?? null;

        if (!$idProfesor || !in_array($estado, ['0', '1'], true)) {
            Session::flash('error', 'Los datos recibidos no son válidos.');

            header('Location: ' . Config::url('profesores'));

            exit();
        }

        $profesorModel = new Profesor();

        if (!$profesorModel->buscarPorId((int) $idProfesor)) {
            Session::flash('error', 'El profesor seleccionado no existe.');

            header('Location: ' . Config::url('profesores'));

            exit();
        }

        try {
            $nuevoEstado = (int) $estado;

            $profesorModel->cambiarEstado((int) $idProfesor, $nuevoEstado);

            Session::flash(
                'success',
                $nuevoEstado === 1
                    ? 'Profesor activado correctamente.'
                    : 'Profesor desactivado correctamente.',
            );
        } catch (\Throwable $e) {
            error_log('Error al cambiar estado del profesor: ' . $e->getMessage());

            Session::flash('error', 'No se pudo cambiar el estado del profesor.');
        }

        header('Location: ' . Config::url('profesores'));

        exit();
    }

    private function leerDatosFormulario(): array
    {
        return [
            'id_usuario' => filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT),
            'id_facultad' => filter_input(INPUT_POST, 'id_facultad', FILTER_VALIDATE_INT),
            'id_departamento' => filter_input(INPUT_POST, 'id_departamento', FILTER_VALIDATE_INT),
            'cip' => trim($_POST['cip'] ?? ''),
            'primer_nombre' => trim($_POST['primer_nombre'] ?? ''),
            'segundo_nombre' => trim($_POST['segundo_nombre'] ?? ''),
            'primer_apellido' => trim($_POST['primer_apellido'] ?? ''),
            'segundo_apellido' => trim($_POST['segundo_apellido'] ?? ''),
            'especialidad' => trim($_POST['especialidad'] ?? ''),
        ];
    }

    private function validarDatos(array $datos, bool $validarUsuario = true): ?string
    {
        if (
            ($validarUsuario && !$datos['id_usuario']) ||
            !$datos['id_facultad'] ||
            !$datos['id_departamento'] ||
            $datos['cip'] === '' ||
            $datos['primer_nombre'] === '' ||
            $datos['primer_apellido'] === ''
        ) {
            return 'Debe completar todos los campos obligatorios.';
        }

        if (mb_strlen($datos['cip']) > 30) {
            return 'El CIP no puede superar los 30 caracteres.';
        }

        if (
            mb_strlen($datos['primer_nombre']) > 60 ||
            mb_strlen($datos['segundo_nombre']) > 60 ||
            mb_strlen($datos['primer_apellido']) > 60 ||
            mb_strlen($datos['segundo_apellido']) > 60
        ) {
            return 'Los nombres y apellidos no pueden superar los 60 caracteres.';
        }

        if (mb_strlen($datos['especialidad']) > 150) {
            return 'La especialidad no puede superar los 150 caracteres.';
        }

        return null;
    }
    private function regresarConError(string $mensaje, array $datos, string $ruta): void
    {
        Session::flash('error', $mensaje);

        Session::flash('old_profesor', $datos);

        header('Location: ' . Config::url($ruta));

        exit();
    }

    public function departamentosPorFacultad(): void
    {
        Auth::check();
        Auth::exigirPermiso('profesores.ver');

        header('Content-Type: application/json; charset=UTF-8');

        $idFacultad = filter_input(INPUT_GET, 'id_facultad', FILTER_VALIDATE_INT);

        if (!$idFacultad) {
            http_response_code(400);

            echo json_encode(
                [
                    'success' => false,
                    'message' => 'La facultad seleccionada no es válida.',
                    'departamentos' => [],
                ],
                JSON_UNESCAPED_UNICODE,
            );

            exit();
        }

        try {
            $departamentoModel = new Departamento();

            $departamentos = $departamentoModel->listarActivosPorFacultad((int) $idFacultad);

            echo json_encode(
                [
                    'success' => true,
                    'departamentos' => $departamentos,
                ],
                JSON_UNESCAPED_UNICODE,
            );
        } catch (\Throwable $e) {
            error_log('Error al cargar departamentos: ' . $e->getMessage());

            http_response_code(500);

            echo json_encode(
                [
                    'success' => false,
                    'message' => 'No se pudieron cargar los departamentos.',
                    'departamentos' => [],
                ],
                JSON_UNESCAPED_UNICODE,
            );
        }

        exit();
    }
}
