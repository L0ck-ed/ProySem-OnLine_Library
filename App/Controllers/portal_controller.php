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

class PortalController extends Controller
{
    public function __construct()
    {
        EstudianteAuth::check();
    }

    private function datosSesion(): array
    {
        return [
            'nombreEstudiante' => Session::get('nombre_estudiante') ?? 'Estudiante',
            'cipSesion' => Session::get('cip') ?? '',
            'carreraSesion' => Session::get('carrera_estudiante') ?? '',
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

        $this->view('Client/Home/inicio', array_merge($this->datosSesion(), [
            'stats' => $stats,
            'categoriasDestacadas' => $categoriasDestacadas,
            'librosRecientes' => $librosRecientes,
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

        $this->view('Client/Catalogo/index', array_merge($this->datosSesion(), [
            'libros' => $libros,
            'categorias' => $categorias,
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
        ]));
    }

    public function prestamos(): void
    {
        $this->view('Client/Prestamos/mis_prestamos', $this->datosSesion());
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

    private function redirigirASolicitudes(): never
    {
        header('Location: ' . \App\Config\Config::url('portal/solicitudes'));
        exit;
    }

    public function perfil(): void
    {
        $this->view('Client/Perfil/perfil', $this->datosSesion());
    }
}