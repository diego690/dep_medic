<?php
require_once(__DIR__ . "/../loader.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailSender {

    public static function send_mail($setFrom, $sendTo, $subject, $body, $altBody = "Hola, parece que tu administrador de correo electrónico no es compatible con HTML, este mensaje ha sido adaptado para que se pueda leer.") {
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_SERVER;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
    
            //Recipients
            $mail->setFrom(SMTP_USERNAME, $setFrom);
            foreach ($sendTo as $email => $name) {
                $mail->addAddress($email, $name);
            }
    
            //Content
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            //echo "Error: {$mail->ErrorInfo}";
            return false;
        }
    }

}