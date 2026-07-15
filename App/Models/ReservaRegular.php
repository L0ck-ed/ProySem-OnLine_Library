<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Sql;

class ReservaRegular extends Model
{
    private const ESTADOS_ACTIVOS = ['Pendiente', 'Reservado', 'Prestado', 'Vencido'];

    /**
     * @return array{ok: bool, mensaje: string}
     */
    public function crearPorUsuario(int $idUsuario, int $idLibro, int $dias = 7): array
    {
        if ($idUsuario <= 0 || $idLibro <= 0 || $dias <= 0) {
            return [
                'ok' => false,
                'mensaje' => 'Los datos de la reserva no son válidos.',
            ];
        }

        try {
            $this->db->beginTransaction();

            if (!$this->usuarioEsRegularActivo($idUsuario)) {
                $this->db->rollBack();

                return [
                    'ok' => false,
                    'mensaje' => 'La cuenta regular no está disponible.',
                ];
            }

            $marcadores = implode(', ', array_fill(0, count(self::ESTADOS_ACTIVOS), '?'));

            $sqlDuplicada = "SELECT id_reserva
                             FROM reservas
                             WHERE id_usuario = ?
                               AND id_libro = ?
                               AND estado IN ({$marcadores})";

            $stmtDuplicada = $this->db->prepare($sqlDuplicada);
            $stmtDuplicada->execute([
                $idUsuario,
                $idLibro,
                ...self::ESTADOS_ACTIVOS,
            ]);

            if ($stmtDuplicada->fetch()) {
                $this->db->rollBack();

                return [
                    'ok' => false,
                    'mensaje' => 'Ya tienes una reserva activa de este libro.',
                ];
            }

            $sqlLibro = "UPDATE libros
                         SET
                            existencias_disponibles = existencias_disponibles - 1,
                            fecha_actualizacion = CURRENT_TIMESTAMP
                         WHERE id_libro = :id_libro
                           AND estado = 1
                           AND existencias_disponibles > 0";

            $stmtLibro = $this->db->prepare($sqlLibro);
            $stmtLibro->execute([
                ':id_libro' => $idLibro,
            ]);

            if ($stmtLibro->rowCount() === 0) {
                $this->db->rollBack();

                return [
                    'ok' => false,
                    'mensaje' => 'No hay unidades disponibles de este libro.',
                ];
            }

            $fechaActual = new \DateTimeImmutable('today');
            $fechaVencimiento = $fechaActual
                ->modify('+' . $dias . ' days')
                ->format('Y-m-d');

            $sqlReserva = "INSERT INTO reservas (
                                id_usuario,
                                id_libro,
                                fecha_reserva,
                                fecha_vencimiento,
                                cantidad,
                                estado
                           )
                           VALUES (
                                :id_usuario,
                                :id_libro,
                                CURRENT_TIMESTAMP,
                                :fecha_vencimiento,
                                1,
                                'Reservado'
                           )";

            $stmtReserva = $this->db->prepare($sqlReserva);
            $stmtReserva->execute([
                ':id_usuario' => $idUsuario,
                ':id_libro' => $idLibro,
                ':fecha_vencimiento' => $fechaVencimiento,
            ]);

            $this->db->commit();

            return [
                'ok' => true,
                'mensaje' => 'Reserva realizada correctamente. Puedes recoger el libro en la biblioteca.',
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log('Error al crear reserva regular: ' . $e->getMessage());

            return [
                'ok' => false,
                'mensaje' => 'No se pudo procesar la reserva.',
            ];
        }
    }

