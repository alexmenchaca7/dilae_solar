<?php

// INTERCEPCIÓN PARA EL SCRIPT DE DESPLIEGUE
// Si la solicitud es para desplegar.php, lo ejecutamos directamente y detenemos todo lo demás.
if (strtok($_SERVER['REQUEST_URI'], '?') === '/desplegar.php') {
    require_once __DIR__ . '/desplegar.php';
    exit;
}

// Cargar todas las dependencias y configuraciones iniciales.
require_once __DIR__ . '/../includes/app.php'; 

use MVC\Router;
use Controllers\PaginasController;

$router = new Router();

// AUTENTICACIÓN DE USUARIOS



// RUTAS API



// PAGINA DE INICIO
$router->get('/', [PaginasController::class, 'index']);
$router->get('/terminos', [PaginasController::class, 'terminos']);
$router->get('/privacy', [PaginasController::class, 'privacy']);
$router->get('/nosotros', [PaginasController::class, 'nosotros']);
$router->get('/soluciones', [PaginasController::class, 'soluciones']);
$router->get('/contacto', [PaginasController::class, 'contacto']);
$router->post('/contacto', [PaginasController::class, 'contacto']);
$router->get('/calculadora', [PaginasController::class, 'calculadora']);
$router->post('/calculadora', [PaginasController::class, 'calculadora']);



// AREA DE ADMINISTRACION



// Comprobar y validar que las rutas existan para asignarles las funciones del Controlador
$router->comprobarRutas();