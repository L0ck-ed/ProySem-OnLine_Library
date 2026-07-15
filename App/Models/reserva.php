<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Sql;
use PDO;

class Reserva extends Model
{
    private const ESTADOS_ACTIVOS = ['Pendiente', 'Reservado', 'Prestado', 'Vencido'];

    public function listarAdmin(array $filtros, int $limit = 10, int $offset = 0): array
    {
        [$where, $parametros] = $this->construirFiltros($filtros);
        $dias = $this->expresionDiasReservado();

        $sql =
            "SELECT
                    r.id_reserva,
                    r.fecha_reserva,
                    r.fecha_vencimiento,
                    r.fecha_devolucion_real,
                    r.cantidad,
                    r.estado,
                    r.observacion,
                    {$dias} AS dias_reservado,
                    u.id_usuario,
                    u.nombre AS nombre_usuario,
                    u.usuario,
                    CASE
                        WHEN e.id_estudiante IS NOT NULL THEN 'Estudiante'
                        WHEN p.id_profesor IS NOT NULL THEN 'Docente'
                        WHEN a.id_administrativo IS NOT NULL THEN 'Administrativo'
                        ELSE 'Otro'
                    END AS tipo_usuario,
                    COALESCE(
                        e.primer_nombre,
                        p.primer_nombre,
                        a.primer_nombre,
                        u.nombre
                    ) AS primer_nombre_persona,
                    COALESCE(
                        e.primer_apellido,
                        p.primer_apellido,
                        a.primer_apellido,
                        ''
                    ) AS primer_apellido_persona,
                    l.id_libro,
                    l.titulo,
                    l.autor,
                    c.nombre AS categoria
                FROM reservas r
                INNER JOIN usuarios u
                    ON u.id_usuario = r.id_usuario
                INNER JOIN libros l
                    ON l.id_libro = r.id_libro
                INNER JOIN categorias c
                    ON c.id_categoria = l.id_categoria
                LEFT JOIN estudiantes e
                    ON e.id_usuario = u.id_usuario
                LEFT JOIN profesores p
                    ON p.id_usuario = u.id_usuario
                LEFT JOIN administrativos a
                    ON a.id_usuario = u.id_usuario
                {$where}
                ORDER BY r.fecha_reserva DESC, r.id_reserva DESC
                " . Sql::limitOffset($this->db);

        $stmt = $this->db->prepare($sql);

        foreach ($parametros as $clave => $valor) {
            $stmt->bindValue($clave, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contarAdmin(array $filtros): int
    {
        [$where, $parametros] = $this->construirFiltros($filtros);

        $sql = "SELECT COUNT(DISTINCT r.id_reserva) AS total
                FROM reservas r
                INNER JOIN usuarios u
                    ON u.id_usuario = r.id_usuario
                INNER JOIN libros l
                    ON l.id_libro = r.id_libro
                INNER JOIN categorias c
                    ON c.id_categoria = l.id_categoria
                LEFT JOIN estudiantes e
                    ON e.id_usuario = u.id_usuario
                LEFT JOIN profesores p
                    ON p.id_usuario = u.id_usuario
                LEFT JOIN administrativos a
                    ON a.id_usuario = u.id_usuario
                {$where}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }

    public function obtenerReporte(array $filtros): array
    {
        [$where, $parametros] = $this->construirFiltros($filtros);
        $dias = $this->expresionDiasReservado();

        $sql = "SELECT
                    r.id_reserva,
                    r.fecha_reserva,
                    r.fecha_vencimiento,
                    r.fecha_devolucion_real,
                    r.estado,
                    r.cantidad,
                    {$dias} AS dias_reservado,
                    u.usuario,
                    CASE
                        WHEN e.id_estudiante IS NOT NULL THEN 'Estudiante'
                        WHEN p.id_profesor IS NOT NULL THEN 'Docente'
                        WHEN a.id_administrativo IS NOT NULL THEN 'Administrativo'
                        ELSE 'Otro'
                    END AS tipo_usuario,
                    COALESCE(
                        e.primer_nombre,
                        p.primer_nombre,
                        a.primer_nombre,
                        u.nombre
                    ) AS primer_nombre_persona,
                    COALESCE(
                        e.primer_apellido,
                        p.primer_apellido,
                        a.primer_apellido,
                        ''
                    ) AS primer_apellido_persona,
                    l.titulo,
                    l.autor,
                    c.nombre AS categoria
                FROM reservas r
                INNER JOIN usuarios u
                    ON u.id_usuario = r.id_usuario
                INNER JOIN libros l
                    ON l.id_libro = r.id_libro
                INNER JOIN categorias c
                    ON c.id_categoria = l.id_categoria
                LEFT JOIN estudiantes e
                    ON e.id_usuario = u.id_usuario
                LEFT JOIN profesores p
                    ON p.id_usuario = u.id_usuario
                LEFT JOIN administrativos a
                    ON a.id_usuario = u.id_usuario
                {$where}
                ORDER BY r.fecha_reserva DESC, r.id_reserva DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idReserva): array|false
    {
        $sql = "SELECT
                    r.*,
                    u.nombre AS nombre_usuario,
                    u.usuario,
                    l.titulo,
                    l.autor
                FROM reservas r
                INNER JOIN usuarios u
                    ON u.id_usuario = r.id_usuario
                INNER JOIN libros l
                    ON l.id_libro = r.id_libro
                WHERE r.id_reserva = :id_reserva";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_reserva' => $idReserva,
        ]);

        return $stmt->fetch();
    }

    public function marcarVencidas(): void
    {
        $sql = "UPDATE reservas
                SET
                    estado = 'Vencido',
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE estado IN ('Reservado', 'Prestado')
                  AND fecha_vencimiento < CURRENT_TIMESTAMP";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    public function aprobar(int $idReserva): bool
    {
        $sql = "UPDATE reservas
                SET
                    estado = 'Reservado',
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_reserva = :id_reserva
                  AND estado = 'Pendiente'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_reserva' => $idReserva,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function prestar(int $idReserva): bool
    {
        $sql = "UPDATE reservas
                SET
                    estado = 'Prestado',
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_reserva = :id_reserva
                  AND estado IN ('Pendiente', 'Reservado')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_reserva' => $idReserva,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function cancelar(int $idReserva): bool
    {
        return $this->cerrarReserva($idReserva, 'Cancelado', ['Pendiente', 'Reservado']);
    }

    public function devolverAdmin(int $idReserva): bool
    {
        return $this->cerrarReserva($idReserva, 'Devuelto', ['Reservado', 'Prestado', 'Vencido']);
    }

    /**
     * Método conservado para PortalController.
     * Recibe id_estudiante, obtiene su id_usuario y crea la reserva.
     *
     * @return array{ok: bool, mensaje: string}
     */
    public function crear(int $idEstudiante, int $idLibro, int $dias = 7): array
    {
        if ($dias <= 0) {
            return [
                'ok' => false,
                'mensaje' => 'La cantidad de días debe ser mayor que cero.',
            ];
        }

        try {
            $this->db->beginTransaction();

            $stmtUsuario = $this->db->prepare(
                "SELECT e.id_usuario
                 FROM estudiantes e
                 INNER JOIN usuarios u
                    ON u.id_usuario = e.id_usuario
                 WHERE e.id_estudiante = :id_estudiante
                   AND e.estado = 1
                   AND u.estado = 1
                   AND u.bloqueado = 0",
            );

            $stmtUsuario->execute([
                ':id_estudiante' => $idEstudiante,
            ]);

            $idUsuario = (int) $stmtUsuario->fetchColumn();

            if ($idUsuario <= 0) {
                $this->db->rollBack();

                return [
                    'ok' => false,
                    'mensaje' => 'La cuenta del estudiante no está disponible.',
                ];
            }

            $marcadores = implode(', ', array_fill(0, count(self::ESTADOS_ACTIVOS), '?'));

            $sqlDuplicada = "SELECT id_reserva
                             FROM reservas
                             WHERE id_usuario = ?
                               AND id_libro = ?
                               AND estado IN ({$marcadores})";

            $stmtDuplicada = $this->db->prepare($sqlDuplicada);
            $stmtDuplicada->execute([$idUsuario, $idLibro, ...self::ESTADOS_ACTIVOS]);

            if ($stmtDuplicada->fetch()) {
                $this->db->rollBack();

                return [
                    'ok' => false,
                    'mensaje' => 'Ya tienes una reserva activa de este libro.',
                ];
            }

            $stmtLibro = $this->db->prepare(
                "UPDATE libros
                 SET
                    existencias_disponibles = existencias_disponibles - 1,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                 WHERE id_libro = :id_libro
                   AND estado = 1
                   AND existencias_disponibles > 0",
            );

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

            $fechaVencimiento = $fechaActual->modify('+' . $dias . ' days')->format('Y-m-d');

            $stmtReserva = $this->db->prepare(
                "INSERT INTO reservas (
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
                 )",
            );

            $stmtReserva->execute([
                ':id_usuario' => $idUsuario,
                ':id_libro' => $idLibro,
                ':fecha_vencimiento' => $fechaVencimiento,
            ]);

            $this->db->commit();

            return [
                'ok' => true,
                'mensaje' =>
                    'Reserva realizada correctamente. Puedes recoger el libro en la biblioteca.',
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log('Error al crear reserva: ' . $e->getMessage());

            return [
                'ok' => false,
                'mensaje' => 'No se pudo procesar la reserva.',
            ];
        }
    }

    public function listarActivasPorEstudiante(int $idEstudiante): array
    {
        $sql = "SELECT
                    r.id_reserva,
                    l.titulo,
                    r.fecha_reserva,
                    r.fecha_vencimiento,
                    r.estado
                FROM reservas r
                INNER JOIN estudiantes e
                    ON e.id_usuario = r.id_usuario
                INNER JOIN libros l
                    ON l.id_libro = r.id_libro
                WHERE e.id_estudiante = :id_estudiante
                  AND r.estado IN (
                    'Pendiente',
                    'Reservado',
                    'Prestado',
                    'Vencido'
                  )
                ORDER BY r.fecha_reserva DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_estudiante' => $idEstudiante,
        ]);

        return $stmt->fetchAll();
    }

    public function listarHistorialPorEstudiante(int $idEstudiante): array
    {
        $sql = "SELECT
                    r.id_reserva,
                    l.titulo,
                    r.fecha_reserva,
                    r.fecha_devolucion_real AS fecha_devolucion,
                    r.estado
                FROM reservas r
                INNER JOIN estudiantes e
                    ON e.id_usuario = r.id_usuario
                INNER JOIN libros l
                    ON l.id_libro = r.id_libro
                WHERE e.id_estudiante = :id_estudiante
                  AND r.estado IN ('Devuelto', 'Cancelado')
                ORDER BY r.fecha_reserva DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_estudiante' => $idEstudiante,
        ]);

        return $stmt->fetchAll();
    }

