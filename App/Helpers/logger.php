<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Configs\DatabaseConfig;
use Throwable;

class Logger
{
    private static bool $registrandoError = false;

    public static function login(
        string $usuario,
        string $resultado,
        ?int $idUsuario = null,
        ?string $detalle = null,
    ): void {
        try {
            $db = DatabaseConfig::connect();
            $sql = "INSERT INTO logs_login (
                        id_usuario, usuario_intentado, ip, navegador,
                        metodo, url, resultado, detalle
                    ) VALUES (
                        :id_usuario, :usuario_intentado, :ip, :navegador,
                        :metodo, :url, :resultado, :detalle
                    )";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $idUsuario,
                ':usuario_intentado' => substr($usuario, 0, 80),
                ':ip' => self::obtenerIp(),
                ':navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 500),
                ':metodo' => substr($_SERVER['REQUEST_METHOD'] ?? 'Desconocido', 0, 10),
                ':url' => substr($_SERVER['REQUEST_URI'] ?? 'Desconocida', 0, 500),
                ':resultado' => substr($resultado, 0, 30),
                ':detalle' => $detalle !== null ? substr($detalle, 0, 500) : null,
            ]);
        } catch (Throwable $e) {
            error_log('Error en Logger::login: ' . $e->getMessage());
        }
    }

    public static function error(
        string $modulo,
        string $mensaje,
        ?Throwable $excepcion = null,
        ?int $idUsuario = null,
    ): void {
        if (self::$registrandoError) {
            error_log($mensaje);
            return;
        }

        self::$registrandoError = true;

        try {
            $idUsuario ??= (int) (Session::get('id_usuario') ?? Session::get('portal_id_usuario') ?? 0);
            $db = DatabaseConfig::connect();
            $stmt = $db->prepare(
                'INSERT INTO logs_errores (
                    id_usuario, modulo, mensaje, excepcion, archivo, linea, ip, url
                ) VALUES (
                    :id_usuario, :modulo, :mensaje, :excepcion, :archivo, :linea, :ip, :url
                )'
            );
            $stmt->execute([
                ':id_usuario' => $idUsuario > 0 ? $idUsuario : null,
                ':modulo' => substr($modulo, 0, 100),
                ':mensaje' => substr($mensaje, 0, 1000),
                ':excepcion' => $excepcion ? substr($excepcion->getTraceAsString(), 0, 8000) : null,
                ':archivo' => $excepcion ? substr($excepcion->getFile(), 0, 500) : null,
                ':linea' => $excepcion?->getLine(),
                ':ip' => self::obtenerIp(),
                ':url' => substr($_SERVER['REQUEST_URI'] ?? 'CLI', 0, 500),
            ]);
        } catch (Throwable $e) {
            error_log('Error de aplicación: ' . $mensaje . ' | Logger: ' . $e->getMessage());
        } finally {
            self::$registrandoError = false;
        }
    }

    private static function obtenerIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $clave) {
            if (empty($_SERVER[$clave])) {
                continue;
            }

            $ip = trim(explode(',', (string) $_SERVER[$clave])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return 'Desconocida';
    }
}
