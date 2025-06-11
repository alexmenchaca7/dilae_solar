<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Classes\Paginacion;

class PaginasController {
    public static function index(Router $router) {
        
        $router->render('paginas/construccion', [
            'titulo' => 'Sitio en Construcción',
            'body_class' => 'construccion-bg'
        ], 'layout-vacio'); // Usamos un layout vacío sin header/footer
    }
}