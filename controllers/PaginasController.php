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

        $alertas = [];
        $datos = [ // Para repoblar el formulario en caso de error o para limpiarlo
            'nombre' => '',
            'email' => '',
            'telefono' => '',
            'horario' => '',
            'codigo_postal' => '',
            'mensaje' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitizar y asignar datos POST
            $datos['nombre'] = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
            $datos['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $datos['telefono'] = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_SPECIAL_CHARS);
            $datos['horario'] = filter_input(INPUT_POST, 'horario', FILTER_SANITIZE_SPECIAL_CHARS);
            $datos['codigo_postal'] = filter_input(INPUT_POST, 'codigo_postal', FILTER_SANITIZE_SPECIAL_CHARS);
            $datos['mensaje'] = filter_input(INPUT_POST, 'mensaje', FILTER_SANITIZE_SPECIAL_CHARS);

            // Validaciones
            if (empty($datos['nombre'])) {
                $alertas['error'][] = 'El nombre es obligatorio.';
            }
            if (empty($datos['email'])) {
                $alertas['error'][] = 'El correo electrónico es obligatorio.';
            } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $alertas['error'][] = 'El formato del correo electrónico no es válido.';
            }
            if (empty($datos['telefono'])) {
                $alertas['error'][] = 'El telefono es obligatorio.';
            }
            if (empty($datos['horario'])) {
                $alertas['error'][] = 'El horario es obligatorio.';
            }
            if (empty($datos['codigo_postal'])) {
                $alertas['error'][] = 'El codigo_postal es obligatorio.';
            }
            if (empty($datos['mensaje'])) {
                $alertas['error'][] = 'El mensaje es obligatorio.';
            }

            if (empty($alertas['error'])) {
                // Todos los datos son válidos, proceder a enviar el email
                $email = new Email(); // No necesitamos pasar parámetros al constructor si no se usan en el envío de contacto
                $enviado = $email->enviarFormularioContacto($datos);

                if ($enviado) {
                    $alertas['exito'][] = '¡Mensaje enviado correctamente! Nos pondremos en contacto contigo a la brevedad.';
                    // Limpiar los datos del formulario después de un envío exitoso
                    $datos = [
                        'nombre' => '', 'email' => '', 'telefono' => '',
                        'horario' => '', 'codigo_postal' => '', 'mensaje' => ''
                    ];
                } else {
                    $alertas['error'][] = 'No se pudo enviar el mensaje. Por favor, inténtalo de nuevo más tarde o contáctanos por otro medio.';
                }
            }
        }

        $router->render('paginas/contacto', [
            'hero' => 'templates/hero-contacto',
            'alertas' => $alertas,
            'datos' => $datos
        ]); 
    }
}