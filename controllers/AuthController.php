<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;

class AuthController {
    public static function login(Router $router) {
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarLogin();

            if(empty($alertas)) {
                // Verificar que el usuario exista
                $usuario = Usuario::where('email', $usuario->email);
                if(!$usuario) {
                    Usuario::setAlerta('error', 'El usuario no existe');
                } else {
                    // El Usuario existe
                    if(password_verify($_POST['pass'], $usuario->pass)) {
                        // Verificar si la cuenta está confirmada
                        if($usuario->confirmado === "1") {
                            // Iniciar la sesión
                            session_start();
                            $_SESSION['id'] = $usuario->id;
                            $_SESSION['nombre'] = $usuario->nombre;
                            $_SESSION['apellido'] = $usuario->apellido;
                            $_SESSION['email'] = $usuario->email;
                            $_SESSION['confirmado'] = $usuario->confirmado;
                            $_SESSION['login'] = true;

                            // Redirección
                            header('Location: /admin/dashboard');
                            exit();
                        } else {
                            Usuario::setAlerta('error', 'Tu cuenta no ha sido confirmada. Revisa tu correo');
                        }
                    } else {
                        Usuario::setAlerta('error', 'El password es incorrecto');
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesión',
            'alertas' => $alertas
        ]);
    }

    public static function logout() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            session_unset(); // Eliminar todas las variables de sesión
            session_destroy(); // Destruir la sesión
            header('Location: /');
            exit();
        }
    }

    public static function olvide(Router $router) {
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)) {
                // Buscar el usuario
                $usuario = Usuario::where('email', $usuario->email);

                if($usuario) {
                    // Generar un nuevo token
                    $usuario->crearToken();
                    $usuario->guardar();

                    // Enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide', [
            'titulo' => 'Olvide mi Password',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router) {

        $token_valido = true;
        $token = s($_GET['token']);

        if(!$token) header('Location: /login');

        // Identificar el usuario con este token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido, intenta de nuevo');
            $token_valido = false;
        }


        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Añadir el nuevo password
            $usuario->sincronizar($_POST);

            // Validar el password
            $alertas = $usuario->validarPassword();

            if(empty($alertas)) {
                // Hashear el nuevo password
                $usuario->hashPassword();

                // Eliminar el Token
                $usuario->token = null;

                // Guardar el usuario en la BD
                $resultado = $usuario->guardar();

                // Redireccionar
                if($resultado) {
                    header('Location: /login');
                }
            }
        }

        $alertas = Usuario::getAlertas();
        
        // Muestra la vista
        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Password',
            'alertas' => $alertas,
            'token_valido' => $token_valido
        ]);
    }

    public static function confirmar(Router $router) {
        $alertas = [];
        $token = s($_GET['token']);

        if(!$token) {
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            $usuario = Usuario::where('token', $token);

            if(empty($usuario)) {
                Usuario::setAlerta('error', 'Token no válido');
            } else {
                $usuario->confirmado = 1;
                $usuario->token = null;
                $usuario->guardar();
                Usuario::setAlerta('exito', 'Cuenta confirmada correctamente');
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu Cuenta',
            'alertas' => $alertas
        ]);
    }

    public static function establecerPassword(Router $router) {
        $alertas = [];
        $token = s($_GET['token']);
        $token_valido = true;

        if(!$token) {
            Usuario::setAlerta('error', 'Token no válido');
            $token_valido = false;
        } else {
            $usuario = Usuario::where('token', $token);

            if(empty($usuario)) {
                Usuario::setAlerta('error', 'Token no válido');
                $token_valido = false;
            }
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarPassword();

            if($_POST['pass'] !== $_POST['pass2']) {
                Usuario::setAlerta('error', 'Los passwords no coinciden');
            }

            if(empty($alertas)) {
                $usuario->hashPassword();
                $usuario->confirmado = 1; // Confirmar la cuenta
                $usuario->token = null;
                $resultado = $usuario->guardar();

                if($resultado) {
                    header('Location: /login');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/establecer-password', [
            'titulo' => 'Establecer Password',
            'alertas' => $alertas,
            'token_valido' => $token_valido
        ]);
    }
}