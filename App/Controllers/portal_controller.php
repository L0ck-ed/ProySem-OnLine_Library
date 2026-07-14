<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Helpers\Sanitizer;
use App\Helpers\Validator;
use App\Middleware\EstudianteAuth;
use App\Models\Solicitud;

class PortalController extends Controller
{
    public function __construct()
    {
        EstudianteAuth::check();
    }

    private function datosSesion(): array
    {
        return [
            'nombreEstudiante' => Session::get('nombre_estudiante') ?? 'Estudiante',
            'cipSesion' => Session::get('cip') ?? '',
        ];
    }

    public function inicio(): void
    {
        $this->view('Client/Home/inicio', $this->datosSesion());
    }

    public function catalogo(): void
    {
        $this->view('Client/Catalogo/index', $this->datosSesion());
    }

    public function detalle(): void
    {
        $this->view('Client/Catalogo/detalle', $this->datosSesion());
    }

    public function prestamos(): void
    {
        $this->view('Client/Prestamos/mis_prestamos', $this->datosSesion());
    }

    public function solicitudes(): void
    {
        $idEstudiante = (int) Session::get('id_estudiante');
        $modelo = new Solicitud();

        $this->view('Client/Solicitudes/solicitar', array_merge($this->datosSesion(), [
            'areas' => Solicitud::areasValidas(),
            'misSolicitudes' => $modelo->listarPorEstudiante($idEstudiante),
            'errorSolicitud' => Session::getFlash('error_solicitud'),
            'exitoSolicitud' => Session::getFlash('exito_solicitud'),
            'tituloAnterior' => Session::getFlash('titulo_anterior'),
            'areaAnterior' => Session::getFlash('area_anterior'),
            'descripcionAnterior' => Session::getFlash('descripcion_anterior'),
        ]));
    }

    public function guardarSolicitud(): void
    {
        $idEstudiante = (int) Session::get('id_estudiante');

        $titulo = Sanitizer::text($_POST['titulo_libro'] ?? '');
        $area = Sanitizer::text($_POST['area'] ?? '');
        $descripcion = Sanitizer::text($_POST['descripcion'] ?? '');

        if (!Validator::required($titulo) || !Validator::min($titulo, 3)) {
            Session::flash('error_solicitud', 'Escribe el título del libro (mínimo 3 caracteres).');
            Session::flash('titulo_anterior', $titulo);
            Session::flash('area_anterior', $area);
            Session::flash('descripcion_anterior', $descripcion);
            $this->redirigirASolicitudes();
        }

        if (!in_array($area, Solicitud::areasValidas(), true)) {
            Session::flash('error_solicitud', 'Selecciona un área válida.');
            Session::flash('titulo_anterior', $titulo);
            Session::flash('area_anterior', $area);
            Session::flash('descripcion_anterior', $descripcion);
            $this->redirigirASolicitudes();
        }

        $modelo = new Solicitud();
        $modelo->crear($idEstudiante, $titulo, $area, $descripcion);

        Session::flash('exito_solicitud', 'Tu solicitud fue enviada. La administración la revisará pronto.');
        $this->redirigirASolicitudes();
    }

    private function redirigirASolicitudes(): never
    {
        header('Location: ' . \App\Config\Config::url('portal/solicitudes'));
        exit;
    }

    public function perfil(): void
    {
        $this->view('Client/Perfil/perfil', $this->datosSesion());
    }
}