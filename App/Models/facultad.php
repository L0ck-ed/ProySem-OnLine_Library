<?php

namespace App\Models;

use App\Core\Model;

class Facultad extends Model
{
    public function listarActivas(): array
    {
        $sql = "SELECT
                    id_facultad,
                    nombre,
                    descripcion
                FROM facultades
                WHERE estado = 1
                ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
