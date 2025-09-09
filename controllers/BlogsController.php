<?php

namespace Controllers;

use Exception;
use Model\Blog;
use MVC\Router;
use Model\Usuario;
use Classes\Paginacion;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

// Definir la carpeta de imágenes si no está definida
if (!defined('CARPETA_IMAGENES_BLOGS')) {
    define('CARPETA_IMAGENES_BLOGS', $_SERVER['DOCUMENT_ROOT'] . '/img/blogs/'); // Ajusta la ruta
}
// Carpeta para imágenes del contenido del editor
if (!defined('CARPETA_IMAGENES_CONTENIDO_BLOGS')) {
    define('CARPETA_IMAGENES_CONTENIDO_BLOGS', $_SERVER['DOCUMENT_ROOT'] . '/img/blogs/contenido/');
}

class BlogsController
{
    public static function index(Router $router)
    {
        if(!is_auth()) {
            header('Location: /login');
            exit;
        }

        // Busqueda
        $busqueda = $_GET['busqueda'] ?? '';
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        
        // Validar página
        if($pagina_actual < 1) {
            header('Location: /admin/blogs?page=1');
            exit();
        }

        // Configuración paginación
        $registros_por_pagina = 5;
        $condiciones = [];

        // Buscar blogs
        if(!empty($busqueda)) {
            $condiciones = Blog::buscar($busqueda);
        }

        // Obtener total de registros
        $total = Blog::totalCondiciones($condiciones);

        // Crear instancia de paginación
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        // Validar páginas totales
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1 && $total > 0) { // Añadir $total > 0
            header('Location: /admin/blogs?page=1');
            exit();
        }

        // Obtener registros con relaciones
        $params = [
            'condiciones' => $condiciones,
            'orden' => 'id DESC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset(),
        ];
        
        $blogs = Blog::metodoSQL($params);

        foreach ($blogs as $blog) {
            if ($blog->autor_id) {
                $autor = Usuario::find($blog->autor_id);
                $blog->nombre_autor = $autor ? ($autor->nombre . ' ' . $autor->apellido) : 'Desconocido';
            } else {
                $blog->nombre_autor = 'Sin autor';
            }
        }

        // Comprueba si la petición viene del script (AJAX)
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            
            // Renderiza la tabla en una variable
            ob_start();
            include __DIR__ . '/../views/admin/blogs/_tabla.php';
            $tabla_html = ob_get_clean();

            // Renderiza la paginación en una variable
            $paginacion_html = $paginacion->paginacion();

