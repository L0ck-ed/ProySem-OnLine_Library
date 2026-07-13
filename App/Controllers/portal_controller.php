<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Middleware\EstudianteAuth;

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
        $this->view('Client/Solicitudes/solicitar', $this->datosSesion());
    }

    public function perfil(): void
    {
        $this->view('Client/Perfil/perfil', $this->datosSesion());
    }
}