<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $conexion = null;

    public static function conectar(): PDO
    {
        if (self::$conexion === null) {
            $dsn = 'mysql:host=' . Config::HOST .
                   ';dbname=' . Config::DB .
                   ';charset=' . Config::CHARSET;

            try {
                self::$conexion = new PDO(
                    $dsn,
                    Config::USER,
                    Config::PASSWORD,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die('Error de conexión: ' . $e->getMessage());
            }
        }

        return self::$conexion;
    }
}
