<?php

define('TEMPLATES_URL', __DIR__ . '/templates');
define('FUNCIONES_URL', __DIR__ . 'funciones.php');
define('CARPETA_IMAGENES', $_SERVER['DOCUMENT_ROOT'] . '/imagenes/');


function incluirTemplate(string $nombre, bool $inicio = false) {
    include TEMPLATES_URL . '/' . $nombre . '.php';
}

function estaAutenticado() {
    session_start();

    if(!$_SESSION['login']) {
        header('Location: /');
    }
}

function debuguear($variable) : string {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}


// Escapar/Sanitizar el HTML
function s($html) : string {
    $s = htmlspecialchars($html);
    return $s;
}

// Validar ID y redireccionar a Inicio si no es una ID valida
function validar_redireccionar(string $url) {
    // Validar la URL por ID valido
    $id = $_GET['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);

    if(!$id) {
        header("Location: $url");
    }

    return $id;
} 

function pagina_actual($path) : bool {
    return str_contains($_SERVER['PATH_INFO'], $path) ? true : false;
}

function is_auth() : bool {
    // Verifica si la sesión no está iniciada para iniciar una nueva
    if (session_status() == PHP_SESSION_NONE) {  
        session_start();  
    }

    return isset($_SESSION['login']) && $_SESSION['login'] === true && isset($_SESSION['confirmado']) && $_SESSION['confirmado'] === "1";
}