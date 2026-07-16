<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Configs\DatabaseConfig;
use App\Core\Sql;
use PDO;

final class FirmaRegistroRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? DatabaseConfig::connect();
    }

    public function guardar(
        string $tabla,
        int $idRegistro,
        string $firmaBase64,
        string $algoritmo,
        ?int $idUsuario,
    ): void {
        $firmaBinaria = base64_decode($firmaBase64, true);

        if ($firmaBinaria === false) {
            throw new \RuntimeException('La firma recibida no es válida.');
        }

        $this->db->beginTransaction();
        try {
            $stmtEliminar = $this->db->prepare(
                'DELETE FROM firmas_registros WHERE tabla = :tabla AND id_registro = :id_registro'
            );
            $stmtEliminar->execute([':tabla' => $tabla, ':id_registro' => $idRegistro]);

            $stmt = $this->db->prepare(
                'INSERT INTO firmas_registros (tabla, id_registro, firma, algoritmo, id_usuario_firmante) VALUES (:tabla, :id_registro, :firma, :algoritmo, :id_usuario)'
            );
            $stmt->bindValue(':tabla', $tabla);
            $stmt->bindValue(':id_registro', $idRegistro, PDO::PARAM_INT);
            $stmt->bindValue(':firma', $firmaBinaria, PDO::PARAM_LOB);
            $stmt->bindValue(':algoritmo', $algoritmo);
            $stmt->bindValue(':id_usuario', $idUsuario, $idUsuario === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->execute();
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function obtenerUltima(string $tabla, int $idRegistro): array|false
    {
        $sql = Sql::esSqlServer($this->db)
            ? 'SELECT TOP (1) firma, algoritmo, fecha_firma FROM firmas_registros WHERE tabla = :tabla AND id_registro = :id_registro ORDER BY id_firma DESC'
            : 'SELECT firma, algoritmo, fecha_firma FROM firmas_registros WHERE tabla = :tabla AND id_registro = :id_registro ORDER BY id_firma DESC LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tabla' => $tabla, ':id_registro' => $idRegistro]);
        $fila = $stmt->fetch();

        if (!$fila) {
            return false;
        }

        $firma = $fila['firma'];
        if (is_resource($firma)) {
            $contenido = stream_get_contents($firma);
            $firma = $contenido === false ? '' : $contenido;
        }

        $fila['firma'] = base64_encode((string) $firma);
        return $fila;
    }
}
