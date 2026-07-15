<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Config;
use App\Core\Controller;
use App\Helpers\Session;
use App\Middleware\Auth;
use App\Models\Carrera;
use App\Models\Departamento;
use App\Models\Facultad;
use Throwable;

class EstructuraAcademicaController extends Controller
{
    private const TIPOS = ['facultad', 'departamento', 'carrera'];

    public function index(): void
    {
        Auth::check();
        Auth::exigirPermiso('estructura.ver');

        $buscar = trim((string) ($_GET['buscar'] ?? ''));
        $facultades = [];
        $departamentos = [];
        $carreras = [];
        $errorEstructura = '';

        try {
            $facultades = (new Facultad())->listarGestion($buscar);
            $departamentos = (new Departamento())->listarGestion($buscar);
            $carreras = (new Carrera())->listarGestion($buscar);
        } catch (Throwable $e) {
            error_log('Error al cargar estructura académica: ' . $e->getMessage());
            $errorEstructura = 'No se pudo cargar la estructura académica. Ejecuta el archivo de actualización SQL incluido en DataBase.';
        }

        $this->view('Admin/EstructuraAcademica/index', [
            'buscar' => $buscar,
            'facultades' => $facultades,
            'departamentos' => $departamentos,
            'carreras' => $carreras,
            'errorEstructura' => $errorEstructura,
        ]);
    }

    public function crear(): void
    {
        Auth::check();
        Auth::exigirPermiso('estructura.crear');

        $tipo = $this->tipoValido((string) ($_GET['tipo'] ?? ''));

        if ($tipo === null) {
            $this->redirigirConError('El tipo de registro seleccionado no es válido.');
        }

        $this->mostrarFormulario($tipo, 'crear', []);
    }

    public function guardar(): void
    {
        Auth::check();
        Auth::exigirPermiso('estructura.crear');

        $tipo = $this->tipoValido((string) ($_POST['tipo'] ?? ''));

        if ($tipo === null) {
            $this->redirigirConError('El tipo de registro seleccionado no es válido.');
        }

        $datos = $this->leerDatosFormulario($tipo);
        $error = $this->validarDatos($tipo, $datos);

        if ($error !== null) {
            $this->regresarFormulario($tipo, 'crear', $error, $datos);
        }

        try {
            $this->validarRelaciones($tipo, $datos);
            $this->validarDuplicado($tipo, $datos, null);
            $this->crearRegistro($tipo, $datos);

            Session::flash('success', $this->nombreTipo($tipo) . ': registro creado correctamente.');
            $this->redirigirListado($tipo);
        } catch (Throwable $e) {
            error_log('Error al crear ' . $tipo . ': ' . $e->getMessage());
            $mensaje = $e instanceof \DomainException
                ? $e->getMessage()
                : 'No se pudo registrar ' . strtolower($this->nombreTipo($tipo)) . '.';
            $this->regresarFormulario($tipo, 'crear', $mensaje, $datos);
        }
    }

    public function editar(): void
    {
        Auth::check();
        Auth::exigirPermiso('estructura.editar');

        $tipo = $this->tipoValido((string) ($_GET['tipo'] ?? ''));
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($tipo === null || !$id) {
            $this->redirigirConError('El registro seleccionado no es válido.');
        }

        $registro = $this->buscarRegistro($tipo, (int) $id);

        if (!$registro) {
            $this->redirigirConError('El registro seleccionado no existe.');
        }

        $this->mostrarFormulario($tipo, 'editar', $registro);
    }

    public function actualizar(): void
    {
        Auth::check();
        Auth::exigirPermiso('estructura.editar');

        $tipo = $this->tipoValido((string) ($_POST['tipo'] ?? ''));
        $id = filter_input(INPUT_POST, 'id_registro', FILTER_VALIDATE_INT);

        if ($tipo === null || !$id) {
            $this->redirigirConError('El registro seleccionado no es válido.');
        }

        $datos = $this->leerDatosFormulario($tipo);
        $datos['id_registro'] = (int) $id;
        $error = $this->validarDatos($tipo, $datos);

        if ($error !== null) {
            $this->regresarFormulario($tipo, 'editar', $error, $datos, (int) $id);
        }

        if (!$this->buscarRegistro($tipo, (int) $id)) {
            $this->redirigirConError('El registro seleccionado no existe.');
        }

        try {
            $this->validarRelaciones($tipo, $datos);
            $this->validarDuplicado($tipo, $datos, (int) $id);
            $this->actualizarRegistro($tipo, (int) $id, $datos);

            Session::flash('success', $this->nombreTipo($tipo) . ': registro actualizado correctamente.');
            $this->redirigirListado($tipo);
        } catch (Throwable $e) {
            error_log('Error al actualizar ' . $tipo . ': ' . $e->getMessage());
            $mensaje = $e instanceof \DomainException
                ? $e->getMessage()
                : 'No se pudo actualizar ' . strtolower($this->nombreTipo($tipo)) . '.';
            $this->regresarFormulario($tipo, 'editar', $mensaje, $datos, (int) $id);
        }
    }

