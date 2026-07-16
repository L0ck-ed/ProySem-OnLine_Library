<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class MensajeContacto extends Model
{
    public function crear(array $data): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO mensajes_contacto (nombre, correo, asunto, mensaje, estado)
             VALUES (:nombre, :correo, :asunto, :mensaje, 'Nuevo')"
        );
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':correo' => $data['correo'],
            ':asunto' => $data['asunto'],
            ':mensaje' => $data['mensaje'],
        ]);
    }
}
