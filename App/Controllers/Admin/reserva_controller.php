<?php

namespace App\Controllers\Admin;

use App\Config\Config;
use App\Core\Controller;
use App\Helpers\Session;
use App\Middleware\Auth;
use App\Models\Reserva;

class ReservaController extends Controller
{
    private const ESTADOS = [
        'Pendiente',
        'Reservado',
        'Prestado',
        'Devuelto',
        'Vencido',
        'Cancelado',
    ];

    private const TIPOS_USUARIO = [
        'Estudiante',
        'Docente',
        'Administrativo',
        'Otro',
    ];

    public function index(): void
    {
        Auth::check();
        Auth::exigirPermiso('reservas.ver');

        $filtros = $this->leerFiltros();
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $reservaModel = new Reserva();
        $reservaModel->marcarVencidas();

        $reservas = $reservaModel->listarAdmin(
            $filtros,
            $porPagina,
            $offset
        );

        $totalRegistros = $reservaModel->contarAdmin($filtros);
        $totalPaginas = max(
            1,
            (int) ceil($totalRegistros / $porPagina)
        );

        $this->view('Admin/Reservas/listar', [
            'reservas' => $reservas,
            'filtros' => $filtros,
            'estados' => self::ESTADOS,
            'tiposUsuario' => self::TIPOS_USUARIO,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros,
        ]);
    }

    public function aprobar(): void
    {
        Auth::check();
        Auth::exigirPermiso('reservas.aprobar');

        $this->procesarAccion(
            fn (Reserva $modelo, int $id): bool =>
                $modelo->aprobar($id),
            'Reserva aprobada correctamente.',
            'La reserva no está pendiente o ya fue procesada.'
        );
    }

    public function prestar(): void
    {
        Auth::check();
        Auth::exigirPermiso('reservas.aprobar');

        $this->procesarAccion(
            fn (Reserva $modelo, int $id): bool =>
                $modelo->prestar($id),
            'Préstamo entregado correctamente.',
            'La reserva no puede marcarse como prestada.'
        );
    }

    public function devolver(): void
    {
        Auth::check();
        Auth::exigirPermiso('reservas.devolver');

        $this->procesarAccion(
            fn (Reserva $modelo, int $id): bool =>
                $modelo->devolverAdmin($id),
            'Devolución registrada correctamente.',
            'La reserva ya fue cerrada o no puede devolverse.'
        );
    }

    public function cancelar(): void
    {
        Auth::check();
        Auth::exigirPermiso('reservas.cancelar');

        $this->procesarAccion(
            fn (Reserva $modelo, int $id): bool =>
                $modelo->cancelar($id),
            'Reserva cancelada correctamente.',
            'La reserva no puede cancelarse en su estado actual.'
        );
    }

    public function reporte(): void
    {
        Auth::check();
        Auth::exigirPermiso('reservas.ver');

        $filtros = $this->leerFiltros();
        $reservaModel = new Reserva();
        $reservaModel->marcarVencidas();
        $reservas = $reservaModel->obtenerReporte($filtros);

        $resumen = [
            'total' => count($reservas),
            'estudiantes' => 0,
            'docentes' => 0,
            'administrativos' => 0,
            'otros' => 0,
        ];

        foreach ($reservas as $reserva) {
            $tipo = $reserva['tipo_usuario'] ?? 'Otro';

            if ($tipo === 'Estudiante') {
                $resumen['estudiantes']++;
            } elseif ($tipo === 'Docente') {
                $resumen['docentes']++;
            } elseif ($tipo === 'Administrativo') {
                $resumen['administrativos']++;
            } else {
                $resumen['otros']++;
            }
        }

        $this->view('Admin/Reservas/reporte', [
            'reservas' => $reservas,
            'filtros' => $filtros,
            'estados' => self::ESTADOS,
            'tiposUsuario' => self::TIPOS_USUARIO,
            'resumen' => $resumen,
        ]);
    }

