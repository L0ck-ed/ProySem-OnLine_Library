<?php

namespace App\Core\Contracts;

interface AutenticableRepositorioInterface
{
    public function buscarPorCredencial(string $credencial): array|false;

    public function aumentarIntentos(int $id): void;

    public function bloquearUsuario(int $id): void;

    public function actualizarLogin(int $id): void;
}
