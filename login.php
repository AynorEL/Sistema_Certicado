<?php
session_start();
require_once('admin/inc/config.php');

$error_message = '';

// Verificar si existe una cookie de sesión
if(!isset($_SESSION['customer']) && isset($_COOKIE['customer_remember'])) {
    try {
        $cookie_data = json_decode($_COOKIE['customer_remember'], true);
        
        if($cookie_data && isset($cookie_data['idcliente']) && isset($cookie_data['nombre_usuario'])) {
            // Verificar que el usuario aún existe y está activo
            $stmt = $pdo->prepare("SELECT u.*, c.nombre, c.apellido FROM usuario u 
                                  LEFT JOIN cliente c ON u.idcliente = c.idcliente 
                                  WHERE u.idusuario = ? AND u.estado = ?");
            $stmt->execute(array($cookie_data['idusuario'], 'Activo'));
            
            if($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $_SESSION['customer'] = [
                    'idcliente'      => $user['idcliente'],
                    'nombre_usuario' => $user['nombre_usuario'],
                    'cust_name'      => $user['nombre'] . ' ' . $user['apellido']
                ];
                header("Location: dashboard.php");
                exit;
            }
        }
    } catch(Exception $e) {
        // Si hay error, simplemente continuamos con el login normal
    }
}

// Si ya está logueado, redirigir a dashboard
if(isset($_SESSION['customer'])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['form1'])) {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        $error_message = 'Debe completar todos los campos.';
    } else {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $remember = isset($_POST['remember']) ? true : false;

        // Buscar en la tabla cliente
        $stmt = $pdo->prepare("SELECT * FROM cliente WHERE email = ?");
        $stmt->execute([$email]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cliente) {
            $error_message = 'Correo no registrado.';
        } elseif (!password_verify($password, $cliente['password'])) {
            $error_message = 'Contraseña incorrecta.';
        } else {
            // Login exitoso
            $_SESSION['customer'] = [
                'idcliente' => $cliente['idcliente'],
                'nombre'    => $cliente['nombre'],
                'apellido'  => $cliente['apellido'],
                'email'     => $cliente['email']
            ];
            // Recordarme (opcional, puedes implementar cookie si lo deseas)
            header("Location: dashboard.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Sistema de Certificados</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

        .login-container {
            width: 100%;
            max-width: 450px;
            margin: 20px;
            position: relative;
            z-index: 1;
        }

        .login-card {
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

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
        }

        .login-header::after {
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

        .login-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .login-header p {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
        }

        .login-body {
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

        .input-group {
            position: relative;
        }

        .input-group-text {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            z-index: 10;
            transition: color 0.3s ease;
        }

        .input-group-text:hover {
            color: #667eea;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .form-check-label {
            font-weight: 500;
            color: #374151;
            cursor: pointer;
        }

        .btn-login {
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

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #764ba2;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-weight: 500;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .alert-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #16a34a;
            border-left: 4px solid #16a34a;
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
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
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        @media (max-width: 768px) {
            .login-container {
                margin: 10px;
            }
            
            .login-header {
                padding: 30px 20px 20px;
            }
            
            .login-header h1 {
                font-size: 1.8rem;
            }
            
            .login-body {
                padding: 30px 20px 20px;
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

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Iniciar Sesión</h1>
                <p>Accede con tu correo electrónico y contraseña de cliente</p>
            </div>
            
            <div class="login-body">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="post" autocomplete="on">
                    <input type="hidden" name="form1" value="1" />
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="ejemplo@correo.com" autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Tu contraseña">
                            <span class="input-group-text"><i class="bi bi-eye-slash toggle-password" data-target="password"></i></span>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Mantener sesión iniciada</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Iniciar Sesión</button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="forget-password.php">¿Olvidaste tu contraseña?</a>
                    </div>
                    <div class="text-center mt-2">
                        ¿No tienes cuenta? <a href="registration.php">Regístrate aquí</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $(".toggle-password").on("click", function () {
                const target = $(this).data("target");
                const input = $("#" + target);
                const type = input.attr("type") === "password" ? "text" : "password";
                input.attr("type", type);
                $(this).toggleClass("bi-eye bi-eye-slash");
            });

            // Animación de entrada
            $('.login-card').hide().fadeIn(800);

            // Efecto de focus en los inputs
            $('.form-control').on('focus', function() {
                $(this).parent().addClass('focused');
            }).on('blur', function() {
                $(this).parent().removeClass('focused');
            });
        });
    </script>
</body>
</html>
