<?php

namespace App\Middleware;

use App\Config\Config;
use App\Helpers\Session;

class UsuarioRegularAuth
{
    public static function check(): void
    {
        $autenticado = Session::get('portal_autenticado');
        $idUsuario = (int) Session::get('portal_id_usuario');

        if ($autenticado !== true || $idUsuario <= 0) {
            header('Location: ' . Config::url('portal/login'));
            exit();
        }
    }
}
