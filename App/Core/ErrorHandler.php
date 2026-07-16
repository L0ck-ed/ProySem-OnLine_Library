<?php

declare(strict_types=1);

namespace App\Core;

use App\Helpers\Logger;
use ErrorException;
use Throwable;

final class ErrorHandler
{
    public static function registrar(): void
    {
        set_error_handler(static function (
            int $severity,
            string $message,
            string $file,
            int $line,
        ): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            $error = new ErrorException($message, 0, $severity, $file, $line);

            if (in_array($severity, [E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
                throw $error;
            }

            Logger::error('php_warning', $message, $error);
            return true;
        });

        set_exception_handler([self::class, 'manejarExcepcion']);

        register_shutdown_function(static function (): void {
            $error = error_get_last();

            if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                return;
            }

            self::manejarExcepcion(new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line'],
            ));
        });
    }

    public static function manejarExcepcion(Throwable $e): never
    {
        $status = $e instanceof HttpException ? $e->statusCode() : 500;
        $mensajePublico = match ($status) {
            403 => 'No tienes permiso para realizar esta acción.',
            404 => 'La página solicitada no existe.',
            419 => 'La sesión del formulario expiró. Recarga la página e inténtalo nuevamente.',
            default => 'Ocurrió un error inesperado. El incidente fue registrado.',
        };

        Logger::error(
            modulo: 'aplicacion',
            mensaje: $e->getMessage(),
            excepcion: $e,
        );

        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: text/html; charset=UTF-8');
        }

        $debug = filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOL);
        $detalle = $debug
            ? $e::class . ': ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine()
            : null;

        echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<title>Error ' . $status . '</title><style>body{margin:0;background:#f6efe5;font-family:Arial,sans-serif;color:#3d2719;display:grid;place-items:center;min-height:100vh}.card{width:min(92%,620px);background:white;border-radius:24px;padding:36px;box-shadow:0 18px 50px #5e351525}.code{font-size:4rem;font-weight:900;color:#ad5e29;margin:0}.msg{font-size:1.1rem;line-height:1.6}.back{display:inline-block;margin-top:18px;padding:12px 18px;border-radius:12px;background:#8b4d24;color:white;text-decoration:none;font-weight:700}.detail{margin-top:20px;padding:14px;background:#fff4e6;border-radius:12px;overflow-wrap:anywhere;font-size:.85rem}</style></head><body><main class="card">';
        echo '<p class="code">' . $status . '</p><h1>No pudimos completar la solicitud</h1><p class="msg">' . htmlspecialchars($mensajePublico, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<a class="back" href="javascript:history.back()">Volver</a>';
        if ($detalle !== null) {
            echo '<div class="detail">' . htmlspecialchars($detalle, ENT_QUOTES, 'UTF-8') . '</div>';
        }
        echo '</main></body></html>';
        exit;
    }
}
