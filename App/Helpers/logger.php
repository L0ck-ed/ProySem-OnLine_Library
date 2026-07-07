<?php

namespace App\Helpers;

use App\Config\Database;

class Logger
{
    public static function login(string $usuario, string $resultado): void
    {
        $pdo = Database::conectar();

        $sql = "INSERT INTO logs_login
                (usuario, ip, navegador, metodo, url, resultado)
                VALUES
                (:usuario, :ip, :navegador, :metodo, :url, :resultado)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':usuario' => $usuario,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida',
            ':navegador' => $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido',
            ':metodo' => $_SERVER['REQUEST_METHOD'] ?? 'desconocido',
            ':url' => $_SERVER['REQUEST_URI'] ?? 'desconocida',
            ':resultado' => $resultado
        ]);
    }
}
