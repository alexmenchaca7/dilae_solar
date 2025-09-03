<?php
// Cargar el autoloader de Composer para tener acceso a las librerías
require_once __DIR__ . '/vendor/autoload.php';

// Cargar las variables de entorno desde el archivo .env del directorio raíz
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Obtener el token secreto del entorno. Usamos 'DEPLOY_TOKEN' como ejemplo.
$token_secreto_servidor = $_ENV['DEPLOY_TOKEN'] ?? null;
$token_recibido_webhook = $_GET['token'] ?? null;

// Verificar que el token exista y coincida
if ($token_secreto_servidor && $token_recibido_webhook && $token_recibido_webhook === $token_secreto_servidor) {
    
    echo "✅ Autenticación correcta. Ejecutando script de despliegue...\n\n";
    
    // Ejecuta el script deploy.sh y captura toda la salida (normal y de error)
    $output = shell_exec('bash ' . __DIR__ . '/deploy.sh 2>&1');
    
    // Imprime la salida para que puedas verla en los logs del webhook
    echo "<pre>$output</pre>";
    
    echo "🚀 Proceso finalizado.";

} else {
    // Si el token es incorrecto, no está configurado, o no se proporcionó, denegar acceso.
    http_response_code(403);
    echo '❌ Acceso denegado. Token inválido o no configurado en el servidor.';
}
?>