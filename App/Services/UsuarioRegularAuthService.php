<?php

namespace App\Services;

use App\Helpers\Session;
use App\Models\Usuario;
use App\Models\UsuarioRegular;

class UsuarioRegularAuthService
{
    /**
     * @return array{ok: bool, mensaje: string}
     */
    public function login(string $credencial, string $clave): array
    {
        $modeloRegular = new UsuarioRegular();
        $usuario = $modeloRegular->buscarPorCredencial($credencial);

        if (!$usuario) {
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
                    return [
                        'ok' => false,
                        'mensaje' => 'La cuenta está bloqueada temporalmente. Intenta nuevamente más tarde.',
                    ];
                }

                $modeloRegular->desbloquear($idUsuario);
            } else {
                return [
                    'ok' => false,
                    'mensaje' => 'La cuenta se encuentra bloqueada.',
                ];
            }
        }

        if (!password_verify($clave, (string) $usuario['password_hash'])) {
            $modeloRegular->registrarIntentoFallido($idUsuario);

            return [
                'ok' => false,
                'mensaje' => 'Usuario, correo, CIP o contraseña incorrectos.',
            ];
        }

        $modeloRegular->actualizarLogin($idUsuario);

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
