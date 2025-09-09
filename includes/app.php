<?php 

require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

// Cargar Dotenv apuntando al directorio raíz del proyecto
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// --- CONFIGURACIÓN DE ERRORES DINÁMICA BASADA EN EL ENTORNO ---
// Este será el único lugar donde se configure el manejo de errores.
if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development') {
    // Modo Desarrollo: Muestra todos los errores en pantalla (excepto los "Deprecated")
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Modo Producción (o si APP_ENV no está definida): No muestra errores, solo los registra.
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_error.log');
    error_reporting(E_ALL & ~E_DEPRECATED);
}

require 'funciones.php';
require 'database.php';

// Conectarnos a la base de datos
use Model\ActiveRecord;
ActiveRecord::setDB($db);