    public function devolver(int $idReserva, int $idEstudiante): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "SELECT r.id_libro
                    FROM reservas r
                    INNER JOIN estudiantes e
                        ON e.id_usuario = r.id_usuario
                    WHERE r.id_reserva = :id_reserva
                      AND e.id_estudiante = :id_estudiante
                      AND r.estado IN (
                        'Reservado',
                        'Prestado',
                        'Vencido'
                      )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_reserva' => $idReserva,
                ':id_estudiante' => $idEstudiante,
            ]);

            $reserva = $stmt->fetch();

            if (!$reserva) {
                $this->db->rollBack();
                return false;
            }

            $this->actualizarReservaCerrada($idReserva, 'Devuelto');

            $this->devolverExistencia((int) $reserva['id_libro']);

            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log('Error al devolver reserva: ' . $e->getMessage());

            return false;
        }
    }

    public function contarActivasPorEstudiante(int $idEstudiante): int
    {
        return $this->contarPorEstudianteYEstados($idEstudiante, self::ESTADOS_ACTIVOS);
    }

    public function contarHistorialPorEstudiante(int $idEstudiante): int
    {
        return $this->contarPorEstudianteYEstados($idEstudiante, ['Devuelto', 'Cancelado']);
    }

    private function cerrarReserva(
        int $idReserva,
        string $nuevoEstado,
        array $estadosPermitidos,
    ): bool {
        try {
            $this->db->beginTransaction();

            $marcadores = implode(', ', array_fill(0, count($estadosPermitidos), '?'));

            $stmt = $this->db->prepare(
                "SELECT id_libro
                 FROM reservas
                 WHERE id_reserva = ?
                   AND estado IN ({$marcadores})",
            );

            $stmt->execute([$idReserva, ...$estadosPermitidos]);

            $reserva = $stmt->fetch();

            if (!$reserva) {
                $this->db->rollBack();
                return false;
            }

            $this->actualizarReservaCerrada($idReserva, $nuevoEstado);

            $this->devolverExistencia((int) $reserva['id_libro']);

            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    private function actualizarReservaCerrada(int $idReserva, string $estado): void
    {
        $fechaDevolucion = $estado === 'Devuelto' ? 'CURRENT_TIMESTAMP' : 'NULL';

        $sql = "UPDATE reservas
                SET
                    estado = :estado,
                    fecha_devolucion_real = {$fechaDevolucion},
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_reserva = :id_reserva";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':estado' => $estado,
            ':id_reserva' => $idReserva,
        ]);
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

    private function contarPorEstudianteYEstados(int $idEstudiante, array $estados): int
    {
        $marcadores = implode(', ', array_fill(0, count($estados), '?'));

        $sql = "SELECT COUNT(*) AS total
                FROM reservas r
                INNER JOIN estudiantes e
                    ON e.id_usuario = r.id_usuario
                WHERE e.id_estudiante = ?
                  AND r.estado IN ({$marcadores})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idEstudiante, ...$estados]);

        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }

    /**
     * @return array{0: string, 1: array<string, int|string>}
     */
    private function construirFiltros(array $filtros): array
    {
        $condiciones = ['1 = 1'];
        $parametros = [];

        $buscar = trim((string) ($filtros['buscar'] ?? ''));

        if ($buscar !== '') {
            $condiciones[] = "(
                l.titulo LIKE :buscar_titulo
                OR l.autor LIKE :buscar_autor
                OR u.nombre LIKE :buscar_usuario_nombre
                OR u.usuario LIKE :buscar_usuario
                OR e.primer_nombre LIKE :buscar_estudiante_nombre
                OR e.primer_apellido LIKE :buscar_estudiante_apellido
                OR p.primer_nombre LIKE :buscar_profesor_nombre
                OR p.primer_apellido LIKE :buscar_profesor_apellido
                OR a.primer_nombre LIKE :buscar_admin_nombre
                OR a.primer_apellido LIKE :buscar_admin_apellido
            )";

            $termino = '%' . $buscar . '%';
            $parametros[':buscar_titulo'] = $termino;
            $parametros[':buscar_autor'] = $termino;
            $parametros[':buscar_usuario_nombre'] = $termino;
            $parametros[':buscar_usuario'] = $termino;
            $parametros[':buscar_estudiante_nombre'] = $termino;
            $parametros[':buscar_estudiante_apellido'] = $termino;
            $parametros[':buscar_profesor_nombre'] = $termino;
            $parametros[':buscar_profesor_apellido'] = $termino;
            $parametros[':buscar_admin_nombre'] = $termino;
            $parametros[':buscar_admin_apellido'] = $termino;
        }

        $estado = trim((string) ($filtros['estado'] ?? ''));

        if ($estado !== '') {
            $condiciones[] = 'r.estado = :estado';
            $parametros[':estado'] = $estado;
        }

        $tipoUsuario = trim((string) ($filtros['tipo_usuario'] ?? ''));

        if ($tipoUsuario === 'Estudiante') {
            $condiciones[] = 'e.id_estudiante IS NOT NULL';
        } elseif ($tipoUsuario === 'Docente') {
            $condiciones[] = 'p.id_profesor IS NOT NULL';
        } elseif ($tipoUsuario === 'Administrativo') {
            $condiciones[] = 'a.id_administrativo IS NOT NULL';
        } elseif ($tipoUsuario === 'Otro') {
            $condiciones[] = "e.id_estudiante IS NULL
                              AND p.id_profesor IS NULL
                              AND a.id_administrativo IS NULL";
        }

        $fechaInicio = trim((string) ($filtros['fecha_inicio'] ?? ''));

        if ($fechaInicio !== '') {
            $condiciones[] = 'r.fecha_reserva >= :fecha_inicio';
            $parametros[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
        }

        $fechaFin = trim((string) ($filtros['fecha_fin'] ?? ''));

        if ($fechaFin !== '') {
            $condiciones[] = 'r.fecha_reserva < :fecha_fin_exclusiva';

            $fechaFinObjeto = new \DateTimeImmutable($fechaFin);

            $parametros[':fecha_fin_exclusiva'] = $fechaFinObjeto
                ->modify('+1 day')
                ->format('Y-m-d 00:00:00');
        }

        $diasMinimos = filter_var($filtros['dias_minimos'] ?? null, FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => 0,
            ],
        ]);

        if ($diasMinimos !== false && $diasMinimos !== null) {
            $condiciones[] = $this->expresionDiasReservado() . ' >= :dias_minimos';

            $parametros[':dias_minimos'] = (int) $diasMinimos;
        }

        return ['WHERE ' . implode(' AND ', $condiciones), $parametros];
    }

    private function expresionDiasReservado(): string
    {
        if (Sql::esSqlServer($this->db)) {
            return "DATEDIFF(
                        DAY,
                        CAST(r.fecha_reserva AS DATE),
                        COALESCE(
                            CAST(r.fecha_devolucion_real AS DATE),
                            CAST(CURRENT_TIMESTAMP AS DATE)
                        )
                    )";
        }

        return "DATEDIFF(
                    COALESCE(
                        DATE(r.fecha_devolucion_real),
                        CURRENT_DATE
                    ),
                    DATE(r.fecha_reserva)
                )";
    }

    public function librosMasUsados(
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        int $limite = 5,
    ): array {
        $limite = max(1, min($limite, 50));

        $topInfo = Sql::top($this->db, $limite);

        $condicionesReserva = ['r.id_libro = l.id_libro', 'r.estado <> :estado_cancelado'];

        $parametros = [
            ':estado_cancelado' => 'Cancelado',
        ];

        if ($fechaInicio !== null && trim($fechaInicio) !== '') {
            $condicionesReserva[] = 'r.fecha_reserva >= :fecha_inicio';

            $parametros[':fecha_inicio'] = trim($fechaInicio) . ' 00:00:00';
        }

        if ($fechaFin !== null && trim($fechaFin) !== '') {
            $fechaFinObjeto = new \DateTimeImmutable(trim($fechaFin));

            $fechaFinExclusiva = $fechaFinObjeto->modify('+1 day')->format('Y-m-d 00:00:00');

            $condicionesReserva[] = 'r.fecha_reserva < :fecha_fin_exclusiva';

            $parametros[':fecha_fin_exclusiva'] = $fechaFinExclusiva;
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

                COALESCE(
                    c.nombre,
                    'Sin categoría'
                ) AS categoria,

                COUNT(r.id_reserva) AS total_reservas,
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
}
