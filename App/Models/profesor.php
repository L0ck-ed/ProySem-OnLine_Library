<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Sql;
use PDO;

class Profesor extends Model
{
    public function listar(string $buscar = '', int $limit = 10, int $offset = 0): array
    {
        $sql =
            "SELECT
            p.id_profesor,
            p.id_usuario,
            p.id_departamento,
            p.cip,
            p.primer_nombre,
            p.segundo_nombre,
            p.primer_apellido,
            p.segundo_apellido,
            COALESCE(
                d.nombre,
                p.departamento,
                'Sin departamento'
            ) AS departamento,
            d.id_facultad,
            COALESCE(
                f.nombre,
                'Sin facultad'
            ) AS facultad,
            p.especialidad,
            p.estado,
            p.fecha_creacion,
            u.nombre AS nombre_usuario,
            u.usuario,
            u.correo
        FROM profesores p
        INNER JOIN usuarios u
            ON u.id_usuario = p.id_usuario
        LEFT JOIN departamentos d
            ON d.id_departamento = p.id_departamento
        LEFT JOIN facultades f
            ON f.id_facultad = d.id_facultad
        WHERE p.cip LIKE :buscar_cip
           OR p.primer_nombre LIKE :buscar_nombre
           OR p.primer_apellido LIKE :buscar_apellido
           OR COALESCE(
                d.nombre,
                p.departamento,
                ''
           ) LIKE :buscar_departamento
           OR COALESCE(
                f.nombre,
                ''
           ) LIKE :buscar_facultad
           OR COALESCE(
                p.especialidad,
                ''
           ) LIKE :buscar_especialidad
           OR u.usuario LIKE :buscar_usuario
        ORDER BY p.id_profesor DESC
        " . Sql::limitOffset($this->db);

        $termino = '%' . $buscar . '%';

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':buscar_cip', $termino, PDO::PARAM_STR);
        $stmt->bindValue(':buscar_nombre', $termino, PDO::PARAM_STR);
        $stmt->bindValue(':buscar_apellido', $termino, PDO::PARAM_STR);
        $stmt->bindValue(':buscar_departamento', $termino, PDO::PARAM_STR);
        $stmt->bindValue(':buscar_facultad', $termino, PDO::PARAM_STR);
        $stmt->bindValue(':buscar_especialidad', $termino, PDO::PARAM_STR);
        $stmt->bindValue(':buscar_usuario', $termino, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contar(string $buscar = ''): int
    {
        $sql = "SELECT
                COUNT(DISTINCT p.id_profesor) AS total
            FROM profesores p
            INNER JOIN usuarios u
                ON u.id_usuario = p.id_usuario
            LEFT JOIN departamentos d
                ON d.id_departamento = p.id_departamento
            LEFT JOIN facultades f
                ON f.id_facultad = d.id_facultad
            WHERE p.cip LIKE :buscar_cip
               OR p.primer_nombre LIKE :buscar_nombre
               OR p.primer_apellido LIKE :buscar_apellido
               OR COALESCE(
                    d.nombre,
                    p.departamento,
                    ''
               ) LIKE :buscar_departamento
               OR COALESCE(
                    f.nombre,
                    ''
               ) LIKE :buscar_facultad
               OR COALESCE(
                    p.especialidad,
                    ''
               ) LIKE :buscar_especialidad
               OR u.usuario LIKE :buscar_usuario";

        $termino = '%' . $buscar . '%';

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':buscar_cip' => $termino,
            ':buscar_nombre' => $termino,
            ':buscar_apellido' => $termino,
            ':buscar_departamento' => $termino,
            ':buscar_facultad' => $termino,
            ':buscar_especialidad' => $termino,
            ':buscar_usuario' => $termino,
        ]);

        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }

    public function buscarPorId(int $idProfesor): array|false
    {
        $sql = "SELECT
                p.id_profesor,
                p.id_usuario,
                p.id_departamento,
                p.cip,
                p.primer_nombre,
                p.segundo_nombre,
                p.primer_apellido,
                p.segundo_apellido,
                COALESCE(
                    d.nombre,
                    p.departamento
                ) AS departamento,
                d.id_facultad,
                f.nombre AS facultad,
                p.especialidad,
                p.estado,
                p.fecha_creacion,
                p.fecha_actualizacion,
                u.nombre AS nombre_usuario,
                u.usuario,
                u.correo
            FROM profesores p
            INNER JOIN usuarios u
                ON u.id_usuario = p.id_usuario
            LEFT JOIN departamentos d
                ON d.id_departamento = p.id_departamento
            LEFT JOIN facultades f
                ON f.id_facultad = d.id_facultad
            WHERE p.id_profesor = :id_profesor";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_profesor' => $idProfesor,
        ]);

        return $stmt->fetch();
    }

    public function buscarPorCip(string $cip): array|false
    {
        $sql = "SELECT
                    id_profesor,
                    id_usuario,
                    cip
                FROM profesores
                WHERE cip = :cip";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':cip' => $cip,
        ]);

        return $stmt->fetch();
    }

    public function buscarPorUsuario(int $idUsuario): array|false
    {
        $sql = "SELECT
                    id_profesor,
                    id_usuario,
                    cip
                FROM profesores
                WHERE id_usuario = :id_usuario";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_usuario' => $idUsuario,
        ]);

        return $stmt->fetch();
    }

    public function crear(array $data): bool
    {
        $sql = "INSERT INTO profesores (
                id_usuario,
                id_departamento,
                cip,
                primer_nombre,
                segundo_nombre,
                primer_apellido,
                segundo_apellido,
                departamento,
                especialidad,
                estado
            )
            VALUES (
                :id_usuario,
                :id_departamento,
                :cip,
                :primer_nombre,
                :segundo_nombre,
                :primer_apellido,
                :segundo_apellido,
                :departamento,
                :especialidad,
                1
            )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_usuario' => $data['id_usuario'],
            ':id_departamento' => $data['id_departamento'],
            ':cip' => $data['cip'],
            ':primer_nombre' => $data['primer_nombre'],
            ':segundo_nombre' => $data['segundo_nombre'] !== '' ? $data['segundo_nombre'] : null,
            ':primer_apellido' => $data['primer_apellido'],
            ':segundo_apellido' =>
                $data['segundo_apellido'] !== '' ? $data['segundo_apellido'] : null,
            ':departamento' => $data['departamento'],
            ':especialidad' => $data['especialidad'] !== '' ? $data['especialidad'] : null,
        ]);
    }

    public function actualizar(array $data): bool
    {
        $sql = "UPDATE profesores
            SET
                id_departamento = :id_departamento,
                cip = :cip,
                primer_nombre = :primer_nombre,
                segundo_nombre = :segundo_nombre,
                primer_apellido = :primer_apellido,
                segundo_apellido = :segundo_apellido,
                departamento = :departamento,
                especialidad = :especialidad,
                fecha_actualizacion = CURRENT_TIMESTAMP
            WHERE id_profesor = :id_profesor";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_departamento' => $data['id_departamento'],
            ':cip' => $data['cip'],
            ':primer_nombre' => $data['primer_nombre'],
            ':segundo_nombre' => $data['segundo_nombre'] !== '' ? $data['segundo_nombre'] : null,
            ':primer_apellido' => $data['primer_apellido'],
            ':segundo_apellido' =>
                $data['segundo_apellido'] !== '' ? $data['segundo_apellido'] : null,
            ':departamento' => $data['departamento'],
            ':especialidad' => $data['especialidad'] !== '' ? $data['especialidad'] : null,
            ':id_profesor' => $data['id_profesor'],
        ]);
    }

    public function cambiarEstado(int $idProfesor, int $estado): bool
    {
        $sql = "UPDATE profesores
                SET
                    estado = :estado,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_profesor = :id_profesor";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':estado' => $estado,
            ':id_profesor' => $idProfesor,
        ]);
    }
}
