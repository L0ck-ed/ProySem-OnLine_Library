<?php

namespace App\Models;

use App\Core\Model;

class Reserva extends Model
{
    public function listarActivasPorEstudiante(int $idEstudiante): array
    {
        $sql = "SELECT r.id_reserva, l.titulo, r.fecha_reserva, r.estado
                FROM reservas r
                JOIN libros l ON r.id_libro = l.id_libro
                WHERE r.id_estudiante = :id_estudiante AND r.estado = 'Prestado'
                ORDER BY r.fecha_reserva DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_estudiante' => $idEstudiante]);
        return $stmt->fetchAll();
    }

    public function listarHistorialPorEstudiante(int $idEstudiante): array
    {
        $sql = "SELECT r.id_reserva, l.titulo, r.fecha_reserva, r.fecha_devolucion, r.estado
                FROM reservas r
                JOIN libros l ON r.id_libro = l.id_libro
                WHERE r.id_estudiante = :id_estudiante AND r.estado IN ('Devuelto', 'Cancelado')
                ORDER BY r.fecha_reserva DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_estudiante' => $idEstudiante]);
        return $stmt->fetchAll();
    }

    public function devolver(int $idReserva, int $idEstudiante): bool
    {
        // Primero verificar que la reserva pertenezca al estudiante y esté en estado 'Prestado'
        $sql = "SELECT id_libro FROM reservas WHERE id_reserva = :id_reserva AND id_estudiante = :id_estudiante AND estado = 'Prestado'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_reserva' => $idReserva, ':id_estudiante' => $idEstudiante]);
        $reserva = $stmt->fetch();

        if (!$reserva) {
            return false;
        }

        // Actualizar reserva
        $sqlUpdate = "UPDATE reservas SET estado = 'Devuelto', fecha_devolucion = NOW() WHERE id_reserva = :id_reserva";
        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $ok = $stmtUpdate->execute([':id_reserva' => $idReserva]);

        if ($ok) {
            // Incrementar existencias del libro
            $sqlLibro = "UPDATE libros SET existencias = existencias + 1 WHERE id_libro = :id_libro";
            $stmtLibro = $this->db->prepare($sqlLibro);
            $stmtLibro->execute([':id_libro' => $reserva['id_libro']]);
        }

        return $ok;
    }

    public function contarActivasPorEstudiante(int $idEstudiante): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM reservas
                WHERE id_estudiante = :id_estudiante AND estado = 'Prestado'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_estudiante' => $idEstudiante]);
        $resultado = $stmt->fetch();
        return (int) ($resultado['total'] ?? 0);
    }

    public function contarHistorialPorEstudiante(int $idEstudiante): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM reservas
                WHERE id_estudiante = :id_estudiante AND estado IN ('Devuelto', 'Cancelado')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_estudiante' => $idEstudiante]);
        $resultado = $stmt->fetch();
        return (int) ($resultado['total'] ?? 0);
    }
}