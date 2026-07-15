<?php

namespace App\Models;

use App\Core\Model;

class Departamento extends Model
{
    /** @return array<int, array<string, mixed>> */
    public function listarActivosPorFacultad(int $idFacultad): array
    {
        $sql = "SELECT
                    d.id_departamento,
                    d.id_facultad,
                    d.nombre,
                    d.descripcion
                FROM departamentos d
                INNER JOIN facultades f
                    ON f.id_facultad = d.id_facultad
                   AND f.estado = 1
                WHERE d.id_facultad = :id_facultad
                  AND d.estado = 1
                ORDER BY d.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_facultad' => $idFacultad]);

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function listarGestion(string $buscar = ''): array
    {
        $sql = "SELECT
                    d.id_departamento,
                    d.id_facultad,
                    d.nombre,
                    d.descripcion,
                    d.estado,
                    d.fecha_creacion,
                    f.nombre AS facultad,
                    (
                        SELECT COUNT(*)
                        FROM profesores p
                        WHERE p.id_departamento = d.id_departamento
                    ) AS total_profesores,
                    (
                        SELECT COUNT(*)
                        FROM carreras c
                        WHERE c.id_departamento = d.id_departamento
                    ) AS total_carreras
                FROM departamentos d
                INNER JOIN facultades f
                    ON f.id_facultad = d.id_facultad
                WHERE d.nombre LIKE :buscar_nombre
                   OR f.nombre LIKE :buscar_facultad
                   OR COALESCE(d.descripcion, '') LIKE :buscar_descripcion
                ORDER BY d.estado DESC, f.nombre ASC, d.nombre ASC";

        $termino = '%' . $buscar . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':buscar_nombre' => $termino,
            ':buscar_facultad' => $termino,
            ':buscar_descripcion' => $termino,
        ]);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idDepartamento): array|false
    {
        $sql = "SELECT
                    d.id_departamento,
                    d.id_facultad,
                    d.nombre,
                    d.descripcion,
                    d.estado,
                    d.fecha_creacion,
                    f.nombre AS facultad
                FROM departamentos d
                INNER JOIN facultades f
                    ON f.id_facultad = d.id_facultad
                WHERE d.id_departamento = :id_departamento";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_departamento' => $idDepartamento]);

        return $stmt->fetch();
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

    public function buscarPorNombreEnFacultad(string $nombre, int $idFacultad): array|false
    {
        $sql = "SELECT id_departamento, nombre
                FROM departamentos
                WHERE nombre = :nombre
                  AND id_facultad = :id_facultad";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':id_facultad' => $idFacultad,
        ]);

        return $stmt->fetch();
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO departamentos (
                    id_facultad,
                    nombre,
                    descripcion,
                    estado
                ) VALUES (
                    :id_facultad,
                    :nombre,
                    :descripcion,
                    1
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_facultad' => $datos['id_facultad'],
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
        ]);
    }

    public function actualizar(array $datos): bool
    {
        $sql = "UPDATE departamentos
                SET id_facultad = :id_facultad,
                    nombre = :nombre,
                    descripcion = :descripcion
                WHERE id_departamento = :id_departamento";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_departamento' => $datos['id_departamento'],
            ':id_facultad' => $datos['id_facultad'],
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
        ]);
    }

    public function cambiarEstado(int $idDepartamento, int $estado): bool
    {
        $sql = "UPDATE departamentos
                SET estado = :estado
                WHERE id_departamento = :id_departamento";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_departamento' => $idDepartamento,
            ':estado' => $estado,
        ]);
    }

    /** @return array{profesores: int, carreras: int} */
    public function contarDependencias(int $idDepartamento): array
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM profesores WHERE id_departamento = :id_profesores) AS profesores,
                    (SELECT COUNT(*) FROM carreras WHERE id_departamento = :id_carreras) AS carreras";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_profesores' => $idDepartamento,
            ':id_carreras' => $idDepartamento,
        ]);

        $resultado = $stmt->fetch() ?: [];

        return [
            'profesores' => (int) ($resultado['profesores'] ?? 0),
            'carreras' => (int) ($resultado['carreras'] ?? 0),
        ];
    }

    public function eliminar(int $idDepartamento): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM departamentos WHERE id_departamento = :id_departamento',
        );

        return $stmt->execute([':id_departamento' => $idDepartamento]);
    }
}
