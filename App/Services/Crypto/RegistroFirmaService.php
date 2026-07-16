<?php

declare(strict_types=1);

namespace App\Services\Crypto;

use App\Core\Contracts\ServicioCriptograficoInterface;
use App\Repositories\FirmaRegistroRepository;

final class RegistroFirmaService
{
    public function __construct(
        private readonly ServicioCriptograficoInterface $criptografia = new OpenSslSignatureService(),
        private readonly FirmaRegistroRepository $repositorio = new FirmaRegistroRepository(),
    ) {
    }

    public function firmar(string $tabla, int $idRegistro, array $datos, ?int $idUsuario): void
    {
        $canonico = $this->canonizar($datos);
        $firma = $this->criptografia->transformar($canonico);
        $this->repositorio->guardar($tabla, $idRegistro, $firma, $this->criptografia->algoritmo(), $idUsuario);
    }

    public function verificar(string $tabla, int $idRegistro, array $datos): ?bool
    {
        $registro = $this->repositorio->obtenerUltima($tabla, $idRegistro);

        if (!$registro) {
            return null;
        }

        return $this->criptografia->verificar($this->canonizar($datos), (string) $registro['firma']);
    }

    private function canonizar(array $datos): string
    {
        ksort($datos);
        return (string) json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    }
}
