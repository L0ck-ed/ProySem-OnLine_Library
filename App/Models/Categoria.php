<?php

namespace App\Models;

use App\Core\Model;

class Categoria extends Model
{
    public function listar(): array
    {
        $stmt = $this->db->query(
            "SELECT id_categoria, nombre
             FROM categorias
             WHERE estado = 'Activo'
             ORDER BY nombre ASC"
        );

        return $stmt->fetchAll();
    }

    public function contar(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM categorias WHERE estado = 'Activo'");
        return (int) $stmt->fetch()['total'];
    }
}