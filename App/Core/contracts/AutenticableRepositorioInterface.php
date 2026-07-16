<?php

namespace App\Core\Contracts;

/**
 * Contrato que debe cumplir cualquier modelo que represente
 * una entidad capaz de autenticarse (Usuario administrativo, Estudiante, etc).
 *
 * Permite que AuthServiceBase trabaje con cualquier entidad autenticable
 * sin conocer sus detalles internos (Principio de Inversión de Dependencias).
 */
interface AutenticableRepositorioInterface
{
    public function buscarPorCredencial(string $credencial): array|false;

    public function aumentarIntentos(int $id): void;

    public function bloquearUsuario(int $id): void;

    public function actualizarLogin(int $id): void;
}