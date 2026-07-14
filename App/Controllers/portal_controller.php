<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Middleware\EstudianteAuth;
use App\Models\Libro;
use App\Models\Categoria;

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
        $this->view('Client/Solicitudes/solicitar', $this->datosSesion());
    }

    public function perfil(): void
    {
        $this->view('Client/Perfil/perfil', $this->datosSesion());
    }
}