<?php

namespace App\Controllers\Admin;

use App\Config\Config;
use App\Core\Controller;
use App\Helpers\Session;
use App\Middleware\Auth;
use App\Models\Carrera;
use App\Models\Estudiante;
use App\Models\Facultad;
use App\Models\Usuario;

class EstudianteController extends Controller
{
    public function index(): void
    {
        Auth::check();
        Auth::exigirPermiso('estudiantes.ver');

        $buscar = trim($_GET['buscar'] ?? '');
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $estudianteModel = new Estudiante();

        $estudiantes = $estudianteModel->listar($buscar, $porPagina, $offset);

        $totalRegistros = $estudianteModel->contar($buscar);

        $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));

        $this->view('Admin/Estudiantes/listar', [
            'estudiantes' => $estudiantes,
            'buscar' => $buscar,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros,
        ]);
    }

    public function crear(): void
    {
        Auth::check();
        Auth::exigirPermiso('estudiantes.crear');

        $facultadModel = new Facultad();
        $usuarioModel = new Usuario();

        $facultades = $facultadModel->listarActivas();

        $usuariosDisponibles = $usuarioModel->listarDisponiblesParaEstudiante();

        $this->view('Admin/Estudiantes/crear', [
            'facultades' => $facultades,
            'usuariosDisponibles' => $usuariosDisponibles,
            'carreras' => [],
        ]);
    }

    public function guardar(): void
    {
        Auth::check();
        Auth::exigirPermiso('estudiantes.crear');

        $datos = [
            'id_usuario' => filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT),
            'id_facultad' => filter_input(INPUT_POST, 'id_facultad', FILTER_VALIDATE_INT),
            'id_carrera' => filter_input(INPUT_POST, 'id_carrera', FILTER_VALIDATE_INT),
            'cip' => trim($_POST['cip'] ?? ''),
            'primer_nombre' => trim($_POST['primer_nombre'] ?? ''),
            'segundo_nombre' => trim($_POST['segundo_nombre'] ?? ''),
            'primer_apellido' => trim($_POST['primer_apellido'] ?? ''),
            'segundo_apellido' => trim($_POST['segundo_apellido'] ?? ''),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
        ];

        $regresar = static function (string $mensaje, array $datos): void {
            Session::flash('error', $mensaje);

            Session::flash('old_estudiante', $datos);

            header('Location: ' . Config::url('estudiantes/crear'));

            exit();
        };

        if (
            !$datos['id_usuario'] ||
            !$datos['id_facultad'] ||
            !$datos['id_carrera'] ||
            $datos['cip'] === '' ||
            $datos['primer_nombre'] === '' ||
            $datos['primer_apellido'] === '' ||
            $datos['fecha_nacimiento'] === ''
        ) {
            $regresar('Debe completar todos los campos obligatorios.', $datos);
        }

        if (
            mb_strlen($datos['cip']) > 30 ||
            mb_strlen($datos['primer_nombre']) > 50 ||
            mb_strlen($datos['segundo_nombre']) > 50 ||
            mb_strlen($datos['primer_apellido']) > 50 ||
            mb_strlen($datos['segundo_apellido']) > 50
        ) {
            $regresar('Uno o más campos superan la longitud permitida.', $datos);
        }

        $fecha = \DateTime::createFromFormat('Y-m-d', $datos['fecha_nacimiento']);

        $fechaValida = $fecha !== false && $fecha->format('Y-m-d') === $datos['fecha_nacimiento'];

        if (!$fechaValida) {
            $regresar('La fecha de nacimiento no es válida.', $datos);
        }

        $hoy = new \DateTime('today');

        if ($fecha > $hoy) {
            $regresar('La fecha de nacimiento no puede ser futura.', $datos);
        }

        $estudianteModel = new Estudiante();
        $usuarioModel = new Usuario();
        $carreraModel = new Carrera();

        if ($estudianteModel->buscarPorCip($datos['cip'])) {
            $regresar('Ya existe un estudiante registrado con ese CIP.', $datos);
        }

        if ($estudianteModel->buscarPorUsuario((int) $datos['id_usuario'])) {
            $regresar('La cuenta seleccionada ya está vinculada a un estudiante.', $datos);
        }

        $usuario = $usuarioModel->buscarPorId((int) $datos['id_usuario']);

        if (!$usuario || (int) $usuario['estado'] !== 1) {
            $regresar('La cuenta seleccionada no existe o está inactiva.', $datos);
        }

        $rolesUsuario = $usuarioModel->obtenerNombresRolesUsuario((int) $datos['id_usuario']);

        if (!in_array('Estudiante', $rolesUsuario, true)) {
            $regresar('La cuenta seleccionada no tiene el rol Estudiante.', $datos);
        }

        $carreraValida = $carreraModel->buscarActivaPorFacultad(
            (int) $datos['id_carrera'],
            (int) $datos['id_facultad'],
        );

        if (!$carreraValida) {
            $regresar('La carrera seleccionada no pertenece a la facultad indicada.', $datos);
        }

        try {
            $estudianteModel->crear([
                'id_usuario' => (int) $datos['id_usuario'],
                'id_carrera' => (int) $datos['id_carrera'],
                'cip' => $datos['cip'],
                'primer_nombre' => $datos['primer_nombre'],
                'segundo_nombre' => $datos['segundo_nombre'],
                'primer_apellido' => $datos['primer_apellido'],
                'segundo_apellido' => $datos['segundo_apellido'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
            ]);

            Session::flash('success', 'Estudiante registrado correctamente.');

            header('Location: ' . Config::url('estudiantes'));

            exit();
        } catch (\Throwable $e) {
            error_log('Error al registrar estudiante: ' . $e->getMessage());

            $regresar('No se pudo registrar el estudiante.', $datos);
        }
    }

    public function carrerasPorFacultad(): void
    {
        Auth::check();
        Auth::exigirPermiso('estudiantes.crear');

        header('Content-Type: application/json; charset=UTF-8');

        $idFacultad = filter_input(INPUT_GET, 'id_facultad', FILTER_VALIDATE_INT);

        if (!$idFacultad) {
            http_response_code(400);

            echo json_encode(
                [
                    'success' => false,
                    'message' => 'La facultad seleccionada no es válida.',
                    'carreras' => [],
                ],
                JSON_UNESCAPED_UNICODE,
            );

            exit();
        }

        try {
            $carreraModel = new Carrera();

            $carreras = $carreraModel->listarActivasPorFacultad((int) $idFacultad);

            echo json_encode(
                [
                    'success' => true,
                    'carreras' => $carreras,
                ],
                JSON_UNESCAPED_UNICODE,
            );
        } catch (\Throwable $e) {
            error_log('Error al cargar carreras por facultad: ' . $e->getMessage());

            http_response_code(500);

            echo json_encode(
                [
                    'success' => false,
                    'message' => 'No se pudieron cargar las carreras.',
                    'carreras' => [],
                ],
                JSON_UNESCAPED_UNICODE,
            );
        }

        exit();
    }

    public function editar(): void
    {
        Auth::check();
        Auth::exigirPermiso('estudiantes.editar');

        $idEstudiante = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$idEstudiante) {
            Session::flash('error', 'El estudiante seleccionado no es válido.');

            header('Location: ' . Config::url('estudiantes'));

            exit();
        }

        $estudianteModel = new Estudiante();
        $facultadModel = new Facultad();

        $estudiante = $estudianteModel->buscarPorId((int) $idEstudiante);

        if (!$estudiante) {
            Session::flash('error', 'El estudiante seleccionado no existe.');

            header('Location: ' . Config::url('estudiantes'));

            exit();
        }

        $facultades = $facultadModel->listarActivas();

        $this->view('Admin/Estudiantes/editar', [
            'estudiante' => $estudiante,
            'facultades' => $facultades,
        ]);
    }

    public function actualizar(): void
    {
        Auth::check();
        Auth::exigirPermiso('estudiantes.editar');

        $datos = [
            'id_estudiante' => filter_input(INPUT_POST, 'id_estudiante', FILTER_VALIDATE_INT),
            'id_facultad' => filter_input(INPUT_POST, 'id_facultad', FILTER_VALIDATE_INT),
            'id_carrera' => filter_input(INPUT_POST, 'id_carrera', FILTER_VALIDATE_INT),
            'cip' => trim($_POST['cip'] ?? ''),
            'primer_nombre' => trim($_POST['primer_nombre'] ?? ''),
            'segundo_nombre' => trim($_POST['segundo_nombre'] ?? ''),
            'primer_apellido' => trim($_POST['primer_apellido'] ?? ''),
            'segundo_apellido' => trim($_POST['segundo_apellido'] ?? ''),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
        ];

        $urlEditar = Config::url('estudiantes/editar?id=' . (int) $datos['id_estudiante']);

        $regresar = static function (string $mensaje, array $datos, string $urlEditar): void {
            Session::flash('error', $mensaje);
            Session::flash('old_estudiante', $datos);

            header('Location: ' . $urlEditar);
            exit();
        };

        if (
            !$datos['id_estudiante'] ||
            !$datos['id_facultad'] ||
            !$datos['id_carrera'] ||
            $datos['cip'] === '' ||
            $datos['primer_nombre'] === '' ||
            $datos['primer_apellido'] === '' ||
            $datos['fecha_nacimiento'] === ''
        ) {
            $regresar('Debe completar todos los campos obligatorios.', $datos, $urlEditar);
        }

        if (
            mb_strlen($datos['cip']) > 30 ||
            mb_strlen($datos['primer_nombre']) > 50 ||
            mb_strlen($datos['segundo_nombre']) > 50 ||
            mb_strlen($datos['primer_apellido']) > 50 ||
            mb_strlen($datos['segundo_apellido']) > 50
        ) {
            $regresar('Uno o más campos superan la longitud permitida.', $datos, $urlEditar);
        }

        $fecha = \DateTime::createFromFormat('Y-m-d', $datos['fecha_nacimiento']);

        $fechaValida = $fecha !== false && $fecha->format('Y-m-d') === $datos['fecha_nacimiento'];

        if (!$fechaValida) {
            $regresar('La fecha de nacimiento no es válida.', $datos, $urlEditar);
        }

        if ($fecha > new \DateTime('today')) {
            $regresar('La fecha de nacimiento no puede ser futura.', $datos, $urlEditar);
        }

        $estudianteModel = new Estudiante();
        $carreraModel = new Carrera();

        $estudianteActual = $estudianteModel->buscarPorId((int) $datos['id_estudiante']);

        if (!$estudianteActual) {
            Session::flash('error', 'El estudiante seleccionado no existe.');

            header('Location: ' . Config::url('estudiantes'));

            exit();
        }

        $estudianteConCip = $estudianteModel->buscarPorCip($datos['cip']);

        if (
            $estudianteConCip &&
            (int) $estudianteConCip['id_estudiante'] !== (int) $datos['id_estudiante']
        ) {
            $regresar('Ya existe otro estudiante con ese CIP.', $datos, $urlEditar);
        }

        $carreraValida = $carreraModel->buscarActivaPorFacultad(
            (int) $datos['id_carrera'],
            (int) $datos['id_facultad'],
        );

        if (!$carreraValida) {
            $regresar(
                'La carrera seleccionada no pertenece a la facultad indicada.',
                $datos,
                $urlEditar,
            );
        }

        try {
            $estudianteModel->actualizar([
                'id_estudiante' => (int) $datos['id_estudiante'],
                'id_carrera' => (int) $datos['id_carrera'],
                'cip' => $datos['cip'],
                'primer_nombre' => $datos['primer_nombre'],
                'segundo_nombre' => $datos['segundo_nombre'],
                'primer_apellido' => $datos['primer_apellido'],
                'segundo_apellido' => $datos['segundo_apellido'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
            ]);

            Session::flash('success', 'Estudiante actualizado correctamente.');

            header('Location: ' . Config::url('estudiantes'));

            exit();
        } catch (\Throwable $e) {
            error_log('Error al actualizar estudiante: ' . $e->getMessage());

            $regresar('No se pudo actualizar el estudiante.', $datos, $urlEditar);
        }
    }

    public function cambiarEstado(): void
    {
        Auth::check();
        Auth::exigirPermiso('estudiantes.eliminar');

        $idEstudiante = filter_input(INPUT_POST, 'id_estudiante', FILTER_VALIDATE_INT);

        $estado = $_POST['estado'] ?? null;

        if (!$idEstudiante || !in_array($estado, ['0', '1'], true)) {
            Session::flash('error', 'Los datos recibidos no son válidos.');

            header('Location: ' . Config::url('estudiantes'));

            exit();
        }

        $estudianteModel = new Estudiante();

        $estudiante = $estudianteModel->buscarPorId((int) $idEstudiante);

        if (!$estudiante) {
            Session::flash('error', 'El estudiante seleccionado no existe.');

            header('Location: ' . Config::url('estudiantes'));

            exit();
        }

        try {
            $nuevoEstado = (int) $estado;

            $estudianteModel->cambiarEstado((int) $idEstudiante, $nuevoEstado);

            Session::flash(
                'success',
                $nuevoEstado === 1
                    ? 'Estudiante activado correctamente.'
                    : 'Estudiante desactivado correctamente.',
            );
        } catch (\Throwable $e) {
            error_log('Error al cambiar el estado del estudiante: ' . $e->getMessage());

            Session::flash('error', 'No se pudo cambiar el estado del estudiante.');
        }

        header('Location: ' . Config::url('estudiantes'));

        exit();
    }
}
