<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Libro extends Model
{
    public function listar(string $buscar = '', string $categoria = '', int $limit = 12, int $offset = 0): array
    {
        $sql = "SELECT l.id_libro, l.titulo, l.autor, l.existencias, l.imagen,
                       c.nombre AS categoria
                FROM libros l
                INNER JOIN categorias c ON c.id_categoria = l.id_categoria
                WHERE l.estado = 'Activo'
                  AND (l.titulo LIKE :buscar OR l.autor LIKE :buscar)
                  AND (:categoria = '' OR c.nombre = :categoria)
                ORDER BY l.id_libro DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
        $stmt->bindValue(':categoria', $categoria, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contar(string $buscar = '', string $categoria = ''): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM libros l
                INNER JOIN categorias c ON c.id_categoria = l.id_categoria
                WHERE l.estado = 'Activo'
                  AND (l.titulo LIKE :buscar OR l.autor LIKE :buscar)
                  AND (:categoria = '' OR c.nombre = :categoria)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':buscar' => '%' . $buscar . '%',
            ':categoria' => $categoria
        ]);

        return (int) $stmt->fetch()['total'];
    }

    public function contarTotal(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM libros WHERE estado = 'Activo'");
        return (int) $stmt->fetch()['total'];
    }

    public function contarDisponibles(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM libros WHERE estado = 'Activo' AND existencias > 0");
        return (int) $stmt->fetch()['total'];
    }

    public function recientes(int $limit = 4): array
    {
        $sql = "SELECT l.id_libro, l.titulo, l.autor, l.existencias,
                       c.nombre AS categoria
                FROM libros l
                INNER JOIN categorias c ON c.id_categoria = l.id_categoria
                WHERE l.estado = 'Activo'
                ORDER BY l.id_libro DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT l.*, c.nombre AS categoria
                FROM libros l
                INNER JOIN categorias c ON c.id_categoria = l.id_categoria
                WHERE l.id_libro = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: false;
    }
}