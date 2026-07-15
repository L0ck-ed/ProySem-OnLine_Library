<?php

namespace App\Models;

use App\Core\Model;

class Carrera extends Model
{
    /** @return array<int, array<string, mixed>> */
    public function listarActivas(): array
    {
        $sql = "SELECT
                    c.id_carrera,
                    c.id_facultad,
                    c.id_departamento,
                    c.nombre,
                    c.descripcion
                FROM carreras c
                INNER JOIN facultades f
                    ON f.id_facultad = c.id_facultad
                   AND f.estado = 1
                LEFT JOIN departamentos d
                    ON d.id_departamento = c.id_departamento
                WHERE c.estado = 1
                  AND (c.id_departamento IS NULL OR d.estado = 1)
                ORDER BY c.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function listarActivasPorFacultad(int $idFacultad): array
    {
        $sql = "SELECT
                    c.id_carrera,
                    c.id_facultad,
                    c.id_departamento,
                    c.nombre,
                    c.descripcion
                FROM carreras c
                INNER JOIN facultades f
                    ON f.id_facultad = c.id_facultad
                   AND f.estado = 1
                LEFT JOIN departamentos d
                    ON d.id_departamento = c.id_departamento
                WHERE c.estado = 1
                  AND c.id_facultad = :id_facultad
                  AND (c.id_departamento IS NULL OR d.estado = 1)
                ORDER BY c.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_facultad' => $idFacultad]);

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function listarGestion(string $buscar = ''): array
    {
        $sql = "SELECT
                    c.id_carrera,
                    c.id_facultad,
                    c.id_departamento,
                    c.nombre,
                    c.descripcion,
                    c.estado,
                    c.fecha_creacion,
                    COALESCE(f.nombre, 'Sin facultad') AS facultad,
                    COALESCE(d.nombre, 'Sin departamento asignado') AS departamento,
                    (
                        SELECT COUNT(*)
                        FROM estudiantes e
                        WHERE e.id_carrera = c.id_carrera
                    ) AS total_estudiantes
                FROM carreras c
                LEFT JOIN departamentos d
                    ON d.id_departamento = c.id_departamento
                LEFT JOIN facultades f
                    ON f.id_facultad = COALESCE(c.id_facultad, d.id_facultad)
                WHERE c.nombre LIKE :buscar_nombre
                   OR COALESCE(c.descripcion, '') LIKE :buscar_descripcion
                   OR COALESCE(f.nombre, '') LIKE :buscar_facultad
                   OR COALESCE(d.nombre, '') LIKE :buscar_departamento
                ORDER BY c.estado DESC, f.nombre ASC, c.nombre ASC";

        $termino = '%' . $buscar . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':buscar_nombre' => $termino,
            ':buscar_descripcion' => $termino,
            ':buscar_facultad' => $termino,
            ':buscar_departamento' => $termino,
        ]);

        return $stmt->fetchAll();
    }

    public function buscarActivaPorFacultad(int $idCarrera, int $idFacultad): array|false
    {
        $sql = "SELECT
                    c.id_carrera,
                    c.id_facultad,
                    c.id_departamento,
                    c.nombre,
                    c.descripcion,
                    c.estado
                FROM carreras c
                INNER JOIN facultades f
                    ON f.id_facultad = c.id_facultad
                   AND f.estado = 1
                LEFT JOIN departamentos d
                    ON d.id_departamento = c.id_departamento
                WHERE c.id_carrera = :id_carrera
                  AND c.id_facultad = :id_facultad
                  AND c.estado = 1
                  AND (c.id_departamento IS NULL OR d.estado = 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_carrera' => $idCarrera,
            ':id_facultad' => $idFacultad,
        ]);

        return $stmt->fetch();
    }

    public function buscarPorId(int $idCarrera): array|false
    {
        $sql = "SELECT
                    c.id_carrera,
                    c.id_facultad,
                    c.id_departamento,
                    c.nombre,
                    c.descripcion,
                    c.estado,
                    c.fecha_creacion,
                    f.nombre AS facultad,
                    d.nombre AS departamento
                FROM carreras c
                LEFT JOIN departamentos d
                    ON d.id_departamento = c.id_departamento
                LEFT JOIN facultades f
                    ON f.id_facultad = COALESCE(c.id_facultad, d.id_facultad)
                WHERE c.id_carrera = :id_carrera";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_carrera' => $idCarrera]);

        return $stmt->fetch();
    }

    public function buscarPorNombre(string $nombre): array|false
    {
        $sql = "SELECT id_carrera, nombre
                FROM carreras
                WHERE nombre = :nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);

        return $stmt->fetch();
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO carreras (
                    id_facultad,
                    id_departamento,
                    nombre,
                    descripcion,
                    estado
                ) VALUES (
                    :id_facultad,
                    :id_departamento,
                    :nombre,
                    :descripcion,
                    1
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_facultad' => $datos['id_facultad'],
            ':id_departamento' => $datos['id_departamento'] ?: null,
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
        ]);
    }

    public function actualizar(array $datos): bool
    {
        $sql = "UPDATE carreras
                SET id_facultad = :id_facultad,
                    id_departamento = :id_departamento,
                    nombre = :nombre,
                    descripcion = :descripcion
                WHERE id_carrera = :id_carrera";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_carrera' => $datos['id_carrera'],
            ':id_facultad' => $datos['id_facultad'],
            ':id_departamento' => $datos['id_departamento'] ?: null,
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
        ]);
    }

    public function cambiarEstado(int $idCarrera, int $estado): bool
    {
        $sql = "UPDATE carreras
                SET estado = :estado
                WHERE id_carrera = :id_carrera";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_carrera' => $idCarrera,
            ':estado' => $estado,
        ]);
    }

    public function contarEstudiantes(int $idCarrera): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS total FROM estudiantes WHERE id_carrera = :id_carrera',
        );
        $stmt->execute([':id_carrera' => $idCarrera]);
        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }

    public function eliminar(int $idCarrera): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM carreras WHERE id_carrera = :id_carrera',
        );

        return $stmt->execute([':id_carrera' => $idCarrera]);
    }
}
