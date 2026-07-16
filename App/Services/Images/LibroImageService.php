<?php

declare(strict_types=1);

namespace App\Services\Images;

use GdImage;
use RuntimeException;

final class LibroImageService
{
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const MIME_PERMITIDOS = ['image/jpeg', 'image/png', 'image/webp'];

    public function procesar(?array $archivo): ?array
    {
        if ($archivo === null || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Ocurrió un error al subir la imagen.');
        }
        if ((int) ($archivo['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException('La imagen no puede superar los 5 MB.');
        }
        if (!extension_loaded('gd')) {
            throw new RuntimeException('La extensión GD de PHP debe estar habilitada para procesar imágenes.');
        }

        $temporal = (string) ($archivo['tmp_name'] ?? '');
        if (!is_uploaded_file($temporal)) {
            throw new RuntimeException('El archivo de imagen recibido no es válido.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($temporal);
        if (!is_string($mime) || !in_array($mime, self::MIME_PERMITIDOS, true)) {
            throw new RuntimeException('La imagen debe ser JPG, PNG o WEBP.');
        }

        $medidas = getimagesize($temporal);
        if ($medidas === false) {
            throw new RuntimeException('No se pudo leer la imagen subida.');
        }
        [$ancho, $alto] = $medidas;
        $origen = $this->crearRecurso($temporal, $mime);
        if ($origen === false) {
            throw new RuntimeException('No se pudo procesar la imagen subida.');
        }

        $base = dirname(__DIR__, 3) . '/Public/Assets/Uploads/Libros';
        $dirOriginal = $base . '/Originales';
        $dirMiniatura = $base . '/Miniaturas';
        foreach ([$dirOriginal, $dirMiniatura] as $directorio) {
            if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
                imagedestroy($origen);
                throw new RuntimeException('No se pudo crear el directorio de imágenes.');
            }
        }

        $token = bin2hex(random_bytes(16));
        $nombreOriginal = 'libro_' . $token . '.jpg';
        $nombreMiniatura = 'thumb_' . $token . '.jpg';
        try {
            $this->guardarRedimensionada($origen, $ancho, $alto, $dirOriginal . '/' . $nombreOriginal, 1200, 1600);
            $this->guardarRedimensionada($origen, $ancho, $alto, $dirMiniatura . '/' . $nombreMiniatura, 240, 320);
        } finally {
            imagedestroy($origen);
        }

        return [
            'imagen_nombre' => $nombreOriginal,
            'imagen_ruta' => 'Uploads/Libros/Originales/' . $nombreOriginal,
            'thumbnail_nombre' => $nombreMiniatura,
            'thumbnail_ruta' => 'Uploads/Libros/Miniaturas/' . $nombreMiniatura,
        ];
    }

    public function eliminar(array $imagen): void
    {
        $base = dirname(__DIR__, 3) . '/Public/Assets/';
        foreach (['imagen_ruta', 'thumbnail_ruta'] as $campo) {
            $ruta = $imagen[$campo] ?? null;
            if (!is_string($ruta) || $ruta === '') {
                continue;
            }
            $rutaSegura = str_replace(['..', '\\'], ['', '/'], $ruta);
            $archivo = $base . ltrim($rutaSegura, '/');
            if (is_file($archivo)) {
                @unlink($archivo);
            }
        }
    }

    private function crearRecurso(string $ruta, string $mime): GdImage|false
    {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($ruta),
            'image/png' => imagecreatefrompng($ruta),
            'image/webp' => imagecreatefromwebp($ruta),
            default => false,
        };
    }

    private function guardarRedimensionada(GdImage $origen, int $anchoOriginal, int $altoOriginal, string $destino, int $anchoMaximo, int $altoMaximo): void
    {
        $escala = min($anchoMaximo / $anchoOriginal, $altoMaximo / $altoOriginal, 1);
        $anchoNuevo = max(1, (int) round($anchoOriginal * $escala));
        $altoNuevo = max(1, (int) round($altoOriginal * $escala));
        $lienzo = imagecreatetruecolor($anchoNuevo, $altoNuevo);
        if ($lienzo === false) {
            throw new RuntimeException('No se pudo crear la imagen redimensionada.');
        }
        $blanco = imagecolorallocate($lienzo, 255, 255, 255);
        imagefill($lienzo, 0, 0, $blanco);
        imagecopyresampled($lienzo, $origen, 0, 0, 0, 0, $anchoNuevo, $altoNuevo, $anchoOriginal, $altoOriginal);
        $guardada = imagejpeg($lienzo, $destino, 88);
        imagedestroy($lienzo);
        if (!$guardada) {
            throw new RuntimeException('No se pudo guardar la imagen redimensionada.');
        }
    }
}