            // Devuelve una respuesta JSON
            header('Content-Type: application/json');
            echo json_encode([
                'tabla_html' => $tabla_html,
                'paginacion_html' => $paginacion_html
            ]);
            return; // Detiene la ejecución para no renderizar la página completa
        }

        $router->render('admin/blogs/index', [
            'titulo' => 'Administrar Entradas de Blog',
            'blogs' => $blogs,
            'paginacion' => $paginacion->paginacion(),
            'busqueda' => $busqueda
        ], 'admin-layout');
    }

    public static function crear(Router $router)
    {
        if(!is_auth()) {
            header('Location: /login');
            exit;
        }

        $blog = new Blog();
        $alertas = Blog::getAlertas(); // Limpiar/obtener alertas iniciales

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth()) { 
                header('Location: /login');
                exit;
            }

            $blog->sincronizar($_POST);

            if (empty($_SESSION['id'])) {
                @session_start(); 
            }
            $blog->autor_id = $_SESSION['id'] ?? null;

            // Gestión de la imagen destacada
            $nombreImagenDestacada = null;
            if (isset($_FILES['imagen']['tmp_name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $nombreImagenDestacada = md5(uniqid(rand(), true)) . ".webp";
                $blog->setImagen($nombreImagenDestacada);
            }
            
            $alertas = $blog->validar();

            if (empty($alertas['error'])) {
                // Si hay una imagen para subir, la procesamos
                if ($nombreImagenDestacada) {
                    try {
                        if (!is_dir(CARPETA_IMAGENES_BLOGS)) {
                            mkdir(CARPETA_IMAGENES_BLOGS, 0755, true);
                        }

                        $manager = new ImageManager(new Driver());
                        $imagenProcesada = $manager->read($_FILES['imagen']['tmp_name']);
                        $imagenProcesada->scaleDown(width: 800, height: 600);

                        // Guardar como WebP
                        $rutaWebp = CARPETA_IMAGENES_BLOGS . $nombreImagenDestacada;
                        $imagenProcesada->toWebp(85)->save($rutaWebp);

                    } catch (Exception $e) {
                        Blog::setAlerta('error', 'Hubo un error al procesar la imagen: ' . $e->getMessage());
                        $alertas = Blog::getAlertas();
                    }
                }

                // Guardar en la base de datos si no hay errores
                if (empty($alertas['error'])) {
                    $resultado = $blog->guardar();
                    if ($resultado) {
                        header('Location: /admin/blogs?resultado=1');
                        exit;
                    } else {
                        Blog::setAlerta('error', 'Ocurrió un error al guardar la entrada del blog.');
                    }
                }
            }
        }

        $alertas = Blog::getAlertas(); // Asegurar que las últimas alertas se pasen a la vista
        $router->render('admin/blogs/crear', [
            'titulo' => 'Crear Nueva Entrada de Blog',
            'blog' => $blog,
            'alertas' => $alertas,
        ], 'admin-layout');
    }

    public static function editar(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
            exit;
        }

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: /admin/blogs');
            exit;
        }

        $blog = Blog::find($id);
        if (!$blog) {
            header('Location: /admin/blogs?resultado=error_no_encontrado');
            exit;
        }

        $imagen_destacada_original = $blog->imagen;
        $alertas = Blog::getAlertas(); 

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth()) {
                header('Location: /login');
                exit;
            }

            $blog->sincronizar($_POST); 

            $seSubioNuevaImagenDestacada = false;
            $nombreImagenDestacadaNueva = null;

            // Manejo de la nueva imagen destacada
            if (isset($_FILES['imagen']['tmp_name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $nombreImagenDestacadaNueva = md5(uniqid(rand(), true)) . '.webp';
                $seSubioNuevaImagenDestacada = true;
            }

            $alertas = $blog->validar();

            if (empty($alertas['error'])) {
                // Si se subió una nueva imagen, la procesamos
                if ($seSubioNuevaImagenDestacada && $nombreImagenDestacadaNueva) {
                    try {
                        // Eliminar la imagen anterior si existe
                        if ($imagen_destacada_original && file_exists(CARPETA_IMAGENES_BLOGS . $imagen_destacada_original)) {
                            unlink(CARPETA_IMAGENES_BLOGS . $imagen_destacada_original);
                        }
                        
                        if (!is_dir(CARPETA_IMAGENES_BLOGS)) {
                            mkdir(CARPETA_IMAGENES_BLOGS, 0755, true);
                        }

                        $manager = new ImageManager(new Driver());
                        $imagenProcesada = $manager->read($_FILES['imagen']['tmp_name']);
                        $imagenProcesada->scaleDown(width: 800, height: 600);

                        // Guardar la nueva imagen como WebP
                        $rutaWebpNueva = CARPETA_IMAGENES_BLOGS . $nombreImagenDestacadaNueva;
                        $imagenProcesada->toWebp(85)->save($rutaWebpNueva);
                        
                        // Actualizamos el nombre en el objeto
                        $blog->setImagen($nombreImagenDestacadaNueva);

                    } catch (Exception $e) {
                        Blog::setAlerta('error', 'Error al procesar la nueva imagen: ' . $e->getMessage());
                    }
                }

                // Guardar en la base de datos si no hay errores
                if (empty(Blog::getAlertas()['error'])) {
                    $resultadoGuardado = $blog->guardar();
                    
                    if ($resultadoGuardado) {
                        header('Location: /admin/blogs?resultado=2');
                        exit;
                    }
                }
            }
        }

        $alertas = Blog::getAlertas();
        $router->render('admin/blogs/editar', [
            'titulo' => 'Editar Entrada de Blog',
            'blog' => $blog,
            'alertas' => $alertas,
        ], 'admin-layout');
    }

    public static function eliminar() {
        if (!is_auth()) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth()) {
                header('Location: /login');
                exit;
            }

            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                header('Location: /admin/blogs?resultado=id_invalido_eliminar');
                exit;
            }

            $blog = Blog::find($id);
            if (!$blog) {
                header('Location: /admin/blogs?resultado=no_encontrado_eliminar');
                exit;
            }

            // 1. Eliminar la imagen DESTACADA del servidor
            if ($blog->imagen) {
                // Construimos la ruta completa usando el nombre de archivo desde la BD
                $rutaImagenDestacada = CARPETA_IMAGENES_BLOGS . $blog->imagen;
                
                // Verificamos si el archivo existe y lo eliminamos
                if (file_exists($rutaImagenDestacada)) {
                    unlink($rutaImagenDestacada);
                }
            }

            // 2. Eliminar las imágenes insertadas en el CONTENIDO del blog
            if ($blog->contenido) {
                $imagenesEnContenido = self::extraerImagenesDeContenido($blog->contenido);
                if (!empty($imagenesEnContenido)) {
                    self::eliminarArchivosDeImagen($imagenesEnContenido, CARPETA_IMAGENES_CONTENIDO_BLOGS);
                }
            }

            // Finalmente, elimina el registro del blog de la base de datos
            $resultado = $blog->eliminar();

            if ($resultado) {
                header('Location: /admin/blogs?resultado=3');
                exit;
            } else {
                header('Location: /admin/blogs?resultado=error_eliminar');
                exit;
            }
        }

        header('Location: /admin/blogs');
        exit;
    }

    public static function uploadEditorImage() {
        // Verificar autenticación y permisos si es necesario
        if (!is_auth()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autorizado']);
            http_response_code(403);
            exit;
        }
    
        $uploadDir = CARPETA_IMAGENES_CONTENIDO_BLOGS; // Usar la constante definida
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => ['message' => 'No se pudo crear el directorio de subida.']]);
                http_response_code(500);
                exit;
            }
        }
    
        $uploadedFile = null;
        if (!empty($_FILES)) {
            $fileKey = array_key_first($_FILES);
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $uploadedFile = $_FILES[$fileKey];
            } else {
                 $error_code = $_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE;
                 // ... (mapeo de errores) ...
                 header('Content-Type: application/json');
                 echo json_encode(['error' => ['message' => 'Error en la subida del archivo. Código: ' . $error_code]]);
                 http_response_code(400);
                 exit;
            }
        }
    
        if (!$uploadedFile) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No se recibió ningún archivo.']);
            http_response_code(400);
            exit;
        }
    
        $tempName = $uploadedFile['tmp_name'];
        $originalName = $uploadedFile['name'];
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Tipo de archivo no permitido.']);
            http_response_code(400);
            exit;
        }
    
        $newFileNameBase = md5(uniqid(rand(), true)); // Nombre base único
        $newFileName = $newFileNameBase . '.' . $fileExtension;
        $destination = $uploadDir . $newFileName;
    
        // Usar Intervention Image para procesar y guardar (opcional, pero bueno para optimizar/redimensionar)
        $newFileNameBase = md5(uniqid(rand(), true));
    
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($tempName);

            $maxWidth = 1024; $maxHeight = 1024; // O los valores que prefieras
            $image->scaleDown(width: $maxWidth, height: $maxHeight); // CAMBIO: Usar scaleDown
            error_log("Imagen del editor redimensionada (si fue necesario) para el blog");

            $urlPublica = '';
            $nombreArchivoServidor = ''; // Necesitamos el nombre del archivo final
            
            // Priorizar WebP para el editor, pero manejar GIF de forma especial
            if ($fileExtension === 'gif') {
                $newFileNameGif = $newFileNameBase . '.gif';
                $destinationGif = $uploadDir . $newFileNameGif;
                if (move_uploaded_file($tempName, $destinationGif)) { // Mover el GIF original para preservar animación
                    error_log("GIF del editor movido directamente: " . $destinationGif);
                    $urlPublica = '/img/blogs/contenido/' . $newFileNameGif;
                    $nombreArchivoServidor = $newFileNameGif; // Guardar nombre de archivo
                } else {
                    throw new Exception("No se pudo mover el archivo GIF.");
                }
            } else {
                // Para otros formatos, convertir a WebP
                $newFileNameWebp = $newFileNameBase . '.webp';
                $destinationWebp = $uploadDir . $newFileNameWebp;
                $image->toWebp(85)->save($destinationWebp);
                error_log("Imagen del editor convertida y guardada como WEBP: " . $destinationWebp);
                $urlPublica = '/img/blogs/contenido/' . $newFileNameWebp;
                $nombreArchivoServidor = $newFileNameWebp; // Guardar nombre de archivo
            }

            // Rastrear imagen subida en la sesión
            if (!isset($_SESSION['editor_temp_uploads'])) {
                $_SESSION['editor_temp_uploads'] = [];
            }

            // Solo almacenamos el nombre del archivo, no la ruta completa
            $_SESSION['editor_temp_uploads'][] = $nombreArchivoServidor; 
    
            header('Content-Type: application/json');
            echo json_encode(['location' => $urlPublica]); 
            exit;
    
        } catch (Exception $e) {
            error_log("ERROR al procesar la imagen del editor: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => ['message' => 'Error procesando la imagen: ' . $e->getMessage()]]);
            http_response_code(500);
            exit;
        }
    }    


    
    // --- FUNCIONES HELPER PARA IMÁGENES DE CONTENIDO ---
    private static function extraerImagenesDeContenido(string $contenidoHtml): array
    {
        $imagenes = [];
        // Busca URLs que empiecen con /img/blogs/contenido/ y terminen en una extensión de imagen común
        preg_match_all('/<img[^>]+src\s*=\s*["\'](\/img\/blogs\/contenido\/[a-zA-Z0-9_.-]+\.(?:jpg|jpeg|png|gif|webp))["\'][^>]*>/i', $contenidoHtml, $matches);
        if (!empty($matches[1])) {
            $imagenes = array_unique($matches[1]);
        }
        error_log("Imágenes extraídas del contenido: " . print_r($imagenes, true));
        return $imagenes;
    }

    private static function eliminarArchivosDeImagen(array $urlsRelativasImagenes, string $directorioBaseServidor)
    {
        if (empty($urlsRelativasImagenes)) {
            return;
        }
        error_log("Intentando eliminar archivos de imagen del servidor: " . print_r($urlsRelativasImagenes, true));
        foreach ($urlsRelativasImagenes as $urlRelativa) {
            // Construir la ruta completa del servidor
            // Asumimos que $urlRelativa es algo como "/img/blogs/contenido/archivo.jpg"
            // y $directorioBaseServidor es "/var/www/html/img/blogs/contenido/"
            // Necesitamos quitar la parte del path que ya está en $directorioBaseServidor o construirla bien.
            
            // Si $urlRelativa ya es la parte después de DOCUMENT_ROOT:
            $nombreArchivo = basename($urlRelativa); // Obtiene solo "archivo.jpg"
            $rutaArchivoServidor = rtrim($directorioBaseServidor, '/') . '/' . $nombreArchivo;

            // Alternativa más segura si $urlRelativa es la URL completa desde la raíz del sitio:
            // $rutaArchivoServidor = $_SERVER['DOCUMENT_ROOT'] . $urlRelativa;


            if (file_exists($rutaArchivoServidor)) {
                if (unlink($rutaArchivoServidor)) { // Quitado el @ para ver errores
                    error_log("Archivo de contenido eliminado del servidor: " . $rutaArchivoServidor);
                } else {
                    error_log("FALLO al eliminar archivo de contenido del servidor: " . $rutaArchivoServidor . " - Verifica permisos o si el archivo está bloqueado.");
                }
            } else {
                error_log("Archivo de contenido no encontrado en servidor para eliminar: " . $rutaArchivoServidor);
            }
        }
    }
    

    private static function eliminarArchivosPorNombre(array $nombresArchivos, string $directorioBaseServidor)
    {
        if (empty($nombresArchivos)) {
            return;
        }
        error_log("Intentando eliminar archivos (por nombre) del servidor: " . print_r($nombresArchivos, true));
        foreach ($nombresArchivos as $nombreArchivo) {
            $rutaArchivoServidor = rtrim($directorioBaseServidor, '/') . '/' . $nombreArchivo;

            // Reutilizar los logs de la otra función de eliminar
            error_log("Ruta construida para eliminar (por nombre): " . $rutaArchivoServidor); 

            if (file_exists($rutaArchivoServidor)) {
                error_log("Archivo SÍ existe en (por nombre): " . $rutaArchivoServidor); 
                if (unlink($rutaArchivoServidor)) { 
                    error_log("Archivo de contenido (por nombre) eliminado del servidor: " . $rutaArchivoServidor);
                } else {
                    error_log("FALLO al eliminar archivo de contenido (por nombre) del servidor: " . $rutaArchivoServidor);
                    $error = error_get_last(); 
                    if ($error) { 
                        error_log("Detalle del error de PHP para unlink (por nombre): " . print_r($error, true)); 
                    }
                }
            } else {
                error_log("Archivo de contenido (por nombre) NO ENCONTRADO en servidor para eliminar: " . $rutaArchivoServidor);
            }
        }
    }
}