    public function cambiarEstado(): void
    {
        Auth::check();
        Auth::exigirPermiso('estructura.eliminar');

        $tipo = $this->tipoValido((string) ($_POST['tipo'] ?? ''));
        $id = filter_input(INPUT_POST, 'id_registro', FILTER_VALIDATE_INT);
        $estado = $_POST['estado'] ?? null;

        if ($tipo === null || !$id || !in_array($estado, ['0', '1'], true)) {
            $this->redirigirConError('Los datos recibidos no son válidos.');
        }

        $registro = $this->buscarRegistro($tipo, (int) $id);

        if (!$registro) {
            $this->redirigirConError('El registro seleccionado no existe.');
        }

        try {
            if ((int) $estado === 1) {
                $this->validarActivacion($tipo, $registro);
            }

            $this->cambiarEstadoRegistro($tipo, (int) $id, (int) $estado);
            Session::flash(
                'success',
                $this->nombreTipo($tipo) . ((int) $estado === 1 ? ': registro activado correctamente.' : ': registro desactivado correctamente.'),
            );
        } catch (Throwable $e) {
            error_log('Error al cambiar estado de ' . $tipo . ': ' . $e->getMessage());
            Session::flash(
                'error',
                $e instanceof \DomainException
                    ? $e->getMessage()
                    : 'No se pudo cambiar el estado del registro.',
            );
        }

        $this->redirigirListado($tipo);
    }

    public function eliminar(): void
    {
        Auth::check();
        Auth::exigirPermiso('estructura.eliminar');

        $tipo = $this->tipoValido((string) ($_POST['tipo'] ?? ''));
        $id = filter_input(INPUT_POST, 'id_registro', FILTER_VALIDATE_INT);

        if ($tipo === null || !$id) {
            $this->redirigirConError('El registro seleccionado no es válido.');
        }

        if (!$this->buscarRegistro($tipo, (int) $id)) {
            $this->redirigirConError('El registro seleccionado no existe.');
        }

        try {
            $mensajeDependencias = $this->mensajeDependencias($tipo, (int) $id);

            if ($mensajeDependencias !== null) {
                Session::flash('error', $mensajeDependencias . ' Puedes desactivar el registro para conservar el historial.');
                $this->redirigirListado($tipo);
            }

            $this->eliminarRegistro($tipo, (int) $id);
            Session::flash('success', $this->nombreTipo($tipo) . ': registro eliminado definitivamente.');
        } catch (Throwable $e) {
            error_log('Error al eliminar ' . $tipo . ': ' . $e->getMessage());
            Session::flash('error', 'No se pudo eliminar el registro.');
        }

        $this->redirigirListado($tipo);
    }

