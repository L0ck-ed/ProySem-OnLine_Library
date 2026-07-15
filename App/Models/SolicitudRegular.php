<?php

namespace App\Models;

use App\Core\Model;

class SolicitudRegular extends Model
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

    public function crearPorUsuario(
        int $idUsuario,
        string $tituloLibro,
        string $area,
        ?string $descripcion,
    ): bool {
        if ($idUsuario <= 0 || trim($tituloLibro) === '') {
            return false;
        }

        $motivo = trim((string) $descripcion);

        if ($motivo === '') {
            $motivo = 'Solicitud realizada desde el portal regular.';
        }

        $sql = "INSERT INTO solicitudes_libros (
                    id_usuario,
                    titulo_libro,
                    autor,
                    materia,
                    motivo_interes,
                    estado
                )
                VALUES (
                    :id_usuario,
                    :titulo_libro,
                    NULL,
                    :materia,
                    :motivo_interes,
                    'Pendiente'
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':titulo_libro' => $tituloLibro,
            ':materia' => $area,
            ':motivo_interes' => $motivo,
        ]);
    }

    public function listarPorUsuario(int $idUsuario): array
    {
        $sql = "SELECT
                    id_solicitud,
                    titulo_libro AS titulo,
                    autor,
                    materia AS area,
                    motivo_interes AS descripcion,
                    fecha_solicitud AS fecha,
                    estado,
                    respuesta,
                    fecha_respuesta
                FROM solicitudes_libros
                WHERE id_usuario = :id_usuario
                ORDER BY
                    fecha_solicitud DESC,
                    id_solicitud DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $idUsuario,
        ]);

        return $stmt->fetchAll();
    }

    public function contarPorUsuario(int $idUsuario): int
    {
        $sql = "SELECT COUNT(*)
                FROM solicitudes_libros
                WHERE id_usuario = :id_usuario";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $idUsuario,
        ]);

        return (int) $stmt->fetchColumn();
    }
}
