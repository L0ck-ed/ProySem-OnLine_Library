<?php

namespace App\Controllers;

use App\Config\Config;
use App\Core\Controller;
use App\Helpers\Sanitizer;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Middleware\UsuarioRegularAuth;
use App\Models\Categoria;
use App\Models\Libro;
use App\Models\PrestamoInterbibliotecario;
use App\Models\ReservaRegular;
use App\Models\SolicitudRegular;
use App\Models\Usuario;
use App\Models\UsuarioRegular;

class PortalController extends Controller
{
    private ?array $perfilActualCache = null;
    private ?array $permisosActualesCache = null;

    public function __construct()
    {
        UsuarioRegularAuth::check();
    }

    public function inicio(): void
    {
        $idUsuario = $this->obtenerIdUsuarioSesion();
        $puedeVerLibros = $this->tienePermisoPortal('libros.ver');
        $reservaModel = new ReservaRegular();

        $stats = [
            'total_libros' => 0,
            'disponibles_ahora' => 0,
            'categorias' => 0,
            'prestamos_activos' => $reservaModel->contarActivasPorUsuario($idUsuario),
        ];

        $categoriasDestacadas = [];
        $librosRecientes = [];
        $topLibrosPorPeriodo = [];
        $categorias = [];

        if ($puedeVerLibros) {
            $libroModel = new Libro();
            $categoriaModel = new Categoria();

            $stats['total_libros'] = $libroModel->contarTotal();
            $stats['disponibles_ahora'] = $libroModel->contarDisponibles();
            $stats['categorias'] = $categoriaModel->contar();

            $iconosPorCategoria = [
                'Sistemas' => 'fa-solid fa-microchip',
                'Matemática' => 'fa-solid fa-square-root-variable',
                'Química' => 'fa-solid fa-flask',
                'Lógica' => 'fa-solid fa-diagram-project',
                'Estadística' => 'fa-solid fa-chart-line',
            ];

            $categoriasListado = $categoriaModel->listar();

            $categoriasDestacadas = array_map(
                static fn(array $categoria): array => [
                    'nombre' => $categoria['nombre'],
                    'icono' => $iconosPorCategoria[$categoria['nombre']] ?? 'fa-solid fa-tag',
                ],
                $categoriasListado,
            );

            $categorias = array_map(
                static fn(array $categoria): string => (string) $categoria['nombre'],
                $categoriasListado,
            );

            $librosRecientes = $libroModel->recientes(4);

            $year = date('Y');
            $periodos = [
                [
                    'nombre' => 'Ene - Abr',
                    'inicio' => $year . '-01-01',
                    'fin' => $year . '-04-30',
                ],
                [
                    'nombre' => 'May - Ago',
                    'inicio' => $year . '-05-01',
                    'fin' => $year . '-08-31',
                ],
                [
                    'nombre' => 'Sep - Dic',
                    'inicio' => $year . '-09-01',
                    'fin' => $year . '-12-31',
                ],
            ];

            foreach ($periodos as $periodo) {
                $libros = $reservaModel->librosMasUsados(
                    $periodo['inicio'],
                    $periodo['fin'],
                    5,
                );

                $topLibrosPorPeriodo[] = [
                    'nombre' => $periodo['nombre'],
                    'libros' => $libros,
                    'labels' => array_column($libros, 'titulo'),
                    'data' => array_map(
                        static fn(array $libro): int => (int) (
                            $libro['total_prestamos']
                            ?? $libro['total_reservas']
                            ?? $libro['total_usos']
                            ?? 0
                        ),
                        $libros,
                    ),
                ];
            }
        }

        $this->view(
            'Client/Home/inicio',
            array_merge(
                $this->datosSesion(),
                [
                    'stats' => $stats,
                    'categoriasDestacadas' => $categoriasDestacadas,
                    'librosRecientes' => $librosRecientes,
                    'topLibrosPorPeriodo' => $topLibrosPorPeriodo,
                    'busqueda' => '',
                    'categorias' => $categorias,
                    'categoriaSeleccionada' => '',
                ],
            ),
        );
    }

