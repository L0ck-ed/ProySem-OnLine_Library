<?php

namespace App\Services;

use App\Helpers\Logger;
use App\Helpers\Session;
use App\Models\Usuario;
use App\Models\UsuarioRegular;

class UsuarioRegularAuthService
{
    private const MAX_INTENTOS = 3;

    /**
     * @return array{ok: bool, mensaje: string}
     */
    public function login(string $credencial, string $clave): array
    {
        $modeloRegular = new UsuarioRegular();
        $usuario = $modeloRegular->buscarPorCredencial($credencial);

        if (!$usuario) {
            Logger::login(
                'portal:' . $credencial,
                'usuario_no_existe',
                null,
                'No se encontró una cuenta regular activa con esa credencial.',
            );

            return [
                'ok' => false,
                'mensaje' => 'Usuario, correo, CIP o contraseña incorrectos.',
            ];
        }

        $idUsuario = (int) $usuario['id_usuario'];

        if ((int) ($usuario['bloqueado'] ?? 0) === 1) {
            $bloqueadoHasta = $usuario['bloqueado_hasta'] ?? null;

            if ($bloqueadoHasta) {
                $fechaBloqueo = new \DateTimeImmutable((string) $bloqueadoHasta);
                $ahora = new \DateTimeImmutable();

                if ($fechaBloqueo > $ahora) {
                    Logger::login(
                        'portal:' . $credencial,
                        'usuario_bloqueado',
                        $idUsuario,
                        'La cuenta regular continúa dentro del periodo de bloqueo.',
                    );

                    return [
                        'ok' => false,
                        'mensaje' => 'Cuenta bloqueada por 3 intentos fallidos. Intenta nuevamente después de 15 minutos.',
                    ];
                }

                $modeloRegular->desbloquear($idUsuario);
                $usuario['intentos_fallidos'] = 0;
                $usuario['bloqueado'] = 0;
                $usuario['bloqueado_hasta'] = null;
            } else {
                Logger::login(
                    'portal:' . $credencial,
                    'usuario_bloqueado',
                    $idUsuario,
                    'La cuenta regular está bloqueada sin fecha de desbloqueo.',
                );

                return [
                    'ok' => false,
                    'mensaje' => 'La cuenta se encuentra bloqueada. Contacta al administrador.',
                ];
            }
        }

        if (!password_verify($clave, (string) $usuario['password_hash'])) {
            $intentos = $modeloRegular->registrarIntentoFallido($idUsuario);

            Logger::login(
                'portal:' . $credencial,
                'password_incorrecta',
                $idUsuario,
                "Intento fallido número {$intentos}.",
            );

            if ($intentos >= self::MAX_INTENTOS) {
                Logger::login(
                    'portal:' . $credencial,
                    'bloqueado_por_intentos',
                    $idUsuario,
                    'La cuenta regular fue bloqueada temporalmente después de tres intentos.',
                );

                return [
                    'ok' => false,
                    'mensaje' => 'Cuenta bloqueada por 3 intentos fallidos. Podrás intentarlo nuevamente en 15 minutos.',
                ];
            }

            $restantes = self::MAX_INTENTOS - $intentos;
            $textoIntento = $restantes === 1 ? 'intento' : 'intentos';

            return [
                'ok' => false,
                'mensaje' => "Credenciales incorrectas. Intento {$intentos} de "
                    . self::MAX_INTENTOS
                    . ". Te quedan {$restantes} {$textoIntento}.",
            ];
        }

        $modeloRegular->actualizarLogin($idUsuario);

        Logger::login(
            'portal:' . $credencial,
            'correcto',
            $idUsuario,
            'Inicio de sesión regular exitoso.',
        );

        $usuarioModel = new Usuario();
        $permisos = $usuarioModel->obtenerPermisosUsuario($idUsuario);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        Session::set('portal_autenticado', true);
        Session::set('portal_id_usuario', $idUsuario);
        Session::set('portal_tipo_usuario', $usuario['tipo_usuario']);
        Session::set('portal_nombre', $usuario['nombre_portal']);
        Session::set('portal_cip', $usuario['cip']);
        Session::set('portal_permisos', array_values(array_unique($permisos)));

        // Compatibilidad temporal con vistas o código antiguo.
        Session::set('id_estudiante', (int) ($usuario['id_estudiante'] ?? 0));
        Session::set('id_profesor', (int) ($usuario['id_profesor'] ?? 0));
        Session::set('nombre_estudiante', $usuario['nombre_portal']);
        Session::set('cip', $usuario['cip']);

        return [
            'ok' => true,
            'mensaje' => 'Inicio de sesión correcto.',
        ];
    }
}
