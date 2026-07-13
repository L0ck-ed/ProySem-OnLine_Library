<?php

namespace App\Services;

use App\Core\AuthServiceBase;
use App\Helpers\Session;
use App\Models\Estudiante;

class EstudianteAuthService extends AuthServiceBase
{
    public function __construct()
    {
        parent::__construct(new Estudiante());
    }

    public function login(string $credencial, string $clave): void
    {
        parent::login($credencial, $clave);
    }

    public function logout(): void
    {
        Session::destroy();
        header('Location: ' . \App\Config\Config::url('portal/login'));
        exit;
    }

    protected function etiquetaLog(): string
    {
        return 'estudiante';
    }

    protected function campoClave(): string
    {
        return 'pin_hash';
    }

    protected function urlLoginFallido(): string
    {
        return 'portal/login';
    }

    protected function urlLoginExitoso(): string
    {
    return 'portal/inicio';
    }

    protected function crearSesion(array $fila): void
    {
        Session::set('id_estudiante', $fila['id']);
        Session::set('cip', $fila['cip']);
        Session::set('nombre_estudiante', trim($fila['primer_nombre'] . ' ' . $fila['primer_apellido']));
        Session::set('tipo_sesion', 'estudiante');
    }
}