<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Sql;
use PDO;

class Categoria extends Model
{
    public function listar(string $buscar = '', int $limit = 10, int $offset = 0): array
    {
        $sql =
            "SELECT
                    id_categoria,
                    nombre,
                    descripcion,
                    estado
                FROM categorias
                WHERE nombre LIKE :buscar_nombre
                   OR COALESCE(
                        descripcion,
                        ''
                   ) LIKE :buscar_descripcion
                ORDER BY id_categoria DESC
                " . Sql::limitOffset($this->db);

        $termino = '%' . $buscar . '%';

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':buscar_nombre', $termino, PDO::PARAM_STR);

        $stmt->bindValue(':buscar_descripcion', $termino, PDO::PARAM_STR);

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contar(string $buscar = ''): int
    {
        $sql = "SELECT
                    COUNT(*) AS total
                FROM categorias
                WHERE nombre LIKE :buscar_nombre
                   OR COALESCE(
                        descripcion,
                        ''
                   ) LIKE :buscar_descripcion";

        $termino = '%' . $buscar . '%';

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':buscar_nombre' => $termino,
            ':buscar_descripcion' => $termino,
        ]);

        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }

    public function buscarPorId(int $idCategoria): array|false
    {
        $sql = "SELECT
                    id_categoria,
                    nombre,
                    descripcion,
                    estado
                FROM categorias
                WHERE id_categoria = :id_categoria";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_categoria' => $idCategoria,
        ]);

        return $stmt->fetch();
    }

    public function buscarPorNombre(string $nombre): array|false
    {
        $sql = "SELECT
                    id_categoria,
                    nombre,
                    descripcion,
                    estado
                FROM categorias
                WHERE nombre = :nombre";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nombre' => $nombre,
        ]);

        return $stmt->fetch();
    }

    public function crear(array $data): bool
    {
        $sql = "INSERT INTO categorias (
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
            ':descripcion' => $data['descripcion'] !== '' ? $data['descripcion'] : null,
        ]);
    }

    public function actualizar(array $data): bool
    {
        $sql = "UPDATE categorias
                SET
                    nombre = :nombre,
                    descripcion = :descripcion
                WHERE id_categoria = :id_categoria";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] !== '' ? $data['descripcion'] : null,
            ':id_categoria' => $data['id_categoria'],
        ]);
    }

    public function cambiarEstado(int $idCategoria, int $estado): bool
    {
        $sql = "UPDATE categorias
                SET estado = :estado
                WHERE id_categoria = :id_categoria";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':estado' => $estado,
            ':id_categoria' => $idCategoria,
        ]);
    }

    public function listarActivas(): array
    {
        $sql = "SELECT
                    id_categoria,
                    nombre,
                    descripcion
                FROM categorias
                WHERE estado = 1
                ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
