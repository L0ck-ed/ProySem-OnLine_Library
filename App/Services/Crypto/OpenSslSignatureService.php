<?php

declare(strict_types=1);

namespace App\Services\Crypto;

use App\Core\Contracts\ServicioCriptograficoInterface;
use RuntimeException;

final class OpenSslSignatureService implements ServicioCriptograficoInterface
{
    private string $privateKeyPath;
    private string $publicKeyPath;

    public function __construct(?string $keyDirectory = null)
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('La extensión OpenSSL no está habilitada en PHP.');
        }

        $directory = $keyDirectory ?? dirname(__DIR__, 2) . '/Storage/keys';
        $this->privateKeyPath = rtrim($directory, '/\\') . '/private.pem';
        $this->publicKeyPath = rtrim($directory, '/\\') . '/public.pem';
        $this->asegurarClaves($directory);
    }

    public function transformar(string $dato): string
    {
        $privateKey = openssl_pkey_get_private((string) file_get_contents($this->privateKeyPath));

        if ($privateKey === false) {
            throw new RuntimeException('No se pudo abrir la clave privada de firma.');
        }

        $firma = '';
        $ok = openssl_sign($dato, $firma, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$ok) {
            throw new RuntimeException('No se pudo generar la firma digital.');
        }

        return base64_encode($firma);
    }

    public function verificar(string $dato, string $transformado): bool
    {
        $firma = base64_decode($transformado, true);

        if ($firma === false) {
            return false;
        }

        $publicKey = openssl_pkey_get_public((string) file_get_contents($this->publicKeyPath));

        if ($publicKey === false) {
            return false;
        }

        return openssl_verify($dato, $firma, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    public function algoritmo(): string
    {
        return 'RSA-SHA256';
    }

    private function asegurarClaves(string $directory): void
    {
        if (is_file($this->privateKeyPath) && is_file($this->publicKeyPath)) {
            return;
        }

        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('No se pudo crear la carpeta segura de claves.');
        }

        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($resource === false) {
            throw new RuntimeException('No se pudo generar el par de claves OpenSSL.');
        }

        $privatePem = '';
        if (!openssl_pkey_export($resource, $privatePem)) {
            throw new RuntimeException('No se pudo exportar la clave privada.');
        }

        $details = openssl_pkey_get_details($resource);
        $publicPem = is_array($details) ? ($details['key'] ?? '') : '';

        if ($publicPem === '') {
            throw new RuntimeException('No se pudo exportar la clave pública.');
        }

        file_put_contents($this->privateKeyPath, $privatePem, LOCK_EX);
        file_put_contents($this->publicKeyPath, $publicPem, LOCK_EX);
        @chmod($this->privateKeyPath, 0600);
        @chmod($this->publicKeyPath, 0644);
    }
}
