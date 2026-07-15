<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Sql;

class UsuarioRegular extends Model
{
    public function buscarPorCredencial(string $credencial): array|false
    {
        $topInfo = Sql::top($this->db, 1);

        $sql = "SELECT {$topInfo['antes']}
                    u.id_usuario,
                    u.nombre AS nombre_cuenta,
                    u.usuario,
                    u.correo,
                    u.password_hash,
                    u.estado AS estado_usuario,
                    u.intentos_fallidos,
                    u.bloqueado,
                    u.bloqueado_hasta,

                    e.id_estudiante,
                    e.cip AS cip_estudiante,
                    e.primer_nombre AS estudiante_primer_nombre,
                    e.segundo_nombre AS estudiante_segundo_nombre,
                    e.primer_apellido AS estudiante_primer_apellido,
                    e.segundo_apellido AS estudiante_segundo_apellido,
                    e.fecha_nacimiento,
                    c.nombre AS carrera,
                    fe.nombre AS facultad_estudiante,

                    p.id_profesor,
                    p.cip AS cip_profesor,
                    p.primer_nombre AS profesor_primer_nombre,
                    p.segundo_nombre AS profesor_segundo_nombre,
                    p.primer_apellido AS profesor_primer_apellido,
                    p.segundo_apellido AS profesor_segundo_apellido,
                    p.especialidad,
                    d.nombre AS departamento,
                    fp.nombre AS facultad_profesor,

                    CASE
                        WHEN e.id_estudiante IS NOT NULL
                             AND EXISTS (
                                SELECT 1
                                FROM usuarios_roles ure
                                INNER JOIN roles re
                                    ON re.id_rol = ure.id_rol
                                WHERE ure.id_usuario = u.id_usuario
                                  AND re.nombre = 'Estudiante'
                                  AND re.estado = 1
                             )
                            THEN 'Estudiante'

                        WHEN p.id_profesor IS NOT NULL
                             AND EXISTS (
                                SELECT 1
                                FROM usuarios_roles urp
                                INNER JOIN roles rp
                                    ON rp.id_rol = urp.id_rol
                                WHERE urp.id_usuario = u.id_usuario
                                  AND rp.nombre = 'Profesor'
                                  AND rp.estado = 1
                             )
                            THEN 'Profesor'

                        ELSE NULL
                    END AS tipo_usuario

                FROM usuarios u

                LEFT JOIN estudiantes e
                    ON e.id_usuario = u.id_usuario
                   AND e.estado = 1

                LEFT JOIN carreras c
                    ON c.id_carrera = e.id_carrera

                LEFT JOIN facultades fe
                    ON fe.id_facultad = c.id_facultad

                LEFT JOIN profesores p
                    ON p.id_usuario = u.id_usuario
                   AND p.estado = 1

                LEFT JOIN departamentos d
                    ON d.id_departamento = p.id_departamento

                LEFT JOIN facultades fp
                    ON fp.id_facultad = d.id_facultad

                WHERE (
                    u.usuario = :credencial_usuario
                    OR u.correo = :credencial_correo
                    OR e.cip = :credencial_estudiante
                    OR p.cip = :credencial_profesor
                )
                  AND u.estado = 1
                  AND (
                    e.id_estudiante IS NOT NULL
                    OR p.id_profesor IS NOT NULL
                  )

                ORDER BY
                    CASE
                        WHEN e.cip = :prioridad_estudiante THEN 0
                        WHEN p.cip = :prioridad_profesor THEN 0
                        WHEN e.id_estudiante IS NOT NULL THEN 1
                        ELSE 2
                    END

                {$topInfo['despues']}";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':credencial_usuario' => $credencial,
            ':credencial_correo' => $credencial,
            ':credencial_estudiante' => $credencial,
            ':credencial_profesor' => $credencial,
            ':prioridad_estudiante' => $credencial,
            ':prioridad_profesor' => $credencial,
        ]);

        $usuario = $stmt->fetch();

        if (!$usuario || empty($usuario['tipo_usuario'])) {
            return false;
        }

        return $this->normalizarPerfil($usuario);
    }

    public function obtenerPorIdUsuario(int $idUsuario): array|false
    {
        $sql = "SELECT
                    u.id_usuario,
                    u.nombre AS nombre_cuenta,
                    u.usuario,
                    u.correo,
                    u.estado AS estado_usuario,

                    e.id_estudiante,
                    e.cip AS cip_estudiante,
                    e.primer_nombre AS estudiante_primer_nombre,
                    e.segundo_nombre AS estudiante_segundo_nombre,
                    e.primer_apellido AS estudiante_primer_apellido,
                    e.segundo_apellido AS estudiante_segundo_apellido,
                    e.fecha_nacimiento,
                    c.nombre AS carrera,
                    fe.nombre AS facultad_estudiante,

                    p.id_profesor,
                    p.cip AS cip_profesor,
                    p.primer_nombre AS profesor_primer_nombre,
                    p.segundo_nombre AS profesor_segundo_nombre,
                    p.primer_apellido AS profesor_primer_apellido,
                    p.segundo_apellido AS profesor_segundo_apellido,
                    p.especialidad,
                    d.nombre AS departamento,
                    fp.nombre AS facultad_profesor,

                    CASE
                        WHEN e.id_estudiante IS NOT NULL
                             AND EXISTS (
                                SELECT 1
                                FROM usuarios_roles ure
                                INNER JOIN roles re
                                    ON re.id_rol = ure.id_rol
                                WHERE ure.id_usuario = u.id_usuario
                                  AND re.nombre = 'Estudiante'
                                  AND re.estado = 1
                             )
                            THEN 'Estudiante'

                        WHEN p.id_profesor IS NOT NULL
                             AND EXISTS (
                                SELECT 1
                                FROM usuarios_roles urp
                                INNER JOIN roles rp
                                    ON rp.id_rol = urp.id_rol
                                WHERE urp.id_usuario = u.id_usuario
                                  AND rp.nombre = 'Profesor'
                                  AND rp.estado = 1
                             )
                            THEN 'Profesor'

                        ELSE NULL
                    END AS tipo_usuario

                FROM usuarios u

                LEFT JOIN estudiantes e
                    ON e.id_usuario = u.id_usuario
                   AND e.estado = 1

                LEFT JOIN carreras c
                    ON c.id_carrera = e.id_carrera

                LEFT JOIN facultades fe
                    ON fe.id_facultad = c.id_facultad

                LEFT JOIN profesores p
                    ON p.id_usuario = u.id_usuario
                   AND p.estado = 1

                LEFT JOIN departamentos d
                    ON d.id_departamento = p.id_departamento

                LEFT JOIN facultades fp
                    ON fp.id_facultad = d.id_facultad

                WHERE u.id_usuario = :id_usuario
                  AND u.estado = 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_usuario' => $idUsuario,
        ]);

        $usuario = $stmt->fetch();

        if (!$usuario || empty($usuario['tipo_usuario'])) {
            return false;
        }

        return $this->normalizarPerfil($usuario);
    }

    public function registrarIntentoFallido(int $idUsuario): int
    {
        if (Sql::esSqlServer($this->db)) {
            $sql = "UPDATE usuarios
                    SET
                        intentos_fallidos = COALESCE(intentos_fallidos, 0) + 1,
                        ultimo_intento = CURRENT_TIMESTAMP
                    OUTPUT INSERTED.intentos_fallidos
                    WHERE id_usuario = :id_usuario";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $idUsuario,
            ]);

            $intentos = (int) $stmt->fetchColumn();
            $stmt->closeCursor();

            if ($intentos >= 3) {
                $bloquear = $this->db->prepare(
                    "UPDATE usuarios
                     SET
                         bloqueado = 1,
                         bloqueado_hasta = DATEADD(MINUTE, 15, CURRENT_TIMESTAMP)
                     WHERE id_usuario = :id_usuario",
                );
                $bloquear->execute([
                    ':id_usuario' => $idUsuario,
                ]);
            }

            return $intentos;
        }

        $this->db->beginTransaction();

        try {
            $sql = "UPDATE usuarios
                    SET
                        intentos_fallidos = COALESCE(intentos_fallidos, 0) + 1,
                        ultimo_intento = CURRENT_TIMESTAMP
                    WHERE id_usuario = :id_usuario";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $idUsuario,
            ]);

            $consulta = $this->db->prepare(
                'SELECT intentos_fallidos FROM usuarios WHERE id_usuario = :id_usuario',
            );
            $consulta->execute([
                ':id_usuario' => $idUsuario,
            ]);

            $intentos = (int) $consulta->fetchColumn();

            if ($intentos >= 3) {
                $bloquear = $this->db->prepare(
                    "UPDATE usuarios
                     SET
                         bloqueado = 1,
                         bloqueado_hasta = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE)
                     WHERE id_usuario = :id_usuario",
                );
                $bloquear->execute([
                    ':id_usuario' => $idUsuario,
                ]);
            }

            $this->db->commit();

            return $intentos;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function actualizarLogin(int $idUsuario): void
    {
        $sql = "UPDATE usuarios
                SET
                    ultimo_login = CURRENT_TIMESTAMP,
                    ultimo_intento = CURRENT_TIMESTAMP,
                    intentos_fallidos = 0,
                    bloqueado = 0,
                    bloqueado_hasta = NULL
                WHERE id_usuario = :id_usuario";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $idUsuario,
        ]);
    }

    public function desbloquear(int $idUsuario): void
    {
        $sql = "UPDATE usuarios
                SET
                    intentos_fallidos = 0,
                    bloqueado = 0,
                    bloqueado_hasta = NULL
                WHERE id_usuario = :id_usuario";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $idUsuario,
        ]);
    }

    private function normalizarPerfil(array $usuario): array
    {
        $tipoUsuario = (string) $usuario['tipo_usuario'];

        if ($tipoUsuario === 'Profesor') {
            $partesNombre = [
                $usuario['profesor_primer_nombre'] ?? '',
                $usuario['profesor_segundo_nombre'] ?? '',
                $usuario['profesor_primer_apellido'] ?? '',
                $usuario['profesor_segundo_apellido'] ?? '',
            ];

            $usuario['cip'] = $usuario['cip_profesor'] ?? '';
            $usuario['detalle_perfil'] = $usuario['departamento'] ?? 'Departamento no especificado';
            $usuario['facultad'] = $usuario['facultad_profesor'] ?? 'Facultad no especificada';
            $usuario['primer_nombre'] = $usuario['profesor_primer_nombre'] ?? '';
            $usuario['segundo_nombre'] = $usuario['profesor_segundo_nombre'] ?? '';
            $usuario['primer_apellido'] = $usuario['profesor_primer_apellido'] ?? '';
            $usuario['segundo_apellido'] = $usuario['profesor_segundo_apellido'] ?? '';
            $usuario['carrera'] = $usuario['departamento'] ?? 'Departamento no especificado';
        } else {
            $partesNombre = [
                $usuario['estudiante_primer_nombre'] ?? '',
                $usuario['estudiante_segundo_nombre'] ?? '',
                $usuario['estudiante_primer_apellido'] ?? '',
                $usuario['estudiante_segundo_apellido'] ?? '',
            ];

            $usuario['cip'] = $usuario['cip_estudiante'] ?? '';
            $usuario['detalle_perfil'] = $usuario['carrera'] ?? 'Carrera no especificada';
            $usuario['facultad'] = $usuario['facultad_estudiante'] ?? 'Facultad no especificada';
            $usuario['primer_nombre'] = $usuario['estudiante_primer_nombre'] ?? '';
            $usuario['segundo_nombre'] = $usuario['estudiante_segundo_nombre'] ?? '';
            $usuario['primer_apellido'] = $usuario['estudiante_primer_apellido'] ?? '';
            $usuario['segundo_apellido'] = $usuario['estudiante_segundo_apellido'] ?? '';
        }

        $partesNombre = array_filter(
            $partesNombre,
            static fn(mixed $parte): bool => trim((string) $parte) !== '',
        );

        $usuario['nombre_portal'] = trim(implode(' ', $partesNombre));

        if ($usuario['nombre_portal'] === '') {
            $usuario['nombre_portal'] = $usuario['nombre_cuenta'] ?? 'Usuario';
        }

        return $usuario;
    }
}
