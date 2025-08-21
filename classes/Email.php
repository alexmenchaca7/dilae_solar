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

    private function getMailerInstance() {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host       = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_USER'];
        $mail->Password   = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'ssl'; 
        $mail->Port       = $_ENV['EMAIL_PORT'];
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->setFrom('contacto@dilaesolar.com', 'Dilae Solar');
        return $mail;
    }

    private function generateStyledHTML($title, $content) {
        $host = $_ENV['HOST']; // Asegúrate que HOST en .env sea tu URL pública, ej: https://www.dilaesolar.com
        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>" . htmlspecialchars($title) . "</title>
            <style> body { margin: 0; padding: 0; } </style>
        </head>
        <body style='margin: 0 !important; padding: 0 !important; background-color: #f5f5f5;'>
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr>
                    <td bgcolor='#f5f5f5' align='center' style='padding: 20px 0;'>
                        <table border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px;'>
                            <tr>
                                <td align='center' bgcolor='#181818' style='padding: 20px 0;'>
                                    <a href='{$host}' target='_blank'>
                                        <img src='{$host}/build/img/logo-white.png' alt='Dilae Solar logo' style='display: block; width: 150px;' border='0'>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td bgcolor='#ffffff' align='left' style='padding: 30px; font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; color: #4D4D4D;'>
                                    {$content}
                                </td>
                            </tr>
                            <tr>
                                <td bgcolor='#f5f5f5' align='center' style='padding: 20px; font-family: Arial, sans-serif; font-size: 12px; line-height: 18px; color: #999999;'>
                                    Dilae Solar | CALZADA DE LAS FLORES #1111, INT LOCAL 4, ZAPOPAN, JAL 45133<br><br>
                                    Este es un correo electrónico automático, por favor no respondas a este mensaje.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
        return $html;
    }

    private function generateStyledButton($url, $text) {
        return "
        <table border='0' cellspacing='0' cellpadding='0' role='presentation' width='100%'>
            <tr>
                <td align='center' style='padding: 20px 0;'>
                    <table border='0' cellspacing='0' cellpadding='0' role='presentation'>
                        <tr>
                            <td align='center' bgcolor='#001F3F' style='border-radius: 5px;'>
                                <a href='{$url}' target='_blank' style='font-size: 16px; font-family: Arial, sans-serif; color: #ffffff; text-decoration: none; padding: 15px 25px; border-radius: 5px; display: inline-block; font-weight: bold;'>{$text}</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>";
    }

    public function enviarConfirmacion() {
        $mail = $this->getMailerInstance();
        $mail->addAddress($this->email, $this->nombre);
        $mail->Subject = 'Establece tu Contraseña en Dilae';

        $buttonURL = $_ENV['HOST'] . "/establecer-password?token=" . $this->token;
        $buttonHTML = $this->generateStyledButton($buttonURL, 'Establecer Contraseña');

        $bodyContent = "
            <h2 style='color: #181818; font-family: Arial, sans-serif;'>¡Hola, {$this->nombre}!</h2>
            <p>Has sido registrado como administrador en Dilae. Para completar el proceso y acceder al panel, necesitas establecer tu contraseña.</p>
            {$buttonHTML}
            <p>Si no esperabas este correo, puedes ignorarlo de forma segura.</p>";
        
        $mail->Body = $this->generateStyledHTML('Establecer Contraseña', $bodyContent);
        $mail->send();
    }

    public function enviarConfirmacionSuscripcion() {
        $mail = $this->getMailerInstance();
        $mail->addAddress($this->email, 'Nuevo Suscriptor');
        $mail->Subject = 'Confirma tu suscripción a Dilae';

        $buttonURL = $_ENV['HOST'] . "/confirmar-suscripcion?token=" . $this->token;
        $buttonHTML = $this->generateStyledButton($buttonURL, 'Confirmar mi Suscripción');

        $bodyContent = "
            <h2 style='color: #181818; font-family: Arial, sans-serif;'>Casi listo...</h2>
            <p>Gracias por tu interés en nuestro boletín. Para completar tu suscripción, por favor confirma tu correo electrónico haciendo clic en el siguiente botón:</p>
            {$buttonHTML}
            <p>Si no solicitaste esto, puedes ignorar este correo de forma segura.</p>";

        $mail->Body = $this->generateStyledHTML('Confirmar Suscripción', $bodyContent);
        $mail->send();
    }

    public function enviarInstrucciones() {
        $mail = $this->getMailerInstance();
        $mail->addAddress($this->email, $this->nombre);
        $mail->Subject = 'Reestablece tu Contraseña';

        $buttonURL = $_ENV['HOST'] . "/reestablecer?token=" . $this->token;
        $buttonHTML = $this->generateStyledButton($buttonURL, 'Reestablecer Contraseña');

        $bodyContent = "
            <h2 style='color: #181818; font-family: Arial, sans-serif;'>¡Hola, {$this->nombre}!</h2>
            <p>Has solicitado reestablecer tu contraseña. Haz clic en el siguiente botón para continuar con el proceso.</p>
            {$buttonHTML}
            <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>";

        $mail->Body = $this->generateStyledHTML('Reestablecer Contraseña', $bodyContent);
        $mail->send();
    }

    public function enviarFormularioContacto($datosFormulario) {
        $mail = $this->getMailerInstance();
        $emailAdmin = 'forms@dilaesolar.com'; 
        $mail->addAddress($emailAdmin, 'Administrador Dilae Solar');     
        if (!empty($datosFormulario['email'])) {
            $mail->addReplyTo($datosFormulario['email'], $datosFormulario['nombre']);
        }
        $mail->Subject = 'Nuevo Mensaje desde el Formulario de Contacto';

        $bodyContent = "
            <h2 style='color: #181818; font-family: Arial, sans-serif;'>Nuevo Mensaje de Contacto</h2>
            <p><strong>Nombre:</strong> " . htmlspecialchars($datosFormulario['nombre']) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($datosFormulario['email']) . "</p>
            <p><strong>Telefono de Contacto:</strong> " . htmlspecialchars($datosFormulario['telefono']) . "</p>
            <p><strong>Horario de Preferencia:</strong> " . htmlspecialchars($datosFormulario['horario']) . "</p>
            <p><strong>Codigo Postal:</strong> " . htmlspecialchars($datosFormulario['codigo_postal']) . "</p>
            <h3 style='color: #181818; border-top: 1px solid #eeeeee; padding-top: 20px; margin-top: 20px;'>Mensaje:</h3>
            <p style='background-color: #f5f5f5; padding: 15px; border-radius: 5px;'>" . nl2br(htmlspecialchars($datosFormulario['mensaje'])) . "</p>
        ";
        
        $mail->Body = $this->generateStyledHTML('Nuevo Mensaje de Contacto', $bodyContent);
        try {
            return $mail->send();
        } catch (\Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    public function enviarNotificacionSuscripcion($emailUsuario) {
        $mailAdmin = $this->getMailerInstance();
        $mailAdmin->addAddress('contacto@dilaesolar.com', 'Administrador Dilae Solar');
        $mailAdmin->Subject = 'Nueva Suscripción al Boletín';

        $bodyContent = "
            <h2 style='color: #181818; font-family: Arial, sans-serif;'>Nueva Suscripción</h2>
            <p>Un nuevo usuario se ha suscrito al boletín a través de la web.</p>
            <p><strong>Correo electrónico:</strong> " . htmlspecialchars($emailUsuario) . "</p>
            <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
        ";

        $mailAdmin->Body = $this->generateStyledHTML('Nueva Suscripción', $bodyContent);
        try {
            return $mailAdmin->send();
        } catch (\Exception $e) {
            error_log("Mailer Error (Notificación de Suscripción): " . $mailAdmin->ErrorInfo);
            return false;
        }
    }
}