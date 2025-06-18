<?php

namespace MVC;

class Router {

    public $rutasGET = []; // Arreglo para almacenar rutas que responden a solicitudes GET
    public $rutasPOST = []; // Arreglo para almacenar rutas que responden a solicitudes POST

    // Método para registrar rutas GET
    public function get($url, $fn) {
        $this->rutasGET[$url] = $fn; // Asocia la URL con una función a ejecutar
    }

    // Método para registrar rutas POST
    public function post($url, $fn) {
        $this->rutasPOST[$url] = $fn; // Asocia la URL con una función a ejecutar
    }

    // Método para comprobar qué ruta se ha solicitado y ejecutar la función asociada
    public function comprobarRutas() {
        $urlActual = strtok($_SERVER['REQUEST_URI'], '?') ?? '/';
        $urlActual = $urlActual === '' ? '/' : $urlActual;
        $metodo = $_SERVER['REQUEST_METHOD'];
        
        $rutas = $metodo === 'GET' ? $this->rutasGET : $this->rutasPOST;
        $fn = null;
        $params = [];

        foreach ($rutas as $ruta => $handler) {
            $pattern = $this->convertirPatron($ruta);
            if (preg_match($pattern, $urlActual, $matches)) {
                $fn = $handler;
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                break;
            }
        }

        if ($fn) {
            call_user_func_array($fn, array_merge([$this], $params));
        } else {
            header('Location: /');
            exit; // Es una buena práctica usar exit después de una redirección
        }
    }

    private function convertirPatron($ruta) {
        $pattern = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^\/]+)', $ruta);
        return '#^' . $pattern . '$#';
    }

    // Método para renderizar vistas
    public function render($view, $datos = [], $layout = 'layout') {

        // Extrae los datos enviados para usarlos en la vista
        foreach($datos as $key => $value) {
            $$key = $value; // Convierte los elementos del array asociativo en variables con el mismo nombre de la clave
        }

        ob_start(); // Inicia el almacenamiento en memoria para capturar la salida del buffer
        include __DIR__ . "/views/$view.php"; // Incluye la vista específica

        $contenido = ob_get_clean(); // Obtiene el contenido del buffer y limpia el almacenamiento

        include __DIR__ . "/views/$layout.php"; // Incluye la plantilla base y pasa el contenido de la vista
    }
}