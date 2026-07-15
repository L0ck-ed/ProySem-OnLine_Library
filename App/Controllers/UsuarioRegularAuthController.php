<?php

namespace App\Controllers;

use App\Config\Config;
use App\Core\Controller;
use App\Helpers\Session;
use App\Services\UsuarioRegularAuthService;

class UsuarioRegularAuthController extends Controller
{
    public function index(): void
    {
        if (
            Session::get('portal_autenticado') === true &&
            (int) Session::get('portal_id_usuario') > 0
        ) {
            header('Location: ' . Config::url('portal/inicio'));
            exit();
        }

        $this->view('Client/Auth/login', [
            'error' => Session::getFlash('error_portal'),
            'credencialAnterior' => Session::getFlash('credencial_portal'),
        ]);
    }

    public function autenticar(): void
    {
        $credencial = trim($_POST['credencial'] ?? '');
        $clave = (string) ($_POST['clave'] ?? '');

        if ($credencial === '' || $clave === '') {
            Session::flash('error_portal', 'Debes completar la credencial y la contraseña.');
            Session::flash('credencial_portal', $credencial);
            $this->redirigirLogin();
        }

        if (mb_strlen($credencial) > 150 || mb_strlen($clave) > 255) {
            Session::flash('error_portal', 'Los datos ingresados no son válidos.');
            Session::flash('credencial_portal', $credencial);
            $this->redirigirLogin();
        }

        $servicio = new UsuarioRegularAuthService();
        $resultado = $servicio->login($credencial, $clave);

        if (!$resultado['ok']) {
            Session::flash('error_portal', $resultado['mensaje']);
            Session::flash('credencial_portal', $credencial);
            $this->redirigirLogin();
        }

        header('Location: ' . Config::url('portal/inicio'));
        exit();
    }

    public function logout(): void
    {
        Session::start();

        $clavesPortal = [
            'portal_autenticado',
            'portal_id_usuario',
            'portal_tipo_usuario',
            'portal_nombre',
            'portal_cip',
            'portal_permisos',
            'permisos_portal',
            'id_estudiante',
            'id_profesor',
            'nombre_estudiante',
            'cip',
            'tipo_sesion',
            'error_permiso',
        ];

        foreach ($clavesPortal as $clave) {
            unset($_SESSION[$clave]);
        }

        session_regenerate_id(true);

        header('Location: ' . Config::url());
        exit();
    }

    private function redirigirLogin(): never
    {
        header('Location: ' . Config::url('portal/login'));
        exit();
    }
}
