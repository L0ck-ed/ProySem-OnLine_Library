<?php

namespace App\Configs;

use PDO;
use PDOException;
use RuntimeException;

class DatabaseConfig
{
    private const DRIVER = 'sqlsrv';

    // MySQL
    private const MYSQL_HOST = 'localhost';
    private const MYSQL_DB = 'myprojectbiblioteca_v2';
    private const MYSQL_USER = 'root';
    private const MYSQL_PASSWORD = '';
    private const MYSQL_CHARSET = 'utf8mb4';

    // SQL Server
    private const SQLSRV_SERVER = '.\\SQLEXPRESS';
    private const SQLSRV_DATABASE = 'BibliotecaDigitalDB';
    private const SQLSRV_USER = 'sa';
    private const SQLSRV_PASSWORD = '01022005@';

    public static function connect(): PDO
    {
        try {
            $connection =
                self::DRIVER === 'mysql' ? self::conectarMysql() : self::conectarSqlServer();

            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $connection;
        } catch (PDOException $e) {
            error_log($e->getMessage());

            throw new RuntimeException('No se pudo conectar con la base de datos.');
        }
    }

    private static function conectarMysql(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            self::MYSQL_HOST,
            self::MYSQL_DB,
            self::MYSQL_CHARSET,
        );

        return new PDO($dsn, self::MYSQL_USER, self::MYSQL_PASSWORD);
    }

    private static function conectarSqlServer(): PDO
    {
        $dsn = sprintf(
            'sqlsrv:Server=%s;Database=%s;TrustServerCertificate=true',
            self::SQLSRV_SERVER,
            self::SQLSRV_DATABASE,
        );

        return new PDO($dsn, self::SQLSRV_USER, self::SQLSRV_PASSWORD);
    }
}
