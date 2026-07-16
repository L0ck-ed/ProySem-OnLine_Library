<?php

declare(strict_types=1);

namespace App\Helpers;

class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $esHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key): mixed
    {
        self::start();
        return $_SESSION[$key] ?? null;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        self::set($key, $value);
    }

    public static function getFlash(string $key): mixed
    {
        self::start();

        if (!isset($_SESSION[$key])) {
            return null;
        }

        $value = $_SESSION[$key];
        unset($_SESSION[$key]);

        return $value;
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly'],
            );
        }

        session_destroy();
    }
}
