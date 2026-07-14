<?php

namespace App\Configs;

use PDO;
use PDOException;

class DatabaseConfig
{
    /**
     * ÚNICO ajuste que cada integrante del equipo debe cambiar en su máquina:
     * 'mysql'  -> si usas MySQL/WAMP
     * 'sqlsrv' -> si usas SQL Server
     * El resto del sistema (modelos, queries) funciona igual sin tocar nada más.
     */
    private const DRIVER = 'sqlsrv';

    // --- Credenciales MySQL ---
    private const MYSQL_HOST = 'localhost';
    private const MYSQL_DB = 'myprojectbiblioteca_v2';
    private const MYSQL_USER = 'root';
    private const MYSQL_PASSWORD = '';
    private const MYSQL_CHARSET = 'utf8mb4';

    // --- Credenciales SQL Server ---
    private const SQLSRV_SERVER = '.\\SQLEXPRESS';
    private const SQLSRV_DATABASE = 'OnLineLibrary';
    private const SQLSRV_USER = 'sa';
    private const SQLSRV_PASSWORD = '01022005@';

    public static function connect(): PDO
    {
        try {
            $connection = self::DRIVER === 'mysql'
                ? self::conectarMysql()
                : self::conectarSqlServer();

            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $connection;
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos (' . self::DRIVER . '): ' . $e->getMessage());
        }
    }

    private static function conectarMysql(): PDO
    {
        $dsn = 'mysql:host=' . self::MYSQL_HOST . ';dbname=' . self::MYSQL_DB . ';charset=' . self::MYSQL_CHARSET;

        return new PDO($dsn, self::MYSQL_USER, self::MYSQL_PASSWORD);
    }

    private static function conectarSqlServer(): PDO
    {
        $dsn = 'sqlsrv:Server=' . self::SQLSRV_SERVER . ';Database=' . self::SQLSRV_DATABASE;

        return new PDO($dsn, self::SQLSRV_USER, self::SQLSRV_PASSWORD);
    }
}