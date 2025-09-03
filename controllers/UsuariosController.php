<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;
use Classes\Paginacion;

class UsuariosController {
    public static function index(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        // Busqueda
        $busqueda = $_GET['busqueda'] ?? '';
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;

        // Validar página
        if($pagina_actual < 1) {
            header('Location: /admin/usuarios?page=1');
            exit();
        }

        // Configuración paginación
        $registros_por_pagina = 10;
        $condiciones = [];

        // Usar método del modelo para buscar
        if(!empty($busqueda)) {
            $condiciones = Usuario::buscar($busqueda);
        }

        // Obtener total de registros
        $total = Usuario::totalCondiciones($condiciones);

        // Crear instancia de paginación
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);
        
        // Validar páginas totales
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1 && $total > 0) {
            header('Location: /admin/usuarios?page=1');
            exit();
        }

        // Obtener registros
        $params = [
            'condiciones' => $condiciones,
            'orden' => 'id DESC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ];
        
        $usuarios = Usuario::metodoSQL($params);

        // Comprueba si la petición viene del script (AJAX)
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            
            // Renderiza la tabla en una variable
            ob_start();
            include __DIR__ . '/../views/admin/usuarios/_tabla.php';
            $tabla_html = ob_get_clean();

            // Renderiza la paginación en una variable
            $paginacion_html = $paginacion->paginacion();

            // Devuelve una respuesta JSON
            header('Content-Type: application/json');
            echo json_encode([
                'tabla_html' => $tabla_html,
                'paginacion_html' => $paginacion_html
            ]);
            return; // Detiene la ejecución para no renderizar la página completa
        }

        // Renderizar vista
        $router->render('admin/usuarios/index', [
            'titulo' => 'Usuarios',
            'usuarios' => $usuarios,
            'paginacion' => $paginacion->paginacion(),
            'busqueda' => $busqueda
        ], 'admin-layout');
    }

    public static function crear(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        $usuario = new Usuario;
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarRegistro();

            if(empty($alertas)) {
                $usuario->crearToken(); // Crear un token para la configuración de la contraseña
                $resultado = $usuario->guardar();

                if($resultado) {
                    // Enviar el email de configuración de contraseña
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    // Redirigir a la vista de confirmación
                    header('Location: /admin/usuarios/crear?confirmacion=1');
                    exit();
                }
            }
        }

        $router->render('admin/usuarios/crear', [
            'titulo' => 'Registrar Usuario',
            'alertas' => $alertas,
            'usuario' => $usuario
        ], 'admin-layout');
    }

    public static function editar(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        $alertas = [];
        $id = $_GET['id'];
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if(!$id) {
            header('Location: /admin/usuarios');
        }

        $usuario = Usuario::find($id);

        if(!$usuario) {
            header('Location: /admin/usuarios');
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarRegistro();

            if(empty($alertas)) {
                $resultado = $usuario->guardar();

                if($resultado) {
                    header('Location: /admin/usuarios');
                }
            }
        }

        $router->render('admin/usuarios/editar', [
            'titulo' => 'Actualizar Usuario',
            'alertas' => $alertas,
            'usuario' => $usuario
        ], 'admin-layout');
    }

    public static function eliminar() {
        if(!is_auth()) {
            header('Location: /login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $usuario = Usuario::find($id);
    
            if (!$usuario) {
                header('Location: /admin/usuarios');
            }
    
            // Eliminar sesiones del usuario
            $user_id = $usuario->id;
            $sessionSavePath = session_save_path();
            $sessionFiles = glob($sessionSavePath . '/sess_*');
    
            foreach ($sessionFiles as $sessionFile) {
                $data = file_get_contents($sessionFile);
                $_SESSION = [];
                session_decode($data);
                if (isset($_SESSION['id']) && $_SESSION['id'] == $user_id) {
                    unlink($sessionFile);
                }
            }
    
            // Si el usuario eliminado es el mismo que está logueado, cerrar sesión
            if ($user_id == $_SESSION['id']) {
                session_unset();
                session_destroy();
            }
    
            $resultado = $usuario->eliminar();
    
            if ($resultado) {
                // Redirigir adecuadamente
                if ($user_id == $_SESSION['id'] ?? null) {
                    header('Location: /login');
                } else {
                    header('Location: /admin/usuarios');
                }
                exit();
            }
        }
    }
}