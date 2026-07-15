<?php

namespace App\Models;

use App\Core\Model;

class Tema extends Model
{
    public function listarActivos(): array
    {
        $sql = "SELECT
                    id_tema,
                    nombre,
                    descripcion
                FROM temas
                WHERE estado = 1
                ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
