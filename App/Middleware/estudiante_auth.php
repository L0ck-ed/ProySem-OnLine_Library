<?php

namespace App\Middleware;

use App\Helpers\Session;
use App\Config\Config;

class EstudianteAuth
{
    public static function check(): void
    {
        Session::start();

        if (!Session::has('id_estudiante') || Session::get('tipo_sesion') !== 'estudiante') {
            Session::flash('error', 'Debe iniciar sesión para continuar.');
            header('Location: ' . Config::url('portal/login'));
            exit;
        }
    }
}