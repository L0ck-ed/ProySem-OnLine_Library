<?php

namespace App\Configs;

use PDO;
use PDOException;

class DatabaseConfig
{
    private const SERVER = '.\\SQLEXPRESS';
    private const DATABASE = 'OnLineLibrary';
    private const USER = 'sa';
    private const PASSWORD = '01022005@';

    public static function connect(): PDO
    {
        try {
            $connection = new PDO(
                "sqlsrv:Server=" . self::SERVER . ";Database=" . self::DATABASE,
                self::USER,
                self::PASSWORD
            );

            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $connection;
        } catch (PDOException $e) {
            die("Error de conexión SQL Server: " . $e->getMessage());
        }
    }
}