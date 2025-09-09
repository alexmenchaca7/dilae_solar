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
    define('CARPETA_IMAGENES_BLOGS', $_SERVER['DOCUMENT_ROOT'] . '/public/img/blogs/'); // Ajusta la ruta
}
// Carpeta para imágenes del contenido del editor
if (!defined('CARPETA_IMAGENES_CONTENIDO_BLOGS')) {
    define('CARPETA_IMAGENES_CONTENIDO_BLOGS', $_SERVER['DOCUMENT_ROOT'] . '/public/img/blogs/contenido/');
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
            if (!is_auth()) { // Doble verificación por si acaso
                header('Location: /login');
                exit;
            }

            $blog->sincronizar($_POST);

            if (empty($_SESSION['id'])) {
                @session_start(); // Asegurar que la sesión esté iniciada para obtener el ID del usuario (@ suprime warning si la sesion ya estaba iniciada)
            }
            $blog->autor_id = $_SESSION['id'] ?? null;

            // Validación y manejo de la imagen destacada
            $nombreImagen = null; // Nombre base del archivo que se generará

            if (isset($_FILES['imagen']['name']) && !empty($_FILES['imagen']['name'])) {
                if (isset($_FILES['imagen']['tmp_name']) && is_uploaded_file($_FILES['imagen']['tmp_name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $nombreImagenDestacada = md5(uniqid(rand(), true));
                    if (!is_dir(CARPETA_IMAGENES_BLOGS)) {
                       if (!mkdir(CARPETA_IMAGENES_BLOGS, 0755, true)) { /*...*/ }
                    }
                    $blog->setImagen($nombreImagenDestacada); // Esto setea $blog->imagen
                } else { /* ... manejo de errores de subida ... */ 
                    if(isset($_FILES['imagen']['error']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                        $upload_errors = [ /* ... */ ]; $error_message = $upload_errors[$_FILES['imagen']['error']] ?? "Error desconocido";
                        Blog::setAlerta('error', 'Error al subir la imagen destacada: ' . $error_message);
                    }
                }
            } else {
                if (isset($_FILES['imagen']['error']) && $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
                    error_log("Confirmado: UPLOAD_ERR_NO_FILE (Ningún archivo fue subido), esto es normal si el campo se dejó vacío.");
                }
            }
            
            $alertas = $blog->validar();

            if (empty($alertas['error'])) {
                // Procesamiento físico de la imagen DESTACADA
                if ($nombreImagenDestacada && isset($_FILES['imagen']['tmp_name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    try {
                        $manager = new ImageManager(new Driver());
                        $imagenProcesada = $manager->read($_FILES['imagen']['tmp_name']);
                        
                        $maxWidthDestacada = 800; 
                        $maxHeightDestacada = 600; 
                        $imagenProcesada->scaleDown(width: $maxWidthDestacada, height: $maxHeightDestacada); // CAMBIO: Usar scaleDown

                        $rutaWebp = CARPETA_IMAGENES_BLOGS . $nombreImagenDestacada . '.webp';
                        $rutaPng = CARPETA_IMAGENES_BLOGS . $nombreImagenDestacada . '.png';
                        $imagenProcesada->toWebp(85)->save($rutaWebp);
                        $imagenProcesada->toPng()->save($rutaPng);
                        error_log("Imagen DESTACADA procesada y guardada (WebP: $rutaWebp, PNG: $rutaPng)");
                    } catch (Exception $e) { /* ... manejo de error ... */ }
                }
                
                $alertas = Blog::getAlertas();
                if (empty($alertas['error'])) {
                    error_log("Valor de blog->imagen (destacada) ANTES de guardar en BD: '" . $blog->imagen . "'");
                    $resultado = $blog->guardar(); // Guarda el blog, incluyendo $blog->imagen y $blog->contenido
                    
                    if ($resultado) {
                        error_log("Blog CREADO exitosamente. ID: " . $blog->id . ". Redirigiendo...");
                        // NO hay imágenes de contenido previas que limpiar al crear.
                        header('Location: /admin/blogs?resultado=1');
                        exit;
                    } else {
                        error_log("ERROR: blog->guardar() devolvió falso.");
                        Blog::setAlerta('error', 'Ocurrió un error al guardar la entrada del blog en la base de datos.');
                        // También podrías obtener errores de la BD aquí si tu ActiveRecord los setea
                        // error_log("Errores de BD (si los hay): " . print_r(Blog::getAlertas(), true));
                    }
                } else {
                    error_log("Hay errores DESPUÉS del procesamiento de imagen, no se guardará en BD: " . print_r($alertas['error'], true));
                }
            } else {
                error_log("Hay errores de validación, no se procesará imagen ni se guardará en BD: " . print_r($alertas['error'], true));
            }
        }

        $alertas = Blog::getAlertas(); // Asegurar que las últimas alertas se pasen a la vista
        $router->render('admin/blogs/crear', [
            'titulo' => 'Crear Nueva Entrada de Blog',
            'blog' => $blog,
            'alertas' => $alertas,
        ], 'admin-layout');
    }

    public static function editar(Router $router)
    {
        error_log("--- BlogsController: editar (GET o inicio de POST) ---");
        if (!is_auth()) {
            error_log("Usuario no autenticado, redirigiendo a /login");
            header('Location: /login');
            exit;
        }

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        error_log("ID para editar: " . ($id ?: 'NINGUNO'));
        if (!$id) {
            error_log("ID no válido, redirigiendo a /admin/blogs");
            header('Location: /admin/blogs');
            exit;
        }

        $blog = Blog::find($id);
        if (!$blog) {
            error_log("Blog con ID " . $id . " no encontrado, redirigiendo.");
            header('Location: /admin/blogs?resultado=error_no_encontrado');
            exit;
        }
        error_log("Blog encontrado para editar. ID: " . $blog->id . ". blog->imagen actual en BD: '" . $blog->imagen . "'");
        error_log("blog->imagen_actual (del constructor): '" . $blog->imagen_actual . "'");
        $contenidoOriginal = $blog->contenido; 
        error_log("=== CONTENIDO ORIGINAL DIRECTO DE BD (Blog ID: {$id}) ===");
        error_log($contenidoOriginal); // Imprime el HTML crudo
        error_log("=========================================================");

        $contenidoOriginalHtmlDecoded = html_entity_decode($contenidoOriginal, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        error_log("=== CONTENIDO ORIGINAL DECODIFICADO (Blog ID: {$id}) ===");
        error_log($contenidoOriginalHtmlDecoded); // Imprime el HTML decodificado
        error_log("=========================================================");

        $imagenesOriginalesEnContenido = self::extraerImagenesDeContenido($contenidoOriginalHtmlDecoded); 
        error_log("IMÁGENES EXTRAÍDAS DEL CONTENIDO ORIGINAL (Blog ID: {$id}): " . print_r($imagenesOriginalesEnContenido, true));

        $alertas = Blog::getAlertas(); // Limpiar/obtener alertas iniciales

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("--- BlogsController: editar (POST request) ---");
            if (!is_auth()) {
                error_log("Usuario no autenticado en POST, redirigiendo a /login");
                header('Location: /login');
                exit;
            }

            error_log("Datos POST recibidos para edición: " . print_r($_POST, true));
            $args = $_POST;
            $imagen_destacada_original_en_db = $blog->imagen; // Imagen DESTACADA original
            error_log("Imagen DESTACADA original en BD (antes de sincronizar): '" . $imagen_destacada_original_en_db . "'");

            $blog->sincronizar($args); // $blog->contenido ahora tiene el nuevo contenido
            error_log("Blog sincronizado con POST. blog->imagen (destacada) después de sincronizar: '" . $blog->imagen . "'");

            // --- NUEVA LÓGICA DE LIMPIEZA PRE-GUARDADO ---
            $imagenesSubidasEstaSesion = $_SESSION['editor_temp_uploads'] ?? [];
            $imagenesEnContenidoActualEditor = self::extraerImagenesDeContenido($blog->contenido); // Contenido del POST

            // Convertir URLs completas a solo nombres de archivo para comparar con la sesión
            $nombresImagenesEnContenidoActualEditor = array_map('basename', $imagenesEnContenidoActualEditor);

            $imagenesASubirYEliminarInmediatamente = [];
            if (!empty($imagenesSubidasEstaSesion)) {
                foreach ($imagenesSubidasEstaSesion as $imgSubida) {
                    // Si una imagen se subió en esta sesión pero YA NO ESTÁ en el contenido actual del editor
                    if (!in_array($imgSubida, $nombresImagenesEnContenidoActualEditor)) {
                        $imagenesASubirYEliminarInmediatamente[] = $imgSubida; // Guardamos solo el nombre del archivo
                    }
                }
            }

            if (!empty($imagenesASubirYEliminarInmediatamente)) {
                error_log("Imágenes subidas en esta sesión y eliminadas antes de guardar: " . print_r($imagenesASubirYEliminarInmediatamente, true));
                // Necesitamos pasar los nombres de archivo a eliminarArchivosDeImagen, no URLs relativas
                // Así que modificamos ligeramente cómo se pasan o adaptar eliminarArchivosDeImagen
                self::eliminarArchivosPorNombre($imagenesASubirYEliminarInmediatamente, CARPETA_IMAGENES_CONTENIDO_BLOGS);
            }

            $blog->autor_id = $_POST['autor_id'] ?? $blog->autor_id ?? $_SESSION['id'] ?? null;
            error_log("autor_id para edición: " . $blog->autor_id);

            $nombreImagenDestacadaNuevaBase = null;
            $seSubioNuevaImagenDestacada = false;

            // Manejo de la IMAGEN DESTACADA
            if (isset($_FILES['imagen']['name']) && !empty($_FILES['imagen']['name'])) {
                if (isset($_FILES['imagen']['tmp_name']) && is_uploaded_file($_FILES['imagen']['tmp_name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $nombreImagenDestacadaNuevaBase = md5(uniqid(rand(), true));
                    $seSubioNuevaImagenDestacada = true;
                    error_log("Nueva imagen DESTACADA seleccionada. Nombre base: '" . $nombreImagenDestacadaNuevaBase . "'");
                } else {
                    error_log("NO se cumplieron todas las condiciones para procesar _FILES['imagen'] (nueva imagen para editar).");
                     if(isset($_FILES['imagen']['error']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                        // Mismos códigos de error que en crear...
                        $upload_errors = [ /* ... (copiar array de errores de crear) ... */ ];
                        $error_message = $upload_errors[$_FILES['imagen']['error']] ?? "Error desconocido en la subida.";
                        error_log("Código de error de subida _FILES['imagen']['error']: " . $_FILES['imagen']['error'] . " - " . $error_message);
                        Blog::setAlerta('error', 'Error al subir la nueva imagen: ' . $error_message);
                    }
                }
            } else {
                error_log("No se envió una nueva imagen (_FILES['imagen']['name'] vacío o no seteado).");
            }

            $alertas = $blog->validar();
            error_log("Alertas después de blog->validar() en edición: " . print_r($alertas, true));

            if (empty($alertas['error'])) {
                $imagenProcesadaYGuardadaCorrectamente = true;

                // CASO 1: Se subió una nueva imagen DESTACADA
                if ($seSubioNuevaImagenDestacada && $nombreImagenDestacadaNuevaBase) {
                    try {
                        if ($imagen_destacada_original_en_db) {
                            error_log("Eliminando imagen DESTACADA anterior del servidor: '" . $imagen_destacada_original_en_db . "'");
                            $rutaAnteriorPNG = CARPETA_IMAGENES_BLOGS . $imagen_destacada_original_en_db . '.png';
                            $rutaAnteriorWEBP = CARPETA_IMAGENES_BLOGS . $imagen_destacada_original_en_db . '.webp';
                            if (file_exists($rutaAnteriorPNG)) { unlink($rutaAnteriorPNG); } // Quitado @
                            if (file_exists($rutaAnteriorWEBP)) { unlink($rutaAnteriorWEBP); } // Quitado @
                        }

                        if (!is_dir(CARPETA_IMAGENES_BLOGS)) mkdir(CARPETA_IMAGENES_BLOGS, 0755, true);

                        $manager = new ImageManager(new Driver());
                        $imagenProcesada = $manager->read($_FILES['imagen']['tmp_name']);
                        
                        $maxWidthDestacada = 800; $maxHeightDestacada = 600;
                        $imagenProcesada->scaleDown(width: $maxWidthDestacada, height: $maxHeightDestacada); // CAMBIO: Usar scaleDown

                        $rutaWebpNueva = CARPETA_IMAGENES_BLOGS . $nombreImagenDestacadaNuevaBase . '.webp';
                        $rutaPngNueva = CARPETA_IMAGENES_BLOGS . $nombreImagenDestacadaNuevaBase . '.png';
                        $imagenProcesada->toWebp(85)->save($rutaWebpNueva);
                        $imagenProcesada->toPng()->save($rutaPngNueva);
                        
                        $blog->imagen = $nombreImagenDestacadaNuevaBase; // Actualiza $blog->imagen
                        error_log("blog->imagen (destacada) actualizado con nuevo nombre: '" . $blog->imagen . "'");

                    } catch (Exception $e) {
                        error_log("ERROR al procesar la nueva imagen: " . $e->getMessage());
                        Blog::setAlerta('error', 'Error al procesar la nueva imagen: ' . $e->getMessage());
                        $imagenProcesadaYGuardadaCorrectamente = false;
                        // Importante: $blog->imagen NO se actualizó al nuevo nombre si hubo error aquí,
                        // debería retener el valor original que tenía después de sincronizar.
                        error_log("Debido al error, blog->imagen debería ser (revisar): '" . $blog->imagen . "' (probablemente la original o la sincronizada si el campo imagen fuera un input text).");
                    }
                // CASO 2: Se marcó "eliminar imagen actual" (DESTACADA)
                } elseif (isset($_POST['eliminar_imagen_actual']) && $_POST['eliminar_imagen_actual'] == '1' && !$seSubioNuevaImagenDestacada) {
                    if ($imagen_destacada_original_en_db) {
                        error_log("Eliminando imagen DESTACADA actual (checkbox) del servidor: '" . $imagen_destacada_original_en_db . "'");
                        $rutaAnteriorPNG = CARPETA_IMAGENES_BLOGS . $imagen_destacada_original_en_db . '.png';
                        $rutaAnteriorWEBP = CARPETA_IMAGENES_BLOGS . $imagen_destacada_original_en_db . '.webp';
                        if (file_exists($rutaAnteriorPNG)) { unlink($rutaAnteriorPNG); } // Quitado @
                        if (file_exists($rutaAnteriorWEBP)) { unlink($rutaAnteriorWEBP); } // Quitado @
                        $blog->imagen = ''; 
                        error_log("blog->imagen (destacada) limpiado.");
                    }
                }
                
                $alertas = Blog::getAlertas();
                if (empty($alertas['error']) && $imagenProcesadaYGuardadaCorrectamente) {
                    error_log("Valor de blog->imagen (destacada) ANTES de guardar en BD (edición): '" . $blog->imagen . "'");
                    $resultadoGuardado = $blog->guardar(); // Guarda $blog->imagen y el NUEVO $blog->contenido
                    
                    if ($resultadoGuardado) {
                        error_log("Blog ACTUALIZADO exitosamente. ID: " . $blog->id);
                        
                        // --- LÓGICA DE LIMPIEZA POST-GUARDADO (para imágenes que estaban ANTES en la BD) ---
                        $imagenesNuevasEnContenidoGuardado = self::extraerImagenesDeContenido($blog->contenido); // $blog->contenido ya es el de la BD
                        $imagenesAEliminarDelContenidoPersistido = array_diff($imagenesOriginalesEnContenido, $imagenesNuevasEnContenidoGuardado);
                        
                        if (!empty($imagenesAEliminarDelContenidoPersistido)) {
                            error_log("Imágenes (persistidas) que ya no se usan y se eliminarán: " . print_r($imagenesAEliminarDelContenidoPersistido, true));
                            // Esta función espera URLs relativas, lo cual es correcto para $imagenesAEliminarDelContenidoPersistido
                            self::eliminarArchivosDeImagen($imagenesAEliminarDelContenidoPersistido, CARPETA_IMAGENES_CONTENIDO_BLOGS);
                        } else {
                            error_log("No hay imágenes (persistidas) antiguas para eliminar o todas siguen en uso.");
                        }

                        // Limpiar la sesión de subidas temporales DESPUÉS de que todo se procesó
                        unset($_SESSION['editor_temp_uploads']);

                        header('Location: /admin/blogs?resultado=2');
                        exit;
                    }
                } else {
                    error_log("Hay errores o la imagen no se procesó correctamente, no se guardará en BD. Alertas: " . print_r($alertas['error'] ?? 'ninguna', true) . " Flag imagenProcesada: " . ($imagenProcesadaYGuardadaCorrectamente ? 'true' : 'false'));
                }
            } else {
                 error_log("Hay errores de validación en edición, no se procesará imagen ni se guardará en BD: " . print_r($alertas['error'], true));
            }
            
            $alertas = Blog::getAlertas(); // Asegurar que las últimas alertas se capturen
        }

        // Al cargar el formulario de edición por GET, también es buena idea limpiar la sesión de subidas
        // previas para no confundir con una nueva sesión de edición.
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            unset($_SESSION['editor_temp_uploads']);
        }

        error_log("Renderizando vista admin/blogs/editar");
        $router->render('admin/blogs/editar', [
            'titulo' => 'Editar Entrada de Blog',
            'blog' => $blog,
            'alertas' => $alertas,
        ], 'admin-layout');
    }

    public static function eliminar()
    {
        error_log("--- BlogsController: eliminar ---");
        if (!is_auth()) {
            error_log("Usuario no autenticado, redirigiendo a /login");
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("POST request para eliminar.");
             if (!is_auth()) {
                error_log("Usuario no autenticado en POST (eliminar), redirigiendo a /login");
                header('Location: /login');
                exit;
            }

            $id = $_POST['id'] ?? null;
            $id = filter_var($id, FILTER_VALIDATE_INT);
            error_log("ID para eliminar: " . ($id ?: 'NINGUNO/INVÁLIDO'));

            if (!$id) {
                error_log("ID no válido para eliminar, redirigiendo.");
                header('Location: /admin/blogs?resultado=id_invalido_eliminar');
                exit;
            }

            $blog = Blog::find($id);

            if (!$blog) {
                error_log("Blog con ID " . $id . " no encontrado para eliminar, redirigiendo.");
                header('Location: /admin/blogs?resultado=no_encontrado_eliminar');
                exit;
            }
            error_log("Blog encontrado para eliminar. ID: " . $blog->id . ". Imagen asociada: '" . $blog->imagen . "'");

            // --- INICIO ELIMINAR IMÁGENES DEL CONTENIDO ---
            if ($blog->contenido) {
                $imagenesEnContenido = self::extraerImagenesDeContenido($blog->contenido);
                if (!empty($imagenesEnContenido)) {
                    error_log("Eliminando imágenes del contenido del blog ID " . $blog->id . " que se va a borrar.");
                    self::eliminarArchivosDeImagen($imagenesEnContenido, CARPETA_IMAGENES_CONTENIDO_BLOGS);
                }
            }
            // --- FIN ELIMINAR IMÁGENES DEL CONTENIDO ---

            // Eliminar la imagen DESTACADA del servidor
            if ($blog->imagen) {
                $nombreBaseImagenDestacada = $blog->imagen;
                error_log("Intentando eliminar imagen DESTACADA (al eliminar blog) del servidor: '" . $nombreBaseImagenDestacada . "'");
                $rutaWebp = CARPETA_IMAGENES_BLOGS . $nombreBaseImagenDestacada . '.webp';
                $rutaPng = CARPETA_IMAGENES_BLOGS . $nombreBaseImagenDestacada . '.png';
                if (file_exists($rutaWebp)) { unlink($rutaWebp); } // Quitado @
                if (file_exists($rutaPng)) { unlink($rutaPng); } // Quitado @
            }
            
            error_log("Intentando eliminar registro del blog de la BD...");
            $resultado = $blog->eliminar(); // Elimina el registro de la BD

            if ($resultado) {
                error_log("Blog eliminado exitosamente de la BD. Redirigiendo.");
                header('Location: /admin/blogs?resultado=3');
                exit;
            } else {
                error_log("ERROR al eliminar el blog de la BD.");
                header('Location: /admin/blogs?resultado=error_eliminar');
                exit;
            }
        }
        error_log("Solicitud a eliminar no fue POST, redirigiendo.");
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