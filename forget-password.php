<?php
session_start();
include "admin/inc/config.php";
include "phpmailer-config.php"; // Incluir configuración de email

$error_message = '';
$success_message = '';

// Debug: mostrar información del POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST recibido: " . print_r($_POST, true));
}

if(isset($_POST['form1'])) {
    $email = trim($_POST['email']);
    
    if(empty($email)) {
        $error_message = "Por favor, ingresa tu dirección de correo electrónico.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Por favor, ingresa una dirección de correo electrónico válida.";
    } else {
        // Verificar si el email existe en la tabla usuario (nombre_usuario)
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE nombre_usuario = ? AND estado = 'Activo'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if(!$user) {
            $error_message = "No se encontró una cuenta con esa dirección de correo electrónico.";
        } else {
            // Generar token único
            $token = bin2hex(random_bytes(32));
            
            // Guardar token en la base de datos
            $stmt = $pdo->prepare("UPDATE usuario SET token = ? WHERE idusuario = ?");
            $result = $stmt->execute([$token, $user['idusuario']]);
            
            // Verificar si se guardó correctamente
            if ($result) {
                // Verificar que realmente se guardó
                $stmt = $pdo->prepare("SELECT token FROM usuario WHERE idusuario = ?");
                $stmt->execute([$user['idusuario']]);
                $verification = $stmt->fetch();
                
                if ($verification && $verification['token'] === $token) {
                    // Token guardado correctamente - intentar enviar email
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $token;
                    
                    // Crear mensaje HTML
                    $subject = "Restablecimiento de Contraseña - Sistema de Certificados";
                    $message = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                            .btn { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; }
                            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>🔐 Restablecimiento de Contraseña</h1>
                                <p>Sistema de Certificados</p>
                            </div>
                            <div class='content'>
                                <h2>Hola,</h2>
                                <p>Has solicitado restablecer tu contraseña. Haz clic en el botón de abajo para continuar:</p>
                                
                                <div style='text-align: center;'>
                                    <a href='{$reset_link}' class='btn'>Restablecer Contraseña</a>
                                </div>
                                
                                <p>O copia y pega este enlace en tu navegador:</p>
                                <p style='word-break: break-all; background: #f0f0f0; padding: 10px; border-radius: 5px;'>{$reset_link}</p>
                                
                                <div class='warning'>
                                    <strong>⚠️ Importante:</strong>
                                    <ul>
                                        <li>Este enlace expira en 1 hora</li>
                                        <li>Si no solicitaste este cambio, ignora este email</li>
                                        <li>Nunca compartas este enlace con nadie</li>
                                    </ul>
                                </div>
                                
                                <p>Saludos,<br>Equipo de Sistema de Certificados</p>
                            </div>
                        </div>
                    </body>
                    </html>";
                    
                    // Intentar enviar email
                    $email_sent = sendEmailWithPHPMailer($email, $subject, $message);
                    
                    if ($email_sent) {
                        $success_message = "✅ Se ha enviado un enlace de restablecimiento a tu correo electrónico. Revisa tu bandeja de entrada y carpeta de spam.";
                    } else {
                        // Si falla el email, mostrar el enlace directamente
                        $success_message = "Tu enlace de restablecimiento de contraseña está listo. Copia y pega este enlace en tu navegador: <br><br><strong>" . $reset_link . "</strong>";
                    }
                } else {
                    $error_message = "Error al guardar el token. Por favor, inténtalo de nuevo.";
                }
            } else {
                $error_message = "Error al actualizar la base de datos. Por favor, inténtalo de nuevo.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - Sistema de Certificados</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }

        .forget-container {
            width: 100%;
            max-width: 500px;
            margin: 20px;
            position: relative;
            z-index: 1;
        }

        .forget-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 8px 16px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            position: relative;
        }

        .forget-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        }

        .forget-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
        }

        .forget-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .forget-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .forget-header p {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
        }

        .forget-body {
            padding: 40px 30px 30px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            display: block;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .btn-send {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-send::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-send:hover::before {
            left: 100%;
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-send:active {
            transform: translateY(0);
        }

        .btn-back {
            width: 100%;
            padding: 12px;
            background: transparent;
            border: 2px solid #667eea;
            border-radius: 12px;
            color: #667eea;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 16px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-back:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-weight: 500;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.2rem;
            z-index: 2;
        }

        .form-control {
            padding-left: 50px;
        }

        @media (max-width: 576px) {
            .forget-container {
                margin: 10px;
            }
            
            .forget-header h1 {
                font-size: 1.8rem;
            }
            
            .forget-body {
                padding: 30px 20px 20px;
            }
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="forget-container">
        <div class="forget-card">
            <div class="forget-header">
                <h1><i class="bi bi-shield-lock"></i></h1>
                <h1>Recuperar Contraseña</h1>
                <p>Sistema de Certificados</p>
            </div>
            
            <div class="forget-body">
                <?php if($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($success_message): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" id="forgetForm">
                    <input type="hidden" name="form1" value="1">
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-2"></i>
                            Correo Electrónico
                        </label>
                        <div style="position: relative;">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   placeholder="Ingresa tu correo electrónico"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   required>
                        </div>
                    </div>
                    
                    <button class="btn btn-send" type="submit">
                        <i class="bi bi-send me-2"></i>
                        Enviar
                    </button>
                </form>
                
                <a href="login.php" class="btn-back">
                    <i class="bi bi-arrow-left me-2"></i>
                    Volver al Login
                </a>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Validación en tiempo real
            $('#email').on('input', function() {
                const email = $(this).val();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    $(this).css('border-color', '#dc3545');
                    $(this).css('box-shadow', '0 0 0 4px rgba(220, 53, 69, 0.1)');
                } else {
                    $(this).css('border-color', '#e5e7eb');
                    $(this).css('box-shadow', 'none');
                }
            });
            
            // Animación del formulario
            $('.forget-card').hide().fadeIn(800);
            
            // Efecto de carga en el botón
            $('#forgetForm').on('submit', function() {
                $('.btn-send').html('<i class="bi bi-hourglass-split me-2"></i>Enviando...');
                $('.btn-send').prop('disabled', true);
            });
        });
    </script>
</body>
</html>