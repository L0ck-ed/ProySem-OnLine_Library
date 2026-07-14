<?php
/*
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


PARA CONECTAR CON SQL SERVER EL CONDIGO DE ARRIBA, PARA MYSQL EL DE ABAJO
*/
namespace App\Configs;

use App\Config\Config;
use PDO;
use PDOException;

class DatabaseConfig
{
    public static function connect(): PDO
    {
        try {
            $dsn = "mysql:host=" . Config::HOST . ";dbname=" . Config::DB . ";charset=" . Config::CHARSET;

            $connection = new PDO(
                $dsn,
                Config::USER,
                Config::PASSWORD
            );

            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $connection;
        } catch (PDOException $e) {
            die("Error de conexión MySQL: " . $e->getMessage());
        }
    }
}