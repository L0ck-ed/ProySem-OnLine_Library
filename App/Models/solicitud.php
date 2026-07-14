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
        $sql = "SELECT
                    titulo_libro AS titulo,
                    area,
                    fecha,
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

        return (int)($resultado['total'] ?? 0);
    }
}