<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Helpers\Sanitizer;
use App\Helpers\Validator;
use App\Middleware\EstudianteAuth;
use App\Models\Libro;
use App\Models\Categoria;
use App\Models\Solicitud;
use App\Models\Reserva;
use App\Models\Estudiante;

class PortalController extends Controller
{
    public function __construct()
    {
        EstudianteAuth::check();
    }

    private function datosSesion(): array
    {
        $idEstudiante = (int) Session::get('id_estudiante');

        // Obtener datos del estudiante desde la BD (incluye carrera)
        $estudianteModel = new Estudiante();
        $estudiante = $estudianteModel->obtenerPorId($idEstudiante);

        return [
            'nombreEstudiante' => Session::get('nombre_estudiante') ?? 'Estudiante',
            'cipSesion' => Session::get('cip') ?? '',
            'carreraSesion' => $estudiante['carrera'] ?? 'Carrera no especificada',
        ];
    }

    public function inicio(): void
    {
        $libroModel = new Libro();
        $categoriaModel = new Categoria();

        $stats = [
            'total_libros' => $libroModel->contarTotal(),
            'disponibles_ahora' => $libroModel->contarDisponibles(),
            'categorias' => $categoriaModel->contar(),
            'prestamos_activos' => 0,
        ];

        $iconosPorCategoria = [
            'Sistemas' => 'fa-solid fa-microchip',
            'Matemática' => 'fa-solid fa-square-root-variable',
            'Química' => 'fa-solid fa-flask',
            'Lógica' => 'fa-solid fa-diagram-project',
            'Estadística' => 'fa-solid fa-chart-line',
        ];

        $categoriasDestacadas = array_map(
            fn($cat) => [
                'nombre' => $cat['nombre'],
                'icono' => $iconosPorCategoria[$cat['nombre']] ?? 'fa-solid fa-tag',
            ],
            $categoriaModel->listar()
        );

        $librosRecientes = $libroModel->recientes(4);

        // Obtener categorías para el select del home
        $categorias = array_map(fn($c) => $c['nombre'], $categoriaModel->listar());

        $this->view('Client/Home/inicio', array_merge($this->datosSesion(), [
            'stats' => $stats,
            'categoriasDestacadas' => $categoriasDestacadas,
            'librosRecientes' => $librosRecientes,
            'busqueda' => '',
            'categorias' => $categorias,
            'categoriaSeleccionada' => '',
        ]));
    }


    public function catalogo(): void
    {
        $libroModel = new Libro();
        $categoriaModel = new Categoria();

        $buscar = trim($_GET['buscar'] ?? '');
        $categoria = trim($_GET['categoria'] ?? '');
        $porPagina = 12;
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $offset = ($pagina - 1) * $porPagina;

        $libros = $libroModel->listar($buscar, $categoria, $porPagina, $offset);
        $total = $libroModel->contar($buscar, $categoria);
        $totalPaginas = (int) ceil($total / $porPagina);

        $categorias = array_map(fn($c) => $c['nombre'], $categoriaModel->listar());


        // Pasar datos a la vista
        $this->view('Client/Catalogo/index', array_merge($this->datosSesion(), [
            'libros' => $libros,
            'categorias' => $categorias,
            'busqueda' => $buscar,
            'categoriaSeleccionada' => $categoria,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas,
        ]));
    }

    public function detalle(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $libro = (new Libro())->buscarPorId($id);
        $this->view('Client/Catalogo/detalle', array_merge($this->datosSesion(), [
            'libro' => $libro,
            'exitoReserva' => Session::getFlash('exito_reserva'),
            'errorReserva' => Session::getFlash('error_reserva'),
        ]));
    }

    public function reservar(): void
    {
        $idLibro = (int) ($_POST['id_libro'] ?? 0);
        $idEstudiante = (int) Session::get('id_estudiante');

        if ($idLibro <= 0 || $idEstudiante <= 0) {
            Session::flash('error_reserva', 'Datos inválidos para procesar la reserva.');
            $this->redirigirADetalle($idLibro);
        }

        $reservaModel = new Reserva();
        $resultado = $reservaModel->crear($idEstudiante, $idLibro);

        if ($resultado['ok']) {
            Session::flash('exito_reserva', $resultado['mensaje']);
        } else {
            Session::flash('error_reserva', $resultado['mensaje']);
        }

        $this->redirigirADetalle($idLibro);
    }

