<?php

namespace App\Helpers;

use App\Configs\DatabaseConfig;

class Logger
{
    public static function login(string $usuario, string $resultado): void
    {
        try {
            $db = DatabaseConfig::connect();

            $sql = "INSERT INTO dbo.logs_login
                    (usuario, ip, navegador, metodo, url, resultado, fecha)
                    VALUES
                    (:usuario, :ip, :navegador, :metodo, :url, :resultado, SYSDATETIME())";

            $stmt = $db->prepare($sql);

            $stmt->execute([
                ':usuario' => $usuario,
                ':ip' => self::obtenerIp(),
                ':navegador' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'desconocido', 0, 500),
                ':metodo' => $_SERVER['REQUEST_METHOD'] ?? 'desconocido',
                ':url' => substr($_SERVER['REQUEST_URI'] ?? 'desconocida', 0, 255),
                ':resultado' => $resultado
            ]);
        } catch (\Throwable $e) {
            error_log('Error en Logger::login: ' . $e->getMessage());
        }
    }

    private static function obtenerIp(): string
    {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $clave) {
            if (!empty($_SERVER[$clave])) {
                $ip = trim(explode(',', $_SERVER[$clave])[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return 'desconocida';
    }
}