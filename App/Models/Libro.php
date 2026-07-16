<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Sql;
use PDO;

class Libro extends Model
{
    public function listarAdmin(
        string $buscar = '',
        int $limit = 10,
        int $offset = 0
    ): array {
        $temas = $this->subconsultaTemas();

        $sql = "SELECT
                    l.id_libro,
                    l.titulo,
                    l.autor,
                    l.isbn,
                    l.editorial,
                    l.anio_publicacion,
                    l.costo,
                    l.existencias_totales,
                    l.existencias_disponibles,
                    l.imagen_ruta,
                    l.thumbnail_ruta,
                    l.ubicacion_fisica,
                    l.estado,
                    l.fecha_creacion,
                    c.nombre AS categoria,
                    COALESCE(({$temas}), 'Sin temas') AS temas
                FROM libros l
                INNER JOIN categorias c
                    ON c.id_categoria = l.id_categoria
                WHERE l.titulo LIKE :buscar_titulo
                   OR l.autor LIKE :buscar_autor
                   OR COALESCE(l.isbn, '') LIKE :buscar_isbn
                   OR c.nombre LIKE :buscar_categoria
                   OR EXISTS (
                        SELECT 1
                        FROM libros_temas lt_busqueda
                        INNER JOIN temas t_busqueda
                            ON t_busqueda.id_tema = lt_busqueda.id_tema
                        WHERE lt_busqueda.id_libro = l.id_libro
                          AND t_busqueda.nombre LIKE :buscar_tema
                   )
                ORDER BY l.id_libro DESC
                " . Sql::limitOffset($this->db);

        $termino = '%' . $buscar . '%';
        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':buscar_titulo', $termino, PDO::PARAM_STR);
        $stmt->bindValue(':buscar_autor', $termino, PDO::PARAM_STR);
        $stmt->bindValue(':buscar_isbn', $termino, PDO::PARAM_STR);
        $stmt->bindValue(':buscar_categoria', $termino, PDO::PARAM_STR);
        $stmt->bindValue(':buscar_tema', $termino, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contarAdmin(string $buscar = ''): int
    {
        $sql = "SELECT COUNT(DISTINCT l.id_libro) AS total
                FROM libros l
                INNER JOIN categorias c
                    ON c.id_categoria = l.id_categoria
                WHERE l.titulo LIKE :buscar_titulo
                   OR l.autor LIKE :buscar_autor
                   OR COALESCE(l.isbn, '') LIKE :buscar_isbn
                   OR c.nombre LIKE :buscar_categoria
                   OR EXISTS (
                        SELECT 1
                        FROM libros_temas lt_busqueda
                        INNER JOIN temas t_busqueda
                            ON t_busqueda.id_tema = lt_busqueda.id_tema
                        WHERE lt_busqueda.id_libro = l.id_libro
                          AND t_busqueda.nombre LIKE :buscar_tema
                   )";

        $termino = '%' . $buscar . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':buscar_titulo' => $termino,
            ':buscar_autor' => $termino,
            ':buscar_isbn' => $termino,
            ':buscar_categoria' => $termino,
            ':buscar_tema' => $termino,
        ]);

        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }


    public function listarReporteAdmin(string $buscar = ''): array
    {
        $temas = $this->subconsultaTemas();
        $sql = "SELECT
                    l.id_libro, l.titulo, l.autor, l.isbn, l.editorial,
                    l.anio_publicacion, l.descripcion, l.costo,
                    l.existencias_totales, l.existencias_disponibles,
                    l.ubicacion_fisica, l.estado, l.fecha_creacion,
                    c.nombre AS categoria,
                    COALESCE(({$temas}), 'Sin temas') AS temas
                FROM libros l
                INNER JOIN categorias c ON c.id_categoria = l.id_categoria
                WHERE l.titulo LIKE :buscar_titulo
                   OR l.autor LIKE :buscar_autor
                   OR COALESCE(l.isbn, '') LIKE :buscar_isbn
                   OR c.nombre LIKE :buscar_categoria
                   OR EXISTS (
                        SELECT 1 FROM libros_temas lt_busqueda
                        INNER JOIN temas t_busqueda ON t_busqueda.id_tema = lt_busqueda.id_tema
                        WHERE lt_busqueda.id_libro = l.id_libro
                          AND t_busqueda.nombre LIKE :buscar_tema
                   )
                ORDER BY l.titulo ASC";
        $termino = '%' . $buscar . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':buscar_titulo' => $termino,
            ':buscar_autor' => $termino,
            ':buscar_isbn' => $termino,
            ':buscar_categoria' => $termino,
            ':buscar_tema' => $termino,
        ]);
        return $stmt->fetchAll();
    }

    public function datosParaFirma(int $idLibro): array
    {
        $stmt = $this->db->prepare(
            "SELECT id_libro, titulo, autor, isbn, editorial, anio_publicacion,
                    descripcion, costo, existencias_totales,
                    imagen_nombre, imagen_ruta, thumbnail_nombre, thumbnail_ruta,
                    ubicacion_fisica, id_categoria, estado
             FROM libros WHERE id_libro = :id_libro"
        );
        $stmt->execute([':id_libro' => $idLibro]);
        $fila = $stmt->fetch();
        return is_array($fila) ? $fila : [];
    }

    public function buscarPorId(int $idLibro): array|false
    {
        $temas = $this->subconsultaTemas();

        $sql = "SELECT
                    l.*,
                    c.nombre AS categoria,
                    COALESCE(({$temas}), 'Sin temas') AS temas,
                    l.existencias_disponibles AS existencias,
                    l.ubicacion_fisica AS ubicacion
                FROM libros l
                INNER JOIN categorias c
                    ON c.id_categoria = l.id_categoria
                WHERE l.id_libro = :id_libro";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_libro' => $idLibro,
        ]);

        return $stmt->fetch();
    }

    public function buscarPorIsbn(string $isbn): array|false
    {
        $sql = "SELECT
                    id_libro,
                    isbn,
                    titulo
                FROM libros
                WHERE isbn = :isbn";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':isbn' => $isbn,
        ]);

        return $stmt->fetch();
    }

    public function obtenerTemasLibro(int $idLibro): array
    {
        $sql = "SELECT id_tema
                FROM libros_temas
                WHERE id_libro = :id_libro
                ORDER BY id_tema ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_libro' => $idLibro,
        ]);

        return array_map(
            'intval',
            $stmt->fetchAll(PDO::FETCH_COLUMN)
        );
    }

    public function crear(array $data, array $idsTemas): int
    {
        try {
            $this->db->beginTransaction();

            $esSqlServer = Sql::esSqlServer($this->db);

            $sql = "INSERT INTO libros (
                        titulo,
                        autor,
                        isbn,
                        editorial,
                        anio_publicacion,
                        descripcion,
                        costo,
                        existencias_totales,
                        existencias_disponibles,
                        imagen_nombre,
                        imagen_ruta,
                        thumbnail_nombre,
                        thumbnail_ruta,
                        ubicacion_fisica,
                        id_categoria,
                        estado
                    )";

            if ($esSqlServer) {
                $sql .= " OUTPUT INSERTED.id_libro";
            }

            $sql .= " VALUES (
                        :titulo,
                        :autor,
                        :isbn,
                        :editorial,
                        :anio_publicacion,
                        :descripcion,
                        :costo,
                        :existencias_totales,
                        :existencias_disponibles,
                        :imagen_nombre,
                        :imagen_ruta,
                        :thumbnail_nombre,
                        :thumbnail_ruta,
                        :ubicacion_fisica,
                        :id_categoria,
                        1
                    )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($this->parametrosLibro($data));

            if ($esSqlServer) {
                $idLibro = (int) $stmt->fetchColumn();
                $stmt->closeCursor();
            } else {
                $idLibro = (int) $this->db->lastInsertId();
            }

            if ($idLibro <= 0) {
                throw new \RuntimeException(
                    'No se pudo obtener el identificador del libro.'
                );
            }

            $this->sincronizarTemas($idLibro, $idsTemas);
            $this->db->commit();

            return $idLibro;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function actualizar(array $data, array $idsTemas): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE libros
                    SET
                        titulo = :titulo,
                        autor = :autor,
                        isbn = :isbn,
                        editorial = :editorial,
                        anio_publicacion = :anio_publicacion,
                        descripcion = :descripcion,
                        costo = :costo,
                        existencias_totales = :existencias_totales,
                        existencias_disponibles = :existencias_disponibles,
                        imagen_nombre = :imagen_nombre,
                        imagen_ruta = :imagen_ruta,
                        thumbnail_nombre = :thumbnail_nombre,
                        thumbnail_ruta = :thumbnail_ruta,
                        ubicacion_fisica = :ubicacion_fisica,
                        id_categoria = :id_categoria,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id_libro = :id_libro";

            $parametros = $this->parametrosLibro($data);
            $parametros[':id_libro'] = $data['id_libro'];

            $stmt = $this->db->prepare($sql);
            $stmt->execute($parametros);

            $this->sincronizarTemas(
                (int) $data['id_libro'],
                $idsTemas
            );

            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function cambiarEstado(int $idLibro, int $estado): bool
    {
        $sql = "UPDATE libros
                SET
                    estado = :estado,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_libro = :id_libro";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':estado' => $estado,
            ':id_libro' => $idLibro,
        ]);
    }

    /* =========================================================
       MÉTODOS DEL PORTAL PÚBLICO/CLIENTE
       ========================================================= */

    public function listar(
        string $buscar = '',
        string $categoria = '',
        int $limit = 12,
        int $offset = 0
    ): array {
        $sql = "SELECT
                    l.*,
                    c.nombre AS categoria,
                    l.existencias_disponibles AS existencias,
                    l.ubicacion_fisica AS ubicacion
                FROM libros l
                INNER JOIN categorias c
                    ON c.id_categoria = l.id_categoria
                WHERE l.estado = 1";

        $parametros = [];

        if ($buscar !== '') {
            $sql .= " AND (
                        l.titulo LIKE :buscar_titulo
                        OR l.autor LIKE :buscar_autor
                        OR EXISTS (
                            SELECT 1
                            FROM libros_temas lt
                            INNER JOIN temas t
                                ON t.id_tema = lt.id_tema
                            WHERE lt.id_libro = l.id_libro
                              AND t.nombre LIKE :buscar_tema
                        )
                    )";

            $termino = '%' . $buscar . '%';
            $parametros[':buscar_titulo'] = $termino;
            $parametros[':buscar_autor'] = $termino;
            $parametros[':buscar_tema'] = $termino;
        }

        if ($categoria !== '') {
            $sql .= " AND c.nombre = :categoria";
            $parametros[':categoria'] = $categoria;
        }

        $sql .= " ORDER BY l.titulo ASC " . Sql::limitOffset($this->db);

        $stmt = $this->db->prepare($sql);

        foreach ($parametros as $clave => $valor) {
            $stmt->bindValue($clave, $valor, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contar(
        string $buscar = '',
        string $categoria = ''
    ): int {
        $sql = "SELECT COUNT(DISTINCT l.id_libro) AS total
                FROM libros l
                INNER JOIN categorias c
                    ON c.id_categoria = l.id_categoria
                WHERE l.estado = 1";

        $parametros = [];

        if ($buscar !== '') {
            $sql .= " AND (
                        l.titulo LIKE :buscar_titulo
                        OR l.autor LIKE :buscar_autor
                        OR EXISTS (
                            SELECT 1
                            FROM libros_temas lt
                            INNER JOIN temas t
                                ON t.id_tema = lt.id_tema
                            WHERE lt.id_libro = l.id_libro
                              AND t.nombre LIKE :buscar_tema
                        )
                    )";

            $termino = '%' . $buscar . '%';
            $parametros[':buscar_titulo'] = $termino;
            $parametros[':buscar_autor'] = $termino;
            $parametros[':buscar_tema'] = $termino;
        }

        if ($categoria !== '') {
            $sql .= " AND c.nombre = :categoria";
            $parametros[':categoria'] = $categoria;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }

    public function contarTotal(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM libros
             WHERE estado = 1"
        );

        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }

    public function contarDisponibles(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM libros
             WHERE estado = 1
               AND existencias_disponibles > 0"
        );

        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }

    public function recientes(int $limit = 4): array
    {
        $top = Sql::top($this->db, $limit);

        $sql = "SELECT {$top['antes']}
                    l.id_libro,
                    l.titulo,
                    l.autor,
                    l.existencias_disponibles AS existencias,
                    l.thumbnail_ruta,
                    c.nombre AS categoria
                FROM libros l
                INNER JOIN categorias c
                    ON c.id_categoria = l.id_categoria
                WHERE l.estado = 1
                ORDER BY l.id_libro DESC
                {$top['despues']}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function parametrosLibro(array $data): array
    {
        return [
            ':titulo' => $data['titulo'],
            ':autor' => $data['autor'],
            ':isbn' => $data['isbn'] !== '' ? $data['isbn'] : null,
            ':editorial' => $data['editorial'] !== ''
                ? $data['editorial']
                : null,
            ':anio_publicacion' => $data['anio_publicacion'] ?: null,
            ':descripcion' => $data['descripcion'] !== ''
                ? $data['descripcion']
                : null,
            ':costo' => $data['costo'],
            ':existencias_totales' => $data['existencias_totales'],
            ':existencias_disponibles' => $data['existencias_disponibles'],
            ':imagen_nombre' => $data['imagen_nombre'],
            ':imagen_ruta' => $data['imagen_ruta'],
            ':thumbnail_nombre' => $data['thumbnail_nombre'],
            ':thumbnail_ruta' => $data['thumbnail_ruta'],
            ':ubicacion_fisica' => $data['ubicacion_fisica'] !== ''
                ? $data['ubicacion_fisica']
                : null,
            ':id_categoria' => $data['id_categoria'],
        ];
    }

    private function sincronizarTemas(
        int $idLibro,
        array $idsTemas
    ): void {
        $stmtEliminar = $this->db->prepare(
            "DELETE FROM libros_temas
             WHERE id_libro = :id_libro"
        );

        $stmtEliminar->execute([
            ':id_libro' => $idLibro,
        ]);

        if ($idsTemas === []) {
            return;
        }

        $stmtInsertar = $this->db->prepare(
            "INSERT INTO libros_temas (
                id_libro,
                id_tema
             )
             VALUES (
                :id_libro,
                :id_tema
             )"
        );

        foreach ($idsTemas as $idTema) {
            $stmtInsertar->execute([
                ':id_libro' => $idLibro,
                ':id_tema' => (int) $idTema,
            ]);
        }
    }

    private function subconsultaTemas(): string
    {
        if (Sql::esSqlServer($this->db)) {
            return "SELECT STRING_AGG(t.nombre, ', ')
                    FROM libros_temas lt
                    INNER JOIN temas t
                        ON t.id_tema = lt.id_tema
                    WHERE lt.id_libro = l.id_libro";
        }

        return "SELECT GROUP_CONCAT(t.nombre SEPARATOR ', ')
                FROM libros_temas lt
                INNER JOIN temas t
                    ON t.id_tema = lt.id_tema
                WHERE lt.id_libro = l.id_libro";
    }
}
