<?php

namespace App\Helpers;

use App\Configs\DatabaseConfig;
use Throwable;

class Logger
{
    public static function login(
        string $usuario,
        string $resultado,
        ?int $idUsuario = null,
        ?string $detalle = null
    ): void {
        try {
            $db = DatabaseConfig::connect();

            $sql = "INSERT INTO logs_login (
                        id_usuario,
                        usuario_intentado,
                        ip,
                        navegador,
                        metodo,
                        url,
                        resultado,
                        detalle
                    )
                    VALUES (
                        :id_usuario,
                        :usuario_intentado,
                        :ip,
                        :navegador,
                        :metodo,
                        :url,
                        :resultado,
                        :detalle
                    )";

            $stmt = $db->prepare($sql);

            $stmt->execute([
                ':id_usuario' => $idUsuario,
                ':usuario_intentado' => $usuario,
                ':ip' => self::obtenerIp(),
                ':navegador' => substr(
                    $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido',
                    0,
                    500
                ),
                ':metodo' => substr(
                    $_SERVER['REQUEST_METHOD'] ?? 'Desconocido',
                    0,
                    10
                ),
                ':url' => substr(
                    $_SERVER['REQUEST_URI'] ?? 'Desconocida',
                    0,
                    500
                ),
                ':resultado' => substr($resultado, 0, 30),
                ':detalle' => $detalle
            ]);

        } catch (Throwable $e) {
            error_log(
                'Error en Logger::login: ' . $e->getMessage()
            );
        }
    }

    private static function obtenerIp(): string
    {
        $claves = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($claves as $clave) {
            if (empty($_SERVER[$clave])) {
                continue;
            }

            $ip = trim(
                explode(',', $_SERVER[$clave])[0]
            );

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return 'Desconocida';
    }
}