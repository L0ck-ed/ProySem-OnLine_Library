<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Config;
use App\Core\Controller;
use App\Middleware\Auth;
use App\Models\Carrera;
use App\Models\Estadistica;
use App\Models\Facultad;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Throwable;

class EstadisticaController extends Controller
{
    private const PERIODOS = [
        'mes_actual',
        'ultimos_30',
        'semestre_actual',
        'anio_actual',
        'personalizado',
    ];

    private const TIPOS_USUARIO = ['Todos', 'Estudiante', 'Docente'];
    private const METRICAS = ['uso', 'solicitudes'];
    private const LIMITES = [5, 10, 15, 20];
    private const INTERVALOS = ['auto', 'dia', 'semana', 'mes', 'anio'];

    public function index(): void
    {
        Auth::check();
        Auth::exigirPermiso('reportes.ver');

        [$filtros, $advertencia] = $this->leerFiltros();
        $datos = $this->generarEstadisticas($filtros);
        $facultades = [];
        $carreras = [];

        try {
            $facultades = (new Facultad())->listarActivas();
            $carreras = (new Carrera())->listarActivas();
        } catch (Throwable $e) {
            error_log('Error al cargar filtros académicos: ' . $e->getMessage());
            $advertencia = trim($advertencia . ' Ejecuta la actualización de estructura académica para habilitar los filtros por facultad y carrera.');
        }

        $this->view('Admin/Estadisticas/index', [
            'filtros' => $filtros,
            'advertencia' => $advertencia,
            'datos' => $datos,
            'periodos' => self::PERIODOS,
            'tiposUsuario' => self::TIPOS_USUARIO,
            'metricas' => self::METRICAS,
            'limites' => self::LIMITES,
            'intervalos' => self::INTERVALOS,
            'facultades' => $facultades,
            'carreras' => $carreras,
        ]);
    }

    public function exportarExcel(): void
    {
        Auth::check();
        Auth::exigirPermiso('reportes.exportar_excel');

        [$filtros] = $this->leerFiltros();
        $datos = $this->generarEstadisticas($filtros);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $nombreArchivo = sprintf(
            'estadisticas_libros_%s_%s.xls',
            $filtros['fecha_inicio'],
            $filtros['fecha_fin'],
        );

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<h2>Estadísticas de uso de libros</h2>';
        echo '<p><strong>Período:</strong> ' . $this->escapar($filtros['fecha_inicio']) .
            ' al ' . $this->escapar($filtros['fecha_fin']) . '</p>';
        echo '<p><strong>Población:</strong> ' . $this->escapar($filtros['tipo_usuario']) . '</p>';
        echo '<p><strong>Métrica:</strong> ' . $this->escapar($datos['resumen']['metrica_label'] ?? '') . '</p>';
        echo '<p><strong>Facultad:</strong> ' . $this->escapar($datos['resumen_academico']['facultad_filtro'] ?? 'Todas') . '</p>';
        echo '<p><strong>Carrera:</strong> ' . $this->escapar($datos['resumen_academico']['carrera_filtro'] ?? 'Todas') . '</p>';
        echo '<p><strong>Total:</strong> ' . (int) $datos['resumen']['total_filtrado'] . ' ' .
            $this->escapar($datos['resumen']['unidad'] ?? 'movimientos') . '</p>';

        $this->imprimirTablaExcel(
            'Ranking general de libros por ' . ($datos['resumen']['metrica_label'] ?? 'métrica'),
            [
                'Posición', 'Libro', 'Autor', 'Categoría', 'Estudiantes',
                'Docentes', 'Total', 'Según filtro', 'Participación',
            ],
            array_map(
                static fn (array $libro, int $indice): array => [
                    $indice + 1,
                    $libro['titulo'],
                    $libro['autor'],
                    $libro['categoria'],
                    $libro['usos_estudiantes'],
                    $libro['usos_docentes'],
                    $libro['total_usos'],
                    $libro['valor_filtrado'],
                    number_format((float) $libro['porcentaje'], 2) . '%',
                ],
                $datos['ranking'],
                array_keys($datos['ranking']),
            ),
        );

        $filasAcademicas = array_map(
            static fn (array $fila): array => [
                $fila['facultad'],
                $fila['tipo_unidad'],
                $fila['unidad'],
                $fila['titulo'],
                $fila['categoria'],
                $fila['usos_estudiantes'],
                $fila['usos_docentes'],
                $fila['total_usos'],
                $fila['periodo_pico'],
            ],
            $datos['detalle_academico'],
        );

        $this->imprimirTablaExcel(
            'Demanda por facultad, carrera o departamento',
            [
                'Facultad', 'Tipo de unidad', 'Carrera/Departamento', 'Libro',
                'Categoría', 'Estudiantes', 'Docentes', 'Total', 'Pico de demanda',
            ],
            $filasAcademicas,
        );

        echo '</body></html>';
        exit();
    }

