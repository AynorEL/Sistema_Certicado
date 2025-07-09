<?php
// Carga de dependencias y configuración .env
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Verifica que el archivo .env existe y carga las variables de entorno
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} else {
    die('Error: El archivo .env no existe. Por favor, copia env.example a .env y configura tus variables.');
}

// Función para enviar email con PHPMailer
function sendEmailWithPHPMailer($to, $subject, $message, $from_name = 'Sistema de Certificados') {
    $gmail_user = $_ENV['MAIL_USER'] ?? '';
    $gmail_pass = $_ENV['MAIL_PASS'] ?? '';

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $gmail_user;
        $mail->Password = $gmail_pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['MAIL_PORT'] ?? 587;
        $mail->CharSet = 'UTF-8';

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ];

        $mail->setFrom($gmail_user, $from_name);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar email: " . $mail->ErrorInfo);
        return false;
    }
}

// Función básica como fallback en caso de que PHPMailer falle
function sendBasicEmail($to, $subject, $message, $from_email, $from_name) {
    $headers = [
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8",
        "From: {$from_name} <{$from_email}>",
        "Reply-To: {$from_email}",
        "X-Mailer: PHP/" . phpversion()
    ];
    return mail($to, $subject, $message, implode("\r\n", $headers), "-f{$from_email}");
}

// Verifica que las variables necesarias estén configuradas
function isEmailConfigured() {
    return !empty($_ENV['MAIL_USER']) && !empty($_ENV['MAIL_PASS']);
}
?>
