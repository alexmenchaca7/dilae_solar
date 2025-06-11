<?php

// Cargar todas las dependencias y configuraciones iniciales.
require_once __DIR__ . '/../includes/app.php'; 

use MVC\Router;
use Controllers\PaginasController;

$router = new Router();

// AUTENTICACIÓN DE USUARIOS



// RUTAS API



// PAGINA DE INICIO
$router->get('/', [PaginasController::class, 'index']);



// AREA DE ADMINISTRACION



// Comprobar y validar que las rutas existan para asignarles las funciones del Controlador
$router->comprobarRutas();