    /** @return array{0: array<string, mixed>, 1: string} */
    private function leerFiltros(): array
    {
        $hoy = new DateTimeImmutable('today');
        $periodo = trim((string) ($_GET['periodo'] ?? 'mes_actual'));
        $tipoUsuario = trim((string) ($_GET['tipo_usuario'] ?? 'Todos'));
        $metrica = trim((string) ($_GET['metrica'] ?? 'uso'));
        $intervalo = trim((string) ($_GET['intervalo'] ?? 'auto'));
        $limite = filter_var($_GET['limite'] ?? 10, FILTER_VALIDATE_INT);
        $idFacultad = filter_var($_GET['id_facultad'] ?? 0, FILTER_VALIDATE_INT);
        $idCarrera = filter_var($_GET['id_carrera'] ?? 0, FILTER_VALIDATE_INT);
        $advertencia = '';

        if (!in_array($periodo, self::PERIODOS, true)) {
            $periodo = 'mes_actual';
        }

        if (!in_array($tipoUsuario, self::TIPOS_USUARIO, true)) {
            $tipoUsuario = 'Todos';
        }

        if (!in_array($metrica, self::METRICAS, true)) {
            $metrica = 'uso';
        }

        if (!in_array($intervalo, self::INTERVALOS, true)) {
            $intervalo = 'auto';
        }

        if (!in_array($limite, self::LIMITES, true)) {
            $limite = 10;
        }

        $idFacultad = $idFacultad && $idFacultad > 0 ? (int) $idFacultad : 0;
        $idCarrera = $idCarrera && $idCarrera > 0 ? (int) $idCarrera : 0;
        [$fechaInicio, $fechaFin] = $this->fechasDelPeriodo($periodo, $hoy);

        if ($periodo === 'personalizado') {
            $inicioSolicitado = $this->fechaValida((string) ($_GET['fecha_inicio'] ?? ''));
            $finSolicitado = $this->fechaValida((string) ($_GET['fecha_fin'] ?? ''));

            if ($inicioSolicitado !== null && $finSolicitado !== null) {
                $fechaInicio = $inicioSolicitado;
                $fechaFin = $finSolicitado;

                if ($fechaInicio > $fechaFin) {
                    [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
                    $advertencia = 'Las fechas estaban invertidas y se ordenaron automáticamente.';
                }
            } else {
                $periodo = 'mes_actual';
                [$fechaInicio, $fechaFin] = $this->fechasDelPeriodo($periodo, $hoy);
                $advertencia = 'El período personalizado no era válido; se mostró el mes actual.';
            }
        }

        return [[
            'periodo' => $periodo,
            'tipo_usuario' => $tipoUsuario,
            'metrica' => $metrica,
            'intervalo' => $intervalo,
            'limite' => (int) $limite,
            'id_facultad' => $idFacultad,
            'id_carrera' => $idCarrera,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
        ], $advertencia];
    }

    /** @return array{0: DateTimeImmutable, 1: DateTimeImmutable} */
    private function fechasDelPeriodo(string $periodo, DateTimeImmutable $hoy): array
    {
        return match ($periodo) {
            'ultimos_30' => [$hoy->sub(new DateInterval('P29D')), $hoy],
            'semestre_actual' => $this->fechasSemestreActual($hoy),
            'anio_actual' => [$hoy->setDate((int) $hoy->format('Y'), 1, 1), $hoy],
            default => [$hoy->modify('first day of this month'), $hoy],
        };
    }

    /** @return array{0: DateTimeImmutable, 1: DateTimeImmutable} */
    private function fechasSemestreActual(DateTimeImmutable $hoy): array
    {
        $mesInicio = (int) $hoy->format('n') <= 6 ? 1 : 7;

        return [$hoy->setDate((int) $hoy->format('Y'), $mesInicio, 1), $hoy];
    }

    private function fechaValida(string $fecha): ?DateTimeImmutable
    {
        $fecha = trim($fecha);

        if ($fecha === '') {
            return null;
        }

        $objeto = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);

        return $objeto !== false && $objeto->format('Y-m-d') === $fecha ? $objeto : null;
    }

