<?php

namespace App\Controllers\Admin;

use App\Config\Config;
use App\Core\Controller;
use App\Helpers\Session;
use App\Middleware\Auth;
use App\Models\Categoria;
use App\Models\Libro;
use App\Models\Tema;

class LibroController extends Controller
{
    private const MAX_IMAGEN_BYTES = 5 * 1024 * 1024;

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

            $libroModel->crear(
                array_merge($datos, $this->datosImagen($imagen)),
                $idsTemas
            );

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
        if (
            $archivo === null ||
            ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
        ) {
            return null;
        }

        if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(
                'Ocurrió un error al subir la imagen.'
            );
        }

        if ((int) ($archivo['size'] ?? 0) > self::MAX_IMAGEN_BYTES) {
            throw new \RuntimeException(
                'La imagen no puede superar los 5 MB.'
            );
        }

        if (!extension_loaded('gd')) {
            throw new \RuntimeException(
                'La extensión GD de PHP debe estar habilitada para procesar imágenes.'
            );
        }

        $temporal = (string) ($archivo['tmp_name'] ?? '');

        if (!is_uploaded_file($temporal)) {
            throw new \RuntimeException(
                'El archivo de imagen recibido no es válido.'
            );
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($temporal);

        $permitidos = [
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        if (!in_array($mime, $permitidos, true)) {
            throw new \RuntimeException(
                'La imagen debe ser JPG, PNG o WEBP.'
            );
        }

        $medidas = getimagesize($temporal);

        if ($medidas === false) {
            throw new \RuntimeException(
                'No se pudo leer la imagen subida.'
            );
        }

        [$ancho, $alto] = $medidas;
        $origen = $this->crearRecursoImagen($temporal, $mime);

        if ($origen === false) {
            throw new \RuntimeException(
                'No se pudo procesar la imagen subida.'
            );
        }

        $base = dirname(__DIR__, 3) .
            '/Public/Assets/Uploads/Libros';

        $dirOriginal = $base . '/Originales';
        $dirMiniatura = $base . '/Miniaturas';

        foreach ([$dirOriginal, $dirMiniatura] as $directorio) {
            if (
                !is_dir($directorio) &&
                !mkdir($directorio, 0775, true) &&
                !is_dir($directorio)
            ) {
                imagedestroy($origen);

                throw new \RuntimeException(
                    'No se pudo crear el directorio de imágenes.'
                );
            }
        }

        $token = bin2hex(random_bytes(16));
        $nombreOriginal = 'libro_' . $token . '.jpg';
        $nombreMiniatura = 'thumb_' . $token . '.jpg';

        $rutaOriginalFisica = $dirOriginal . '/' . $nombreOriginal;
        $rutaMiniaturaFisica = $dirMiniatura . '/' . $nombreMiniatura;

        try {
            $this->guardarRedimensionada(
                $origen,
                $ancho,
                $alto,
                $rutaOriginalFisica,
                1200,
                1600
            );

            $this->guardarRedimensionada(
                $origen,
                $ancho,
                $alto,
                $rutaMiniaturaFisica,
                240,
                320
            );
        } finally {
            imagedestroy($origen);
        }

        return [
            'imagen_nombre' => $nombreOriginal,
            'imagen_ruta' =>
                'Uploads/Libros/Originales/' . $nombreOriginal,
            'thumbnail_nombre' => $nombreMiniatura,
            'thumbnail_ruta' =>
                'Uploads/Libros/Miniaturas/' . $nombreMiniatura,
        ];
    }

    private function crearRecursoImagen(
        string $ruta,
        string $mime
    ): \GdImage|false {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($ruta),
            'image/png' => imagecreatefrompng($ruta),
            'image/webp' => imagecreatefromwebp($ruta),
            default => false,
        };
    }

    private function guardarRedimensionada(
        \GdImage $origen,
        int $anchoOriginal,
        int $altoOriginal,
        string $destino,
        int $anchoMaximo,
        int $altoMaximo
    ): void {
        $escala = min(
            $anchoMaximo / $anchoOriginal,
            $altoMaximo / $altoOriginal,
            1
        );

        $anchoNuevo = max(
            1,
            (int) round($anchoOriginal * $escala)
        );

        $altoNuevo = max(
            1,
            (int) round($altoOriginal * $escala)
        );

        $lienzo = imagecreatetruecolor($anchoNuevo, $altoNuevo);

        if ($lienzo === false) {
            throw new \RuntimeException(
                'No se pudo crear la imagen redimensionada.'
            );
        }

        $blanco = imagecolorallocate($lienzo, 255, 255, 255);
        imagefill($lienzo, 0, 0, $blanco);

        imagecopyresampled(
            $lienzo,
            $origen,
            0,
            0,
            0,
            0,
            $anchoNuevo,
            $altoNuevo,
            $anchoOriginal,
            $altoOriginal
        );

        $guardada = imagejpeg($lienzo, $destino, 88);
        imagedestroy($lienzo);

        if (!$guardada) {
            throw new \RuntimeException(
                'No se pudo guardar la imagen redimensionada.'
            );
        }
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
        $base = dirname(__DIR__, 3) . '/Public/Assets/';

        foreach (['imagen_ruta', 'thumbnail_ruta'] as $campo) {
            $ruta = $imagen[$campo] ?? null;

            if (!is_string($ruta) || $ruta === '') {
                continue;
            }

            $rutaSegura = str_replace(['..', '\\'], ['', '/'], $ruta);
            $archivo = $base . ltrim($rutaSegura, '/');

            if (is_file($archivo)) {
                @unlink($archivo);
            }
        }
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
