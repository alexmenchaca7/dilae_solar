<?php

namespace Model;

class Blog extends ActiveRecord {
    
    protected static $tabla = 'blogs';
    protected static $columnasDB = [
        'id', 'titulo', 'slug', 'contenido', 'imagen', 'autor_id', 
        'estado', 'fecha_creacion', 'fecha_actualizacion', 'meta_title', 'meta_description',
        'lectura_estimada', 'likes'
    ];

    // Columnas para la función de búsqueda 
    protected static $buscarColumnasDirectas = ['titulo', 'contenido']; 

    public $id;
    public $titulo;
    public $slug;
    public $contenido;
    public $imagen;
    public $autor_id;
    public $estado;
    public $fecha_creacion;
    public $fecha_actualizacion;
    public $meta_title;
    public $meta_description; 
    public $lectura_estimada;
    public $likes;


    public $imagen_actual; // Para mantener la referencia en la edición si no se cambia

   public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->titulo = $args['titulo'] ?? '';
        $this->slug = $args['slug'] ?? '';
        $this->contenido = $args['contenido'] ?? '';
        $this->imagen = $args['imagen'] ?? '';
        $this->autor_id = $args['autor_id'] ?? null;
        $this->estado = $args['estado'] ?? 'borrador';
        $this->fecha_creacion = $args['fecha_creacion'] ?? date('Y-m-d');
        $this->fecha_actualizacion = $args['fecha_actualizacion'] ?? date('Y-m-d');
        $this->meta_title = $args['meta_title'] ?? ''; 
        $this->meta_description = $args['meta_description'] ?? '';
        $this->lectura_estimada = $args['lectura_estimada'] ?? 0;
        $this->likes = $args['likes'] ?? 0;
        
