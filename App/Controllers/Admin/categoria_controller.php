<?php

namespace App\Controllers\Admin;

use App\Config\Config;
use App\Core\Controller;
use App\Helpers\Session;
use App\Middleware\Auth;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function index(): void
    {
        Auth::check();
        Auth::exigirPermiso('categorias.ver');

        $buscar = trim($_GET['buscar'] ?? '');

        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));

        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $categoriaModel = new Categoria();

        $categorias = $categoriaModel->listar($buscar, $porPagina, $offset);

        $totalRegistros = $categoriaModel->contar($buscar);

        $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));

        $this->view('Admin/Categorias/listar', [
            'categorias' => $categorias,
            'buscar' => $buscar,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros,
        ]);
    }

    public function crear(): void
    {
        Auth::check();
        Auth::exigirPermiso('categorias.crear');

        $this->view('Admin/Categorias/formulario', [
            'modo' => 'crear',
            'categoria' => [],
        ]);
    }

    public function guardar(): void
    {
        Auth::check();
        Auth::exigirPermiso('categorias.crear');

        $datos = $this->leerDatosFormulario();

        $error = $this->validarDatos($datos);

        if ($error !== null) {
            $this->regresarConError($error, $datos, 'categorias/crear');
        }

        $categoriaModel = new Categoria();

        if ($categoriaModel->buscarPorNombre($datos['nombre'])) {
            $this->regresarConError(
                'Ya existe una categoría con ese nombre.',
                $datos,
                'categorias/crear',
            );
        }

        try {
            $categoriaModel->crear([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'],
            ]);

            Session::flash('success', 'Categoría registrada correctamente.');

            header('Location: ' . Config::url('categorias'));

            exit();
        } catch (\Throwable $e) {
            error_log('Error al registrar categoría: ' . $e->getMessage());

            $this->regresarConError(
                'No se pudo registrar la categoría.',
                $datos,
                'categorias/crear',
            );
        }
    }

    public function editar(): void
    {
        Auth::check();
        Auth::exigirPermiso('categorias.editar');

        $idCategoria = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$idCategoria) {
            Session::flash('error', 'La categoría seleccionada no es válida.');

            header('Location: ' . Config::url('categorias'));

            exit();
        }

        $categoriaModel = new Categoria();

        $categoria = $categoriaModel->buscarPorId((int) $idCategoria);

        if (!$categoria) {
            Session::flash('error', 'La categoría seleccionada no existe.');

            header('Location: ' . Config::url('categorias'));

            exit();
        }

        $this->view('Admin/Categorias/formulario', [
            'modo' => 'editar',
            'categoria' => $categoria,
        ]);
    }

    public function actualizar(): void
    {
        Auth::check();
        Auth::exigirPermiso('categorias.editar');

        $datos = $this->leerDatosFormulario();

        $datos['id_categoria'] = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT);

        if (!$datos['id_categoria']) {
            Session::flash('error', 'La categoría seleccionada no es válida.');

            header('Location: ' . Config::url('categorias'));

            exit();
        }

        $rutaEditar = 'categorias/editar?id=' . (int) $datos['id_categoria'];

        $error = $this->validarDatos($datos);

        if ($error !== null) {
            $this->regresarConError($error, $datos, $rutaEditar);
        }

        $categoriaModel = new Categoria();

        $categoriaActual = $categoriaModel->buscarPorId((int) $datos['id_categoria']);

        if (!$categoriaActual) {
            Session::flash('error', 'La categoría seleccionada no existe.');

            header('Location: ' . Config::url('categorias'));

            exit();
        }

        $categoriaConNombre = $categoriaModel->buscarPorNombre($datos['nombre']);

        if (
            $categoriaConNombre &&
            (int) $categoriaConNombre['id_categoria'] !== (int) $datos['id_categoria']
        ) {
            $this->regresarConError(
                'Ya existe otra categoría con ese nombre.',
                $datos,
                $rutaEditar,
            );
        }

        try {
            $categoriaModel->actualizar([
                'id_categoria' => (int) $datos['id_categoria'],
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'],
            ]);

            Session::flash('success', 'Categoría actualizada correctamente.');

            header('Location: ' . Config::url('categorias'));

            exit();
        } catch (\Throwable $e) {
            error_log('Error al actualizar categoría: ' . $e->getMessage());

            $this->regresarConError('No se pudo actualizar la categoría.', $datos, $rutaEditar);
        }
    }

    public function cambiarEstado(): void
    {
        Auth::check();
        Auth::exigirPermiso('categorias.eliminar');

        $idCategoria = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT);

        $estado = $_POST['estado'] ?? null;

        if (!$idCategoria || !in_array($estado, ['0', '1'], true)) {
            Session::flash('error', 'Los datos recibidos no son válidos.');

            header('Location: ' . Config::url('categorias'));

            exit();
        }

        $categoriaModel = new Categoria();

        $categoria = $categoriaModel->buscarPorId((int) $idCategoria);

        if (!$categoria) {
            Session::flash('error', 'La categoría seleccionada no existe.');

            header('Location: ' . Config::url('categorias'));

            exit();
        }

        try {
            $nuevoEstado = (int) $estado;

            $categoriaModel->cambiarEstado((int) $idCategoria, $nuevoEstado);

            Session::flash(
                'success',
                $nuevoEstado === 1
                    ? 'Categoría activada correctamente.'
                    : 'Categoría desactivada correctamente.',
            );
        } catch (\Throwable $e) {
            error_log('Error al cambiar estado de categoría: ' . $e->getMessage());

            Session::flash('error', 'No se pudo cambiar el estado de la categoría.');
        }

        header('Location: ' . Config::url('categorias'));

        exit();
    }

    private function leerDatosFormulario(): array
    {
        return [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
        ];
    }

    private function validarDatos(array $datos): ?string
    {
        if ($datos['nombre'] === '') {
            return 'El nombre de la categoría es obligatorio.';
        }

        if (mb_strlen($datos['nombre']) > 100) {
            return 'El nombre no puede superar los 100 caracteres.';
        }

        if (mb_strlen($datos['descripcion']) > 255) {
            return 'La descripción no puede superar los 255 caracteres.';
        }

        return null;
    }

    private function regresarConError(string $mensaje, array $datos, string $ruta): void
    {
        Session::flash('error', $mensaje);

        Session::flash('old_categoria', $datos);

        header('Location: ' . Config::url($ruta));

        exit();
    }
}