    /** @param array<string, mixed> $filtros @return array<string, mixed> */
    private function generarEstadisticas(array $filtros): array
    {
        $fechaInicio = new DateTimeImmutable((string) $filtros['fecha_inicio']);
        $fechaFin = new DateTimeImmutable((string) $filtros['fecha_fin']);
        $fechaFinExclusiva = $fechaFin->add(new DateInterval('P1D'));

        try {
            $movimientos = (new Estadistica())->obtenerMovimientosPorPeriodo(
                $fechaInicio->format('Y-m-d'),
                $fechaFinExclusiva->format('Y-m-d'),
            );
        } catch (Throwable $e) {
            error_log('Error al generar estadísticas: ' . $e->getMessage());
            $movimientos = [];
        }

        $movimientos = array_values(array_filter(
            $movimientos,
            fn (array $movimiento): bool =>
                $this->coincideMetrica($movimiento, (string) $filtros['metrica']) &&
                $this->coincideFiltroAcademico($movimiento, $filtros),
        ));

        $libros = [];
        $totalEstudiantes = 0;
        $totalDocentes = 0;
        $tendenciaCruda = [];
        $facultadesAgrupadas = [];
        $unidades = [];
        $detalleAcademico = [];
        $tendenciaUnidadesCruda = [];
        $tipoSeleccionado = (string) $filtros['tipo_usuario'];
        $metricaSeleccionada = (string) $filtros['metrica'];
        $unidad = $metricaSeleccionada === 'solicitudes' ? 'solicitudes' : 'usos';
        $metricaLabel = $metricaSeleccionada === 'solicitudes'
            ? 'Demanda solicitada (sin canceladas)'
            : 'Uso confirmado';

        foreach ($movimientos as $movimiento) {
            $tipo = (string) ($movimiento['tipo_usuario'] ?? 'Otro');

            if (!in_array($tipo, ['Estudiante', 'Docente'], true)) {
                continue;
            }

            $cantidad = max(1, (int) ($movimiento['cantidad'] ?? 1));
            $idLibro = (int) ($movimiento['id_libro'] ?? 0);

            if ($idLibro <= 0) {
                continue;
            }

            if (!isset($libros[$idLibro])) {
                $libros[$idLibro] = [
                    'id_libro' => $idLibro,
                    'titulo' => (string) ($movimiento['titulo'] ?? 'Libro sin título'),
                    'autor' => (string) ($movimiento['autor'] ?? 'Autor no indicado'),
                    'categoria' => (string) ($movimiento['categoria'] ?? 'Sin categoría'),
                    'usos_estudiantes' => 0,
                    'usos_docentes' => 0,
                    'total_usos' => 0,
                ];
            }

            if ($tipo === 'Estudiante') {
                $libros[$idLibro]['usos_estudiantes'] += $cantidad;
                $totalEstudiantes += $cantidad;
            } else {
                $libros[$idLibro]['usos_docentes'] += $cantidad;
                $totalDocentes += $cantidad;
            }

            $libros[$idLibro]['total_usos'] += $cantidad;
            $fechaMovimiento = substr((string) ($movimiento['fecha_reserva'] ?? ''), 0, 10);

            if ($this->fechaValida($fechaMovimiento) !== null) {
                $tendenciaCruda[$fechaMovimiento] ??= ['estudiantes' => 0, 'docentes' => 0];
                $claveTipo = $tipo === 'Estudiante' ? 'estudiantes' : 'docentes';
                $tendenciaCruda[$fechaMovimiento][$claveTipo] += $cantidad;
            }

            if ($tipoSeleccionado !== 'Todos' && $tipoSeleccionado !== $tipo) {
                continue;
            }

            $datosUnidad = $this->datosUnidadAcademica($movimiento, $tipo);
            $idFacultad = $datosUnidad['id_facultad'];
            $facultad = $datosUnidad['facultad'];
            $claveUnidad = $datosUnidad['clave'];

            $facultadesAgrupadas[$idFacultad] ??= [
                'id_facultad' => $idFacultad,
                'nombre' => $facultad,
                'usos_estudiantes' => 0,
                'usos_docentes' => 0,
                'total_usos' => 0,
            ];
            $facultadesAgrupadas[$idFacultad]['total_usos'] += $cantidad;
            $facultadesAgrupadas[$idFacultad][$tipo === 'Estudiante' ? 'usos_estudiantes' : 'usos_docentes'] += $cantidad;

            $unidades[$claveUnidad] ??= [
                'clave' => $claveUnidad,
                'id_facultad' => $idFacultad,
                'facultad' => $facultad,
                'tipo_unidad' => $datosUnidad['tipo_unidad'],
                'unidad' => $datosUnidad['unidad'],
                'etiqueta' => $datosUnidad['etiqueta'],
                'total_usos' => 0,
                'libros' => [],
            ];
            $unidades[$claveUnidad]['total_usos'] += $cantidad;
            $unidades[$claveUnidad]['libros'][$idLibro] =
                ($unidades[$claveUnidad]['libros'][$idLibro] ?? 0) + $cantidad;

            if ($this->fechaValida($fechaMovimiento) !== null) {
                $tendenciaUnidadesCruda[$fechaMovimiento] ??= [];
                $tendenciaUnidadesCruda[$fechaMovimiento][$claveUnidad] =
                    ($tendenciaUnidadesCruda[$fechaMovimiento][$claveUnidad] ?? 0) + $cantidad;
            }

            $claveDetalle = $claveUnidad . '|' . $idLibro;
            $detalleAcademico[$claveDetalle] ??= [
                'facultad' => $facultad,
                'tipo_unidad' => $datosUnidad['tipo_unidad'],
                'unidad' => $datosUnidad['unidad'],
                'titulo' => (string) ($movimiento['titulo'] ?? ''),
                'autor' => (string) ($movimiento['autor'] ?? ''),
                'categoria' => (string) ($movimiento['categoria'] ?? ''),
                'usos_estudiantes' => 0,
                'usos_docentes' => 0,
                'total_usos' => 0,
                'fechas' => [],
            ];
            $detalleAcademico[$claveDetalle]['total_usos'] += $cantidad;
            $detalleAcademico[$claveDetalle][$tipo === 'Estudiante' ? 'usos_estudiantes' : 'usos_docentes'] += $cantidad;

            if ($this->fechaValida($fechaMovimiento) !== null) {
                $detalleAcademico[$claveDetalle]['fechas'][$fechaMovimiento] =
                    ($detalleAcademico[$claveDetalle]['fechas'][$fechaMovimiento] ?? 0) + $cantidad;
            }
        }

        $libros = array_values($libros);
        $totalGeneral = $totalEstudiantes + $totalDocentes;
        $totalFiltrado = match ($tipoSeleccionado) {
            'Estudiante' => $totalEstudiantes,
            'Docente' => $totalDocentes,
            default => $totalGeneral,
        };

        foreach ($libros as &$libro) {
            $valorFiltrado = match ($tipoSeleccionado) {
                'Estudiante' => (int) $libro['usos_estudiantes'],
                'Docente' => (int) $libro['usos_docentes'],
                default => (int) $libro['total_usos'],
            };
            $libro['valor_filtrado'] = $valorFiltrado;
            $libro['porcentaje'] = $totalFiltrado > 0 ? ($valorFiltrado / $totalFiltrado) * 100 : 0.0;
        }
        unset($libro);

        $rankingCompleto = array_values(array_filter(
            $libros,
            static fn (array $libro): bool => (int) $libro['valor_filtrado'] > 0,
        ));
        $this->ordenarDescendente($rankingCompleto, 'valor_filtrado', 'titulo');
        $ranking = array_slice($rankingCompleto, 0, (int) $filtros['limite']);
        $topEstudiantes = $this->topPorCampo($libros, 'usos_estudiantes', 5);
        $topDocentes = $this->topPorCampo($libros, 'usos_docentes', 5);

        $intervalo = $this->resolverIntervalo(
            (string) $filtros['intervalo'],
            $fechaInicio,
            $fechaFin,
        );
        $tendencia = $this->construirTendencia(
            $fechaInicio,
            $fechaFin,
            $tendenciaCruda,
            $intervalo,
        );

        $facultadesRanking = array_values($facultadesAgrupadas);
        $this->ordenarDescendente($facultadesRanking, 'total_usos', 'nombre');
        $facultadesRanking = array_slice($facultadesRanking, 0, 8);

        $unidadesRanking = array_values($unidades);
        $this->ordenarDescendente($unidadesRanking, 'total_usos', 'etiqueta');
        $unidadesRanking = array_slice($unidadesRanking, 0, 8);
        $matriz = $this->construirMatrizUnidadesLibros($unidadesRanking, $libros, 6);
        $tendenciaAcademica = $this->construirTendenciaUnidades(
            $fechaInicio,
            $fechaFin,
            $tendenciaUnidadesCruda,
            array_slice($unidadesRanking, 0, 5),
            $intervalo,
        );

        $detalleAcademico = array_values($detalleAcademico);

        foreach ($detalleAcademico as &$fila) {
            $fila['periodo_pico'] = $this->periodoPico($fila['fechas'], $intervalo, $unidad);
            unset($fila['fechas']);
        }
        unset($fila);
        $this->ordenarDescendente($detalleAcademico, 'total_usos', 'titulo');
        $detalleAcademico = array_slice($detalleAcademico, 0, 60);

        $facultadFiltro = $this->nombreFiltro($movimientos, 'id_facultad', 'facultad', (int) $filtros['id_facultad'], 'Todas');
        $carreraFiltro = $this->nombreFiltro($movimientos, 'id_carrera', 'carrera', (int) $filtros['id_carrera'], 'Todas');

        return [
            'resumen' => [
                'total_general' => $totalGeneral,
                'total_filtrado' => $totalFiltrado,
                'estudiantes' => $totalEstudiantes,
                'docentes' => $totalDocentes,
                'libros_distintos' => count($rankingCompleto),
                'libro_mas_usado' => $ranking[0]['titulo'] ?? 'Sin datos',
                'metrica' => $metricaSeleccionada,
                'metrica_label' => $metricaLabel,
                'unidad' => $unidad,
            ],
            'resumen_academico' => [
                'facultades_activas' => count($facultadesAgrupadas),
                'unidades_activas' => count($unidades),
                'facultad_mayor_demanda' => $facultadesRanking[0]['nombre'] ?? 'Sin datos',
                'unidad_mayor_demanda' => $unidadesRanking[0]['etiqueta'] ?? 'Sin datos',
                'intervalo' => $intervalo,
                'facultad_filtro' => $facultadFiltro,
                'carrera_filtro' => $carreraFiltro,
            ],
            'ranking' => $ranking,
            'top_estudiantes' => $topEstudiantes,
            'top_docentes' => $topDocentes,
            'tendencia' => $tendencia,
            'detalle_academico' => $detalleAcademico,
            'grafico' => [
                'unidad' => $unidad,
                'metrica' => $metricaSeleccionada,
                'ranking' => [
                    'labels' => array_column($ranking, 'titulo'),
                    'values' => array_map(static fn (array $libro): int => (int) $libro['valor_filtrado'], $ranking),
                ],
                'estudiantes' => [
                    'labels' => array_column($topEstudiantes, 'titulo'),
                    'values' => array_map(static fn (array $libro): int => (int) $libro['usos_estudiantes'], $topEstudiantes),
                ],
                'docentes' => [
                    'labels' => array_column($topDocentes, 'titulo'),
                    'values' => array_map(static fn (array $libro): int => (int) $libro['usos_docentes'], $topDocentes),
                ],
                'distribucion' => [
                    'labels' => ['Estudiantes', 'Docentes'],
                    'values' => [$totalEstudiantes, $totalDocentes],
                ],
                'tendencia' => $tendencia,
                'facultades' => [
                    'labels' => array_column($facultadesRanking, 'nombre'),
                    'values' => array_map(static fn (array $fila): int => (int) $fila['total_usos'], $facultadesRanking),
                ],
                'unidades_libros' => $matriz,
                'tendencia_academica' => $tendenciaAcademica,
            ],
        ];
    }

