<?php
session_start();
require_once('admin/inc/config.php');

if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit;
}

$idcliente = $_SESSION['customer']['idcliente'];
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_password'])) {
    $password_actual = $_POST['password_actual'] ?? '';
    $nueva_password = $_POST['nueva_password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    if (empty($password_actual) || empty($nueva_password) || empty($confirmar_password)) {
        $error_message = 'Todos los campos son obligatorios.';
    } elseif ($nueva_password !== $confirmar_password) {
        $error_message = 'Las contraseñas nuevas no coinciden.';
    } else {
        // Obtener hash de la contraseña actual
        $stmt = $pdo->prepare("SELECT password FROM usuario WHERE idcliente = ?");
        $stmt->execute([$idcliente]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password_actual, $usuario['password'])) {
            // Actualizar contraseña con hash
            $hash_nuevo = password_hash($nueva_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuario SET password = ? WHERE idcliente = ?");
            $stmt->execute([$hash_nuevo, $idcliente]);
            $success_message = 'Contraseña actualizada correctamente.';
        } else {
            $error_message = 'La contraseña actual es incorrecta.';
        }
    }
}
?>

<?php require_once('header.php'); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-primary text-white text-center rounded-top-4">
                    <h4 class="mb-0">Cambiar Contraseña</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php elseif ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <input type="hidden" name="form_password" value="1">

                        <div class="mb-3">
                            <label for="password_actual" class="form-label">Contraseña Actual *</label>
                            <input type="password" id="password_actual" name="password_actual" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="nueva_password" class="form-label">Nueva Contraseña *</label>
                            <input type="password" id="nueva_password" name="nueva_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="confirmar_password" class="form-label">Confirmar Nueva Contraseña *</label>
                            <input type="password" id="confirmar_password" name="confirmar_password" class="form-control" required>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success px-4">Actualizar</button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="dashboard.php">← Volver al panel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
