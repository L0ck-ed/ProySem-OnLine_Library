<?php

declare(strict_types=1);

namespace App\Services\Crypto;

use App\Core\Contracts\ServicioCriptograficoInterface;
use RuntimeException;

final class PasswordHashService implements ServicioCriptograficoInterface
{
    public function transformar(string $dato): string
    {
        $hash = password_hash($dato, PASSWORD_DEFAULT);

        if (!is_string($hash)) {
            throw new RuntimeException('No se pudo proteger la contraseña.');
        }

        return $hash;
    }

    public function verificar(string $dato, string $transformado): bool
    {
        return $transformado !== '' && password_verify($dato, $transformado);
    }

    public function algoritmo(): string
    {
        return PASSWORD_DEFAULT === PASSWORD_BCRYPT ? 'password_bcrypt' : 'password_default';
    }
}
