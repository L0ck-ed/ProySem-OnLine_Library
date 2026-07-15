<?php

namespace App\Models;

use App\Core\Model;

class Carrera extends Model
{
    public function listarActivas(): array
    {
        $sql = "SELECT
                    id_carrera,
                    nombre,
                    descripcion
                FROM carreras
                WHERE estado = 1
                ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listarActivasPorFacultad(int $idFacultad): array
    {
        $sql = "SELECT
                id_carrera,
                id_facultad,
                nombre,
                descripcion
            FROM carreras
            WHERE estado = 1
              AND id_facultad = :id_facultad
            ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_facultad' => $idFacultad,
        ]);

        return $stmt->fetchAll();
    }

    public function buscarActivaPorFacultad(int $idCarrera, int $idFacultad): array|false
    {
        $sql = "SELECT
                id_carrera,
                id_facultad,
                nombre,
                descripcion,
                estado
            FROM carreras
            WHERE id_carrera = :id_carrera
              AND id_facultad = :id_facultad
              AND estado = 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_carrera' => $idCarrera,
            ':id_facultad' => $idFacultad,
        ]);

        return $stmt->fetch();
    }
}
