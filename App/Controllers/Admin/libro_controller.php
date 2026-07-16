<?php

namespace App\Controllers\Admin;

use App\Config\Config;
use App\Core\Controller;
use App\Helpers\Session;
use App\Middleware\Auth;
use App\Models\Categoria;
use App\Models\Libro;
use App\Models\Tema;
use App\Services\Crypto\RegistroFirmaService;
use App\Services\Images\LibroImageService;

class LibroController extends Controller
{
    private const MAX_IMAGEN_BYTES = 5 * 1024 * 1024;
    private ?RegistroFirmaService $firmaService = null;

    public function index(): void
    {
        Auth::check();
        Auth::exigirPermiso('libros.ver');

        $buscar = trim($_GET['buscar'] ?? '');
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $libroModel = new Libro();

        $libros = $libroModel->listarAdmin(
            $buscar,
            $porPagina,
            $offset
        );

        foreach ($libros as &$libro) {
            $libro['integridad'] = $this->verificarIntegridadLibro(
                $libroModel,
                (int) $libro['id_libro']
            );
        }
        unset($libro);

        $totalRegistros = $libroModel->contarAdmin($buscar);
        $totalPaginas = max(
            1,
            (int) ceil($totalRegistros / $porPagina)
        );

        $this->view('Admin/Libros/listar', [
            'libros' => $libros,
            'buscar' => $buscar,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros,
        ]);
    }

    public function crear(): void
    {
        Auth::check();
        Auth::exigirPermiso('libros.crear');

        $categoriaModel = new Categoria();
        $temaModel = new Tema();

        $this->view('Admin/Libros/formulario', [
            'modo' => 'crear',
            'libro' => [],
            'categorias' => $categoriaModel->listarActivas(),
            'temas' => $temaModel->listarActivos(),
            'temasSeleccionados' => [],
        ]);
    }

    public function guardar(): void
    {
        Auth::check();
        Auth::exigirPermiso('libros.crear');

        $datos = $this->leerDatosFormulario();
        $idsTemas = $this->leerIdsTemas();
        $datos['id_temas'] = $idsTemas;

        $error = $this->validarDatos($datos, $idsTemas);

        if ($error !== null) {
            $this->regresarConError(
                $error,
                $datos,
                'libros/crear'
            );
        }

        $libroModel = new Libro();

        if ($datos['isbn'] !== '') {
            $libroConIsbn = $libroModel->buscarPorIsbn($datos['isbn']);

            if ($libroConIsbn) {
                $this->regresarConError(
                    'Ya existe un libro registrado con ese ISBN.',
                    $datos,
                    'libros/crear'
                );
            }
        }

        $imagen = null;

        try {
            $imagen = $this->procesarImagen($_FILES['imagen'] ?? null);

            $idLibro = $libroModel->crear(
                array_merge($datos, $this->datosImagen($imagen)),
                $idsTemas
            );
            $this->firmarLibroSeguro($libroModel, $idLibro);

            Session::flash(
                'success',
                'Libro registrado correctamente.'
            );

            header('Location: ' . Config::url('libros'));
            exit();
        } catch (\Throwable $e) {
            if ($imagen !== null) {
                $this->eliminarArchivosImagen($imagen);
            }

            error_log(
                'Error al registrar libro: ' .
                $e->getMessage()
            );

            $mensaje = $e instanceof \RuntimeException
                ? $e->getMessage()
                : 'No se pudo registrar el libro.';

            $this->regresarConError(
                $mensaje,
                $datos,
                'libros/crear'
            );
        }
    }

    public function editar(): void
    {
        Auth::check();
        Auth::exigirPermiso('libros.editar');

        $idLibro = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$idLibro) {
            Session::flash(
                'error',
                'El libro seleccionado no es válido.'
            );

            header('Location: ' . Config::url('libros'));
            exit();
        }

        $libroModel = new Libro();
        $libro = $libroModel->buscarPorId((int) $idLibro);

        if (!$libro) {
            Session::flash(
                'error',
                'El libro seleccionado no existe.'
            );

            header('Location: ' . Config::url('libros'));
            exit();
        }

        $categoriaModel = new Categoria();
        $temaModel = new Tema();