    public function catalogo(): void
    {
        $this->exigirPermisoPortal(
            'libros.ver',
            'No tienes permiso para consultar el catálogo de libros.',
        );

        $libroModel = new Libro();
        $categoriaModel = new Categoria();

        $buscar = trim($_GET['buscar'] ?? '');
        $categoria = trim($_GET['categoria'] ?? '');
        $porPagina = 12;
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $offset = ($pagina - 1) * $porPagina;

        $libros = $libroModel->listar($buscar, $categoria, $porPagina, $offset);
        $total = $libroModel->contar($buscar, $categoria);
        $totalPaginas = max(1, (int) ceil($total / $porPagina));

        $categoriasListado = $categoriaModel->listar();
        $categorias = array_map(
            static fn(array $item): string => (string) $item['nombre'],
            $categoriasListado,
        );

        $this->view(
            'Client/Catalogo/index',
            array_merge(
                $this->datosSesion(),
                [
                    'libros' => $libros,
                    'categorias' => $categorias,
                    'busqueda' => $buscar,
                    'categoriaSeleccionada' => $categoria,
                    'paginaActual' => $pagina,
                    'totalPaginas' => $totalPaginas,
                ],
            ),
        );
    }

    public function detalle(): void
    {
        $this->exigirPermisoPortal(
            'libros.ver',
            'No tienes permiso para consultar los libros.',
        );

        $idLibro = (int) ($_GET['id'] ?? 0);
        $libroModel = new Libro();
        $libro = $libroModel->buscarPorId($idLibro);

        $this->view(
            'Client/Catalogo/detalle',
            array_merge(
                $this->datosSesion(),
                [
                    'libro' => $libro,
                    'exitoReserva' => Session::getFlash('exito_reserva'),
                    'errorReserva' => Session::getFlash('error_reserva'),
                ],
            ),
        );
    }

    public function reservar(): void
    {
        $this->exigirPermisoPortal(
            'libros.ver',
            'No tienes permiso para reservar libros.',
        );

        $idLibro = (int) ($_POST['id_libro'] ?? 0);
        $idUsuario = $this->obtenerIdUsuarioSesion();

        if ($idLibro <= 0 || $idUsuario <= 0) {
            Session::flash('error_reserva', 'Datos inválidos para procesar la reserva.');
            $this->redirigirADetalle($idLibro);
        }

        $reservaModel = new ReservaRegular();
        $resultado = $reservaModel->crearPorUsuario($idUsuario, $idLibro);

        if ($resultado['ok']) {
            Session::flash('exito_reserva', $resultado['mensaje']);
        } else {
            Session::flash('error_reserva', $resultado['mensaje']);
        }

        $this->redirigirADetalle($idLibro);
    }

    public function prestamos(): void
    {
        $idUsuario = $this->obtenerIdUsuarioSesion();
        $reservaModel = new ReservaRegular();

        $prestamosActivos = $reservaModel->listarActivasPorUsuario($idUsuario);
        $historial = $reservaModel->listarHistorialPorUsuario($idUsuario);

        $this->view(
            'Client/Prestamos/mis_prestamos',
            array_merge(
                $this->datosSesion(),
                [
                    'prestamosActivos' => $prestamosActivos,
                    'historial' => $historial,
                    'exitoDevolucion' => Session::getFlash('exito_devolucion'),
                    'errorDevolucion' => Session::getFlash('error_devolucion'),
                ],
            ),
        );
    }

    public function devolver(): void
    {
        $idReserva = (int) ($_POST['id_reserva'] ?? 0);
        $idUsuario = $this->obtenerIdUsuarioSesion();

        if ($idReserva <= 0 || $idUsuario <= 0) {
            Session::flash('error_devolucion', 'Datos inválidos.');
            $this->redirigirAPrestamos();
        }

        $reservaModel = new ReservaRegular();
        $resultado = $reservaModel->cerrarPorUsuario($idReserva, $idUsuario);

        if ($resultado['ok']) {
            Session::flash('exito_devolucion', $resultado['mensaje']);
        } else {
            Session::flash('error_devolucion', $resultado['mensaje']);
        }

        $this->redirigirAPrestamos();
    }

    public function solicitudes(): void
    {
        $idUsuario = $this->obtenerIdUsuarioSesion();
        $modelo = new SolicitudRegular();

        $this->view(
            'Client/Solicitudes/solicitar',
            array_merge(
                $this->datosSesion(),
                [
                    'areas' => SolicitudRegular::areasValidas(),
                    'misSolicitudes' => $modelo->listarPorUsuario($idUsuario),
                    'errorSolicitud' => Session::getFlash('error_solicitud'),
                    'exitoSolicitud' => Session::getFlash('exito_solicitud'),
                    'tituloAnterior' => Session::getFlash('titulo_anterior'),
                    'areaAnterior' => Session::getFlash('area_anterior'),
                    'descripcionAnterior' => Session::getFlash('descripcion_anterior'),
                ],
            ),
        );
    }

