<?php

namespace App\Middleware;

use App\Helpers\Session;
use App\Config\Config;

class Auth
{
    public static function check(): void
    {
        Session::start();

        if (!Session::has('usuario')) {
            header('Location: ' . Config::url());
            exit;
        }
    }
}
