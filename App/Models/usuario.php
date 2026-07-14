<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Sql;
use PDO;

class Usuario extends Model
{
    public function buscarPorUsuario(string $usuario): array|false
    {
        $topInfo = Sql::top($this->db, 1);

        $sql = "SELECT {$topInfo['antes']} *
                FROM usuarios
                WHERE usuario = :usuario
                {$topInfo['despues']}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':usuario' => $usuario
        ]);

        return $stmt->fetch();
    }

    public function actualizarLogin(int $idUsuario): void
    {
        $sql = "UPDATE usuarios
                SET ultimo_login = CURRENT_TIMESTAMP,
                    ultimo_intento = CURRENT_TIMESTAMP,
                    intentos_fallidos = 0
                WHERE id_usuario = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $idUsuario
        ]);
    }

    public function aumentarIntentos(int $idUsuario): void
    {
        $sql = "UPDATE usuarios
                SET intentos_fallidos = intentos_fallidos + 1,
                    ultimo_intento = CURRENT_TIMESTAMP
                WHERE id_usuario = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $idUsuario
        ]);
    }

    public function bloquearUsuario(int $idUsuario): void
    {
        $sql = "UPDATE usuarios
                SET bloqueado = 1
                WHERE id_usuario = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $idUsuario
        ]);
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
                WHERE nombre LIKE :buscar_nombre
                   OR usuario LIKE :buscar_usuario
                ORDER BY id_usuario DESC
                " . Sql::limitOffset($this->db);

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':buscar_nombre', '%' . $buscar . '%', PDO::PARAM_STR);
        $stmt->bindValue(':buscar_usuario', '%' . $buscar . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contar(string $buscar = ''): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM usuarios
                WHERE nombre LIKE :buscar_nombre
                   OR usuario LIKE :buscar_usuario";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':buscar_nombre' => '%' . $buscar . '%',
            ':buscar_usuario' => '%' . $buscar . '%'
        ]);

        $resultado = $stmt->fetch();

        return (int) $resultado['total'];
    }
}