    public function prestamos(): void
    {
        $idEstudiante = (int) Session::get('id_estudiante');
        $reservaModel = new Reserva();

        $prestamosActivos = $reservaModel->listarActivasPorEstudiante($idEstudiante);
        $historial = $reservaModel->listarHistorialPorEstudiante($idEstudiante);

        $this->view('Client/Prestamos/mis_prestamos', array_merge($this->datosSesion(), [
            'prestamosActivos' => $prestamosActivos,
            'historial' => $historial,
        ]));
    }

    public function devolver(): void
    {
        $idReserva = (int) ($_POST['id_reserva'] ?? 0);
        $idEstudiante = (int) Session::get('id_estudiante');

        if ($idReserva <= 0 || $idEstudiante <= 0) {
            Session::flash('error_devolucion', 'Datos inválidos.');
            $this->redirigirAPrestamos();
        }

        $reservaModel = new Reserva();
        $ok = $reservaModel->devolver($idReserva, $idEstudiante);

        if ($ok) {
            Session::flash('exito_devolucion', 'Préstamo devuelto correctamente.');
        } else {
            Session::flash('error_devolucion', 'No se pudo procesar la devolución. Verifica que el préstamo exista y esté activo.');
        }

        $this->redirigirAPrestamos();
    }

    public function solicitudes(): void
    {
        $idEstudiante = (int) Session::get('id_estudiante');
        $modelo = new Solicitud();

        $this->view('Client/Solicitudes/solicitar', array_merge($this->datosSesion(), [
            'areas' => Solicitud::areasValidas(),
            'misSolicitudes' => $modelo->listarPorEstudiante($idEstudiante),
            'errorSolicitud' => Session::getFlash('error_solicitud'),
            'exitoSolicitud' => Session::getFlash('exito_solicitud'),
            'tituloAnterior' => Session::getFlash('titulo_anterior'),
            'areaAnterior' => Session::getFlash('area_anterior'),
            'descripcionAnterior' => Session::getFlash('descripcion_anterior'),
        ]));
    }

    public function guardarSolicitud(): void
    {
        $idEstudiante = (int) Session::get('id_estudiante');

        $titulo = Sanitizer::text($_POST['titulo_libro'] ?? '');
        $area = Sanitizer::text($_POST['area'] ?? '');
        $descripcion = Sanitizer::text($_POST['descripcion'] ?? '');

        if (!Validator::required($titulo) || !Validator::min($titulo, 3)) {
            Session::flash('error_solicitud', 'Escribe el título del libro (mínimo 3 caracteres).');
            Session::flash('titulo_anterior', $titulo);
            Session::flash('area_anterior', $area);
            Session::flash('descripcion_anterior', $descripcion);
            $this->redirigirASolicitudes();
        }

        if (!in_array($area, Solicitud::areasValidas(), true)) {
            Session::flash('error_solicitud', 'Selecciona un área válida.');
            Session::flash('titulo_anterior', $titulo);
            Session::flash('area_anterior', $area);
            Session::flash('descripcion_anterior', $descripcion);
            $this->redirigirASolicitudes();
        }

        $modelo = new Solicitud();
        $modelo->crear($idEstudiante, $titulo, $area, $descripcion);

        Session::flash('exito_solicitud', 'Tu solicitud fue enviada. La administración la revisará pronto.');
        $this->redirigirASolicitudes();
    }

    public function perfil(): void
    {
        $idEstudiante = (int) Session::get('id_estudiante');
        $estudianteModel = new Estudiante();
        $estudiante = $estudianteModel->obtenerPorId($idEstudiante);

        // Estadísticas de préstamos y solicitudes
        $reservaModel = new Reserva();
        $prestamosActivos = $reservaModel->listarActivasPorEstudiante($idEstudiante);
        $historial = $reservaModel->listarHistorialPorEstudiante($idEstudiante);
        $solicitudModel = new Solicitud();
        $solicitudes = $solicitudModel->listarPorEstudiante($idEstudiante);

        $statsPerfil = [
            'prestamos_activos' => count($prestamosActivos),
            'prestamos_historial' => count($historial),
            'solicitudes' => count($solicitudes),
        ];

        $this->view('Client/Perfil/perfil', array_merge($this->datosSesion(), [
            'estudiante' => $estudiante,
            'statsPerfil' => $statsPerfil,
        ]));
    }

    private function redirigirADetalle(int $idLibro): never
    {
        header('Location: ' . \App\Config\Config::url('portal/catalogo/detalle') . '?id=' . $idLibro);
        exit;
    }

    private function redirigirASolicitudes(): never
    {
        header('Location: ' . \App\Config\Config::url('portal/solicitudes'));
        exit;
    }

    private function redirigirAPrestamos(): never
    {
        header('Location: ' . \App\Config\Config::url('portal/prestamos'));
        exit;
    }
}