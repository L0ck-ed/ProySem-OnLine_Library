<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    public function buscarPorUsuario(string $usuario): array|false
    {
        $sql = "SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario' => $usuario]);

        return $stmt->fetch();
    }

    public function actualizarLogin(int $idUsuario): void
    {
        $sql = "UPDATE usuarios
                SET ultimo_login = NOW(),
                    ultimo_intento = NOW(),
                    intentos_fallidos = 0
                WHERE id_usuario = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
    }

    public function aumentarIntentos(int $idUsuario): void
    {
        $sql = "UPDATE usuarios
                SET intentos_fallidos = intentos_fallidos + 1,
                    ultimo_intento = NOW()
                WHERE id_usuario = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
    }

    public function bloquearUsuario(int $idUsuario): void
    {
        $sql = "UPDATE usuarios SET bloqueado = 1 WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
    }

    public function crear(array $data): bool
    {
        $sql = "INSERT INTO usuarios
                (nombre, usuario, password, rol, estado)
                VALUES
                (:nombre, :usuario, :password, :rol, 'Activo')";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':usuario' => $data['usuario'],
            ':password' => $data['password'],
            ':rol' => $data['rol']
        ]);
    }

    public function listar(string $buscar = '', int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT id_usuario, nombre, usuario, rol, estado, fecha_creacion
                FROM usuarios
                WHERE nombre LIKE :buscar OR usuario LIKE :buscar
                ORDER BY id_usuario DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':buscar', '%' . $buscar . '%');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contar(string $buscar = ''): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM usuarios
                WHERE nombre LIKE :buscar OR usuario LIKE :buscar";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':buscar' => '%' . $buscar . '%']);

        return (int)$stmt->fetch()['total'];
    }
}
