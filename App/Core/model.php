<?php

namespace App\Core;

use App\Configs\DatabaseConfig;
use PDO;

class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConfig::connect();
    }

    protected function esSqlServer(): bool
    {
        return $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlsrv';
    }

    protected function esMySql(): bool
    {
        return $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
    }
}