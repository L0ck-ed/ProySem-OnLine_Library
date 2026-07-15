<?php

namespace App\Models;

use App\Core\Model;

class Rol extends Model
{
    public function listar(): array
    {
        $sql = "SELECT
                    r.id_rol,
                    r.nombre,
                    r.descripcion,
                    r.estado,
                    r.fecha_creacion,
                    COUNT(DISTINCT rp.id_permiso) AS total_permisos
                FROM roles r
                LEFT JOIN roles_permisos rp
                    ON rp.id_rol = r.id_rol
                GROUP BY
                    r.id_rol,
                    r.nombre,
                    r.descripcion,
                    r.estado,
                    r.fecha_creacion
                ORDER BY r.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listarPermisos(): array
    {
        $sql = "SELECT
                id_permiso,
                codigo,
                modulo,
                accion,
                descripcion,
                estado
            FROM permisos
            WHERE estado = 1
            ORDER BY modulo ASC, accion ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idRol): array|false
    {
        $sql = "SELECT
                id_rol,
                nombre,
                descripcion,
                estado,
                fecha_creacion
            FROM roles
            WHERE id_rol = :id_rol";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_rol' => $idRol,
        ]);

        return $stmt->fetch();
    }

    public function obtenerPermisosDelRol(int $idRol): array
    {
        $sql = "SELECT
                id_permiso
            FROM roles_permisos
            WHERE id_rol = :id_rol
            ORDER BY id_permiso ASC";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_rol' => $idRol,
        ]);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function actualizarPermisos(int $idRol, array $idPermisos): bool
    {
        try {
            $this->db->beginTransaction();

            $sqlEliminar = "DELETE FROM roles_permisos
                        WHERE id_rol = :id_rol";

            $stmtEliminar = $this->db->prepare($sqlEliminar);

            $stmtEliminar->execute([
                ':id_rol' => $idRol,
            ]);

            if (!empty($idPermisos)) {
                $sqlInsertar = "INSERT INTO roles_permisos (
                                id_rol,
                                id_permiso
                            )
                            VALUES (
                                :id_rol,
                                :id_permiso
                            )";

                $stmtInsertar = $this->db->prepare($sqlInsertar);

                foreach ($idPermisos as $idPermiso) {
                    $stmtInsertar->execute([
                        ':id_rol' => $idRol,
                        ':id_permiso' => (int) $idPermiso,
                    ]);
                }
            }

            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function buscarPorNombre(string $nombre): array|false
    {
        $sql = "SELECT
                id_rol,
                nombre,
                descripcion,
                estado
            FROM roles
            WHERE nombre = :nombre";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nombre' => $nombre,
        ]);

        return $stmt->fetch();
    }

    public function crear(array $data): bool
    {
        $sql = "INSERT INTO roles (
                nombre,
                descripcion,
                estado
            )
            VALUES (
                :nombre,
                :descripcion,
                1
            )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'],
        ]);
    }

    public function actualizar(array $data): bool
    {
        $sql = "UPDATE roles
            SET
                nombre = :nombre,
                descripcion = :descripcion,
                estado = :estado
            WHERE id_rol = :id_rol";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'],
            ':estado' => $data['estado'],
            ':id_rol' => $data['id_rol'],
        ]);
    }

    public function cambiarEstado(int $idRol, int $estado): bool
    {
        $sql = "UPDATE roles
            SET estado = :estado
            WHERE id_rol = :id_rol";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':estado' => $estado,
            ':id_rol' => $idRol,
        ]);
    }
}