        $this->view('Admin/Libros/formulario', [
            'modo' => 'editar',
            'libro' => $libro,
            'categorias' => $categoriaModel->listarActivas(),
            'temas' => $temaModel->listarActivos(),
            'temasSeleccionados' =>
                $libroModel->obtenerTemasLibro((int) $idLibro),
        ]);
    }

    public function actualizar(): void
    {
        Auth::check();
        Auth::exigirPermiso('libros.editar');

        $datos = $this->leerDatosFormulario();
        $datos['id_libro'] = filter_input(
            INPUT_POST,
            'id_libro',
            FILTER_VALIDATE_INT
        );

        $idsTemas = $this->leerIdsTemas();
        $datos['id_temas'] = $idsTemas;

        if (!$datos['id_libro']) {
            Session::flash(
                'error',
                'El libro seleccionado no es válido.'
            );

            header('Location: ' . Config::url('libros'));
            exit();
        }

        $rutaEditar = 'libros/editar?id=' . (int) $datos['id_libro'];
        $error = $this->validarDatos($datos, $idsTemas);

        if ($error !== null) {
            $this->regresarConError(
                $error,
                $datos,
                $rutaEditar
            );
        }

        $libroModel = new Libro();
        $libroActual = $libroModel->buscarPorId(
            (int) $datos['id_libro']
        );

        if (!$libroActual) {
            Session::flash(
                'error',
                'El libro seleccionado no existe.'
            );

            header('Location: ' . Config::url('libros'));
            exit();
        }

        if ($datos['isbn'] !== '') {
            $libroConIsbn = $libroModel->buscarPorIsbn($datos['isbn']);

            if (
                $libroConIsbn &&
                (int) $libroConIsbn['id_libro'] !==
                (int) $datos['id_libro']
            ) {
                $this->regresarConError(
                    'Ya existe otro libro con ese ISBN.',
                    $datos,
                    $rutaEditar
                );
            }
        }

        $imagenNueva = null;

        try {
            $imagenNueva = $this->procesarImagen(
                $_FILES['imagen'] ?? null
            );

            $datosImagen = $imagenNueva !== null
                ? $this->datosImagen($imagenNueva)
                : [
                    'imagen_nombre' => $libroActual['imagen_nombre'],
                    'imagen_ruta' => $libroActual['imagen_ruta'],
                    'thumbnail_nombre' => $libroActual['thumbnail_nombre'],
                    'thumbnail_ruta' => $libroActual['thumbnail_ruta'],
                ];

            $libroModel->actualizar(
                array_merge($datos, $datosImagen),
                $idsTemas
            );
            $this->firmarLibroSeguro($libroModel, (int) $datos['id_libro']);

            if ($imagenNueva !== null) {
                $this->eliminarArchivosImagen([
                    'imagen_ruta' => $libroActual['imagen_ruta'],
                    'thumbnail_ruta' => $libroActual['thumbnail_ruta'],
                ]);
            }

            Session::flash(
                'success',
                'Libro actualizado correctamente.'
            );

            header('Location: ' . Config::url('libros'));
            exit();
        } catch (\Throwable $e) {
            if ($imagenNueva !== null) {
                $this->eliminarArchivosImagen($imagenNueva);
            }

            error_log(
                'Error al actualizar libro: ' .
                $e->getMessage()
            );

            $mensaje = $e instanceof \RuntimeException
                ? $e->getMessage()
                : 'No se pudo actualizar el libro.';

            $this->regresarConError(
                $mensaje,
                $datos,
                $rutaEditar
            );
        }
    }


    public function exportarExcel(): void
    {
        Auth::check();
        Auth::exigirPermiso('reportes.exportar_excel');

        $buscar = trim((string) ($_GET['buscar'] ?? ''));
        $libroModel = new Libro();
        $libros = $libroModel->listarReporteAdmin($buscar);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $nombre = 'inventario_libros_' . date('Y-m-d_H-i-s') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        header('Cache-Control: max-age=0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<h2>Inventario de libros</h2>';
        echo '<p>Filtro aplicado: ' . htmlspecialchars($buscar !== '' ? $buscar : 'Todos', ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<table border="1"><thead><tr>';
        foreach (['ID','Título','Autor','ISBN','Editorial','Año','Categoría','Temas','Costo','Totales','Disponibles','Disponibilidad','Ubicación','Estado','Integridad'] as $titulo) {
            echo '<th>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($libros as $libro) {
            $fila = [
                $libro['id_libro'] ?? '', $libro['titulo'] ?? '', $libro['autor'] ?? '',
                $libro['isbn'] ?? '', $libro['editorial'] ?? '', $libro['anio_publicacion'] ?? '',
                $libro['categoria'] ?? '', $libro['temas'] ?? '',
                number_format((float) ($libro['costo'] ?? 0), 2, '.', ''),
                $libro['existencias_totales'] ?? 0, $libro['existencias_disponibles'] ?? 0,
                (int) ($libro['existencias_disponibles'] ?? 0) > 0 ? 'Disponible' : 'No disponible',
                $libro['ubicacion_fisica'] ?? '', (int) ($libro['estado'] ?? 0) === 1 ? 'Activo' : 'Inactivo',
                match ($this->verificarIntegridadLibro($libroModel, (int) ($libro['id_libro'] ?? 0))) {
                    true => 'Íntegro',
                    false => 'Alterado',
                    default => 'Sin firma',
                },
            ];
            echo '<tr>';
            foreach ($fila as $valor) {
                echo '<td>' . htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></body></html>';
        exit();
    }

    public function cambiarEstado(): void
    {
        Auth::check();
        Auth::exigirPermiso('libros.eliminar');

        $idLibro = filter_input(
            INPUT_POST,
            'id_libro',
            FILTER_VALIDATE_INT
        );

        $estado = $_POST['estado'] ?? null;

        if (
            !$idLibro ||
            !in_array($estado, ['0', '1'], true)
        ) {
            Session::flash(
                'error',
                'Los datos recibidos no son válidos.'
            );

            header('Location: ' . Config::url('libros'));
            exit();
        }

        $libroModel = new Libro();

        if (!$libroModel->buscarPorId((int) $idLibro)) {
            Session::flash(
                'error',
                'El libro seleccionado no existe.'
            );

            header('Location: ' . Config::url('libros'));
            exit();
        }

        try {
            $nuevoEstado = (int) $estado;

            $libroModel->cambiarEstado(
                (int) $idLibro,
                $nuevoEstado
            );
            $this->firmarLibroSeguro($libroModel, (int) $idLibro);

            Session::flash(
                'success',
                $nuevoEstado === 1
                    ? 'Libro activado correctamente.'
                    : 'Libro desactivado correctamente.'
            );
        } catch (\Throwable $e) {
            error_log(
                'Error al cambiar estado del libro: ' .
                $e->getMessage()
            );

            Session::flash(
                'error',
                'No se pudo cambiar el estado del libro.'
            );
        }

        header('Location: ' . Config::url('libros'));
        exit();
    }


    private function firmarLibroSeguro(Libro $modelo, int $idLibro): void
    {
        try {
            $datos = $modelo->datosParaFirma($idLibro);
            if ($datos !== []) {
                $this->firmaService()->firmar(
                    'libros',
                    $idLibro,
                    $datos,
                    (int) Session::get('id_usuario') ?: null,
                );
            }
        } catch (\Throwable $e) {
            error_log('No se pudo firmar el libro: ' . $e->getMessage());
        }
    }

    private function verificarIntegridadLibro(Libro $modelo, int $idLibro): ?bool
    {
        try {
            $datos = $modelo->datosParaFirma($idLibro);
            return $datos === []
                ? null
                : $this->firmaService()->verificar('libros', $idLibro, $datos);
        } catch (\Throwable $e) {
            error_log('No se pudo verificar la firma del libro: ' . $e->getMessage());
            return null;
        }
    }

    private function firmaService(): RegistroFirmaService
    {
        return $this->firmaService ??= new RegistroFirmaService();
    }

    private function leerDatosFormulario(): array
    {
        $costo = str_replace(
            ',',
            '.',
            trim($_POST['costo'] ?? '0')
        );

        return [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'autor' => trim($_POST['autor'] ?? ''),
            'isbn' => trim($_POST['isbn'] ?? ''),
            'editorial' => trim($_POST['editorial'] ?? ''),
            'anio_publicacion' => filter_input(
                INPUT_POST,
                'anio_publicacion',
                FILTER_VALIDATE_INT
            ) ?: null,
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'costo' => $costo,
            'existencias_totales' => filter_input(
                INPUT_POST,
                'existencias_totales',
                FILTER_VALIDATE_INT
            ),
            'existencias_disponibles' => filter_input(
                INPUT_POST,
                'existencias_disponibles',
                FILTER_VALIDATE_INT
            ),
            'ubicacion_fisica' => trim(
                $_POST['ubicacion_fisica'] ?? ''
            ),
            'id_categoria' => filter_input(
                INPUT_POST,
                'id_categoria',
                FILTER_VALIDATE_INT
            ),
        ];
    }

    private function leerIdsTemas(): array
    {
        $ids = $_POST['id_temas'] ?? [];

        if (!is_array($ids)) {
            return [];
        }

        $ids = array_map('intval', $ids);
        $ids = array_filter(
            $ids,
            static fn (int $id): bool => $id > 0
        );

        return array_values(array_unique($ids));
    }

    private function validarDatos(
        array $datos,
        array $idsTemas
    ): ?string {
        if (
            $datos['titulo'] === '' ||
            $datos['autor'] === '' ||
            !$datos['id_categoria']
        ) {
            return 'Título, autor y categoría son obligatorios.';
        }

        if (
            mb_strlen($datos['titulo']) > 250 ||
            mb_strlen($datos['autor']) > 200 ||
            mb_strlen($datos['isbn']) > 30 ||
            mb_strlen($datos['editorial']) > 150 ||
            mb_strlen($datos['ubicacion_fisica']) > 200
        ) {
            return 'Uno o más campos superan la longitud permitida.';
        }

        if (
            !is_numeric($datos['costo']) ||
            (float) $datos['costo'] < 0
        ) {
            return 'El costo debe ser un número mayor o igual que cero.';
        }

        if (
            $datos['existencias_totales'] === false ||
            $datos['existencias_totales'] === null ||
            $datos['existencias_disponibles'] === false ||
            $datos['existencias_disponibles'] === null
        ) {
            return 'Las existencias deben ser números enteros válidos.';
        }

        if (
            (int) $datos['existencias_totales'] < 0 ||
            (int) $datos['existencias_disponibles'] < 0
        ) {
            return 'Las existencias no pueden ser negativas.';
        }

        if (
            (int) $datos['existencias_disponibles'] >
            (int) $datos['existencias_totales']
        ) {
            return 'Las existencias disponibles no pueden superar las totales.';
        }

        if (
            $datos['anio_publicacion'] !== null &&
            (
                (int) $datos['anio_publicacion'] < 1000 ||
                (int) $datos['anio_publicacion'] > 2100
            )
        ) {
            return 'El año de publicación debe estar entre 1000 y 2100.';
        }

        $categoriaModel = new Categoria();
        $idsCategorias = array_map(
            static fn (array $categoria): int =>
                (int) $categoria['id_categoria'],
            $categoriaModel->listarActivas()
        );

        if (
            !in_array(
                (int) $datos['id_categoria'],
                $idsCategorias,
                true
            )
        ) {
            return 'La categoría seleccionada no es válida.';
        }

        if ($idsTemas !== []) {
            $temaModel = new Tema();
            $idsActivos = array_map(
                static fn (array $tema): int =>
                    (int) $tema['id_tema'],
                $temaModel->listarActivos()
            );

            if (array_diff($idsTemas, $idsActivos) !== []) {
                return 'Uno o más temas seleccionados no son válidos.';
            }
        }

        return null;
    }

    private function procesarImagen(?array $archivo): ?array
    {
        return (new LibroImageService())->procesar($archivo);
    }

    private function datosImagen(?array $imagen): array
    {
        return $imagen ?? [
            'imagen_nombre' => null,
            'imagen_ruta' => null,
            'thumbnail_nombre' => null,
            'thumbnail_ruta' => null,
        ];
    }

    private function eliminarArchivosImagen(array $imagen): void
    {
        (new LibroImageService())->eliminar($imagen);
    }

    private function regresarConError(
        string $mensaje,
        array $datos,
        string $ruta
    ): void {
        Session::flash('error', $mensaje);
        Session::flash('old_libro', $datos);

        header('Location: ' . Config::url($ruta));
        exit();
    }
}