    /** @param array<string, mixed> $movimiento */
    private function coincideMetrica(array $movimiento, string $metrica): bool
    {
        $estado = (string) ($movimiento['estado'] ?? '');

        if ($metrica === 'solicitudes') {
            return in_array($estado, ['Pendiente', 'Reservado', 'Prestado', 'Devuelto', 'Vencido'], true);
        }

        return in_array($estado, ['Prestado', 'Devuelto', 'Vencido'], true);
    }

    /** @param array<string, mixed> $movimiento @param array<string, mixed> $filtros */
    private function coincideFiltroAcademico(array $movimiento, array $filtros): bool
    {
        $idFacultad = (int) $filtros['id_facultad'];
        $idCarrera = (int) $filtros['id_carrera'];

        if ($idFacultad > 0 && (int) ($movimiento['id_facultad'] ?? 0) !== $idFacultad) {
            return false;
        }

        if ($idCarrera > 0 && (int) ($movimiento['id_carrera'] ?? 0) !== $idCarrera) {
            return false;
        }

        return true;
    }

    /** @param array<string, mixed> $movimiento @return array<string, mixed> */
    private function datosUnidadAcademica(array $movimiento, string $tipo): array
    {
        $idFacultad = (int) ($movimiento['id_facultad'] ?? 0);
        $facultad = trim((string) ($movimiento['facultad'] ?? '')) ?: 'Sin facultad asignada';

        if ($tipo === 'Estudiante') {
            $idUnidad = (int) ($movimiento['id_carrera'] ?? 0);
            $unidad = trim((string) ($movimiento['carrera'] ?? '')) ?: 'Estudiantes sin carrera';
            $tipoUnidad = 'Carrera';
            $prefijo = 'C';
        } else {
            $idUnidad = (int) ($movimiento['id_departamento_docente'] ?? 0);
            $unidad = trim((string) ($movimiento['departamento_docente'] ?? '')) ?: 'Docentes sin departamento';
            $tipoUnidad = 'Departamento docente';
            $prefijo = 'D';
        }

        $clave = $prefijo . ':' . $idFacultad . ':' . $idUnidad . ':' . md5($unidad);

        return [
            'id_facultad' => $idFacultad > 0 ? $idFacultad : -1,
            'facultad' => $facultad,
            'tipo_unidad' => $tipoUnidad,
            'unidad' => $unidad,
            'clave' => $clave,
            'etiqueta' => $unidad . ' · ' . $facultad,
        ];
    }