    public function listarActivasPorUsuario(int $idUsuario): array
    {
        $sql = "SELECT
                    r.id_reserva,
                    r.id_libro,
                    l.titulo,
                    l.autor,
                    r.fecha_reserva,
                    r.fecha_vencimiento,
                    r.estado
                FROM reservas r
                INNER JOIN libros l
                    ON l.id_libro = r.id_libro
                WHERE r.id_usuario = :id_usuario
                  AND r.estado IN (
                    'Pendiente',
                    'Reservado',
                    'Prestado',
                    'Vencido'
                  )
                ORDER BY r.fecha_reserva DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $idUsuario,
        ]);

        return $stmt->fetchAll();
    }

    public function listarHistorialPorUsuario(int $idUsuario): array
    {
        $sql = "SELECT
                    r.id_reserva,
                    r.id_libro,
                    l.titulo,
                    l.autor,
                    r.fecha_reserva,
                    r.fecha_devolucion_real AS fecha_devolucion,
                    r.estado
                FROM reservas r
                INNER JOIN libros l
                    ON l.id_libro = r.id_libro
                WHERE r.id_usuario = :id_usuario
                  AND r.estado IN ('Devuelto', 'Cancelado')
                ORDER BY r.fecha_reserva DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $idUsuario,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Cancela una reserva todavía no prestada o devuelve un préstamo entregado.
     *
     * @return array{ok: bool, mensaje: string}
     */
    public function cerrarPorUsuario(int $idReserva, int $idUsuario): array
    {
        try {
            $this->db->beginTransaction();

            $sql = "SELECT
                        id_libro,
                        estado
                    FROM reservas
                    WHERE id_reserva = :id_reserva
                      AND id_usuario = :id_usuario
                      AND estado IN (
                        'Pendiente',
                        'Reservado',
                        'Prestado',
                        'Vencido'
                      )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_reserva' => $idReserva,
                ':id_usuario' => $idUsuario,
            ]);

            $reserva = $stmt->fetch();

            if (!$reserva) {
                $this->db->rollBack();

                return [
                    'ok' => false,
                    'mensaje' => 'La reserva no existe o ya fue cerrada.',
                ];
            }

            $estadoActual = (string) $reserva['estado'];
            $nuevoEstado = in_array($estadoActual, ['Pendiente', 'Reservado'], true)
                ? 'Cancelado'
                : 'Devuelto';

            $fechaDevolucion = $nuevoEstado === 'Devuelto'
                ? 'CURRENT_TIMESTAMP'
                : 'NULL';

            $sqlActualizar = "UPDATE reservas
                              SET
                                estado = :estado,
                                fecha_devolucion_real = {$fechaDevolucion},
                                fecha_actualizacion = CURRENT_TIMESTAMP
                              WHERE id_reserva = :id_reserva";

            $stmtActualizar = $this->db->prepare($sqlActualizar);
            $stmtActualizar->execute([
                ':estado' => $nuevoEstado,
                ':id_reserva' => $idReserva,
            ]);

            $this->devolverExistencia((int) $reserva['id_libro']);
            $this->db->commit();

            return [
                'ok' => true,
                'mensaje' => $nuevoEstado === 'Cancelado'
                    ? 'Reserva cancelada correctamente.'
                    : 'Préstamo devuelto correctamente.',
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log('Error al cerrar reserva regular: ' . $e->getMessage());

            return [
                'ok' => false,
                'mensaje' => 'No se pudo procesar la operación.',
            ];
        }
    }

    public function contarActivasPorUsuario(int $idUsuario): int
    {
        return $this->contarPorUsuarioYEstados($idUsuario, self::ESTADOS_ACTIVOS);
    }

    public function contarHistorialPorUsuario(int $idUsuario): int
    {
        return $this->contarPorUsuarioYEstados($idUsuario, ['Devuelto', 'Cancelado']);
    }

    public function librosMasUsados(
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        int $limite = 5,
    ): array {
        $limite = max(1, min($limite, 50));
        $topInfo = Sql::top($this->db, $limite);

        $condicionesReserva = [
            'r.id_libro = l.id_libro',
            "r.estado <> 'Cancelado'",
        ];

        $parametros = [];

        if ($fechaInicio !== null && trim($fechaInicio) !== '') {
            $condicionesReserva[] = 'r.fecha_reserva >= :fecha_inicio';
            $parametros[':fecha_inicio'] = trim($fechaInicio) . ' 00:00:00';
        }

        if ($fechaFin !== null && trim($fechaFin) !== '') {
            $fechaFinObjeto = new \DateTimeImmutable(trim($fechaFin));
            $parametros[':fecha_fin_exclusiva'] = $fechaFinObjeto
                ->modify('+1 day')
                ->format('Y-m-d 00:00:00');

            $condicionesReserva[] = 'r.fecha_reserva < :fecha_fin_exclusiva';
        }

        $unionReservas = implode("\n AND ", $condicionesReserva);

        $sql = "SELECT {$topInfo['antes']}
                    l.id_libro,
                    l.titulo,
                    l.autor,
                    l.isbn,
                    l.imagen_ruta AS ruta_portada,
                    l.thumbnail_ruta AS ruta_miniatura,
                    l.existencias_disponibles,
                    l.existencias_totales,
                    COALESCE(c.nombre, 'Sin categoría') AS categoria,
                    COUNT(r.id_reserva) AS total_reservas,
                    COUNT(r.id_reserva) AS total_prestamos,
                    COUNT(r.id_reserva) AS total_usos
                FROM libros l
                LEFT JOIN reservas r
                    ON {$unionReservas}
                LEFT JOIN categorias c
                    ON c.id_categoria = l.id_categoria
                WHERE l.estado = 1
                GROUP BY
                    l.id_libro,
                    l.titulo,
                    l.autor,
                    l.isbn,
                    l.imagen_ruta,
                    l.thumbnail_ruta,
                    l.existencias_disponibles,
                    l.existencias_totales,
                    c.nombre
                ORDER BY
                    COUNT(r.id_reserva) DESC,
                    l.titulo ASC
                {$topInfo['despues']}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    private function usuarioEsRegularActivo(int $idUsuario): bool
    {
        $sql = "SELECT COUNT(*)
                FROM usuarios u
                WHERE u.id_usuario = :id_usuario
                  AND u.estado = 1
                  AND u.bloqueado = 0
                  AND (
                    (
                        EXISTS (
                            SELECT 1
                            FROM estudiantes e
                            WHERE e.id_usuario = u.id_usuario
                              AND e.estado = 1
                        )
                        AND EXISTS (
                            SELECT 1
                            FROM usuarios_roles ur
                            INNER JOIN roles r
                                ON r.id_rol = ur.id_rol
                            WHERE ur.id_usuario = u.id_usuario
                              AND r.nombre = 'Estudiante'
                              AND r.estado = 1
                        )
                    )
                    OR
                    (
                        EXISTS (
                            SELECT 1
                            FROM profesores p
                            WHERE p.id_usuario = u.id_usuario
                              AND p.estado = 1
                        )
                        AND EXISTS (
                            SELECT 1
                            FROM usuarios_roles ur
                            INNER JOIN roles r
                                ON r.id_rol = ur.id_rol
                            WHERE ur.id_usuario = u.id_usuario
                              AND r.nombre = 'Profesor'
                              AND r.estado = 1
                        )
                    )
                  )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $idUsuario,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function devolverExistencia(int $idLibro): void
    {
        $sql = "UPDATE libros
                SET
                    existencias_disponibles = CASE
                        WHEN existencias_disponibles < existencias_totales
                            THEN existencias_disponibles + 1
                        ELSE existencias_disponibles
                    END,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_libro = :id_libro";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_libro' => $idLibro,
        ]);
    }

    private function contarPorUsuarioYEstados(int $idUsuario, array $estados): int
    {
        if ($idUsuario <= 0 || empty($estados)) {
            return 0;
        }

        $marcadores = implode(', ', array_fill(0, count($estados), '?'));

        $sql = "SELECT COUNT(*)
                FROM reservas
                WHERE id_usuario = ?
                  AND estado IN ({$marcadores})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $idUsuario,
            ...$estados,
        ]);

        return (int) $stmt->fetchColumn();
    }
}