    public function guardarSolicitud(): void
    {
        $idUsuario = $this->obtenerIdUsuarioSesion();

        $titulo = Sanitizer::text($_POST['titulo_libro'] ?? '');
        $area = Sanitizer::text($_POST['area'] ?? '');
        $descripcion = Sanitizer::text($_POST['descripcion'] ?? '');

        if (!Validator::required($titulo) || !Validator::min($titulo, 3)) {
            $this->guardarDatosSolicitudAnterior($titulo, $area, $descripcion);
            Session::flash('error_solicitud', 'Escribe el título del libro (mínimo 3 caracteres).');
            $this->redirigirASolicitudes();
        }

        if (!in_array($area, SolicitudRegular::areasValidas(), true)) {
            $this->guardarDatosSolicitudAnterior($titulo, $area, $descripcion);
            Session::flash('error_solicitud', 'Selecciona un área válida.');
            $this->redirigirASolicitudes();
        }

        $modelo = new SolicitudRegular();
        $creada = $modelo->crearPorUsuario($idUsuario, $titulo, $area, $descripcion);

        if (!$creada) {
            $this->guardarDatosSolicitudAnterior($titulo, $area, $descripcion);
            Session::flash('error_solicitud', 'No se pudo enviar la solicitud.');
            $this->redirigirASolicitudes();
        }

        Session::flash(
            'exito_solicitud',
            'Tu solicitud fue enviada. La administración la revisará pronto.',
        );

        $this->redirigirASolicitudes();
    }


    public function interbibliotecario(): void
    {
        $this->exigirPermisoPortal(
            'interbibliotecario.ver',
            'No tienes permiso para consultar el catálogo interbibliotecario.',
        );

        $buscar = trim((string) ($_GET['buscar'] ?? ''));
        $idUsuario = $this->obtenerIdUsuarioSesion();
        $modelo = new PrestamoInterbibliotecario();

        $this->view(
            'Client/Interbibliotecario/index',
            array_merge(
                $this->datosSesion(),
                [
                    'catalogo' => $modelo->listarCatalogo($buscar, true),
                    'misSolicitudes' => $modelo->listarPorUsuario($idUsuario),
                    'buscar' => $buscar,
                    'exitoInterbibliotecario' => Session::getFlash('exito_interbibliotecario'),
                    'errorInterbibliotecario' => Session::getFlash('error_interbibliotecario'),
                ],
            ),
        );
    }

    public function solicitarInterbibliotecario(): void
    {
        $this->exigirPermisoPortal(
            'interbibliotecario.crear',
            'No tienes permiso para solicitar préstamos interbibliotecarios.',
        );

        $idLibro = filter_input(INPUT_POST, 'id_libro_externo', FILTER_VALIDATE_INT);
        $observacion = trim((string) ($_POST['observacion'] ?? ''));

        if (!$idLibro || mb_strlen($observacion) > 1000) {
            Session::flash('error_interbibliotecario', 'Los datos de la solicitud no son válidos.');
            $this->redirigirAInterbibliotecario();
        }

        try {
            $resultado = (new PrestamoInterbibliotecario())->solicitar(
                $this->obtenerIdUsuarioSesion(),
                (int) $idLibro,
                $observacion,
            );
            Session::flash(
                $resultado['ok'] ? 'exito_interbibliotecario' : 'error_interbibliotecario',
                $resultado['mensaje'],
            );
        } catch (\Throwable $e) {
            error_log('Solicitud interbibliotecaria: ' . $e->getMessage());
            Session::flash('error_interbibliotecario', 'No se pudo enviar la solicitud.');
        }

        $this->redirigirAInterbibliotecario();
    }

    public function cancelarInterbibliotecario(): void
    {
        $this->exigirPermisoPortal('interbibliotecario.crear');
        $idSolicitud = filter_input(INPUT_POST, 'id_solicitud', FILTER_VALIDATE_INT);
        $ok = $idSolicitud
            ? (new PrestamoInterbibliotecario())->cancelarPorUsuario(
                (int) $idSolicitud,
                $this->obtenerIdUsuarioSesion(),
            )
            : false;

        Session::flash(
            $ok ? 'exito_interbibliotecario' : 'error_interbibliotecario',
            $ok ? 'Solicitud cancelada correctamente.' : 'La solicitud ya no puede cancelarse.',
        );
        $this->redirigirAInterbibliotecario();
    }