    /** @param array<int, array<string, mixed>> $libros @return array<int, array<string, mixed>> */
    private function topPorCampo(array $libros, string $campo, int $limite): array
    {
        $resultado = array_values(array_filter(
            $libros,
            static fn (array $libro): bool => (int) ($libro[$campo] ?? 0) > 0,
        ));
        $this->ordenarDescendente($resultado, $campo, 'titulo');

        return array_slice($resultado, 0, $limite);
    }

    /** @param array<int, array<string, mixed>> $filas */
    private function ordenarDescendente(array &$filas, string $campo, string $desempate): void
    {
        usort($filas, static function (array $a, array $b) use ($campo, $desempate): int {
            $comparacion = ((int) ($b[$campo] ?? 0)) <=> ((int) ($a[$campo] ?? 0));

            return $comparacion !== 0
                ? $comparacion
                : strcasecmp((string) ($a[$desempate] ?? ''), (string) ($b[$desempate] ?? ''));
        });
    }

    private function resolverIntervalo(
        string $solicitado,
        DateTimeImmutable $fechaInicio,
        DateTimeImmutable $fechaFin,
    ): string {
        if ($solicitado !== 'auto' && in_array($solicitado, self::INTERVALOS, true)) {
            return $solicitado;
        }

        $dias = (int) $fechaInicio->diff($fechaFin)->format('%a') + 1;

        return $dias <= 62 ? 'dia' : ($dias <= 240 ? 'semana' : ($dias <= 1095 ? 'mes' : 'anio'));
    }

