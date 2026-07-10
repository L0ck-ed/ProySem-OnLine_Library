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
                    (usuario, resultado, fecha)
                    VALUES
                    (:usuario, :resultado, SYSDATETIME())";

            $stmt = $db->prepare($sql);

            $stmt->execute([
                ':usuario' => $usuario,
                ':resultado' => $resultado
            ]);
        } catch (\Throwable $e) {
            error_log('Error en Logger::login: ' . $e->getMessage());
        }
    }
}