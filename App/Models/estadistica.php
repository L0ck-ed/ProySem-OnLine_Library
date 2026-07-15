<?php

namespace App\Models;

use App\Core\Model;

class Estadistica extends Model
{
    /**
     * Obtiene movimientos de demanda del libro dentro del período.
     *
     * Incluye la estructura académica del usuario para analizar demanda por
     * facultad, carrera (estudiantes) y departamento (docentes).
     *
     * @return array<int, array<string, mixed>>
     */
    public function obtenerMovimientosPorPeriodo(string $fechaInicio, string $fechaFinExclusiva): array
    {
        $sql = "SELECT
                    r.id_reserva,
                    r.fecha_reserva,
                    r.cantidad,
                    r.estado,
                    l.id_libro,
                    l.titulo,
                    l.autor,
                    cat.nombre AS categoria,
                    CASE
                        WHEN e.id_estudiante IS NOT NULL THEN 'Estudiante'
                        WHEN p.id_profesor IS NOT NULL THEN 'Docente'
                        ELSE 'Otro'
                    END AS tipo_usuario,
                    e.id_carrera AS id_carrera,
                    car.nombre AS carrera,
                    car.id_departamento AS id_departamento_carrera,
                    dc.nombre AS departamento_carrera,
                    p.id_departamento AS id_departamento_docente,
                    dp.nombre AS departamento_docente,
                    CASE
                        WHEN e.id_estudiante IS NOT NULL THEN COALESCE(fc.id_facultad, dc.id_facultad)
                        WHEN p.id_profesor IS NOT NULL THEN fp.id_facultad
                        ELSE NULL
                    END AS id_facultad,
                    CASE
                        WHEN e.id_estudiante IS NOT NULL THEN COALESCE(fc.nombre, fdc.nombre)
                        WHEN p.id_profesor IS NOT NULL THEN fp.nombre
                        ELSE NULL
                    END AS facultad
                FROM reservas r
                INNER JOIN libros l
                    ON l.id_libro = r.id_libro
                INNER JOIN categorias cat
                    ON cat.id_categoria = l.id_categoria
                LEFT JOIN estudiantes e
                    ON e.id_usuario = r.id_usuario
                LEFT JOIN carreras car
                    ON car.id_carrera = e.id_carrera
                LEFT JOIN facultades fc
                    ON fc.id_facultad = car.id_facultad
                LEFT JOIN departamentos dc
                    ON dc.id_departamento = car.id_departamento
                LEFT JOIN facultades fdc
                    ON fdc.id_facultad = dc.id_facultad
                LEFT JOIN profesores p
                    ON p.id_usuario = r.id_usuario
                LEFT JOIN departamentos dp
                    ON dp.id_departamento = p.id_departamento
                LEFT JOIN facultades fp
                    ON fp.id_facultad = dp.id_facultad
                WHERE r.fecha_reserva >= :fecha_inicio
                  AND r.fecha_reserva < :fecha_fin_exclusiva
                  AND r.estado IN ('Pendiente', 'Reservado', 'Prestado', 'Devuelto', 'Vencido')
                  AND (
                        e.id_estudiante IS NOT NULL
                        OR p.id_profesor IS NOT NULL
                  )
                ORDER BY r.fecha_reserva ASC, r.id_reserva ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fecha_inicio' => $fechaInicio . ' 00:00:00',
            ':fecha_fin_exclusiva' => $fechaFinExclusiva . ' 00:00:00',
        ]);

        return $stmt->fetchAll();
    }
}
