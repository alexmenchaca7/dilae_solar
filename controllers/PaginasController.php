<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Classes\Paginacion;

class PaginasController {
    public static function index(Router $router) {
        $router->render('paginas/index', [
            'inicio' => true,
            'hero' => 'templates/hero-index'
        ]); 
    }

    public static function nosotros(Router $router) {
        $router->render('paginas/nosotros', [

        ]); 
    }

    public static function soluciones(Router $router) {
        $router->render('paginas/soluciones', [
            'hero' => 'templates/hero-soluciones'
        ]); 
    }

    public static function calculadora(Router $router) {
        $router->render('paginas/calculadora', [
            
        ]); 
    }

    public static function contacto(Router $router) {
        $router->render('paginas/contacto', [
            'hero' => 'templates/hero-contacto'
        ]); 
    }
}