    public function perfil(): void
    {
        $idUsuario = $this->obtenerIdUsuarioSesion();
        $perfil = $this->obtenerPerfilActual();

        $reservaModel = new ReservaRegular();
        $solicitudModel = new SolicitudRegular();

        $prestamosActivos = $reservaModel->listarActivasPorUsuario($idUsuario);
        $historial = $reservaModel->listarHistorialPorUsuario($idUsuario);
        $solicitudes = $solicitudModel->listarPorUsuario($idUsuario);

        $statsPerfil = [
            'prestamos_activos' => count($prestamosActivos),
            'prestamos_historial' => count($historial),
            'solicitudes' => count($solicitudes),
        ];

        $this->view(
            'Client/Perfil/perfil',
            array_merge(
                $this->datosSesion(),
                [
                    'perfil' => $perfil,
                    'estudiante' => $perfil,
                    'statsPerfil' => $statsPerfil,
                ],
            ),
        );
    }

    private function datosSesion(): array
    {
        $perfil = $this->obtenerPerfilActual();

        return [
            'nombreEstudiante' => $perfil['nombre_portal']
                ?? Session::get('portal_nombre')
                ?? 'Usuario',
            'nombreUsuarioPortal' => $perfil['nombre_portal'] ?? 'Usuario',
            'cipSesion' => $perfil['cip']
                ?? Session::get('portal_cip')
                ?? '',
            'carreraSesion' => $perfil['detalle_perfil'] ?? 'Perfil no especificado',
            'tipoUsuarioSesion' => $perfil['tipo_usuario']
                ?? Session::get('portal_tipo_usuario')
                ?? 'Usuario',
            'facultadSesion' => $perfil['facultad'] ?? '',
            'perfilRegular' => $perfil,
            'permisosPortal' => $this->obtenerPermisosActuales(),
            'puedeVerLibros' => $this->tienePermisoPortal('libros.ver'),
            'puedeInterbibliotecario' => $this->tienePermisoPortal('interbibliotecario.ver'),
            'errorPermiso' => Session::getFlash('error_permiso'),
        ];
    }

    private function obtenerPerfilActual(): array
    {
        if ($this->perfilActualCache !== null) {
            return $this->perfilActualCache;
        }

        $idUsuario = $this->obtenerIdUsuarioSesion();
        $modeloRegular = new UsuarioRegular();
        $perfil = $modeloRegular->obtenerPorIdUsuario($idUsuario);

        $this->perfilActualCache = is_array($perfil) ? $perfil : [];

        return $this->perfilActualCache;
    }

    private function obtenerPermisosActuales(): array
    {
        if ($this->permisosActualesCache !== null) {
            return $this->permisosActualesCache;
        }

        $idUsuario = $this->obtenerIdUsuarioSesion();

        if ($idUsuario <= 0) {
            $this->permisosActualesCache = [];
            return [];
        }

        $usuarioModel = new Usuario();
        $permisos = $usuarioModel->obtenerPermisosUsuario($idUsuario);

        $this->permisosActualesCache = array_values(array_unique($permisos));
        Session::set('portal_permisos', $this->permisosActualesCache);

        return $this->permisosActualesCache;
    }

    private function tienePermisoPortal(string $permiso): bool
    {
        return in_array($permiso, $this->obtenerPermisosActuales(), true);
    }

    private function exigirPermisoPortal(
        string $permiso,
        string $mensaje = 'No tienes permiso para acceder a esta sección.',
    ): void {
        if ($this->tienePermisoPortal($permiso)) {
            return;
        }

        Session::flash('error_permiso', $mensaje);
        header('Location: ' . Config::url('portal/inicio'));
        exit();
    }

    private function obtenerIdUsuarioSesion(): int
    {
        return (int) Session::get('portal_id_usuario');
    }

    private function guardarDatosSolicitudAnterior(
        string $titulo,
        string $area,
        string $descripcion,
    ): void {
        Session::flash('titulo_anterior', $titulo);
        Session::flash('area_anterior', $area);
        Session::flash('descripcion_anterior', $descripcion);
    }

    private function redirigirADetalle(int $idLibro): never
    {
        header(
            'Location: ' .
            Config::url('portal/catalogo/detalle') .
            '?id=' .
            $idLibro,
        );

        exit();
    }


    private function redirigirAInterbibliotecario(): never
    {
        header('Location: ' . Config::url('portal/interbibliotecario'));
        exit();
    }

    private function redirigirASolicitudes(): never
    {
        header('Location: ' . Config::url('portal/solicitudes'));
        exit();
    }

    private function redirigirAPrestamos(): never
    {
        header('Location: ' . Config::url('portal/prestamos'));
        exit();
    }
}
