<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Config;
use App\Core\Controller;
use App\Helpers\Session;
use App\Models\MensajeContacto;

class PublicoController extends Controller
{
    public function index(): void
    {
        $this->view('Publico/index', [
            'mensajeExito' => Session::getFlash('contacto_exito'),
            'mensajeError' => Session::getFlash('contacto_error'),
            'datosAnteriores' => Session::getFlash('contacto_old') ?? [],
        ]);
    }

    public function contactar(): void
    {
        $datos = [
            'nombre' => trim((string) ($_POST['nombre'] ?? '')),
            'correo' => trim((string) ($_POST['correo'] ?? '')),
            'asunto' => trim((string) ($_POST['asunto'] ?? '')),
            'mensaje' => trim((string) ($_POST['mensaje'] ?? '')),
        ];

        $ultimoEnvio = (int) (Session::get('contacto_ultimo_envio') ?? 0);
        if ($ultimoEnvio > 0 && time() - $ultimoEnvio < 20) {
            $this->volverContacto('Espera unos segundos antes de enviar otro mensaje.', $datos);
        }

        if (
            $datos['nombre'] === '' || mb_strlen($datos['nombre']) > 150 ||
            !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL) ||
            $datos['asunto'] === '' || mb_strlen($datos['asunto']) > 200 ||
            mb_strlen($datos['mensaje']) < 10 || mb_strlen($datos['mensaje']) > 2000
        ) {
            $this->volverContacto('Completa correctamente todos los campos. El mensaje debe tener entre 10 y 2000 caracteres.', $datos);
        }

        try {
            (new MensajeContacto())->crear($datos);
            Session::set('contacto_ultimo_envio', time());
            Session::flash('contacto_exito', 'Tu mensaje fue enviado correctamente.');
        } catch (\Throwable $e) {
            error_log('Contacto público: ' . $e->getMessage());
            Session::flash('contacto_error', 'No se pudo enviar el mensaje en este momento.');
            Session::flash('contacto_old', $datos);
        }

        header('Location: ' . Config::url('publico') . '#contacto');
        exit();
    }

    private function volverContacto(string $mensaje, array $datos): never
    {
        Session::flash('contacto_error', $mensaje);
        Session::flash('contacto_old', $datos);
        header('Location: ' . Config::url('publico') . '#contacto');
        exit();
    }
}