    /**
     * @param array<string, array{estudiantes: int, docentes: int}> $tendenciaCruda
     * @return array<string, mixed>
     */
    private function construirTendencia(
        DateTimeImmutable $fechaInicio,
        DateTimeImmutable $fechaFin,
        array $tendenciaCruda,
        string $intervalo,
    ): array {
        $agrupado = [];

        foreach ($tendenciaCruda as $fecha => $valores) {
            $clave = $this->clavePeriodo(new DateTimeImmutable($fecha), $intervalo);
            $agrupado[$clave] ??= ['estudiantes' => 0, 'docentes' => 0];
            $agrupado[$clave]['estudiantes'] += (int) $valores['estudiantes'];
            $agrupado[$clave]['docentes'] += (int) $valores['docentes'];
        }

        $claves = $this->clavesPeriodo($fechaInicio, $fechaFin, $intervalo);

        return [
            'granularidad' => $intervalo,
            'labels' => array_map(fn (string $clave): string => $this->etiquetaPeriodo($clave, $intervalo), $claves),
            'estudiantes' => array_map(static fn (string $clave): int => (int) ($agrupado[$clave]['estudiantes'] ?? 0), $claves),
            'docentes' => array_map(static fn (string $clave): int => (int) ($agrupado[$clave]['docentes'] ?? 0), $claves),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $unidades
     * @param array<int, array<string, mixed>> $libros
     * @return array<string, mixed>
     */
    private function construirMatrizUnidadesLibros(array $unidades, array $libros, int $limiteLibros): array
    {
        $totalesLibros = [];

        foreach ($unidades as $unidad) {
            foreach ($unidad['libros'] as $idLibro => $cantidad) {
                $totalesLibros[(int) $idLibro] = ($totalesLibros[(int) $idLibro] ?? 0) + (int) $cantidad;
            }
        }

        arsort($totalesLibros);
        $idsLibros = array_slice(array_keys($totalesLibros), 0, $limiteLibros);
        $librosPorId = [];

        foreach ($libros as $libro) {
            $librosPorId[(int) $libro['id_libro']] = $libro;
        }

        $datasets = [];

        foreach ($idsLibros as $idLibro) {
            $datasets[] = [
                'label' => (string) ($librosPorId[$idLibro]['titulo'] ?? 'Libro'),
                'values' => array_map(
                    static fn (array $unidad): int => (int) ($unidad['libros'][$idLibro] ?? 0),
                    $unidades,
                ),
            ];
        }

        return [
            'labels' => array_column($unidades, 'etiqueta'),
            'datasets' => $datasets,
        ];
    }

    /**
     * @param array<string, array<string, int>> $crudo
     * @param array<int, array<string, mixed>> $unidades
     * @return array<string, mixed>
     */
    private function construirTendenciaUnidades(
        DateTimeImmutable $fechaInicio,
        DateTimeImmutable $fechaFin,
        array $crudo,
        array $unidades,
        string $intervalo,
    ): array {
        $clavesUnidades = array_column($unidades, 'clave');
        $agrupado = [];

        foreach ($crudo as $fecha => $valoresUnidad) {
            $clavePeriodo = $this->clavePeriodo(new DateTimeImmutable($fecha), $intervalo);
            $agrupado[$clavePeriodo] ??= [];

            foreach ($clavesUnidades as $claveUnidad) {
                $agrupado[$clavePeriodo][$claveUnidad] =
                    ($agrupado[$clavePeriodo][$claveUnidad] ?? 0) + (int) ($valoresUnidad[$claveUnidad] ?? 0);
            }
        }

        $clavesPeriodo = $this->clavesPeriodo($fechaInicio, $fechaFin, $intervalo);
        $datasets = [];

        foreach ($unidades as $unidad) {
            $datasets[] = [
                'label' => $unidad['etiqueta'],
                'values' => array_map(
                    static fn (string $periodo): int => (int) ($agrupado[$periodo][$unidad['clave']] ?? 0),
                    $clavesPeriodo,
                ),
            ];
        }

        return [
            'granularidad' => $intervalo,
            'labels' => array_map(fn (string $clave): string => $this->etiquetaPeriodo($clave, $intervalo), $clavesPeriodo),
            'datasets' => $datasets,
        ];
    }

    /** @return array<int, string> */
    private function clavesPeriodo(
        DateTimeImmutable $fechaInicio,
        DateTimeImmutable $fechaFin,
        string $intervalo,
    ): array {
        $claves = [];

        if ($intervalo === 'dia') {
            $periodo = new DatePeriod($fechaInicio, new DateInterval('P1D'), $fechaFin->add(new DateInterval('P1D')));
            foreach ($periodo as $fecha) {
                $claves[] = $fecha->format('Y-m-d');
            }
        } elseif ($intervalo === 'semana') {
            $cursor = $fechaInicio->modify('monday this week');
            $ultimo = $fechaFin->modify('monday this week');
            while ($cursor <= $ultimo) {
                $claves[] = $cursor->format('Y-m-d');
                $cursor = $cursor->add(new DateInterval('P7D'));
            }
        } elseif ($intervalo === 'mes') {
            $cursor = $fechaInicio->modify('first day of this month');
            $ultimo = $fechaFin->modify('first day of this month');
            while ($cursor <= $ultimo) {
                $claves[] = $cursor->format('Y-m');
                $cursor = $cursor->add(new DateInterval('P1M'));
            }
        } else {
            for ($anio = (int) $fechaInicio->format('Y'); $anio <= (int) $fechaFin->format('Y'); $anio++) {
                $claves[] = (string) $anio;
            }
        }

        return $claves;
    }

    private function clavePeriodo(DateTimeImmutable $fecha, string $intervalo): string
    {
        return match ($intervalo) {
            'dia' => $fecha->format('Y-m-d'),
            'semana' => $fecha->modify('monday this week')->format('Y-m-d'),
            'mes' => $fecha->format('Y-m'),
            default => $fecha->format('Y'),
        };
    }

    private function etiquetaPeriodo(string $clave, string $intervalo): string
    {
        if ($intervalo === 'anio') {
            return $clave;
        }

        if ($intervalo === 'dia') {
            return (new DateTimeImmutable($clave))->format('d/m');
        }

        if ($intervalo === 'semana') {
            return 'Sem. ' . (new DateTimeImmutable($clave))->format('d/m');
        }

        $meses = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
        [$anio, $mes] = array_map('intval', explode('-', $clave));

        return ($meses[$mes] ?? (string) $mes) . ' ' . $anio;
    }

    /** @param array<string, int> $fechas */
    private function periodoPico(array $fechas, string $intervalo, string $unidad): string
    {
        if (empty($fechas)) {
            return 'Sin datos';
        }

        $agrupado = [];

        foreach ($fechas as $fecha => $cantidad) {
            $clave = $this->clavePeriodo(new DateTimeImmutable($fecha), $intervalo);
            $agrupado[$clave] = ($agrupado[$clave] ?? 0) + (int) $cantidad;
        }

        arsort($agrupado);
        $clave = (string) array_key_first($agrupado);

        return $this->etiquetaPeriodo($clave, $intervalo) . ' (' . (int) $agrupado[$clave] . ' ' . $unidad . ')';
    }

    /** @param array<int, array<string, mixed>> $movimientos */
    private function nombreFiltro(
        array $movimientos,
        string $campoId,
        string $campoNombre,
        int $idSeleccionado,
        string $predeterminado,
    ): string {
        if ($idSeleccionado <= 0) {
            return $predeterminado;
        }

        foreach ($movimientos as $movimiento) {
            if ((int) ($movimiento[$campoId] ?? 0) === $idSeleccionado) {
                return (string) ($movimiento[$campoNombre] ?? $predeterminado);
            }
        }

        return 'Registro #' . $idSeleccionado;
    }

    /** @param array<int, string> $encabezados @param array<int, array<int, mixed>> $filas */
    private function imprimirTablaExcel(string $titulo, array $encabezados, array $filas): void
    {
        echo '<br><h3>' . $this->escapar($titulo) . '</h3><table border="1"><thead><tr>';

        foreach ($encabezados as $encabezado) {
            echo '<th>' . $this->escapar($encabezado) . '</th>';
        }

        echo '</tr></thead><tbody>';

        foreach ($filas as $fila) {
            echo '<tr>';
            foreach ($fila as $valor) {
                echo '<td>' . $this->escapar((string) $valor) . '</td>';
            }
            echo '</tr>';
        }

        if (empty($filas)) {
            echo '<tr><td colspan="' . count($encabezados) . '">No hay datos para el período seleccionado.</td></tr>';
        }

        echo '</tbody></table>';
    }

    private function escapar(string $valor): string
    {
        return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    }
}
