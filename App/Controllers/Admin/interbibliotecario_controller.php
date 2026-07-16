<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Config;
use App\Core\Controller;
use App\Helpers\Session;
use App\Middleware\Auth;
use App\Models\PrestamoInterbibliotecario;

final class InterbibliotecarioController extends Controller
{
    public function index(): void
    {
        Auth::check();
        Auth::exigirPermiso('interbibliotecario.ver');

        $buscar = trim((string) ($_GET['buscar'] ?? ''));
        $estado = trim((string) ($_GET['estado'] ?? ''));
        $modelo = new PrestamoInterbibliotecario();

        $this->view('Admin/Interbibliotecario/index', [
            'solicitudes' => $modelo->listarSolicitudes([
                'buscar' => $buscar,
                'estado' => $estado,
            ]),
            'instituciones' => $modelo->listarInstituciones(),
            'catalogo' => $modelo->listarCatalogo(trim((string) ($_GET['catalogo'] ?? ''))),
            'estados' => PrestamoInterbibliotecario::ESTADOS,
            'buscar' => $buscar,
            'estado' => $estado,
            'catalogoBuscar' => trim((string) ($_GET['catalogo'] ?? '')),
        ]);
    }

    public function guardarInstitucion(): void
    {
        Auth::check();
        Auth::exigirPermiso('interbibliotecario.gestionar');

        $datos = [
            'id_institucion' => filter_input(INPUT_POST, 'id_institucion', FILTER_VALIDATE_INT) ?: 0,
            'nombre' => trim((string) ($_POST['nombre'] ?? '')),
            'direccion' => trim((string) ($_POST['direccion'] ?? '')),
            'telefono' => trim((string) ($_POST['telefono'] ?? '')),
            'correo' => trim((string) ($_POST['correo'] ?? '')),
            'sitio_web' => trim((string) ($_POST['sitio_web'] ?? '')),
        ];

        if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 200) {
            $this->volver('error', 'El nombre de la institución es obligatorio.');
        }
        if ($datos['correo'] !== '' && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $this->volver('error', 'El correo de la institución no es válido.');
        }
        if ($datos['sitio_web'] !== '' && !filter_var($datos['sitio_web'], FILTER_VALIDATE_URL)) {
            $this->volver('error', 'El sitio web debe ser una URL válida.');
        }

        try {
            (new PrestamoInterbibliotecario())->guardarInstitucion($datos);
            $this->volver('success', $datos['id_institucion'] > 0
                ? 'Institución actualizada correctamente.'
                : 'Institución registrada correctamente.');
        } catch (\Throwable $e) {
            error_log('Interbibliotecario institución: ' . $e->getMessage());
            $this->volver('error', 'No se pudo guardar la institución. Verifica que el nombre no esté duplicado.');
        }
    }

    public function cambiarEstadoInstitucion(): void
    {
        Auth::check();
        Auth::exigirPermiso('interbibliotecario.gestionar');
        $id = filter_input(INPUT_POST, 'id_institucion', FILTER_VALIDATE_INT);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_VALIDATE_INT);

        if (!$id || !in_array($estado, [0, 1], true)) {
            $this->volver('error', 'Datos de institución no válidos.');
        }

        (new PrestamoInterbibliotecario())->cambiarEstadoInstitucion((int) $id, (int) $estado);
        $this->volver('success', (int) $estado === 1 ? 'Institución activada.' : 'Institución desactivada.');
    }

    public function guardarLibro(): void
    {
        Auth::check();
        Auth::exigirPermiso('interbibliotecario.gestionar');

        $datos = [
            'id_libro_externo' => filter_input(INPUT_POST, 'id_libro_externo', FILTER_VALIDATE_INT) ?: 0,
            'id_institucion' => filter_input(INPUT_POST, 'id_institucion', FILTER_VALIDATE_INT) ?: 0,
            'titulo' => trim((string) ($_POST['titulo'] ?? '')),
            'autor' => trim((string) ($_POST['autor'] ?? '')),
            'isbn' => trim((string) ($_POST['isbn'] ?? '')),
            'descripcion' => trim((string) ($_POST['descripcion'] ?? '')),
            'url_catalogo' => trim((string) ($_POST['url_catalogo'] ?? '')),
        ];

        if ($datos['id_institucion'] <= 0 || $datos['titulo'] === '' || $datos['autor'] === '') {
            $this->volver('error', 'Institución, título y autor son obligatorios.');
        }
        if ($datos['url_catalogo'] !== '' && !filter_var($datos['url_catalogo'], FILTER_VALIDATE_URL)) {
            $this->volver('error', 'El enlace del catálogo no es válido.');
        }

        try {
            (new PrestamoInterbibliotecario())->guardarLibroExterno($datos);
            $this->volver('success', $datos['id_libro_externo'] > 0
                ? 'Libro externo actualizado.'
                : 'Libro externo agregado al catálogo.');
        } catch (\Throwable $e) {
            error_log('Interbibliotecario catálogo: ' . $e->getMessage());
            $this->volver('error', 'No se pudo guardar el libro externo.');
        }
    }

    public function cambiarDisponibilidadLibro(): void
    {
        Auth::check();
        Auth::exigirPermiso('interbibliotecario.gestionar');
        $id = filter_input(INPUT_POST, 'id_libro_externo', FILTER_VALIDATE_INT);
        $disponible = filter_input(INPUT_POST, 'disponible', FILTER_VALIDATE_INT);

        if (!$id || !in_array($disponible, [0, 1], true)) {
            $this->volver('error', 'Datos del libro externo no válidos.');
        }

        (new PrestamoInterbibliotecario())->cambiarDisponibilidadLibro((int) $id, (int) $disponible);
        $this->volver('success', (int) $disponible === 1 ? 'Libro marcado como disponible.' : 'Libro marcado como no disponible.');
    }

    public function actualizarSolicitud(): void
    {
        Auth::check();
        Auth::exigirPermiso('interbibliotecario.gestionar');
        $id = filter_input(INPUT_POST, 'id_solicitud', FILTER_VALIDATE_INT);
        $estado = trim((string) ($_POST['estado'] ?? ''));
        $fecha = trim((string) ($_POST['fecha_vencimiento'] ?? ''));
        $observacion = trim((string) ($_POST['observacion'] ?? ''));

        if (!$id || !in_array($estado, PrestamoInterbibliotecario::ESTADOS, true)) {
            $this->volver('error', 'La solicitud o el estado no son válidos.');
        }
        if ($fecha !== '') {
            $obj = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
            if ($obj === false || $obj->format('Y-m-d') !== $fecha) {
                $this->volver('error', 'La fecha de vencimiento no es válida.');
            }
        }

        $ok = (new PrestamoInterbibliotecario())->actualizarSolicitud(
            (int) $id,
            $estado,
            $fecha !== '' ? $fecha : null,
            $observacion,
        );
        $this->volver($ok ? 'success' : 'error', $ok
            ? 'Solicitud interbibliotecaria actualizada.'
            : 'No se encontró la solicitud.');
    }

    private function volver(string $tipo, string $mensaje): never
    {
        Session::flash($tipo, $mensaje);
        header('Location: ' . Config::url('interbibliotecario'));
        exit();
    }
}
