<?php

declare(strict_types=1);

namespace App\Helpers;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        Session::start();

        $token = Session::get(self::SESSION_KEY);

        if (!is_string($token) || strlen($token) < 32) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }

        return $token;
    }

    public static function input(): string
    {
        return '<input type="hidden" name="_csrf" value="' .
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') .
            '">';
    }

    public static function validarSolicitud(): bool
    {
        Session::start();

        $esperado = Session::get(self::SESSION_KEY);
        $recibido = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

        return is_string($esperado)
            && is_string($recibido)
            && $esperado !== ''
            && hash_equals($esperado, $recibido);
    }

    public static function rotar(): void
    {
        Session::set(self::SESSION_KEY, bin2hex(random_bytes(32)));
    }
}
