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
                    id_estudiante AS id,
                    cip,
                    primer_nombre,
                    segundo_nombre,
                    primer_apellido,
                    segundo_apellido,
                    id_carrera,
                    estado,
                    pin_hash,
                    intentos_fallidos,
                    bloqueado
                FROM dbo.estudiantes
                WHERE cip = :cip";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cip' => $credencial]);

        $fila = $stmt->fetch();

        return $fila ?: false;
    }

    public function aumentarIntentos(int $id): void
    {
        $sql = "UPDATE dbo.estudiantes
                SET intentos_fallidos = intentos_fallidos + 1,
                    ultimo_intento = SYSDATETIME()
                WHERE id_estudiante = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public function bloquearUsuario(int $id): void
    {
        $sql = "UPDATE dbo.estudiantes
                SET bloqueado = 1
                WHERE id_estudiante = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public function actualizarLogin(int $id): void
    {
        $sql = "UPDATE dbo.estudiantes
                SET ultimo_login = SYSDATETIME(),
                    ultimo_intento = SYSDATETIME(),
                    intentos_fallidos = 0
                WHERE id_estudiante = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public function establecerPin(int $id, string $pinTextoPlano): bool
    {
        $sql = "UPDATE dbo.estudiantes
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
        $sql = "SELECT * FROM dbo.estudiantes WHERE id_estudiante = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: false;
    }
}