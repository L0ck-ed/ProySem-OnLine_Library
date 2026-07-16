<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Sql;
use PDO;
use RuntimeException;

final class PrestamoInterbibliotecario extends Model
{
    public const ESTADOS = ['Solicitado', 'Aprobado', 'Rechazado', 'Recibido', 'Devuelto', 'Cancelado'];

    public function listarInstituciones(bool $soloActivas = false): array
    {
        $sql = 'SELECT id_institucion, nombre, direccion, telefono, correo, sitio_web, estado, fecha_creacion
                FROM instituciones';
        if ($soloActivas) {
            $sql .= ' WHERE estado = 1';
        }
        $sql .= ' ORDER BY nombre ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function guardarInstitucion(array $data): void
    {
        $id = (int) ($data['id_institucion'] ?? 0);

        if ($id > 0) {
            $sql = 'UPDATE instituciones SET nombre=:nombre, direccion=:direccion, telefono=:telefono,
                    correo=:correo, sitio_web=:sitio_web WHERE id_institucion=:id';
            $params = $this->paramsInstitucion($data) + [':id' => $id];
        } else {
            $sql = 'INSERT INTO instituciones (nombre, direccion, telefono, correo, sitio_web, estado)
                    VALUES (:nombre, :direccion, :telefono, :correo, :sitio_web, 1)';
            $params = $this->paramsInstitucion($data);
        }

        $this->db->prepare($sql)->execute($params);
    }

    public function cambiarEstadoInstitucion(int $id, int $estado): void
    {
        $stmt = $this->db->prepare('UPDATE instituciones SET estado=:estado WHERE id_institucion=:id');
        $stmt->execute([':estado' => $estado, ':id' => $id]);
    }

    public function listarCatalogo(string $buscar = '', bool $soloDisponible = false): array
    {
        $sql = 'SELECT ce.id_libro_externo, ce.id_institucion, ce.titulo, ce.autor, ce.isbn,
                       ce.descripcion, ce.url_catalogo, ce.disponible, ce.fecha_creacion,
                       i.nombre AS institucion, i.estado AS institucion_activa
                FROM catalogo_externo ce
                INNER JOIN instituciones i ON i.id_institucion = ce.id_institucion
                WHERE (ce.titulo LIKE :titulo OR ce.autor LIKE :autor OR COALESCE(ce.isbn, \'\') LIKE :isbn OR i.nombre LIKE :institucion)';
        if ($soloDisponible) {
            $sql .= ' AND ce.disponible = 1 AND i.estado = 1';
        }
        $sql .= ' ORDER BY ce.titulo ASC';
        $termino = '%' . $buscar . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':titulo' => $termino,
            ':autor' => $termino,
            ':isbn' => $termino,
            ':institucion' => $termino,
        ]);
        return $stmt->fetchAll();
    }

    public function guardarLibroExterno(array $data): void
    {
        $id = (int) ($data['id_libro_externo'] ?? 0);
        $params = [
            ':id_institucion' => (int) $data['id_institucion'],
            ':titulo' => $data['titulo'],
            ':autor' => $data['autor'],
            ':isbn' => $data['isbn'] !== '' ? $data['isbn'] : null,
            ':descripcion' => $data['descripcion'] !== '' ? $data['descripcion'] : null,
            ':url_catalogo' => $data['url_catalogo'] !== '' ? $data['url_catalogo'] : null,
        ];

        if ($id > 0) {
            $sql = 'UPDATE catalogo_externo SET id_institucion=:id_institucion, titulo=:titulo, autor=:autor,
                    isbn=:isbn, descripcion=:descripcion, url_catalogo=:url_catalogo
                    WHERE id_libro_externo=:id';
            $params[':id'] = $id;
        } else {
            $sql = 'INSERT INTO catalogo_externo (id_institucion, titulo, autor, isbn, descripcion, url_catalogo, disponible)
                    VALUES (:id_institucion, :titulo, :autor, :isbn, :descripcion, :url_catalogo, 1)';
        }

        $this->db->prepare($sql)->execute($params);
    }

    public function cambiarDisponibilidadLibro(int $id, int $disponible): void
    {
        $stmt = $this->db->prepare('UPDATE catalogo_externo SET disponible=:disponible WHERE id_libro_externo=:id');
        $stmt->execute([':disponible' => $disponible, ':id' => $id]);
    }

    public function listarSolicitudes(array $filtros = []): array
    {
        $sql = "SELECT pi.id_prestamo_interbibliotecario, pi.id_usuario, pi.id_libro_externo,
                       pi.fecha_solicitud, pi.fecha_aprobacion, pi.fecha_recepcion,
                       pi.fecha_vencimiento, pi.fecha_devolucion, pi.estado, pi.observacion,
                       ce.titulo, ce.autor, ce.isbn, ce.disponible,
                       i.nombre AS institucion, u.nombre AS usuario_nombre, u.usuario,
                       CASE
                           WHEN e.id_estudiante IS NOT NULL THEN 'Estudiante'
                           WHEN p.id_profesor IS NOT NULL THEN 'Docente'
                           WHEN a.id_administrativo IS NOT NULL THEN 'Administrativo'
                           ELSE 'Otro'
                       END AS tipo_usuario
                FROM prestamos_interbibliotecarios pi
                INNER JOIN catalogo_externo ce ON ce.id_libro_externo = pi.id_libro_externo
                INNER JOIN instituciones i ON i.id_institucion = ce.id_institucion
                INNER JOIN usuarios u ON u.id_usuario = pi.id_usuario
                LEFT JOIN estudiantes e ON e.id_usuario = u.id_usuario
                LEFT JOIN profesores p ON p.id_usuario = u.id_usuario
                LEFT JOIN administrativos a ON a.id_usuario = u.id_usuario
                WHERE 1=1";
        $params = [];

        $estado = trim((string) ($filtros['estado'] ?? ''));
        $buscar = trim((string) ($filtros['buscar'] ?? ''));
        if ($estado !== '' && in_array($estado, self::ESTADOS, true)) {
            $sql .= ' AND pi.estado = :estado';
            $params[':estado'] = $estado;
        }
        if ($buscar !== '') {
            $sql .= ' AND (ce.titulo LIKE :buscar1 OR ce.autor LIKE :buscar2 OR u.nombre LIKE :buscar3 OR u.usuario LIKE :buscar4 OR i.nombre LIKE :buscar5)';
            $termino = '%' . $buscar . '%';
            foreach (range(1, 5) as $n) {
                $params[':buscar' . $n] = $termino;
            }
        }
        $sql .= ' ORDER BY pi.fecha_solicitud DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listarPorUsuario(int $idUsuario): array
    {
        $stmt = $this->db->prepare(
            'SELECT pi.*, ce.titulo, ce.autor, ce.isbn, ce.url_catalogo, i.nombre AS institucion
             FROM prestamos_interbibliotecarios pi
             INNER JOIN catalogo_externo ce ON ce.id_libro_externo = pi.id_libro_externo
             INNER JOIN instituciones i ON i.id_institucion = ce.id_institucion
             WHERE pi.id_usuario = :id_usuario
             ORDER BY pi.fecha_solicitud DESC'
        );
        $stmt->execute([':id_usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }

    public function solicitar(int $idUsuario, int $idLibroExterno, string $observacion): array
    {
        $this->db->beginTransaction();
        try {
            $stmtLibro = $this->db->prepare(
                'SELECT ce.disponible, i.estado AS institucion_activa
                 FROM catalogo_externo ce INNER JOIN instituciones i ON i.id_institucion=ce.id_institucion
                 WHERE ce.id_libro_externo=:id'
            );
            $stmtLibro->execute([':id' => $idLibroExterno]);
            $libro = $stmtLibro->fetch();
            if (!$libro || (int) $libro['disponible'] !== 1 || (int) $libro['institucion_activa'] !== 1) {
                $this->db->rollBack();
                return ['ok' => false, 'mensaje' => 'Ese libro externo no está disponible.'];
            }

            $stmtExiste = $this->db->prepare(
                "SELECT COUNT(*) FROM prestamos_interbibliotecarios
                 WHERE id_usuario=:usuario AND id_libro_externo=:libro
                   AND estado IN ('Solicitado','Aprobado','Recibido')"
            );
            $stmtExiste->execute([':usuario' => $idUsuario, ':libro' => $idLibroExterno]);
            if ((int) $stmtExiste->fetchColumn() > 0) {
                $this->db->rollBack();
                return ['ok' => false, 'mensaje' => 'Ya tienes una solicitud activa para ese libro.'];
            }

            $stmt = $this->db->prepare(
                "INSERT INTO prestamos_interbibliotecarios
                 (id_usuario, id_libro_externo, estado, observacion)
                 VALUES (:usuario, :libro, 'Solicitado', :observacion)"
            );
            $stmt->execute([
                ':usuario' => $idUsuario,
                ':libro' => $idLibroExterno,
                ':observacion' => $observacion !== '' ? $observacion : null,
            ]);
            $this->db->commit();
            return ['ok' => true, 'mensaje' => 'Solicitud interbibliotecaria enviada correctamente.'];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function cancelarPorUsuario(int $idSolicitud, int $idUsuario): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE prestamos_interbibliotecarios SET estado='Cancelado'
             WHERE id_prestamo_interbibliotecario=:id AND id_usuario=:usuario AND estado='Solicitado'"
        );
        $stmt->execute([':id' => $idSolicitud, ':usuario' => $idUsuario]);
        return $stmt->rowCount() > 0;
    }

    public function actualizarSolicitud(int $idSolicitud, string $estado, ?string $fechaVencimiento, string $observacion): bool
    {
        if (!in_array($estado, self::ESTADOS, true)) {
            throw new RuntimeException('Estado interbibliotecario no válido.');
        }

        $camposFecha = match ($estado) {
            'Aprobado' => 'fecha_aprobacion = COALESCE(fecha_aprobacion, CURRENT_TIMESTAMP),',
            'Recibido' => 'fecha_recepcion = COALESCE(fecha_recepcion, CURRENT_TIMESTAMP),',
            'Devuelto' => 'fecha_devolucion = COALESCE(fecha_devolucion, CURRENT_TIMESTAMP),',
            default => '',
        };

        $sql = "UPDATE prestamos_interbibliotecarios SET
                    {$camposFecha}
                    estado=:estado,
                    fecha_vencimiento=:fecha_vencimiento,
                    observacion=:observacion
                WHERE id_prestamo_interbibliotecario=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':estado' => $estado,
            ':fecha_vencimiento' => $fechaVencimiento,
            ':observacion' => $observacion !== '' ? $observacion : null,
            ':id' => $idSolicitud,
        ]);
        return $stmt->rowCount() > 0;
    }

    private function paramsInstitucion(array $data): array
    {
        return [
            ':nombre' => $data['nombre'],
            ':direccion' => $data['direccion'] !== '' ? $data['direccion'] : null,
            ':telefono' => $data['telefono'] !== '' ? $data['telefono'] : null,
            ':correo' => $data['correo'] !== '' ? $data['correo'] : null,
            ':sitio_web' => $data['sitio_web'] !== '' ? $data['sitio_web'] : null,
        ];
    }
}
