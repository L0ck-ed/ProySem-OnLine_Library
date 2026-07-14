<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Contracts\AutenticableRepositorioInterface;
use PDO;

class Estudiante extends Model implements AutenticableRepositorioInterface
{
    public function buscarPorCredencial(string $credencial): array|false
    {
        $sql = "SELECT
                    e.id_estudiante AS id,
                    e.cip,
                    e.primer_nombre,
                    e.segundo_nombre,
                    e.primer_apellido,
                    e.segundo_apellido,
                    e.id_carrera,
                    c.nombre AS carrera,
                    e.estado,
                    e.pin_hash,
                    e.intentos_fallidos,
                    e.bloqueado
                FROM estudiantes e
                INNER JOIN carreras c ON c.id_carrera = e.id_carrera
                WHERE e.cip = :cip";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cip' => $credencial]);

        $fila = $stmt->fetch();

        return $fila ?: false;
    }

    public function aumentarIntentos(int $id): void
    {
        $sql = "UPDATE estudiantes
                SET intentos_fallidos = intentos_fallidos + 1,
                    ultimo_intento = CURRENT_TIMESTAMP
                WHERE id_estudiante = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public function bloquearUsuario(int $id): void
    {
        $sql = "UPDATE estudiantes
                SET bloqueado = 1
                WHERE id_estudiante = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public function actualizarLogin(int $id): void
    {
        $sql = "UPDATE estudiantes
                SET ultimo_login = CURRENT_TIMESTAMP,
                    ultimo_intento = CURRENT_TIMESTAMP,
                    intentos_fallidos = 0
                WHERE id_estudiante = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public function establecerPin(int $id, string $pinTextoPlano): bool
    {
        $sql = "UPDATE estudiantes
                SET pin_hash = :pin_hash
                WHERE id_estudiante = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':pin_hash' => password_hash($pinTextoPlano, PASSWORD_DEFAULT),
            ':id' => $id
        ]);
    }

    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT * FROM estudiantes WHERE id_estudiante = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: false;
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = "SELECT e.*, c.nombre AS carrera
                FROM estudiantes e
                JOIN carreras c ON e.id_carrera = c.id_carrera
                WHERE e.id_estudiante = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerPorCip(string $cip): array|false
    {
        $sql = "SELECT e.*, c.nombre AS carrera
                FROM estudiantes e
                JOIN carreras c ON e.id_carrera = c.id_carrera
                WHERE e.cip = :cip";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cip' => $cip]);
        return $stmt->fetch();
    }
}