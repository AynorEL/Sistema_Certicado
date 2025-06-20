<?php
session_start();
require_once('admin/inc/config.php');

$error_message = '';

if (isset($_POST['form1'])) {
    if (empty($_POST['nombre_usuario']) || empty($_POST['password'])) {
        $error_message = 'Debe completar todos los campos.';
    } else {
        $nombre_usuario = trim($_POST['nombre_usuario']);
        $password = trim($_POST['password']);

        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE nombre_usuario = ?");
        $stmt->execute([$nombre_usuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            $error_message = 'Usuario no encontrado.';
        } elseif ($usuario['estado'] !== 'Activo') {
            $error_message = 'La cuenta está inactiva.';
        } elseif (!password_verify($password, $usuario['password'])) {
            $error_message = 'Contraseña incorrecta.';
        } else {
            // Obtener datos del cliente relacionado (FK)
            $stmt = $pdo->prepare("SELECT nombre, apellido FROM cliente WHERE idcliente = ?");
            $stmt->execute([$usuario['idcliente']]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                $_SESSION['customer'] = [
                    'idcliente'      => $usuario['idcliente'],
                    'nombre_usuario' => $usuario['nombre_usuario'],
                    'cust_name'      => $cliente['nombre'] . ' ' . $cliente['apellido']
                ];
                header("Location: dashboard.php");
                exit;
            } else {
                $error_message = 'No se encontró el cliente asociado.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .login-card {
            max-width: 420px;
            margin: auto;
            margin-top: 80px;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
        }

        .input-group-text {
            background-color: #fff;
        }

        .form-control:focus {
            box-shadow: none;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card login-card">
        <div class="card-header text-center bg-primary text-white rounded-top">
            <h4 class="mb-0">Iniciar Sesión</h4>
        </div>
        <div class="card-body p-4">
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <input type="hidden" name="form1" value="1">
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="text" name="nombre_usuario" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" required>
                        <span class="input-group-text">
                            <i class="bi bi-eye-slash toggle-password" data-target="password" style="cursor: pointer;"></i>
                        </span>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button class="btn btn-success" type="submit" name="form1">Iniciar sesión</button>
                </div>

                <div class="text-center">
                    ¿No tienes cuenta? <a href="registration.php">Regístrate</a><br>
                    <a href="reset-password.php" class="text-danger">¿Olvidaste tu contraseña?</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mostrar/ocultar contraseña
$(document).ready(function () {
    $(".toggle-password").on("click", function () {
        const input = $("#" + $(this).data("target"));
        const type = input.attr("type") === "password" ? "text" : "password";
        input.attr("type", type);
        $(this).toggleClass("bi-eye bi-eye-slash");
    });
});
</script>

</body>
</html>
