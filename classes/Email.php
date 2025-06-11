<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {

    public $email;
    public $nombre;
    public $token;
    
    public function __construct($email = '', $nombre = '', $token = '')
    {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion() {

        // create a new object
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
    
        $mail->setFrom('no-reply@dilae.com');
        $mail->addAddress($this->email, $this->nombre);
        $mail->Subject = 'Establece tu Contraseña';

        // Set HTML
        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= "<h1>Hola " . $this->nombre .  ":</h1>";
        $contenido .= "<p>Has registrado correctamente tu cuenta en Dilae, pero es necesario establecer tu contraseña...</p>";
        $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['HOST'] . "/establecer-password?token=" . $this->token . "'>Establecer Contraseña</a></p>";
        $contenido .= "<p>Si no creaste esta cuenta puedes ignorar el mensaje.</p>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        //Enviar el mail
        $mail->send();
    }

    public function enviarInstrucciones() {

        // create a new object
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
    
        $mail->setFrom('no-reply@dilaesolar.com');
        $mail->addAddress($this->email, $this->nombre);
        $mail->Subject = 'Reestablece tu password';

        // Set HTML
        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= "<h1>Hola " . $this->nombre .  ":</h1>";
        $contenido .= "<p>Has solicitado reestablecer tu password en Dilae, sigue el siguiente enlace para hacerlo.</p>";
        $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['HOST'] . "/reestablecer?token=" . $this->token . "'>Reestablecer Password</a>";        
        $contenido .= "<p>Si no solicitaste este cambio, puedes ignorar el mensaje</p>";
        $contenido .= '</html>';
        $mail->Body = $contenido;

        //Enviar el mail
        $mail->send();
    }

    public function enviarFormularioContacto($datosFormulario) {
        // Crear un nuevo objeto PHPMailer
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // O 'ssl' si tu puerto es 465

        // Remitente y Destinatario(s)
        // El remitente 'setFrom' idealmente debería ser una dirección de tu dominio.
        // El email del usuario del formulario se usa como 'ReplyTo'.
        $mail->setFrom('no-reply@dilaesolar.com', 'Formulario Contacto Dilae'); // Cambia 'Formulario Contacto Dilae' si quieres
        
        // Email al que se enviará el formulario (el email de DILAE)
        $emailAdmin = 'contacto@dilaesolar.com'; 
        $mail->addAddress($emailAdmin, 'Administrador Dilae');     
        
        // Añadir el email del remitente del formulario como "Responder A"
        if (!empty($datosFormulario['email'])) {
            $mail->addReplyTo($datosFormulario['email'], $datosFormulario['nombre']);
        }

        // Contenido del Email
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Nuevo Mensaje de Contacto: ' . htmlspecialchars($datosFormulario['asunto']);

        $cuerpo = "<html>";
        $cuerpo .= "<head><style>body {font-family: Arial, sans-serif; line-height: 1.6;} h2 {color: #6BB13D;}</style></head>";
        $cuerpo .= "<body>";
        $cuerpo .= "<h2>Nuevo Mensaje Recibido desde el Formulario de Contacto</h2>";
        $cuerpo .= "<p><strong>Nombre:</strong> " . htmlspecialchars($datosFormulario['nombre']) . "</p>";
        $cuerpo .= "<p><strong>Email:</strong> " . htmlspecialchars($datosFormulario['email']) . "</p>";
        if (!empty($datosFormulario['telefono'])) {
            $cuerpo .= "<p><strong>Teléfono:</strong> " . htmlspecialchars($datosFormulario['telefono']) . "</p>";
        }
        $cuerpo .= "<p><strong>Asunto:</strong> " . htmlspecialchars($datosFormulario['asunto']) . "</p>";
        $cuerpo .= "<h3>Mensaje:</h3>";
        $cuerpo .= "<p style='white-space: pre-wrap;'>" . nl2br(htmlspecialchars($datosFormulario['mensaje'])) . "</p>"; // pre-wrap y nl2br para saltos de línea
        $cuerpo .= "</body>";
        $cuerpo .= "</html>";
        
        $mail->Body = $cuerpo;
        $mail->AltBody = strip_tags($cuerpo); // Versión en texto plano

        // Enviar el email
        try {
            return $mail->send();
        } catch (\Exception $e) {
            // Puedes registrar el error si lo deseas: error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}