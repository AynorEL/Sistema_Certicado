<?php
session_start();
require_once('admin/inc/config.php');

$error_message = '';
$success_message = '';

$nombre = $apellido = $dni = $telefono = $email = $direccion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form1'])) {
    $nombre     = trim($_POST['nombre'] ?? '');
    $apellido   = trim($_POST['apellido'] ?? '');
    $dni        = trim($_POST['dni'] ?? '');
    $telefono   = trim($_POST['telefono'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $direccion  = trim($_POST['direccion'] ?? '');
    $password   = $_POST['password'] ?? '';
    $repassword = $_POST['repassword'] ?? '';

    if (empty($nombre) || empty($apellido) || empty($telefono) || empty($email) || empty($direccion) || empty($password) || empty($repassword)) {
        $error_message = "Todos los campos obligatorios deben completarse.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Correo electrónico no válido.";
    } elseif ($password !== $repassword) {
        $error_message = "Las contraseñas no coinciden.";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cliente WHERE email = ?");
        $stmt->execute([$email]);
        $email_cliente = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE nombre_usuario = ?");
        $stmt->execute([$email]);
        $email_usuario = $stmt->fetchColumn();

        if ($email_cliente > 0 || $email_usuario > 0) {
            $error_message = "Este correo ya está registrado.";
        } else {
            try {
                $pdo->beginTransaction();

                $fecha = date('Y-m-d H:i:s');
                $stmt = $pdo->prepare("INSERT INTO cliente (nombre, apellido, dni, telefono, email, direccion, fecha_registro, estado) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo')");
                $stmt->execute([$nombre, $apellido, $dni, $telefono, $email, $direccion, $fecha]);
                $idcliente = $pdo->lastInsertId();

                $hash = password_hash($password, PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(32));

                $stmt = $pdo->prepare("INSERT INTO usuario (nombre_usuario, password, idrol, estado, idcliente, token) 
                                       VALUES (?, ?, 3, 'Activo', ?, ?)");
                $stmt->execute([$email, $hash, $idcliente, $token]);

                $pdo->commit();
                $success_message = "Registro exitoso. Ahora puede iniciar sesión.";
                $nombre = $apellido = $dni = $telefono = $email = $direccion = '';
            } catch (Exception $e) {
                $pdo->rollBack();
                $error_message = "Error al registrar. Intenta de nuevo.";
            }
        }
    }
}
?>

<?php require_once('header.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white text-center rounded-top-4">
                    <h4 class="mb-0">Formulario de Registro</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php elseif ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <form method="post" id="registroForm" novalidate>
                        <input type="hidden" name="form1" value="1" />

                        <h5 class="text-primary mb-3">1. Datos personales</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Nombres *</label>
                                <input type="text" name="nombre" class="form-control" required value="<?php echo htmlspecialchars($nombre); ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Apellidos *</label>
                                <input type="text" name="apellido" class="form-control" required value="<?php echo htmlspecialchars($apellido); ?>">
                            </div>
                            <div class="col-md-6">
                                <label>DNI</label>
                                <input type="text" name="dni" class="form-control" value="<?php echo htmlspecialchars($dni); ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Teléfono *</label>
                                <input type="text" name="telefono" class="form-control" required value="<?php echo htmlspecialchars($telefono); ?>">
                            </div>
                            <div class="col-md-12">
                                <label>Dirección *</label>
                                <input type="text" name="direccion" class="form-control" required value="<?php echo htmlspecialchars($direccion); ?>">
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="text-primary mb-3">2. Datos de acceso</h5>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label>Correo electrónico *</label>
                                <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($email); ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Contraseña *</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" required>
                                    <span class="input-group-text"><i class="bi bi-eye-slash toggle-password" data-target="password" style="cursor: pointer;"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Repetir contraseña *</label>
                                <div class="input-group">
                                    <input type="password" name="repassword" id="repassword" class="form-control" required>
                                    <span class="input-group-text"><i class="bi bi-eye-slash toggle-password" data-target="repassword" style="cursor: pointer;"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success px-5">Registrarse</button>
                        </div>

                        <div class="text-center mt-3">
                            ¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar/ocultar contraseña
$(document).ready(function () {
    $(".toggle-password").on("click", function () {
        const target = $(this).data("target");
        const input = $("#" + target);
        const type = input.attr("type") === "password" ? "text" : "password";
        input.attr("type", type);
        $(this).toggleClass("bi-eye bi-eye-slash");
    });
});

// Validación en tiempo real de contraseña
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("registroForm");
    const password = form.querySelector("input[name='password']");
    const repassword = form.querySelector("input[name='repassword']");

    repassword.addEventListener("input", () => {
        repassword.setCustomValidity(repassword.value !== password.value ? "Las contraseñas no coinciden." : "");
    });

    form.addEventListener("submit", function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add("was-validated");
    }, false);
});
</script>

<?php require_once('footer.php'); ?>
