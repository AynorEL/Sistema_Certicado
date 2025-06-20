<?php
session_start();
require_once('admin/inc/config.php');

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = '<div class="alert alert-danger">Correo inválido.</div>';
    } else {
        $stmt = $pdo->prepare("SELECT idusuario FROM usuario WHERE nombre_usuario = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE usuario SET token = ? WHERE idusuario = ?");
            $stmt->execute([$token, $usuario['idusuario']]);

            // En un sistema real, enviarías esto por correo:
            $link = "http://localhost/reset-password.php?token=" . $token;

            $mensaje = '<div class="alert alert-success">
                Se ha generado un enlace de recuperación. <br>
                <strong>Enlace:</strong><br><a href="' . $link . '">' . $link . '</a>
            </div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Correo no encontrado.</div>';
        }
    }
}
?>

<?php require_once('header.php'); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg rounded-4">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Recuperar Contraseña</h4>
                </div>
                <div class="card-body">
                    <?php echo $mensaje; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label for="email">Correo electrónico</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-success">Enviar enlace</button>
                        </div>
                        <div class="mt-3 text-center">
                            <a href="login.php">Volver al inicio de sesión</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
