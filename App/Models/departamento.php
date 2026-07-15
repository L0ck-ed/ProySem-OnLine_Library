<?php

namespace App\Models;

use App\Core\Model;

class Departamento extends Model
{
    public function listarActivosPorFacultad(int $idFacultad): array
    {
        $sql = "SELECT
                    id_departamento,
                    id_facultad,
                    nombre,
                    descripcion
                FROM departamentos
                WHERE id_facultad = :id_facultad
                  AND estado = 1
                ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_facultad' => $idFacultad,
        ]);

        return $stmt->fetchAll();
    }

    public function buscarActivoPorFacultad(int $idDepartamento, int $idFacultad): array|false
    {
        $sql = "SELECT
                    id_departamento,
                    id_facultad,
                    nombre,
                    descripcion,
                    estado
                FROM departamentos
                WHERE id_departamento = :id_departamento
                  AND id_facultad = :id_facultad
                  AND estado = 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_departamento' => $idDepartamento,
            ':id_facultad' => $idFacultad,
        ]);

        return $stmt->fetch();
    }
}
