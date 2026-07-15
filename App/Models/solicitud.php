<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Sql;
use PDO;

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

    private const ESTADOS_VALIDOS = [
        'Pendiente',
        'En revisión',
        'Aprobada',
        'Rechazada',
        'Adquirida',
    ];

    public static function areasValidas(): array
    {
        return self::AREAS_VALIDAS;
    }

    public static function estadosValidos(): array
    {
        return self::ESTADOS_VALIDOS;
    }

    /* ==================================================
       PORTAL DEL ESTUDIANTE
       ================================================== */

    public function crear(
        int $idEstudiante,
        string $tituloLibro,
        string $area,
        ?string $descripcion,
    ): bool {
        $idUsuario = $this->obtenerIdUsuarioEstudiante($idEstudiante);

        if ($idUsuario <= 0) {
            return false;
        }

        $motivoInteres = trim((string) $descripcion);

        if ($motivoInteres === '') {
            $motivoInteres = 'Solicitud realizada desde el portal estudiantil.';
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
            ':motivo_interes' => $motivoInteres,
        ]);
    }

    public function listarPorEstudiante(int $idEstudiante): array
    {
        $sql = "SELECT
                    s.id_solicitud,
                    s.titulo_libro AS titulo,
                    s.autor,
                    s.materia AS area,
                    s.motivo_interes AS descripcion,
                    s.fecha_solicitud AS fecha,
                    s.estado,
                    s.respuesta,
                    s.fecha_respuesta
                FROM solicitudes_libros s
                INNER JOIN estudiantes e
                    ON e.id_usuario = s.id_usuario
                WHERE e.id_estudiante = :id_estudiante
                ORDER BY
                    s.fecha_solicitud DESC,
                    s.id_solicitud DESC";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_estudiante' => $idEstudiante,
        ]);

        return $stmt->fetchAll();
    }

    public function contarPorEstudiante(int $idEstudiante): int
    {
        $sql = "SELECT
                    COUNT(*) AS total
                FROM solicitudes_libros s
                INNER JOIN estudiantes e
                    ON e.id_usuario = s.id_usuario
                WHERE e.id_estudiante = :id_estudiante";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_estudiante' => $idEstudiante,
        ]);

        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }


    public function listarAdmin(array $filtros, int $limit = 10, int $offset = 0): array
    {
        [$where, $parametros] = $this->construirFiltrosAdmin($filtros);

        $sql =
            "SELECT
                    s.id_solicitud,
                    s.id_usuario,
                    s.titulo_libro,
                    s.autor,
                    s.materia,
                    s.motivo_interes,
                    s.fecha_solicitud,
                    s.estado,
                    s.respuesta,
                    s.fecha_respuesta,
                    s.id_usuario_responde,

                    u.nombre AS nombre_usuario,
                    u.usuario,

                    e.id_estudiante,
                    e.cip AS cip_estudiante,
                    e.primer_nombre AS estudiante_nombre,
                    e.primer_apellido AS estudiante_apellido,

                    p.id_profesor,
                    p.cip AS cip_profesor,
                    p.primer_nombre AS profesor_nombre,
                    p.primer_apellido AS profesor_apellido,

                    CASE
                        WHEN e.id_estudiante IS NOT NULL
                            THEN 'Estudiante'
                        WHEN p.id_profesor IS NOT NULL
                            THEN 'Profesor'
                        ELSE 'Usuario'
                    END AS tipo_usuario,

                    ur.nombre AS nombre_responde

                FROM solicitudes_libros s

                INNER JOIN usuarios u
                    ON u.id_usuario = s.id_usuario

                LEFT JOIN estudiantes e
                    ON e.id_usuario = s.id_usuario

                LEFT JOIN profesores p
                    ON p.id_usuario = s.id_usuario

                LEFT JOIN usuarios ur
                    ON ur.id_usuario =
                        s.id_usuario_responde

                {$where}

                ORDER BY
                    CASE
                        WHEN s.estado = 'Pendiente'
                            THEN 1
                        WHEN s.estado = 'En revisión'
                            THEN 2
                        ELSE 3
                    END,
                    s.fecha_solicitud DESC,
                    s.id_solicitud DESC

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
        [$where, $parametros] = $this->construirFiltrosAdmin($filtros);

        $sql = "SELECT
                    COUNT(DISTINCT s.id_solicitud)
                        AS total
                FROM solicitudes_libros s

                INNER JOIN usuarios u
                    ON u.id_usuario = s.id_usuario

                LEFT JOIN estudiantes e
                    ON e.id_usuario = s.id_usuario

                LEFT JOIN profesores p
                    ON p.id_usuario = s.id_usuario

                {$where}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }

    public function buscarPorIdAdmin(int $idSolicitud): array|false
    {
        $sql = "SELECT
                    s.id_solicitud,
                    s.id_usuario,
                    s.titulo_libro,
                    s.autor,
                    s.materia,
                    s.motivo_interes,
                    s.fecha_solicitud,
                    s.estado,
                    s.respuesta,
                    s.fecha_respuesta,
                    s.id_usuario_responde,

                    u.nombre AS nombre_usuario,
                    u.usuario,
                    u.correo,

                    e.id_estudiante,
                    e.cip AS cip_estudiante,
                    e.primer_nombre AS estudiante_nombre,
                    e.segundo_nombre AS estudiante_segundo_nombre,
                    e.primer_apellido AS estudiante_apellido,
                    e.segundo_apellido AS estudiante_segundo_apellido,

                    p.id_profesor,
                    p.cip AS cip_profesor,
                    p.primer_nombre AS profesor_nombre,
                    p.segundo_nombre AS profesor_segundo_nombre,
                    p.primer_apellido AS profesor_apellido,
                    p.segundo_apellido AS profesor_segundo_apellido,

                    CASE
                        WHEN e.id_estudiante IS NOT NULL
                            THEN 'Estudiante'
                        WHEN p.id_profesor IS NOT NULL
                            THEN 'Profesor'
                        ELSE 'Usuario'
                    END AS tipo_usuario,

                    ur.nombre AS nombre_responde,
                    ur.usuario AS usuario_responde

                FROM solicitudes_libros s

                INNER JOIN usuarios u
                    ON u.id_usuario = s.id_usuario

                LEFT JOIN estudiantes e
                    ON e.id_usuario = s.id_usuario

                LEFT JOIN profesores p
                    ON p.id_usuario = s.id_usuario

                LEFT JOIN usuarios ur
                    ON ur.id_usuario =
                        s.id_usuario_responde

                WHERE s.id_solicitud =
                    :id_solicitud";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_solicitud' => $idSolicitud,
        ]);

        return $stmt->fetch();
    }

    public function gestionarAdmin(
        int $idSolicitud,
        string $estado,
        string $respuesta,
        ?int $idUsuarioResponde,
    ): bool {
        if (!in_array($estado, self::ESTADOS_VALIDOS, true)) {
            return false;
        }

        if ($estado === 'Pendiente') {
            $sql = "UPDATE solicitudes_libros
                    SET
                        estado = 'Pendiente',
                        respuesta = NULL,
                        fecha_respuesta = NULL,
                        id_usuario_responde = NULL
                    WHERE id_solicitud =
                        :id_solicitud";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':id_solicitud' => $idSolicitud,
            ]);

            return $stmt->rowCount() > 0;
        }

        $sql = "UPDATE solicitudes_libros
                SET
                    estado = :estado,
                    respuesta = :respuesta,
                    fecha_respuesta =
                        CURRENT_TIMESTAMP,
                    id_usuario_responde =
                        :id_usuario_responde
                WHERE id_solicitud =
                    :id_solicitud";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':estado' => $estado,
            ':respuesta' => $respuesta !== '' ? $respuesta : null,
            ':id_usuario_responde' => $idUsuarioResponde,
            ':id_solicitud' => $idSolicitud,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function construirFiltrosAdmin(array $filtros): array
    {
        $condiciones = ['1 = 1'];
        $parametros = [];

        $buscar = trim((string) ($filtros['buscar'] ?? ''));

        if ($buscar !== '') {
            $condiciones[] = "(
                s.titulo_libro LIKE :buscar_titulo
                OR COALESCE(
                    s.autor,
                    ''
                ) LIKE :buscar_autor
                OR s.materia LIKE :buscar_materia
                OR u.nombre LIKE :buscar_nombre
                OR u.usuario LIKE :buscar_usuario
                OR COALESCE(
                    e.cip,
                    ''
                ) LIKE :buscar_cip_estudiante
                OR COALESCE(
                    p.cip,
                    ''
                ) LIKE :buscar_cip_profesor
            )";

            $termino = '%' . $buscar . '%';

            $parametros[':buscar_titulo'] = $termino;

            $parametros[':buscar_autor'] = $termino;

            $parametros[':buscar_materia'] = $termino;

            $parametros[':buscar_nombre'] = $termino;

            $parametros[':buscar_usuario'] = $termino;

            $parametros[':buscar_cip_estudiante'] = $termino;

            $parametros[':buscar_cip_profesor'] = $termino;
        }

        $estado = trim((string) ($filtros['estado'] ?? ''));

        if ($estado !== '' && in_array($estado, self::ESTADOS_VALIDOS, true)) {
            $condiciones[] = 's.estado = :estado';

            $parametros[':estado'] = $estado;
        }

        $tipoUsuario = trim((string) ($filtros['tipo_usuario'] ?? ''));

        if ($tipoUsuario === 'Estudiante') {
            $condiciones[] = 'e.id_estudiante IS NOT NULL';
        } elseif ($tipoUsuario === 'Profesor') {
            $condiciones[] = 'p.id_profesor IS NOT NULL';
        } elseif ($tipoUsuario === 'Usuario') {
            $condiciones[] = 'e.id_estudiante IS NULL
                 AND p.id_profesor IS NULL';
        }

        return ['WHERE ' . implode(' AND ', $condiciones), $parametros];
    }

    private function obtenerIdUsuarioEstudiante(int $idEstudiante): int
    {
        $sql = "SELECT
                    e.id_usuario
                FROM estudiantes e
                INNER JOIN usuarios u
                    ON u.id_usuario =
                        e.id_usuario
                WHERE e.id_estudiante =
                    :id_estudiante
                  AND e.estado = 1
                  AND u.estado = 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_estudiante' => $idEstudiante,
        ]);

        return (int) $stmt->fetchColumn();
    }
}
