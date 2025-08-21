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
            
            // --- INICIO DE VALIDACIONES ---

            // Validar nombre
            if (empty($datos['nombre'])) {
                $alertas['error'][] = 'El nombre es obligatorio.';
            }

            // Validar email
            if (empty($datos['email'])) {
                $alertas['error'][] = 'El correo electrónico es obligatorio.';
            } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $alertas['error'][] = 'El formato del correo electrónico no es válido.';
            }

            // Validar teléfono (10 dígitos)
            if (empty($datos['telefono'])) {
                $alertas['error'][] = 'El teléfono es obligatorio.';
            } elseif (!preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
                $alertas['error'][] = 'El formato del teléfono no es válido (debe tener 10 dígitos).';
            }

            // Validar horario
            if (empty($datos['horario'])) {
                $alertas['error'][] = 'El horario es obligatorio.';
            }

            // Validar Código Postal
            if (empty($datos['codigo_postal'])) {
                $alertas['error'][] = 'El código postal es obligatorio.';
            } elseif (!preg_match('/^[0-9]{5}$/', $datos['codigo_postal']) || !self::validarCodigoPostalMx($datos['codigo_postal'])) {
                $alertas['error'][] = 'El código postal no es válido o no existe.';
            }

            // Validar mensaje
            if (empty($datos['mensaje'])) {
                $alertas['error'][] = 'El mensaje es obligatorio.';
            }

            // --- FIN DE VALIDACIONES ---

            if (empty($alertas['error'])) {
                // Todos los datos son válidos, proceder a enviar el email
                $email = new Email(); 
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

    // Valida un código postal mexicano usando la API de COPOMEX.
    private static function validarCodigoPostalMx(string $cp): bool {
        // Token de prueba de la API. Para producción, es recomendable registrarse y obtener uno propio.
        $token = 'pruebas';
        $url = "https://api.copomex.com/query/info_cp/{$cp}?token={$token}";

        // Usar cURL para hacer la petición a la API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // La API devuelve un array vacío y un código 200 si no encuentra el CP.
        // Si el CP existe, devuelve un array con datos.
        if ($http_code == 200) {
            $data = json_decode($response, true);
            // Si la respuesta no está vacía, el CP es válido.
            return !empty($data);
        }
        
        // Si hay algún error en la petición, asumimos que no es válido.
        return false;
    }
}