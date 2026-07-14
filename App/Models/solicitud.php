<?php

namespace App\Models;

use App\Core\Model;

class Solicitud extends Model
{
    private const AREAS_VALIDAS = [
        'Matemáticas',
        'Ciencias',
        'Tecnologías',
        'Deporte',
        'Salud',
        'Revistas Científicas',
    ];

    public static function areasValidas(): array
    {
        return self::AREAS_VALIDAS;
    }

    public function crear(int $idEstudiante, string $tituloLibro, string $area, ?string $descripcion): bool
    {
        // Eliminado "dbo." para MySQL
        $sql = "INSERT INTO solicitudes (id_estudiante, titulo_libro, area, descripcion)
                VALUES (:id_estudiante, :titulo_libro, :area, :descripcion)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_estudiante' => $idEstudiante,
            ':titulo_libro' => $tituloLibro,
            ':area' => $area,
            ':descripcion' => $descripcion !== '' ? $descripcion : null,
        ]);
    }

    public function listarPorEstudiante(int $idEstudiante): array
    {
        // Adaptado a MySQL: DATE_FORMAT en lugar de CONVERT, eliminado dbo.
        $sql = "SELECT
                    titulo_libro AS titulo,
                    area,
                    DATE_FORMAT(fecha, '%Y-%m-%d') AS fecha,
                    estado
                FROM solicitudes
                WHERE id_estudiante = :id_estudiante
                ORDER BY fecha DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_estudiante' => $idEstudiante]);

        return $stmt->fetchAll();
    }

    public function contarPorEstudiante(int $idEstudiante): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM solicitudes
                WHERE id_estudiante = :id_estudiante";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_estudiante' => $idEstudiante]);
        $resultado = $stmt->fetch();
        return (int) ($resultado['total'] ?? 0);
    }
}

