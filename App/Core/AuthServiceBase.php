<?php

namespace App\Core;

use App\Core\Contracts\AutenticableRepositorioInterface;
use App\Helpers\Session;
use App\Helpers\Sanitizer;
use App\Helpers\Validator;
use App\Helpers\Logger;
use App\Config\Config;
use App\Services\Crypto\PasswordHashService;

abstract class AuthServiceBase
{
    private const MAX_INTENTOS = 3;

    public function __construct(
        protected AutenticableRepositorioInterface $repositorio
    ) {
    }

    abstract protected function etiquetaLog(): string;

    abstract protected function campoClave(): string;

    abstract protected function urlLoginFallido(): string;

    abstract protected function urlLoginExitoso(): string;

    abstract protected function crearSesion(array $fila): void;

    public function login(string $credencial, string $clave): void
    {
        Session::start();

        $credencial = Sanitizer::text($credencial);

        if (!Validator::required($credencial) || !Validator::required($clave)) {
            Logger::login($this->etiquetaLog() . ':' . $credencial, 'campos_vacios');
            Session::flash('error', 'Debe completar todos los campos.');
            $this->redirigir($this->urlLoginFallido());
        }

        $fila = $this->repositorio->buscarPorCredencial($credencial);

        if (!$fila) {
            Logger::login($this->etiquetaLog() . ':' . $credencial, 'no_existe');
            Session::flash('error', 'Credenciales incorrectas.');
            $this->redirigir($this->urlLoginFallido());
        }

        $id = (int) $fila['id'];

        if (($fila['bloqueado'] ?? 0) == 1) {
            Logger::login($this->etiquetaLog() . ':' . $credencial, 'bloqueado');
            Session::flash('error', 'Este acceso está bloqueado por intentos fallidos. Contacte al administrador.');
            $this->redirigir($this->urlLoginFallido());
        }

        $hash = $fila[$this->campoClave()] ?? '';

        $passwordService = new PasswordHashService();

        if (!is_string($hash) || $hash === '' || !$passwordService->verificar($clave, $hash)) {
            $this->repositorio->aumentarIntentos($id);
            $intentos = (int) ($fila['intentos_fallidos'] ?? 0) + 1;

            Logger::login($this->etiquetaLog() . ':' . $credencial, 'clave_incorrecta');

            if ($intentos >= self::MAX_INTENTOS) {
                $this->repositorio->bloquearUsuario($id);
                Logger::login($this->etiquetaLog() . ':' . $credencial, 'bloqueado_por_intentos');
                Session::flash('error', 'Bloqueado por 3 intentos fallidos. Contacte al administrador.');
            } else {
                Session::flash('error', "Credenciales incorrectas. Intento {$intentos} de " . self::MAX_INTENTOS . '.');
            }

            $this->redirigir($this->urlLoginFallido());
        }

        $this->repositorio->actualizarLogin($id);
        Logger::login($this->etiquetaLog() . ':' . $credencial, 'correcto');

        $this->crearSesion($fila);

        $this->redirigir($this->urlLoginExitoso());
    }

    protected function redirigir(string $ruta): never
    {
        header('Location: ' . Config::url($ruta));
        exit;
    }
}