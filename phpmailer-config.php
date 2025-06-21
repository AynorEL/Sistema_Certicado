<?php
// Configuración PHPMailer para Gmail - Sistema de Certificados
require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Función para enviar email con PHPMailer
function sendEmailWithPHPMailer($to, $subject, $message, $from_name = 'Sistema de Certificados') {
    $gmail_user = 'ronaldoramirez051@gmail.com';
    $gmail_pass = 'dixphxnnkiqavmdd';
    
    try {
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $gmail_user;
        $mail->Password = $gmail_pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->setFrom($gmail_user, $from_name);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando email: " . $e->getMessage());
        return false;
    }
}

// Función básica de email como fallback
function sendBasicEmail($to, $subject, $message, $from_email, $from_name) {
    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/html; charset=UTF-8";
    $headers[] = "From: {$from_name} <{$from_email}>";
    $headers[] = "Reply-To: {$from_email}";
    $headers[] = "X-Mailer: PHP/" . phpversion();
    
    $header_string = implode("\r\n", $headers);
    return mail($to, $subject, $message, $header_string, "-f{$from_email}");
}

// Función para verificar configuración
function isEmailConfigured() {
    $config_content = file_get_contents(__DIR__ . '/phpmailer-config.php');
    return file_exists(__DIR__ . '/phpmailer-config.php') && 
           strpos($config_content, 'ronaldoramirez051@gmail.com') !== false &&
           strpos($config_content, 'dixphxnnkiqavmdd') !== false;
}
?> 