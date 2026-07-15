<?php

namespace App\Controllers;

use App\Core\Controller;

class PublicoController extends Controller
{
    public function index(): void
    {
        $bondades = [
            [
                'icono' => 'fa-solid fa-magnifying-glass',
                'titulo' => 'Catálogo en línea',
                'texto' => 'Busca libros por título, autor o categoría y consulta la disponibilidad en tiempo real, sin tener que ir físicamente a la biblioteca.',
            ],
            [
                'icono' => 'fa-solid fa-calendar-check',
                'titulo' => 'Reservas simplificadas',
                'texto' => 'Reserva el libro que necesitas y consulta tus préstamos activos y tu historial desde tu propio perfil.',
            ],
            [
                'icono' => 'fa-solid fa-circle-plus',
                'titulo' => 'Solicitud de libros',
                'texto' => '¿No está en el catálogo? Solicítalo por área de interés y la administración evaluará agregarlo.',
            ],
        ];

        $fortalezas = [
            ['icono' => 'fa-solid fa-shield-halved', 'texto' => 'Acceso seguro con control de intentos de inicio de sesión'],
            ['icono' => 'fa-solid fa-layer-group', 'texto' => 'Arquitectura MVC, mantenible y organizada por capas'],
            ['icono' => 'fa-solid fa-tags', 'texto' => 'Categorías claras: Química, Sistemas, Lógica, Matemática y Estadística'],
            ['icono' => 'fa-solid fa-chart-line', 'texto' => 'Estadísticas de los libros más solicitados por período'],
            ['icono' => 'fa-solid fa-file-excel', 'texto' => 'Reportes exportables en Excel para administración'],
            ['icono' => 'fa-solid fa-mobile-screen', 'texto' => 'Interfaz responsive, usable desde celular o computadora'],
        ];

        $desarrolladores = [
            ['nombre' => 'Anthony Castillo',   'rol' => 'Desarrollador Full Stack'],
            ['nombre' => 'Rubén Domínguez',    'rol' => 'Desarrollador Full Stack'],
            ['nombre' => 'Eduardo González',   'rol' => 'Desarrollador Full Stack'],
            ['nombre' => 'Nicole Rosales',     'rol' => 'Desarrolladora Full Stack'],
            ['nombre' => 'Guillermo Siuki',    'rol' => 'Desarrollador Full Stack'],
        ];

        $this->view('Publico/index', [
            'bondades' => $bondades,
            'fortalezas' => $fortalezas,
            'desarrolladores' => $desarrolladores,
        ]);
    }
}