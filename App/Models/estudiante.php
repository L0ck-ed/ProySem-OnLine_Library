<?php

namespace App\Models;
use App\Core\Sql;

use App\Core\Model;
use App\Core\Contracts\AutenticableRepositorioInterface;
use PDO;

class Estudiante extends Model implements AutenticableRepositorioInterface
{
    public function buscarPorCredencial(string $credencial): array|false
    {
        $sql = "SELECT
                e.id_estudiante AS id,
                e.id_estudiante,
                e.id_usuario,
                e.id_carrera,
                e.cip,
                e.primer_nombre,
                e.segundo_nombre,
                e.primer_apellido,
                e.segundo_apellido,
                e.fecha_nacimiento,
                e.estado,
                u.nombre AS nombre_usuario,
                u.usuario,
                u.correo,
                u.password_hash AS pin_hash,
                u.bloqueado,
                u.bloqueado_hasta,
                u.intentos_fallidos,
                u.ultimo_login,
                u.ultimo_intento
            FROM estudiantes e
            INNER JOIN usuarios u
                ON u.id_usuario = e.id_usuario
            WHERE (
                    e.cip = :credencial_cip
                    OR u.usuario = :credencial_usuario
                  )
              AND e.estado = 1
              AND u.estado = 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':credencial_cip' => $credencial,
            ':credencial_usuario' => $credencial,
        ]);

        return $stmt->fetch();
    }

    public function aumentarIntentos(int $idEstudiante): void
    {
        $sql = "UPDATE usuarios
            SET
                intentos_fallidos =
                    intentos_fallidos + 1,
                ultimo_intento =
                    CURRENT_TIMESTAMP
            WHERE id_usuario = (
                SELECT id_usuario
                FROM estudiantes
                WHERE id_estudiante = :id_estudiante
            )";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_estudiante' => $idEstudiante,
        ]);
    }

    public function bloquearUsuario(int $idEstudiante): void
    {
        $sql = "UPDATE usuarios
            SET
                bloqueado = 1,
                bloqueado_hasta =
                    DATEADD(MINUTE, 15, CURRENT_TIMESTAMP)
            WHERE id_usuario = (
                SELECT id_usuario
                FROM estudiantes
                WHERE id_estudiante = :id_estudiante
            )";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_estudiante' => $idEstudiante,
        ]);
    }

    public function actualizarLogin(int $idEstudiante): void
    {
        $sql = "UPDATE usuarios
            SET
                ultimo_login = CURRENT_TIMESTAMP,
                ultimo_intento = CURRENT_TIMESTAMP,
                intentos_fallidos = 0,
                bloqueado = 0,
                bloqueado_hasta = NULL
            WHERE id_usuario = (
                SELECT id_usuario
                FROM estudiantes
                WHERE id_estudiante = :id_estudiante
            )";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_estudiante' => $idEstudiante,
        ]);
    }

    public function establecerPin(int $id, string $pinTextoPlano): bool
    {
        $sql = "UPDATE estudiantes
                SET pin_hash = :pin_hash
                WHERE id_estudiante = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':pin_hash' => password_hash($pinTextoPlano, PASSWORD_DEFAULT),
            ':id' => $id,
        ]);
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = "SELECT e.*, c.nombre AS carrera
                FROM estudiantes e
                JOIN carreras c ON e.id_carrera = c.id_carrera
                WHERE e.id_estudiante = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerPorCip(string $cip): array|false
    {
        $sql = "SELECT e.*, c.nombre AS carrera
                FROM estudiantes e
                JOIN carreras c ON e.id_carrera = c.id_carrera
                WHERE e.cip = :cip";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cip' => $cip]);
        return $stmt->fetch();
    }

    public function listar(string $buscar = '', int $limit = 10, int $offset = 0): array
    {
        $sql =
            "SELECT
                e.id_estudiante,
                e.cip,
                e.primer_nombre,
                e.segundo_nombre,
                e.primer_apellido,
                e.segundo_apellido,
                e.fecha_nacimiento,
                e.estado,
                e.fecha_creacion,
                c.id_carrera,
                c.nombre AS carrera,
                f.id_facultad,
                COALESCE(f.nombre, 'Sin facultad') AS facultad,
                u.id_usuario,
                u.nombre AS nombre_usuario,
                u.usuario,
                u.correo
            FROM estudiantes e
            INNER JOIN carreras c
                ON c.id_carrera = e.id_carrera
            LEFT JOIN facultades f
                ON f.id_facultad = c.id_facultad
            INNER JOIN usuarios u
                ON u.id_usuario = e.id_usuario
            WHERE e.cip LIKE :buscar_cip
               OR e.primer_nombre LIKE :buscar_nombre
               OR e.primer_apellido LIKE :buscar_apellido
               OR c.nombre LIKE :buscar_carrera
               OR f.nombre LIKE :buscar_facultad
               OR u.usuario LIKE :buscar_usuario
            ORDER BY e.id_estudiante DESC
            " . Sql::limitOffset($this->db);

        $termino = '%' . $buscar . '%';

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':buscar_cip', $termino, PDO::PARAM_STR);

        $stmt->bindValue(':buscar_nombre', $termino, PDO::PARAM_STR);

        $stmt->bindValue(':buscar_apellido', $termino, PDO::PARAM_STR);

        $stmt->bindValue(':buscar_carrera', $termino, PDO::PARAM_STR);

        $stmt->bindValue(':buscar_facultad', $termino, PDO::PARAM_STR);

        $stmt->bindValue(':buscar_usuario', $termino, PDO::PARAM_STR);

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contar(string $buscar = ''): int
    {
        $sql = "SELECT
                COUNT(DISTINCT e.id_estudiante) AS total
            FROM estudiantes e
            INNER JOIN carreras c
                ON c.id_carrera = e.id_carrera
            LEFT JOIN facultades f
                ON f.id_facultad = c.id_facultad
            INNER JOIN usuarios u
                ON u.id_usuario = e.id_usuario
            WHERE e.cip LIKE :buscar_cip
               OR e.primer_nombre LIKE :buscar_nombre
               OR e.primer_apellido LIKE :buscar_apellido
               OR c.nombre LIKE :buscar_carrera
               OR f.nombre LIKE :buscar_facultad
               OR u.usuario LIKE :buscar_usuario";

        $termino = '%' . $buscar . '%';

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':buscar_cip' => $termino,
            ':buscar_nombre' => $termino,
            ':buscar_apellido' => $termino,
            ':buscar_carrera' => $termino,
            ':buscar_facultad' => $termino,
            ':buscar_usuario' => $termino,
        ]);

        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }

    public function buscarPorCip(string $cip): array|false
    {
        $sql = "SELECT
                id_estudiante,
                id_usuario,
                id_carrera,
                cip,
                primer_nombre,
                primer_apellido,
                estado
            FROM estudiantes
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
                id_estudiante,
                id_usuario,
                id_carrera,
                cip,
                primer_nombre,
                primer_apellido,
                estado
            FROM estudiantes
            WHERE id_usuario = :id_usuario";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_usuario' => $idUsuario,
        ]);

        return $stmt->fetch();
    }

    public function crear(array $data): bool
    {
        $sql = "INSERT INTO estudiantes (
                id_usuario,
                id_carrera,
                cip,
                primer_nombre,
                segundo_nombre,
                primer_apellido,
                segundo_apellido,
                fecha_nacimiento,
                estado
            )
            VALUES (
                :id_usuario,
                :id_carrera,
                :cip,
                :primer_nombre,
                :segundo_nombre,
                :primer_apellido,
                :segundo_apellido,
                :fecha_nacimiento,
                1
            )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_usuario' => $data['id_usuario'],
            ':id_carrera' => $data['id_carrera'],
            ':cip' => $data['cip'],
            ':primer_nombre' => $data['primer_nombre'],
            ':segundo_nombre' => $data['segundo_nombre'] ?: null,
            ':primer_apellido' => $data['primer_apellido'],
            ':segundo_apellido' => $data['segundo_apellido'] ?: null,
            ':fecha_nacimiento' => $data['fecha_nacimiento'],
        ]);
    }

    public function buscarPorId(int $idEstudiante): array|false
    {
        $sql = "SELECT
                e.id_estudiante,
                e.id_usuario,
                e.id_carrera,
                e.cip,
                e.primer_nombre,
                e.segundo_nombre,
                e.primer_apellido,
                e.segundo_apellido,
                e.fecha_nacimiento,
                e.estado,
                c.nombre AS carrera,
                c.id_facultad,
                f.nombre AS facultad,
                u.nombre AS nombre_usuario,
                u.usuario,
                u.correo
            FROM estudiantes e
            INNER JOIN carreras c
                ON c.id_carrera = e.id_carrera
            LEFT JOIN facultades f
                ON f.id_facultad = c.id_facultad
            INNER JOIN usuarios u
                ON u.id_usuario = e.id_usuario
            WHERE e.id_estudiante = :id_estudiante";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_estudiante' => $idEstudiante,
        ]);

        return $stmt->fetch();
    }

    public function actualizar(array $data): bool
    {
        $sql = "UPDATE estudiantes
            SET
                id_carrera = :id_carrera,
                cip = :cip,
                primer_nombre = :primer_nombre,
                segundo_nombre = :segundo_nombre,
                primer_apellido = :primer_apellido,
                segundo_apellido = :segundo_apellido,
                fecha_nacimiento = :fecha_nacimiento
            WHERE id_estudiante = :id_estudiante";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_carrera' => $data['id_carrera'],
            ':cip' => $data['cip'],
            ':primer_nombre' => $data['primer_nombre'],
            ':segundo_nombre' => $data['segundo_nombre'] !== '' ? $data['segundo_nombre'] : null,
            ':primer_apellido' => $data['primer_apellido'],
            ':segundo_apellido' =>
                $data['segundo_apellido'] !== '' ? $data['segundo_apellido'] : null,
            ':fecha_nacimiento' => $data['fecha_nacimiento'],
            ':id_estudiante' => $data['id_estudiante'],
        ]);
    }

    public function cambiarEstado(int $idEstudiante, int $estado): bool
    {
        $sql = "UPDATE estudiantes
            SET estado = :estado
            WHERE id_estudiante = :id_estudiante";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':estado' => $estado,
            ':id_estudiante' => $idEstudiante,
        ]);
    }
}
