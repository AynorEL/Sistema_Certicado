<?php
session_start();
require_once(__DIR__ . '/admin/inc/config.php');
$statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
$statement->execute();
$row = $statement->fetch(PDO::FETCH_ASSOC);
$favicon = $row['favicon'] ?? 'favicon.png';
?>

$error_message = '';
$success_message = '';

// Verificar si se proporcionó idcliente y token
if (!isset($_GET['idcliente']) || !isset($_GET['token'])) {
    header('location: login.php');
    exit;
}

$idcliente = $_GET['idcliente'];
$token = $_GET['token'];

// Buscar cliente con ese id y token válido
$stmt = $pdo->prepare("SELECT * FROM cliente WHERE idcliente = ? AND token = ?");
$stmt->execute([$idcliente, $token]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    $error_message = 'El enlace de restablecimiento de contraseña no es válido.';
} elseif (empty($cliente['token_expira']) || strtotime($cliente['token_expira']) < time()) {
    $error_message = 'El enlace ha expirado. Solicita uno nuevo.';
}

// Procesar el formulario de restablecimiento
if (isset($_POST['form1']) && !$error_message) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validaciones
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = 'Debe completar todos los campos.';
    } elseif (strlen($new_password) < 4) {
        $error_message = 'La contraseña debe tener al menos 4 caracteres.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'Las contraseñas no coinciden.';
    } else {
        // Actualizar la contraseña
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE cliente SET password = ?, token = NULL, token_expira = NULL WHERE idcliente = ?");
            $stmt->execute([$hashed_password, $idcliente]);
            $success_message = 'Contraseña actualizada exitosamente. Redirigiendo al login...';
            header("refresh:3;url=login.php");
        } catch (PDOException $e) {
            $error_message = 'Error al actualizar la contraseña. Inténtelo de nuevo.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña - Sistema de Certificados</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="assets/uploads/<?php echo $favicon; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="admin/js/jquery-2.2.4.min.js"></script>
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

        .reset-container {
            width: 100%;
            max-width: 500px;
            margin: 20px;
            position: relative;
            z-index: 1;
        }

        .reset-card {
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

        .reset-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        }

        .reset-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
        }

        .reset-header::after {
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

        .reset-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .reset-header p {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
        }

        .reset-body {
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

        .btn-reset {
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

        .btn-reset::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-reset:hover::before {
            left: 100%;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-reset:active {
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

        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border-left: 4px solid #17a2b8;
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

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 2;
        }

        .form-control {
            padding-right: 50px;
        }

        @media (max-width: 576px) {
            .reset-container {
                margin: 10px;
            }
            
            .reset-header h1 {
                font-size: 1.8rem;
            }
            
            .reset-body {
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

    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <h1><i class="bi bi-shield-lock"></i></h1>
                <h1>Restablecer Contraseña</h1>
                <p>Sistema de Certificados</p>
            </div>
            
            <div class="reset-body">
                <?php if($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                    <a href="forget-password.php" class="btn-back">
                        <i class="bi bi-arrow-left me-2"></i>
                        Volver a solicitar restablecimiento
                    </a>
                <?php elseif($success_message): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo $success_message; ?>
                    </div>
                    <div class="text-center">
                        <p class="text-muted">Serás redirigido al login en unos segundos...</p>
                        <a href="login.php" class="btn-back">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Ir al Login
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Hola <?php echo htmlspecialchars($cliente['nombre_cliente'] ?? ''); ?>!</strong><br>
                        Establece tu nueva contraseña a continuación.
                    </div>

                    <form method="POST" id="resetForm">
                        <input type="hidden" name="form1" value="1">
                        
                        <div class="form-group">
                            <label for="new_password" class="form-label">
                                <i class="bi bi-lock me-2"></i>
                                Nueva Contraseña
                            </label>
                            <div style="position: relative;">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" 
                                       class="form-control" 
                                       id="new_password" 
                                       name="new_password" 
                                       placeholder="Mínimo 4 caracteres"
                                       required>
                                <i class="bi bi-eye-slash password-toggle" data-target="new_password"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-lock-fill me-2"></i>
                                Confirmar Contraseña
                            </label>
                            <div style="position: relative;">
                                <i class="bi bi-lock-fill input-icon"></i>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       placeholder="Repite la contraseña"
                                       required>
                                <i class="bi bi-eye-slash password-toggle" data-target="confirm_password"></i>
                            </div>
                        </div>

                        <button class="btn btn-reset" type="submit" name="form1">
                            <i class="bi bi-check-circle me-2"></i>
                            Actualizar Contraseña
                        </button>
                    </form>
                    
                    <a href="login.php" class="btn-back">
                        <i class="bi bi-arrow-left me-2"></i>
                        Volver al Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Animación del formulario
            $('.reset-card').hide().fadeIn(800);
            
            // Mostrar/ocultar contraseña
            $('.password-toggle').on('click', function() {
                const target = $(this).data('target');
                const input = $('#' + target);
                const type = input.attr('type') === 'password' ? 'text' : 'password';
                
                input.attr('type', type);
                $(this).toggleClass('bi-eye bi-eye-slash');
            });
            
            // Validación en tiempo real
            $('#confirm_password').on('input', function() {
                const newPassword = $('#new_password').val();
                const confirmPassword = $(this).val();
                
                if (confirmPassword && newPassword !== confirmPassword) {
                    $(this).css('border-color', '#dc3545');
                    $(this).css('box-shadow', '0 0 0 4px rgba(220, 53, 69, 0.1)');
                } else {
                    $(this).css('border-color', '#e5e7eb');
                    $(this).css('box-shadow', 'none');
                }
            });
            
            // Efecto de carga en el botón
            $('#resetForm').on('submit', function() {
                $('.btn-reset').html('<i class="bi bi-hourglass-split me-2"></i>Actualizando...');
                $('.btn-reset').prop('disabled', true);
            });
        });
    </script>
</body>
</html>