<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Libro extends Model
{
    public function listar(string $buscar = '', string $categoria = '', int $limit = 12, int $offset = 0): array
    {
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

        $sql = "SELECT l.*, c.nombre AS categoria
                FROM libros l
                JOIN categorias c ON l.id_categoria = c.id_categoria
                WHERE l.estado = 'Activo'";

        $params = [];

        if (!empty($buscar)) {
            // Nombres de parámetro DISTINTOS aunque el valor sea el mismo:
            // el driver sqlsrv no admite el mismo parámetro nombrado repetido en una consulta.
            $sql .= " AND (l.titulo LIKE :buscar_titulo OR l.autor LIKE :buscar_autor)";
            $params[':buscar_titulo'] = '%' . $buscar . '%';
            $params[':buscar_autor'] = '%' . $buscar . '%';
        }

        if (!empty($categoria)) {
            $sql .= " AND c.nombre = :categoria";
            $params[':categoria'] = $categoria;
        }

        $sql .= " ORDER BY l.titulo ASC";

        if ($driver === 'sqlsrv') {
            $sql .= " OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
        } else {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contar(string $buscar = '', string $categoria = ''): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM libros l
                JOIN categorias c ON l.id_categoria = c.id_categoria
                WHERE l.estado = 'Activo'";

        $params = [];

        if (!empty($buscar)) {
            $sql .= " AND (l.titulo LIKE :buscar_titulo OR l.autor LIKE :buscar_autor)";
            $params[':buscar_titulo'] = '%' . $buscar . '%';
            $params[':buscar_autor'] = '%' . $buscar . '%';
        }

        if (!empty($categoria)) {
            $sql .= " AND c.nombre = :categoria";
            $params[':categoria'] = $categoria;
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();
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
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlsrv') {

            $sql = "SELECT TOP ($limit)
                           l.id_libro,
                           l.titulo,
                           l.autor,
                           l.existencias,
                           c.nombre AS categoria
                    FROM libros l
                    INNER JOIN categorias c
                        ON c.id_categoria = l.id_categoria
                    WHERE l.estado = 'Activo'
                    ORDER BY l.id_libro DESC";

            $stmt = $this->db->prepare($sql);

        } else {

            $sql = "SELECT
                           l.id_libro,
                           l.titulo,
                           l.autor,
                           l.existencias,
                           c.nombre AS categoria
                    FROM libros l
                    INNER JOIN categorias c
                        ON c.id_categoria = l.id_categoria
                    WHERE l.estado = 'Activo'
                    ORDER BY l.id_libro DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT l.*, c.nombre AS categoria
                FROM libros l
                INNER JOIN categorias c
                    ON c.id_categoria = l.id_categoria
                WHERE l.id_libro = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: false;
    }
}