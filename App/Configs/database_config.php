<?php

declare(strict_types=1);

namespace App\Configs;

use PDO;
use PDOException;
use RuntimeException;

class DatabaseConfig
{
    public static function connect(): PDO
    {
        $config = self::configuracion();

        try {
            $connection = ($config['driver'] ?? 'sqlsrv') === 'mysql'
                ? self::conectarMysql($config)
                : self::conectarSqlServer($config);

            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            return $connection;
        } catch (PDOException $e) {
            error_log('Conexión BD: ' . $e->getMessage());
            throw new RuntimeException('No se pudo conectar con la base de datos.', 0, $e);
        }
    }

    private static function configuracion(): array
    {
        $localPath = __DIR__ . '/database.local.php';
        $local = is_file($localPath) ? require $localPath : [];

        return [
            'driver' => getenv('DB_DRIVER') ?: ($local['driver'] ?? 'sqlsrv'),
            'mysql_host' => getenv('DB_HOST') ?: ($local['mysql_host'] ?? 'localhost'),
            'mysql_db' => getenv('DB_DATABASE') ?: ($local['mysql_db'] ?? 'myprojectbiblioteca_v2'),
            'mysql_user' => getenv('DB_USERNAME') ?: ($local['mysql_user'] ?? 'root'),
            'mysql_password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : ($local['mysql_password'] ?? ''),
            'mysql_charset' => $local['mysql_charset'] ?? 'utf8mb4',
            'sqlsrv_server' => getenv('DB_SERVER') ?: ($local['sqlsrv_server'] ?? '.\\SQLEXPRESS'),
            'sqlsrv_database' => getenv('DB_DATABASE') ?: ($local['sqlsrv_database'] ?? 'BibliotecaDigitalDB'),
            'sqlsrv_user' => getenv('DB_USERNAME') ?: ($local['sqlsrv_user'] ?? ''),
            'sqlsrv_password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : ($local['sqlsrv_password'] ?? ''),
        ];
    }

    private static function conectarMysql(array $config): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['mysql_host'],
            $config['mysql_db'],
            $config['mysql_charset'],
        );
        return new PDO($dsn, $config['mysql_user'], $config['mysql_password']);
    }

    private static function conectarSqlServer(array $config): PDO
    {
        $dsn = sprintf(
            'sqlsrv:Server=%s;Database=%s;TrustServerCertificate=true',
            $config['sqlsrv_server'],
            $config['sqlsrv_database'],
        );
        return new PDO($dsn, $config['sqlsrv_user'], $config['sqlsrv_password']);
    }
}
