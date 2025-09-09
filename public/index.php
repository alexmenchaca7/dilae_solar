<?php

// Cargar todas las dependencias y configuraciones iniciales.
require_once __DIR__ . '/../includes/app.php'; 

use MVC\Router;
use Controllers\ApiController;
use Controllers\AuthController;
use Controllers\BlogsController;
use Controllers\PaginasController;
use Controllers\UsuariosController;
use Controllers\DashboardController;
use Controllers\SuscripcionesController;

$router = new Router();

// AUTENTICACIÓN DE USUARIOS
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/olvide', [AuthController::class, 'olvide']);
$router->post('/olvide', [AuthController::class, 'olvide']);

$router->get('/reestablecer', [AuthController::class, 'reestablecer']);
$router->post('/reestablecer', [AuthController::class, 'reestablecer']);

$router->get('/confirmar-cuenta', [AuthController::class, 'confirmar']);

$router->get('/establecer-password', [AuthController::class, 'establecerPassword']);
$router->post('/establecer-password', [AuthController::class, 'establecerPassword']);


// RUTAS API
$router->post('/api/like', [ApiController::class, 'update_likes']);
$router->post('/api/view', [ApiController::class, 'add_view']);


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
$router->get('/blogs', [PaginasController::class, 'blogs']);
$router->get('/blog/{slug}', [PaginasController::class, 'blog']);
$router->post('/subscribe', [PaginasController::class, 'subscribe']);



// AREA DE ADMINISTRACION
$router->get('/admin/dashboard', [DashboardController::class, 'index']);

$router->get('/admin/usuarios', [UsuariosController::class, 'index']);
$router->get('/admin/usuarios/crear', [UsuariosController::class, 'crear']);
$router->post('/admin/usuarios/crear', [UsuariosController::class, 'crear']);
$router->get('/admin/usuarios/editar', [UsuariosController::class, 'editar']);
$router->post('/admin/usuarios/editar', [UsuariosController::class, 'editar']);
$router->post('/admin/usuarios/eliminar', [UsuariosController::class, 'eliminar']);

$router->get('/admin/blogs', [BlogsController::class, 'index']);
$router->get('/admin/blogs/crear', [BlogsController::class, 'crear']);
$router->post('/admin/blogs/crear', [BlogsController::class, 'crear']);
$router->get('/admin/blogs/editar', [BlogsController::class, 'editar']);
$router->post('/admin/blogs/editar', [BlogsController::class, 'editar']);
$router->post('/admin/blogs/eliminar', [BlogsController::class, 'eliminar']);
$router->post('/admin/blogs/upload-editor-image', [BlogsController::class, 'uploadEditorImage']);

$router->get('/admin/suscripciones', [SuscripcionesController::class, 'index']);
$router->get('/admin/suscripciones/exportar', [SuscripcionesController::class, 'exportar']);
$router->get('/confirmar-suscripcion', [PaginasController::class, 'confirmarSuscripcion']);



// Comprobar y validar que las rutas existan para asignarles las funciones del Controlador
$router->comprobarRutas();