    public function exportarExcel(): void
    {
        Auth::check();
        Auth::exigirPermiso('reservas.ver');

        $filtros = $this->leerFiltros();
        $reservas = (new Reserva())->obtenerReporte($filtros);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $nombreArchivo =
            'reporte_reservas_' . date('Y-m-d_H-i-s') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header(
            'Content-Disposition: attachment; filename="' .
            $nombreArchivo .
            '"'
        );
        header('Cache-Control: max-age=0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1">';
        echo '<thead><tr>';

        foreach ([
            'ID',
            'Fecha reserva',
            'Fecha vencimiento',
            'Fecha devolución',
            'Días',
            'Estado',
            'Tipo de usuario',
            'Persona',
            'Usuario',
            'Libro',
            'Autor',
            'Categoría',
        ] as $encabezado) {
            echo '<th>' . htmlspecialchars($encabezado) . '</th>';
        }

        echo '</tr></thead><tbody>';

        foreach ($reservas as $reserva) {
            $nombre = trim(
                ($reserva['primer_nombre_persona'] ?? '') . ' ' .
                ($reserva['primer_apellido_persona'] ?? '')
            );

            echo '<tr>';

            foreach ([
                $reserva['id_reserva'] ?? '',
                $reserva['fecha_reserva'] ?? '',
                $reserva['fecha_vencimiento'] ?? '',
                $reserva['fecha_devolucion_real'] ?? '',
                $reserva['dias_reservado'] ?? 0,
                $reserva['estado'] ?? '',
                $reserva['tipo_usuario'] ?? '',
                $nombre,
                $reserva['usuario'] ?? '',
                $reserva['titulo'] ?? '',
                $reserva['autor'] ?? '',
                $reserva['categoria'] ?? '',
            ] as $valor) {
                echo '<td>' . htmlspecialchars(
                    (string) $valor,
                    ENT_QUOTES,
                    'UTF-8'
                ) . '</td>';
            }

            echo '</tr>';
        }

        echo '</tbody></table></body></html>';
        exit();
    }

    private function procesarAccion(
        callable $accion,
        string $mensajeExito,
        string $mensajeNoValido
    ): void {
        $idReserva = filter_input(
            INPUT_POST,
            'id_reserva',
            FILTER_VALIDATE_INT
        );

        if (!$idReserva) {
            Session::flash(
                'error',
                'La reserva seleccionada no es válida.'
            );

            $this->redirigirListado();
        }

        try {
            $modelo = new Reserva();
            $ok = $accion($modelo, (int) $idReserva);

            Session::flash(
                $ok ? 'success' : 'error',
                $ok ? $mensajeExito : $mensajeNoValido
            );
        } catch (\Throwable $e) {
            error_log(
                'Error al procesar reserva: ' .
                $e->getMessage()
            );

            Session::flash(
                'error',
                'No se pudo procesar la reserva.'
            );
        }

        $this->redirigirListado();
    }

    private function leerFiltros(): array
    {
        $estado = trim((string) ($_GET['estado'] ?? ''));
        $tipoUsuario = trim(
            (string) ($_GET['tipo_usuario'] ?? '')
        );

        return [
            'buscar' => trim((string) ($_GET['buscar'] ?? '')),
            'estado' => in_array($estado, self::ESTADOS, true)
                ? $estado
                : '',
            'tipo_usuario' => in_array(
                $tipoUsuario,
                self::TIPOS_USUARIO,
                true
            )
                ? $tipoUsuario
                : '',
            'fecha_inicio' => $this->fechaValida(
                (string) ($_GET['fecha_inicio'] ?? '')
            ),
            'fecha_fin' => $this->fechaValida(
                (string) ($_GET['fecha_fin'] ?? '')
            ),
            'dias_minimos' => $this->enteroNoNegativo(
                $_GET['dias_minimos'] ?? ''
            ),
        ];
    }

    private function fechaValida(string $fecha): string
    {
        $fecha = trim($fecha);

        if ($fecha === '') {
            return '';
        }

        $objeto = \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $fecha
        );

        return $objeto !== false &&
        $objeto->format('Y-m-d') === $fecha
            ? $fecha
            : '';
    }

    private function enteroNoNegativo(mixed $valor): string
    {
        if ($valor === '' || $valor === null) {
            return '';
        }

        $entero = filter_var(
            $valor,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 0,
                ],
            ]
        );

        return $entero === false
            ? ''
            : (string) $entero;
    }

    private function redirigirListado(): never
    {
        header('Location: ' . Config::url('reservas'));
        exit();
    }
}
