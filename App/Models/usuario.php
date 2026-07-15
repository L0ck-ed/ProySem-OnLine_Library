<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Sql;
use PDO;

class Usuario extends Model
{
    public function buscarPorUsuario(string $usuario): array|false
    {
        $topInfo = Sql::top($this->db, 1);

        $sql = "SELECT {$topInfo['antes']}
                u.id_usuario,
                u.nombre,
                u.usuario,
                u.password_hash AS password,
                u.correo,
                u.estado,
                u.intentos_fallidos,
                u.bloqueado,
                u.bloqueado_hasta,
                u.ultimo_login,
                u.ultimo_intento,
                r.id_rol,
                r.nombre AS rol
            FROM usuarios u
            LEFT JOIN usuarios_roles ur
                ON ur.id_usuario = u.id_usuario
            LEFT JOIN roles r
                ON r.id_rol = ur.id_rol
            WHERE u.usuario = :usuario
            ORDER BY ur.fecha_asignacion ASC
            {$topInfo['despues']}";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':usuario' => $usuario,
        ]);

        return $stmt->fetch();
    }

    public function buscarPorId(int $idUsuario): array|false
    {
        $sql = "SELECT
                u.id_usuario,
                u.nombre,
                u.usuario,
                u.correo,
                u.estado,
                u.bloqueado,
                u.intentos_fallidos,
                ur.id_rol,
                r.nombre AS rol
            FROM usuarios u
            LEFT JOIN usuarios_roles ur
                ON ur.id_usuario = u.id_usuario
            LEFT JOIN roles r
                ON r.id_rol = ur.id_rol
            WHERE u.id_usuario = :id_usuario
            ORDER BY ur.fecha_asignacion ASC";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_usuario' => $idUsuario,
        ]);

        return $stmt->fetch();
    }

    public function actualizar(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $sqlUsuario = "UPDATE usuarios
                       SET
                           nombre = :nombre,
                           usuario = :usuario,
                           estado = :estado,
                           fecha_actualizacion = CURRENT_TIMESTAMP";

            /*
             * La contraseña solamente se modifica cuando el usuario
             * escribió una nueva.
             */
            if (!empty($data['password_hash'])) {
                $sqlUsuario .= ",
                           password_hash = :password_hash";
            }

            $sqlUsuario .= "
                       WHERE id_usuario = :id_usuario";

            $stmtUsuario = $this->db->prepare($sqlUsuario);

            $parametrosUsuario = [
                ':nombre' => $data['nombre'],
                ':usuario' => $data['usuario'],
                ':estado' => $data['estado'],
                ':id_usuario' => $data['id_usuario'],
            ];

            if (!empty($data['password_hash'])) {
                $parametrosUsuario[':password_hash'] = $data['password_hash'];
            }

            $stmtUsuario->execute($parametrosUsuario);

            /*
             * Por ahora el formulario administra un solo rol.
             * Se elimina la asignación anterior y se coloca la nueva.
             */
            $sqlEliminarRol = "DELETE FROM usuarios_roles
                           WHERE id_usuario = :id_usuario";

            $stmtEliminarRol = $this->db->prepare($sqlEliminarRol);

            $stmtEliminarRol->execute([
                ':id_usuario' => $data['id_usuario'],
            ]);

            $sqlAsignarRol = "INSERT INTO usuarios_roles (
                              id_usuario,
                              id_rol
                          )
                          VALUES (
                              :id_usuario,
                              :id_rol
                          )";

            $stmtAsignarRol = $this->db->prepare($sqlAsignarRol);

            $stmtAsignarRol->execute([
                ':id_usuario' => $data['id_usuario'],
                ':id_rol' => $data['id_rol'],
            ]);

            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function actualizarLogin(int $idUsuario): void
    {
        $sql = "UPDATE usuarios
                SET ultimo_login = CURRENT_TIMESTAMP,
                    ultimo_intento = CURRENT_TIMESTAMP,
                    intentos_fallidos = 0
                WHERE id_usuario = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $idUsuario,
        ]);
    }

    public function aumentarIntentos(int $idUsuario): void
    {
        $sql = "UPDATE usuarios
                SET intentos_fallidos = intentos_fallidos + 1,
                    ultimo_intento = CURRENT_TIMESTAMP
                WHERE id_usuario = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $idUsuario,
        ]);
    }

    public function bloquearUsuario(int $idUsuario): void
    {
        $sql = "UPDATE usuarios
                SET bloqueado = 1
                WHERE id_usuario = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $idUsuario,
        ]);
    }

    public function crear(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $sqlUsuario = "INSERT INTO usuarios (
                            nombre,
                            usuario,
                            password_hash,
                            correo,
                            estado,
                            intentos_fallidos,
                            bloqueado
                       )
                       VALUES (
                            :nombre,
                            :usuario,
                            :password_hash,
                            :correo,
                            1,
                            0,
                            0
                       )";

            $stmtUsuario = $this->db->prepare($sqlUsuario);

            $stmtUsuario->execute([
                ':nombre' => $data['nombre'],
                ':usuario' => $data['usuario'],
                ':password_hash' => $data['password_hash'],
                ':correo' => $data['correo'] ?? null,
            ]);

            $idUsuario = (int) $this->db->lastInsertId();

            if ($idUsuario <= 0) {
                throw new \RuntimeException('No se pudo obtener el identificador del usuario.');
            }

            $sqlRol = "INSERT INTO usuarios_roles (
                        id_usuario,
                        id_rol
                   )
                   VALUES (
                        :id_usuario,
                        :id_rol
                   )";

            $stmtRol = $this->db->prepare($sqlRol);

            $stmtRol->execute([
                ':id_usuario' => $idUsuario,
                ':id_rol' => $data['id_rol'],
            ]);

            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function listar(string $buscar = '', int $limit = 10, int $offset = 0): array
    {
        // GROUP_CONCAT (MySQL) vs STRING_AGG (SQL Server) para unir los roles de un usuario en un solo texto
        $agregarRoles = Sql::esSqlServer($this->db)
            ? "STRING_AGG(r.nombre, ', ')"
            : "GROUP_CONCAT(r.nombre SEPARATOR ', ')";

        $sql =
            "SELECT
                u.id_usuario,
                u.nombre,
                u.usuario,
                COALESCE(
                    {$agregarRoles},
                    'Sin rol'
                ) AS rol,
                CASE
                    WHEN u.estado = 1 THEN 'Activo'
                    ELSE 'Inactivo'
                END AS estado,
                u.fecha_creacion
            FROM usuarios u
            LEFT JOIN usuarios_roles ur
                ON ur.id_usuario = u.id_usuario
            LEFT JOIN roles r
                ON r.id_rol = ur.id_rol
            WHERE u.nombre LIKE :buscar_nombre
               OR u.usuario LIKE :buscar_usuario
            GROUP BY
                u.id_usuario,
                u.nombre,
                u.usuario,
                u.estado,
                u.fecha_creacion
            ORDER BY u.id_usuario DESC
            " . Sql::limitOffset($this->db);

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':buscar_nombre', '%' . $buscar . '%', PDO::PARAM_STR);

        $stmt->bindValue(':buscar_usuario', '%' . $buscar . '%', PDO::PARAM_STR);

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contar(string $buscar = ''): int
    {
        $sql = "SELECT COUNT(DISTINCT u.id_usuario) AS total
                FROM usuarios u
                WHERE u.nombre LIKE :buscar_nombre
                OR u.usuario LIKE :buscar_usuario";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':buscar_nombre' => '%' . $buscar . '%',
            ':buscar_usuario' => '%' . $buscar . '%',
        ]);

        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }

    public function listarRolesActivos(): array
    {
        $sql = "SELECT
                    id_rol,
                    nombre
                FROM roles
                WHERE estado = 1
                ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}