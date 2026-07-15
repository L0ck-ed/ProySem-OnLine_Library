<?php

namespace App\Models;

use App\Core\Model;

class Facultad extends Model
{
    /** @return array<int, array<string, mixed>> */
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

    /** @return array<int, array<string, mixed>> */
    public function listarGestion(string $buscar = ''): array
    {
        $sql = "SELECT
                    f.id_facultad,
                    f.nombre,
                    f.descripcion,
                    f.estado,
                    f.fecha_creacion,
                    (
                        SELECT COUNT(*)
                        FROM departamentos d
                        WHERE d.id_facultad = f.id_facultad
                    ) AS total_departamentos,
                    (
                        SELECT COUNT(*)
                        FROM carreras c
                        WHERE c.id_facultad = f.id_facultad
                    ) AS total_carreras
                FROM facultades f
                WHERE f.nombre LIKE :buscar_nombre
                   OR COALESCE(f.descripcion, '') LIKE :buscar_descripcion
                ORDER BY f.estado DESC, f.nombre ASC";

        $termino = '%' . $buscar . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':buscar_nombre' => $termino,
            ':buscar_descripcion' => $termino,
        ]);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idFacultad): array|false
    {
        $sql = "SELECT
                    id_facultad,
                    nombre,
                    descripcion,
                    estado,
                    fecha_creacion
                FROM facultades
                WHERE id_facultad = :id_facultad";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_facultad' => $idFacultad]);

        return $stmt->fetch();
    }

    public function buscarPorNombre(string $nombre): array|false
    {
        $sql = "SELECT id_facultad, nombre
                FROM facultades
                WHERE nombre = :nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);

        return $stmt->fetch();
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO facultades (nombre, descripcion, estado)
                VALUES (:nombre, :descripcion, 1)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
        ]);
    }

    public function actualizar(array $datos): bool
    {
        $sql = "UPDATE facultades
                SET nombre = :nombre,
                    descripcion = :descripcion
                WHERE id_facultad = :id_facultad";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_facultad' => $datos['id_facultad'],
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
        ]);
    }

    public function cambiarEstado(int $idFacultad, int $estado): bool
    {
        $sql = "UPDATE facultades
                SET estado = :estado
                WHERE id_facultad = :id_facultad";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_facultad' => $idFacultad,
            ':estado' => $estado,
        ]);
    }

    /** @return array{departamentos: int, carreras: int} */
    public function contarDependencias(int $idFacultad): array
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM departamentos WHERE id_facultad = :id_departamentos) AS departamentos,
                    (SELECT COUNT(*) FROM carreras WHERE id_facultad = :id_carreras) AS carreras";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_departamentos' => $idFacultad,
            ':id_carreras' => $idFacultad,
        ]);

        $resultado = $stmt->fetch() ?: [];

        return [
            'departamentos' => (int) ($resultado['departamentos'] ?? 0),
            'carreras' => (int) ($resultado['carreras'] ?? 0),
        ];
    }

    public function eliminar(int $idFacultad): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM facultades WHERE id_facultad = :id_facultad',
        );

        return $stmt->execute([':id_facultad' => $idFacultad]);
    }
}