    public function departamentosPorFacultad(): void
    {
        Auth::check();
        Auth::exigirPermiso('estructura.ver');

        header('Content-Type: application/json; charset=UTF-8');
        $idFacultad = filter_input(INPUT_GET, 'id_facultad', FILTER_VALIDATE_INT);

        if (!$idFacultad) {
            http_response_code(400);
            echo json_encode(['success' => false, 'departamentos' => []], JSON_UNESCAPED_UNICODE);
            exit();
        }

        try {
            $departamentos = (new Departamento())->listarActivosPorFacultad((int) $idFacultad);
            echo json_encode(
                ['success' => true, 'departamentos' => $departamentos],
                JSON_UNESCAPED_UNICODE,
            );
        } catch (Throwable $e) {
            error_log('Error al cargar departamentos: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'departamentos' => []], JSON_UNESCAPED_UNICODE);
        }

        exit();
    }

    private function mostrarFormulario(string $tipo, string $modo, array $registro): void
    {
        $facultades = [];
        $departamentos = [];

        if (in_array($tipo, ['departamento', 'carrera'], true)) {
            $facultades = (new Facultad())->listarActivas();
        }

        $idFacultad = (int) ($registro['id_facultad'] ?? 0);

        if ($tipo === 'carrera' && $idFacultad > 0) {
            $departamentos = (new Departamento())->listarActivosPorFacultad($idFacultad);
        }

        $this->view('Admin/EstructuraAcademica/formulario', [
            'tipo' => $tipo,
            'modo' => $modo,
            'registro' => $registro,
            'facultades' => $facultades,
            'departamentos' => $departamentos,
        ]);
    }

    private function leerDatosFormulario(string $tipo): array
    {
        return [
            'nombre' => trim((string) ($_POST['nombre'] ?? '')),
            'descripcion' => trim((string) ($_POST['descripcion'] ?? '')),
            'id_facultad' => in_array($tipo, ['departamento', 'carrera'], true)
                ? (int) (filter_input(INPUT_POST, 'id_facultad', FILTER_VALIDATE_INT) ?: 0)
                : 0,
            'id_departamento' => $tipo === 'carrera'
                ? (int) (filter_input(INPUT_POST, 'id_departamento', FILTER_VALIDATE_INT) ?: 0)
                : 0,
        ];
    }

    private function validarDatos(string $tipo, array $datos): ?string
    {
        if ($datos['nombre'] === '') {
            return 'El nombre es obligatorio.';
        }

        if (mb_strlen($datos['nombre']) > 150) {
            return 'El nombre no puede superar los 150 caracteres.';
        }

        if (mb_strlen($datos['descripcion']) > 500) {
            return 'La descripción no puede superar los 500 caracteres.';
        }

        if (in_array($tipo, ['departamento', 'carrera'], true) && (int) $datos['id_facultad'] <= 0) {
            return 'Debes seleccionar una facultad.';
        }

        return null;
    }

    private function validarRelaciones(string $tipo, array $datos): void
    {
        if ($tipo === 'facultad') {
            return;
        }

        $facultad = (new Facultad())->buscarPorId((int) $datos['id_facultad']);

        if (!$facultad || (int) ($facultad['estado'] ?? 0) !== 1) {
            throw new \DomainException('La facultad seleccionada no existe o está inactiva.');
        }

        if ($tipo === 'carrera' && (int) $datos['id_departamento'] > 0) {
            $departamento = (new Departamento())->buscarActivoPorFacultad(
                (int) $datos['id_departamento'],
                (int) $datos['id_facultad'],
            );

            if (!$departamento) {
                throw new \DomainException('El departamento no pertenece a la facultad seleccionada o está inactivo.');
            }
        }
    }

    /** @param array<string, mixed> $registro */
    private function validarActivacion(string $tipo, array $registro): void
    {
        if ($tipo === 'facultad') {
            return;
        }

        $idFacultad = (int) ($registro['id_facultad'] ?? 0);
        $facultad = $idFacultad > 0 ? (new Facultad())->buscarPorId($idFacultad) : false;

        if (!$facultad || (int) ($facultad['estado'] ?? 0) !== 1) {
            throw new \DomainException('Activa primero la facultad relacionada con este registro.');
        }

        if ($tipo === 'carrera' && (int) ($registro['id_departamento'] ?? 0) > 0) {
            $departamento = (new Departamento())->buscarActivoPorFacultad(
                (int) $registro['id_departamento'],
                $idFacultad,
            );

            if (!$departamento) {
                throw new \DomainException('Activa primero el departamento relacionado con esta carrera.');
            }
        }
    }

    private function validarDuplicado(string $tipo, array $datos, ?int $idActual): void
    {
        $duplicado = match ($tipo) {
            'facultad' => (new Facultad())->buscarPorNombre($datos['nombre']),
            'departamento' => (new Departamento())->buscarPorNombreEnFacultad(
                $datos['nombre'],
                (int) $datos['id_facultad'],
            ),
            'carrera' => (new Carrera())->buscarPorNombre($datos['nombre']),
        };

        if (!$duplicado) {
            return;
        }

        $campoId = 'id_' . $tipo;

        if ($idActual === null || (int) ($duplicado[$campoId] ?? 0) !== $idActual) {
            throw new \DomainException('Ya existe ' . strtolower($this->nombreTipo($tipo)) . ' con ese nombre.');
        }
    }

    private function crearRegistro(string $tipo, array $datos): void
    {
        match ($tipo) {
            'facultad' => (new Facultad())->crear($datos),
            'departamento' => (new Departamento())->crear($datos),
            'carrera' => (new Carrera())->crear($datos),
        };
    }

    private function actualizarRegistro(string $tipo, int $id, array $datos): void
    {
        match ($tipo) {
            'facultad' => (new Facultad())->actualizar([
                ...$datos,
                'id_facultad' => $id,
            ]),
            'departamento' => (new Departamento())->actualizar([
                ...$datos,
                'id_departamento' => $id,
            ]),
            'carrera' => (new Carrera())->actualizar([
                ...$datos,
                'id_carrera' => $id,
            ]),
        };
    }

    private function buscarRegistro(string $tipo, int $id): array|false
    {
        return match ($tipo) {
            'facultad' => (new Facultad())->buscarPorId($id),
            'departamento' => (new Departamento())->buscarPorId($id),
            'carrera' => (new Carrera())->buscarPorId($id),
        };
    }

    private function cambiarEstadoRegistro(string $tipo, int $id, int $estado): void
    {
        match ($tipo) {
            'facultad' => (new Facultad())->cambiarEstado($id, $estado),
            'departamento' => (new Departamento())->cambiarEstado($id, $estado),
            'carrera' => (new Carrera())->cambiarEstado($id, $estado),
        };
    }

    private function eliminarRegistro(string $tipo, int $id): void
    {
        match ($tipo) {
            'facultad' => (new Facultad())->eliminar($id),
            'departamento' => (new Departamento())->eliminar($id),
            'carrera' => (new Carrera())->eliminar($id),
        };
    }

    private function mensajeDependencias(string $tipo, int $id): ?string
    {
        if ($tipo === 'facultad') {
            $dependencias = (new Facultad())->contarDependencias($id);

            if ($dependencias['departamentos'] > 0 || $dependencias['carreras'] > 0) {
                return sprintf(
                    'No se puede eliminar porque contiene %d departamento(s) y %d carrera(s).',
                    $dependencias['departamentos'],
                    $dependencias['carreras'],
                );
            }
        }

        if ($tipo === 'departamento') {
            $dependencias = (new Departamento())->contarDependencias($id);

            if ($dependencias['profesores'] > 0 || $dependencias['carreras'] > 0) {
                return sprintf(
                    'No se puede eliminar porque está relacionado con %d profesor(es) y %d carrera(s).',
                    $dependencias['profesores'],
                    $dependencias['carreras'],
                );
            }
        }

        if ($tipo === 'carrera') {
            $estudiantes = (new Carrera())->contarEstudiantes($id);

            if ($estudiantes > 0) {
                return sprintf(
                    'No se puede eliminar porque tiene %d estudiante(s) registrado(s).',
                    $estudiantes,
                );
            }
        }

        return null;
    }

    private function tipoValido(string $tipo): ?string
    {
        $tipo = strtolower(trim($tipo));

        return in_array($tipo, self::TIPOS, true) ? $tipo : null;
    }

    private function nombreTipo(string $tipo): string
    {
        return match ($tipo) {
            'facultad' => 'Facultad',
            'departamento' => 'Departamento',
            'carrera' => 'Carrera',
        };
    }

    private function regresarFormulario(
        string $tipo,
        string $modo,
        string $mensaje,
        array $datos,
        ?int $id = null,
    ): never {
        Session::flash('error', $mensaje);
        Session::flash('old_estructura', $datos);

        $ruta = 'estructura-academica/' . ($modo === 'editar' ? 'editar' : 'crear') . '?tipo=' . $tipo;

        if ($modo === 'editar' && $id !== null) {
            $ruta .= '&id=' . $id;
        }

        header('Location: ' . Config::url($ruta));
        exit();
    }

    private function redirigirListado(string $tipo): never
    {
        $ancla = match ($tipo) {
            'facultad' => 'facultades',
            'departamento' => 'departamentos',
            'carrera' => 'carreras',
        };

        header('Location: ' . Config::url('estructura-academica') . '#' . $ancla);
        exit();
    }

    private function redirigirConError(string $mensaje): never
    {
        Session::flash('error', $mensaje);
        header('Location: ' . Config::url('estructura-academica'));
        exit();
    }
}