        // Para el formulario de edición
        $this->imagen_actual = $args['imagen'] ?? '';
    }

    public function validar() {
        // Reiniciar alertas (tu ActiveRecord las maneja como estáticas)
        self::$alertas = ['error' => [], 'exito' => []];

        if(!$this->titulo) {
            self::$alertas['error'][] = 'El título del blog es obligatorio.';
        }
        if(strlen($this->titulo) > 250) {
            self::$alertas['error'][] = 'El título no puede exceder los 250 caracteres.';
        }

        // Generar slug automáticamente si no existe y hay título
        if(empty($this->slug) && !empty($this->titulo)) {
            $this->slug = self::crearSlug($this->titulo);
        } elseif (empty($this->slug) && empty($this->titulo)) {
            self::$alertas['error'][] = 'El slug no se puede generar sin un título.';
        }

        if(empty($this->slug)) {
            self::$alertas['error'][] = 'El slug es obligatorio.';
        } elseif (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $this->slug)) {
            self::$alertas['error'][] = 'El slug solo puede contener letras minúsculas, números y guiones, y no empezar o terminar con guion.';
        }

        // Verificar que el slug sea único
        // El segundo parámetro de where() en tu ActiveRecord parece ser el valor
        $condiciones = ['slug' => $this->slug];
        if($this->id) { // Si es una actualización, excluir el ID actual de la comprobación
            $condiciones[] = "id != " . self::$conexion->escape_string($this->id);
        }
        $query = "SELECT id FROM " . static::$tabla . " WHERE slug = '" . self::$conexion->escape_string($this->slug) . "'";
        if ($this->id) {
            $query .= " AND id != '" . self::$conexion->escape_string($this->id) . "'";
        }
        $query .= " LIMIT 1";

        $existe = self::consultarSQL($query); // Asumiendo que consultarSQL devuelve un array de objetos

        if(!empty($existe)) { // Si $existe no está vacío, significa que encontró un slug igual
            self::$alertas['error'][] = 'El slug ya existe. Por favor, elige otro o modifica el título.';
        }


        if(!$this->contenido) {
            self::$alertas['error'][] = 'El contenido del blog es obligatorio.';
        }

        // Calcular tiempo de lectura
        $this->calcularTiempoLectura();

        $estadosValidos = ['borrador', 'publicado', 'archivado']; // Ajusta según tus estados
        if(!in_array($this->estado, $estadosValidos)) {
            self::$alertas['error'][] = 'El estado seleccionado no es válido.';
        }
        
        return self::$alertas;
    }

    public function calcularTiempoLectura() {
        $palabras_por_minuto = 200;
        $contenido_texto_plano = strip_tags($this->contenido);
        $numero_palabras = str_word_count($contenido_texto_plano);
        $this->lectura_estimada = ceil($numero_palabras / $palabras_por_minuto);
    }

    // Método para crear slugs (tomado de tu modelo Producto y adaptado)
    public static function crearSlug($texto) {
        // Mapa de caracteres especiales
        $map = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U',
            'ñ' => 'n', 'Ñ' => 'N'
        ];
        
        // Convertir caracteres especiales
        $texto = strtr($texto, $map);
        
        // Transliterar otros caracteres acentuados
        if (function_exists('iconv')) {
            $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        }
        
        // Eliminar caracteres no alfanuméricos (excepto guiones y underscores)
        $slug = preg_replace('/[^\p{L}\p{N}_-]+/u', '-', $texto);
        
        // Convertir a minúsculas y limpiar guiones múltiples o al inicio/final
        $slug = strtolower(trim(preg_replace('/-+/', '-', $slug), '-'));
        
        // Si está vacío después de la limpieza, generar algo
        if(empty($slug)) {
            $slug = 'blog-post-' . substr(md5(uniqid()), 0, 8); // Un slug genérico si todo falla
        }
        
        return $slug;
    }

    // Método para manejar la asignación de la imagen
    // El controlador se encarga de subirla y generar el nombre.
    public function setImagen($nombreImagen) {
        if($nombreImagen) {
            $this->imagen = $nombreImagen;
        }
    }

    // Sobrescribir guardar para manejar fechas
    public function guardar() {
        if(is_null($this->id)) {
            $this->fecha_creacion = date('Y-m-d');
        }
        $this->fecha_actualizacion = date('Y-m-d'); // Siempre actualizar esta fecha
        
        // Si el slug está vacío pero el título no, intentar generarlo una última vez
        if (empty($this->slug) && !empty($this->titulo)) {
            $this->slug = self::crearSlug($this->titulo);
        }

        $this->calcularTiempoLectura();

        return parent::guardar();
    }

    // Para obtener el autor
    public function autor() {
        if($this->autor_id) {
            return Usuario::find($this->autor_id); 
        }
        return null;
    }

    // Método para eliminar la imagen del servidor
    public function eliminarImagenAnterior() {
        if ($this->imagen_actual) {
            $rutaCompletaPNG = CARPETA_IMAGENES_BLOGS . $this->imagen_actual . '.png';
            $rutaCompletaWEBP = CARPETA_IMAGENES_BLOGS . $this->imagen_actual . '.webp';
            
            if (file_exists($rutaCompletaPNG)) {
                unlink($rutaCompletaPNG);
            }
            if (file_exists($rutaCompletaWEBP)) {
                unlink($rutaCompletaWEBP);
            }
        }
    }

    public static function obtenerBlogsPublicadosPaginados($limit, $offset) {
        $params = [
            'condiciones' => ["estado = 'publicado'"],
            'orden' => 'fecha_creacion DESC',
            'limite' => $limit,
            'offset' => $offset
        ];
        return self::metodoSQL($params); 
    }

    // Cuenta el total de blogs publicados.
    public static function totalPublicados() {
        return self::totalCondiciones(["estado = 'publicado'"]); 
    }

    // Obtiene un blog publicado por su slug.
    public static function findBySlugPublicado($slug) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE slug = '" . self::$conexion->escape_string($slug) . "' AND estado = 'publicado' LIMIT 1";
        $resultado = self::consultarSQL($query);
        return !empty($resultado) ? array_shift($resultado) : null;
    }

    // Obtiene blogs relacionados o recientes (excluyendo el actual).
    public static function obtenerBlogsRelacionados(int $excludeId, int $limit = 3): array {
        $params = [
           'condiciones' => ["estado = 'publicado'", "id != '" . self::$conexion->escape_string($excludeId) . "'"],
           'orden' => 'fecha_creacion DESC', // Los más recientes primero
           'limite' => $limit
       ];
       return self::metodoSQL($params);
    }

    public static function buscar($termino) {
        $condicionesGenerales = [];
        $terminoGeneral = trim($termino);

        if (empty($terminoGeneral)) {
            return $condicionesGenerales;
        }

        $palabrasBusqueda = array_filter(explode(' ', $terminoGeneral));
        if (empty($palabrasBusqueda)) {
            return $condicionesGenerales;
        }

        foreach ($palabrasBusqueda as $palabra) {
            $palabraEscapada = self::$conexion->escape_string($palabra);
            $palabraLower = mb_strtolower($palabraEscapada, 'UTF-8');
            $condicionesParaEstaPalabra = [];

            foreach (static::$buscarColumnasDirectas as $columna) {
                $condicionesParaEstaPalabra[] = "LOWER(blogs.{$columna}) LIKE '%{$palabraLower}%'";
            }

            $condicionesParaEstaPalabra[] = "EXISTS (
                SELECT 1 FROM usuarios u 
                WHERE u.id = blogs.autor_id AND (
                    LOWER(u.nombre) LIKE '%{$palabraLower}%' OR
                    LOWER(u.apellido) LIKE '%{$palabraLower}%'
                )
            )";
            
            $condicionesGenerales[] = "(" . implode(' OR ', $condicionesParaEstaPalabra) . ")";
        }
        
        return $condicionesGenerales;
    }
}