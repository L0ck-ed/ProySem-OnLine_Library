<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Sql;
use PDO;
use Throwable;

class Reserva extends Model
{
    /**
     * Crea una reserva/préstamo de un libro para un estudiante.
     *
     * Usa una transacción con una actualización atómica de existencias
     * (UPDATE ... WHERE existencias > 0) para evitar que dos estudiantes
     * reserven la última unidad al mismo tiempo (condición de carrera),
     * sin depender de bloqueos específicos de un motor de base de datos
     * (funciona igual en MySQL y SQL Server).
     *
     * @return array{ok: bool, mensaje: string}
     */
    public function crear(int $idEstudiante, int $idLibro): array
    {
        try {
            $this->db->beginTransaction();

            // Ya tiene este mismo libro prestado activamente?
            $sqlExiste = "SELECT id_reserva
                          FROM reservas
                          WHERE id_estudiante = :id_estudiante
                            AND id_libro = :id_libro
                            AND estado = 'Prestado'";

            $stmtExiste = $this->db->prepare($sqlExiste);
            $stmtExiste->execute([
                ':id_estudiante' => $idEstudiante,
                ':id_libro' => $idLibro
            ]);

            if ($stmtExiste->fetch()) {
                $this->db->rollBack();
                return ['ok' => false, 'mensaje' => 'Ya tienes un préstamo activo de este mismo libro.'];
            }

            // Descuenta existencias solo si realmente hay stock (operación atómica)
            $sqlLibro = "UPDATE libros
                         SET existencias = existencias - 1
                         WHERE id_libro = :id_libro
                           AND existencias > 0";

            $stmtLibro = $this->db->prepare($sqlLibro);
            $stmtLibro->execute([':id_libro' => $idLibro]);

            if ($stmtLibro->rowCount() === 0) {
                $this->db->rollBack();
                return ['ok' => false, 'mensaje' => 'No hay unidades disponibles de este libro en este momento.'];
            }

            $sqlReserva = "INSERT INTO reservas (id_estudiante, id_libro, fecha_reserva, estado)
                           VALUES (:id_estudiante, :id_libro, CURRENT_TIMESTAMP, 'Prestado')";

            $stmtReserva = $this->db->prepare($sqlReserva);
            $stmtReserva->execute([
                ':id_estudiante' => $idEstudiante,
                ':id_libro' => $idLibro
            ]);

            $this->db->commit();

            return ['ok' => true, 'mensaje' => '¡Reserva realizada con éxito! Puedes recogerlo en la biblioteca.'];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log('Error al crear reserva: ' . $e->getMessage());

            return ['ok' => false, 'mensaje' => 'Ocurrió un error al procesar la reserva. Intenta de nuevo.'];
        }
    }

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
        try {
            $this->db->beginTransaction();

            $sql = "SELECT id_libro FROM reservas
                    WHERE id_reserva = :id_reserva
                      AND id_estudiante = :id_estudiante
                      AND estado = 'Prestado'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_reserva' => $idReserva, ':id_estudiante' => $idEstudiante]);
            $reserva = $stmt->fetch();

            if (!$reserva) {
                $this->db->rollBack();
                return false;
            }

            $sqlUpdate = "UPDATE reservas
                          SET estado = 'Devuelto', fecha_devolucion = CURRENT_TIMESTAMP
                          WHERE id_reserva = :id_reserva";

            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute([':id_reserva' => $idReserva]);

            $sqlLibro = "UPDATE libros SET existencias = existencias + 1 WHERE id_libro = :id_libro";
            $stmtLibro = $this->db->prepare($sqlLibro);
            $stmtLibro->execute([':id_libro' => $reserva['id_libro']]);

            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log('Error al devolver reserva: ' . $e->getMessage());

            return false;
        }
    }

    public function contarActivasPorEstudiante(int $idEstudiante): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM reservas
                WHERE id_estudiante = :id_estudiante AND estado = 'Prestado'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_estudiante' => $idEstudiante]);

        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function contarHistorialPorEstudiante(int $idEstudiante): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM reservas
                WHERE id_estudiante = :id_estudiante AND estado IN ('Devuelto', 'Cancelado')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_estudiante' => $idEstudiante]);

        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function librosMasUsados(string $fechaInicio, string $fechaFin, int $limite = 10): array
    {
        $topInfo = Sql::top($this->db, $limite);

        $sql = "SELECT {$topInfo['antes']}
                    l.id_libro,
                    l.titulo,
                    l.autor,
                    COUNT(r.id_reserva) AS total_prestamos
                FROM reservas r
                JOIN libros l ON r.id_libro = l.id_libro
                WHERE r.fecha_reserva BETWEEN :inicio AND :fin
                AND r.estado IN ('Prestado', 'Devuelto')
                GROUP BY l.id_libro, l.titulo, l.autor
                ORDER BY total_prestamos DESC
                {$topInfo['despues']}";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':inicio', $fechaInicio);
        $stmt->bindValue(':fin', $fechaFin);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}