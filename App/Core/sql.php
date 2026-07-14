<?php

namespace App\Core;

use PDO;

/**
 * Pequeño helper para las diferencias de sintaxis SQL entre MySQL y SQL Server.
 * Detecta el motor real en tiempo de ejecución (PDO::ATTR_DRIVER_NAME), para que
 * el mismo código funcione sin cambios sin importar qué motor use cada integrante
 * del equipo en su máquina. Evita repetir esta lógica en cada modelo (DRY).
 */
class Sql
{
    public static function esSqlServer(PDO $db): bool
    {
        return $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlsrv';
    }

    /**
     * Fragmento de paginación a agregar al FINAL de la consulta (después del ORDER BY).
     * Los parámetros :limit y :offset se enlazan igual en ambos motores;
     * solo cambia la sintaxis/orden de las palabras clave.
     */
    public static function limitOffset(PDO $db): string
    {
        return self::esSqlServer($db)
            ? 'OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY'
            : 'LIMIT :limit OFFSET :offset';
    }

    /**
     * Para obtener "los primeros N registros" sin paginación (ej. "recientes").
     * SQL Server usa TOP (antes del SELECT), MySQL usa LIMIT (al final).
     * Devuelve ambos fragmentos para que el modelo arme la consulta.
     */
    public static function top(PDO $db, int $n): array
    {
        return self::esSqlServer($db)
            ? ['antes' => "TOP ({$n})", 'despues' => '']
            : ['antes' => '', 'despues' => "LIMIT {$n}"];
    }
}