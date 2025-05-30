<?php

use  PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('PHPMailer/PHPMailer/src/PHPMailer.php');
require_once('PHPMailer/PHPMailer/src/SMTP.php');
require_once('PHPMailer/PHPMailer/src/Exception.php');
require_once __DIR__ . '/../INCLUDES/DatabaseHandler.php';
require_once __DIR__ . '/../MiLog.php';


class MailService 
{
    private $mail;
    private $dbHandler;

    public function __construct($database)
    {
        $this->dbHandler = $database;

        $this->mail = new PHPMailer(true);
        // Configuración del servidor SMTP
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.hostinger.com'; // Servidor SMTP de Gmail
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'administracion@tecnocomerciodigital.com'; // Tu correo
        $this->mail->Password = 'Tecno/3057919818'; // Tu contraseña o clave de aplicación
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        //quien lo envia
        $this->mail->setFrom('administracion@tecnocomerciodigital.com', 'tecnocomerciodigital');


    }
    
    public function enviarMail($datos)
    {
       // writeLog("MailService.enviarMail. INICIO");
        
        $destinatario = $datos['email'];
        $tipo_mail=$datos['tipo_mail'];
        $nombre = $datos['nombre'];
        $templatePath = '';

        $telefono = '';
        $message = '';
       // writeLog("MailService.enviarMail. antes del switch ". $tipo_mail);
        
    try {
        switch($tipo_mail)
        {
            case "cambiocontraseña":
                        $templateFile = __DIR__ . '\mailpass.html';
                        if (! file_exists($templateFile)) {
                            throw new \Exception("No existe la plantilla de mail:". $templateFile);
                        }
                    $this->mail->Subject = 'Cambio de contraseña';
                     // $resetLink = "https://tecnocomerciodigital.com/comerciodigital/login/reset-password/" . $this->insertartoken($datos);
                   //  $resetLink = "http://localhost:4200/comerciodigital/auth/reset-password/" . $this->insertartoken($datos);
                    
                    $resetLink = "https://tecnocomerciodigital.com/comerciodigital/auth/reset-password/" . $this->insertartoken($datos);
                
                break;
            case "contacto":
                    $templateFile = __DIR__ . '\mailcontacto.html';
                    if (! file_exists($templateFile)) {
                        throw new \Exception("No existe la plantilla de mail:". $templateFile);
                    }
                    $this->mail->Subject = 'Confirmacion de pedido de llamada';
                    $telefono = htmlspecialchars($datos['telefono'], ENT_QUOTES, 'UTF-8');
                    $nombre = htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8');
                    $message = htmlspecialchars($datos['message'], ENT_QUOTES, 'UTF-8');
                    $this->mail->addBCC('tecnocomerciodigital1@gmail.com', 'Camilo Ruiz');//copia oculta

                break;
            case "ValidarMail":
                    $templateFile = __DIR__ . '\mailValidar.html';
                    if (! file_exists($templateFile)) {
                        throw new \Exception("No existe la plantilla de mail:". $templateFile);
                    }
                $this->mail->Subject = 'Validar Email';
                 // $resetLink = "https://tecnocomerciodigital.com/comerciodigital/login/reset-password/" . $this->insertartoken($datos);
                
                $resetLink = "https://tecnocomerciodigital.com/tecnocomerciodigital/Login/ValidarMail?token=" . $this->insertartoken($datos);
            
            break;
        }
        
        writeLog("MailService.enviarMail. Carga el contenido del archivo");

        // Carga el contenido del archivo
        $templateContent = file_get_contents($templateFile);
        // Reemplaza los marcadores con los valores personalizados
        $customizedContent = str_replace(
            ['{email}', '{telefono}','{nombre}','{mensaje}', '{resetLink}'], // Marcadores en el HTML
            [ $destinatario, $telefono, $nombre, $message, $resetLink], // Valores a reemplazar
            $templateContent
            );

        $this->mail->addAddress($destinatario, $nombre);
        
        $this->mail->isHTML(true);
        $this->mail->Body = $customizedContent;   
        
       // writeLog("MailService.enviarMail. antes de enviar el MAIL ");

        // Enviar correo
        if ($this->mail->send()) {
            return 'OK';
        } else {
          //  writeLog("MailService.enviarMail. ERROR al enviar el correo: " . $this->mail->ErrorInfo);
            return 'ERROR';
        }
        
        } catch (Exception $e) {
        
            return 'ERROR '.$e ;
        }
    }

    private function insertartoken($datos)
    {
        writeLog("MailService.insertartoken. INICIO");

        try
        {
        // creamos un token
        $token = $this->generarToken();
        $expiracion = date('Y-m-d H:i:s', strtotime('+60 minutes')); // Expira en 60 minutos
        writeLog("MailService.insertartoken. Token generado". $token);

        $reset = [
            'email'   => $datos['email'],
            'token'   => $token,
            'expiracion'    => $expiracion
        ];
        writeLog("MailService.insertartoken. por inertar en BD");

        // Insertar en base de datos (ajusta según tu clase y método de inserción)
        $result = $this->dbHandler->insert('reset_password', $reset);

        writeLog("MailService.insertartoken. Fin");
        
        return urlencode($token);
        } catch (Exception $e) {
            exit;
        }
    }

    private function generarToken() {
        return uniqid(mt_rand(), true);
    }
}
?>