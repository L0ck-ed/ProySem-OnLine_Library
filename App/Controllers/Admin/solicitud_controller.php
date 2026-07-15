<?php

namespace App\Controllers\Admin;

use App\Config\Config;
use App\Core\Controller;
use App\Helpers\Session;
use App\Middleware\Auth;
use App\Models\Solicitud;

class SolicitudController extends Controller
{
    public function index(): void
    {
        Auth::check();
        Auth::exigirPermiso('solicitudes.ver');

        $filtros = [
            'buscar' => trim($_GET['buscar'] ?? ''),
            'estado' => trim($_GET['estado'] ?? ''),
            'tipo_usuario' => trim($_GET['tipo_usuario'] ?? ''),
        ];

        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));

        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $solicitudModel = new Solicitud();

        $solicitudes = $solicitudModel->listarAdmin($filtros, $porPagina, $offset);

        $totalRegistros = $solicitudModel->contarAdmin($filtros);

        $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));

        $this->view('Admin/Solicitudes/listar', [
            'solicitudes' => $solicitudes,
            'filtros' => $filtros,
            'estados' => Solicitud::estadosValidos(),
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros,
        ]);
    }

    public function gestionar(): void
    {
        Auth::check();
        Auth::exigirPermiso('solicitudes.gestionar');

        $idSolicitud = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$idSolicitud) {
            $this->redirigirConError('La solicitud seleccionada no es válida.');
        }

        $solicitudModel = new Solicitud();

        $solicitud = $solicitudModel->buscarPorIdAdmin((int) $idSolicitud);

        if (!$solicitud) {
            $this->redirigirConError('La solicitud seleccionada no existe.');
        }

        $this->view('Admin/Solicitudes/gestionar', [
            'solicitud' => $solicitud,
            'estados' => Solicitud::estadosValidos(),
        ]);
    }

    public function actualizar(): void
    {
        Auth::check();
        Auth::exigirPermiso('solicitudes.gestionar');

        $idSolicitud = filter_input(INPUT_POST, 'id_solicitud', FILTER_VALIDATE_INT);

        $estado = trim($_POST['estado'] ?? '');

        $respuesta = trim($_POST['respuesta'] ?? '');

        if (!$idSolicitud) {
            $this->redirigirConError('La solicitud seleccionada no es válida.');
        }

        $rutaEditar = 'solicitudes/gestionar?id=' . (int) $idSolicitud;

        if (!in_array($estado, Solicitud::estadosValidos(), true)) {
            $this->regresarGestion(
                'El estado seleccionado no es válido.',
                $rutaEditar,
                $estado,
                $respuesta,
            );
        }

        if (
            in_array($estado, ['Aprobada', 'Rechazada', 'Adquirida'], true) &&
            mb_strlen($respuesta) < 5
        ) {
            $this->regresarGestion(
                'Debes escribir una respuesta de al menos 5 caracteres.',
                $rutaEditar,
                $estado,
                $respuesta,
            );
        }

        if (mb_strlen($respuesta) > 1000) {
            $this->regresarGestion(
                'La respuesta no puede superar los 1000 caracteres.',
                $rutaEditar,
                $estado,
                $respuesta,
            );
        }

        $solicitudModel = new Solicitud();

        $solicitud = $solicitudModel->buscarPorIdAdmin((int) $idSolicitud);

        if (!$solicitud) {
            $this->redirigirConError('La solicitud seleccionada no existe.');
        }

        try {
            $actualizada = $solicitudModel->gestionarAdmin(
                (int) $idSolicitud,
                $estado,
                $respuesta,
                $this->obtenerIdUsuarioSesion(),
            );

            if (!$actualizada) {
                throw new \RuntimeException('La solicitud no pudo actualizarse.');
            }

            Session::flash('success', 'Solicitud actualizada correctamente.');

            header('Location: ' . Config::url('solicitudes'));

            exit();
        } catch (\Throwable $e) {
            error_log('Error al gestionar solicitud: ' . $e->getMessage());

            $this->regresarGestion(
                'No se pudo actualizar la solicitud.',
                $rutaEditar,
                $estado,
                $respuesta,
            );
        }
    }

    private function obtenerIdUsuarioSesion(): ?int
    {
        $posiblesClaves = ['id_usuario', 'usuario_id', 'id'];

        foreach ($posiblesClaves as $clave) {
            $valor = Session::get($clave);

            if (is_numeric($valor) && (int) $valor > 0) {
                return (int) $valor;
            }
        }

        return null;
    }

    private function regresarGestion(
        string $mensaje,
        string $ruta,
        string $estado,
        string $respuesta,
    ): never {
        Session::flash('error', $mensaje);

        Session::flash('old_solicitud_gestion', [
            'estado' => $estado,
            'respuesta' => $respuesta,
        ]);

        header('Location: ' . Config::url($ruta));

        exit();
    }

    private function redirigirConError(string $mensaje): never
    {
        Session::flash('error', $mensaje);

        header('Location: ' . Config::url('solicitudes'));

        exit();
    }
}
