<?php
session_start();
require_once('inc/config.php');
require_once('../vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

function envOrError($key, $fallback = null) {
    $value = $_ENV[$key] ?? getenv($key);
    if (!$value && $fallback) {
        $value = $_ENV[$fallback] ?? getenv($fallback);
    }
    if (!$value) {
        die('<div class="alert alert-danger">Falta la variable de entorno: ' . htmlspecialchars($key) . ($fallback ? ' o ' . htmlspecialchars($fallback) : '') . '</div>');
    }
    return $value;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = '<div class="alert alert-danger">Correo inválido.</div>';
    } else {
        $stmt = $pdo->prepare("SELECT id_usuario, estado FROM usuarios_admin WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$usuario) {
            $mensaje = '<div class="alert alert-danger">El correo electrónico no está registrado.</div>';
        } elseif ($usuario['estado'] !== 'Activo') {
            $mensaje = '<div class="alert alert-danger">El usuario está inactivo. Contacte al administrador.</div>';
        } else {
            $token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE usuarios_admin SET reset_token = ? WHERE id_usuario = ?");
            $stmt->execute([$token, $usuario['id_usuario']]);
            $link = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/reset-password.php?token=" . $token . "&id=" . $usuario['id_usuario'];
            // Enviar correo con PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = envOrError('MAIL_HOST');
                $mail->SMTPAuth = true;
                $mail->Username = envOrError('MAIL_USERNAME', 'MAIL_USER');
                $mail->Password = envOrError('MAIL_PASSWORD', 'MAIL_PASS');
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = envOrError('MAIL_PORT');
                $mail->setFrom(envOrError('MAIL_FROM', 'MAIL_USER'), envOrError('MAIL_FROM_NAME'));
                $mail->addAddress($correo);
                $mail->isHTML(true);
                $mail->Subject = 'Recuperación de contraseña - Sistema de Certificados';
                $mail->Body = '<p>Hola,</p><p>Haz clic en el siguiente enlace para restablecer tu contraseña de administrador:</p><p><a href="' . $link . '">' . $link . '</a></p><p>Si no solicitaste este cambio, ignora este correo.</p>';
                $mail->send();
                $mensaje = '<div class="alert alert-success">Se ha enviado un enlace de recuperación a tu correo electrónico.</div>';
            } catch (Exception $e) {
                $mensaje = '<div class="alert alert-danger">No se pudo enviar el correo. Intenta más tarde.<br>Error: ' . $mail->ErrorInfo . '</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Admin</title>
    <link href="../bootstrap-5.3.7-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1a1a2e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            width: 400px;
            padding: 2rem;
            text-align: center;
            background: #2a2b38;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        .title {
            margin-bottom: 1.5rem;
            font-size: 1.5em;
            font-weight: 500;
            color: #f5f5f5;
        }
        .btn {
            margin: 1.5rem 0 0.5rem 0;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 1em;
            text-transform: uppercase;
            padding: 0.8em 1.5em;
            background-color: #ffeba7;
            color: #5e6681;
            box-shadow: 0 8px 24px 0 rgb(255 235 167 / 20%);
            transition: all .3s ease-in-out;
            width: 100%;
        }
        .btn-link {
            color: #f5f5f5;
            display: block;
            font-size: .9em;
            transition: color .3s ease-out;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #5e6681;
            color: #ffeba7;
            box-shadow: 0 8px 24px 0 rgb(16 39 112 / 20%);
        }
        .btn-link:hover {
            color: #ffeba7;
        }
        .alert {
            background-color: #ff4444;
            color: white;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background-color: #28a745;
            color: white;
        }
        .form-label, label {
            color: #f5f5f5;
            text-align: left;
            width: 100%;
        }
        .form-control {
            background: #23243a;
            color: #f5f5f5;
            border: 1px solid #444;
        }
        .form-control:focus {
            background: #23243a;
            color: #fff;
            border-color: #ffeba7;
        }
    </style>
</head>
<body>
    <div class="card">
        <h4 class="title">Recuperar Contraseña</h4>
        <?php echo $mensaje; ?>
        <form method="post" action="">
            <div class="mb-3 text-start">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" name="correo" class="form-control" required>
            </div>
            <button type="submit" class="btn">Enviar enlace</button>
            <a href="login.php" class="btn-link mt-2">Volver al inicio de sesión</a>
        </form>
    </div>
    <script src="../bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 