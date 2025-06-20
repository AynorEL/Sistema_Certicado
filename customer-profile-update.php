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

// Obtener datos actuales para mostrar en el formulario
$stmt = $pdo->prepare("SELECT nombre, apellido, dni, telefono, email, direccion FROM cliente WHERE idcliente = ?");
$stmt->execute([$idcliente]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    // Si no se encuentra cliente, cerrar sesión por seguridad
    header("Location: logout.php");
    exit;
}

// Inicializar variables con datos actuales o con valores POST (si hubo submit con error)
$nombre = $_POST['nombre'] ?? $cliente['nombre'];
$apellido = $_POST['apellido'] ?? $cliente['apellido'];
$dni = $_POST['dni'] ?? $cliente['dni'];
$telefono = $_POST['telefono'] ?? $cliente['telefono'];
$email = $_POST['email'] ?? $cliente['email'];
$direccion = $_POST['direccion'] ?? $cliente['direccion'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_profile'])) {
    // Validar campos obligatorios
    if (empty($nombre) || empty($apellido) || empty($telefono) || empty($email) || empty($direccion)) {
        $error_message = "Por favor, completa todos los campos obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Correo electrónico no válido.";
    } else {
        // Verificar que el email no esté registrado en otro cliente
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cliente WHERE email = ? AND idcliente != ?");
        $stmt->execute([$email, $idcliente]);
        $count_email = $stmt->fetchColumn();

        if ($count_email > 0) {
            $error_message = "El correo electrónico ya está registrado en otro usuario.";
        } else {
            // Actualizar datos
            $stmt = $pdo->prepare("UPDATE cliente SET nombre = ?, apellido = ?, dni = ?, telefono = ?, email = ?, direccion = ? WHERE idcliente = ?");
            $stmt->execute([$nombre, $apellido, $dni, $telefono, $email, $direccion, $idcliente]);

            // Actualizar datos en sesión (si guardas email o nombre en sesión)
            $_SESSION['customer']['cust_name'] = $nombre . ' ' . $apellido;

            $success_message = "Perfil actualizado correctamente.";
        }
    }
}
?>

<?php require_once('header.php'); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white text-center rounded-top-4">
                    <h4 class="mb-0">Actualizar Perfil</h4>
                </div>
                <div class="card-body p-4">

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php elseif ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <form method="post" novalidate>
                        <input type="hidden" name="form_profile" value="1">

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required
                                   value="<?php echo htmlspecialchars($nombre); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="apellido" class="form-label">Apellido *</label>
                            <input type="text" name="apellido" id="apellido" class="form-control" required
                                   value="<?php echo htmlspecialchars($apellido); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" name="dni" id="dni" class="form-control"
                                   value="<?php echo htmlspecialchars($dni); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono *</label>
                            <input type="text" name="telefono" id="telefono" class="form-control" required
                                   value="<?php echo htmlspecialchars($telefono); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico *</label>
                            <input type="email" name="email" id="email" class="form-control" required
                                   value="<?php echo htmlspecialchars($email); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección *</label>
                            <input type="text" name="direccion" id="direccion" class="form-control" required
                                   value="<?php echo htmlspecialchars($direccion); ?>">
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success px-5">Actualizar Perfil</button>
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
