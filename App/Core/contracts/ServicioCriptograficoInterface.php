<?php

declare(strict_types=1);

namespace App\Core\Contracts;

/**
 * Contrato común para transformaciones criptográficas.
 * Permite sustituir hashing o firma digital sin acoplar los controladores
 * a una implementación concreta (DIP / SOLID).
 */
interface ServicioCriptograficoInterface
{
    public function transformar(string $dato): string;

    public function verificar(string $dato, string $transformado): bool;

    public function algoritmo(): string;
}
