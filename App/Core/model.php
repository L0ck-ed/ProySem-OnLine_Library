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
}