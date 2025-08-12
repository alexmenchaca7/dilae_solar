<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Classes\Paginacion;

class PaginasController {
    public static function index(Router $router) {

        $inicio = true;
        
        $router->render('paginas/index', [
            'inicio' => $inicio
        ]); 
    }

    public static function calculadora(Router $router) {

        $router->render('paginas/calculadora', [
            
        ]); 
    }
}