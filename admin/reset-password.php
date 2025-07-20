<?php
session_start();
require_once('inc/config.php');

$error_message = '';
$success_message = '';

if (!isset($_GET['id']) || !isset($_GET['token'])) {
    header('location: login.php');
    exit;
}
$id = $_GET['id'];
$token = $_GET['token'];

$stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE id_usuario = ? AND reset_token = ?");
$stmt->execute([$id, $token]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    $error_message = 'El enlace de restablecimiento de contraseña no es válido.';
} elseif ($usuario['estado'] !== 'Activo') {
    $error_message = 'El usuario está inactivo. Contacte al administrador.';
}

if (isset($_POST['form1']) && !$error_message) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($new_password) || empty($confirm_password)) {
        $error_message = 'Debe completar todos los campos.';
    } elseif (strlen($new_password) < 6) {
        $error_message = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'Las contraseñas no coinciden.';
    } else {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios_admin SET contrasena = ?, reset_token = NULL WHERE id_usuario = ?");
            $stmt->execute([$hashed_password, $id]);
            $success_message = '✅ Contraseña actualizada exitosamente. Redirigiendo al login...';
            echo '<script>setTimeout(function(){ window.location.href = "login.php?correo=' . urlencode($usuario['correo']) . '"; }, 2500);</script>';
        } catch (PDOException $e) {
            $error_message = '❌ Error al actualizar la contraseña. Inténtelo de nuevo.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Restablecer contraseña</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

<div class="w-full max-w-md mx-auto bg-gray-900 rounded-2xl shadow-lg mt-10">
  <div class="border-b border-gray-800 py-6 px-8 text-center font-semibold text-yellow-300 bg-gray-800 rounded-t-2xl">🔐 Restablecer contraseña</div>
  <div class="p-8">
    <form method="POST" class="space-y-6">
      <input type="hidden" name="form1" value="1">
      <div>
        <label for="new_password" class="block text-sm font-medium text-gray-200 mb-1">Nueva contraseña</label>
        <div class="relative">
          <input type="password" class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-300 pr-10" id="new_password" name="new_password" placeholder="Mínimo 6 caracteres" required />
          <button type="button" onclick="togglePassword('new_password', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-yellow-300 focus:outline-none"><i class="ph ph-eye"></i></button>
        </div>
      </div>
      <div>
        <label for="confirm_password" class="block text-sm font-medium text-gray-200 mb-1">Confirmar contraseña</label>
        <div class="relative">
          <input type="password" class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-300 pr-10" id="confirm_password" name="confirm_password" placeholder="Repite la contraseña" required />
          <button type="button" onclick="togglePassword('confirm_password', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-yellow-300 focus:outline-none"><i class="ph ph-eye"></i></button>
        </div>
      </div>
      <button type="submit" class="w-full py-2 rounded-lg bg-yellow-400 hover:bg-yellow-300 text-gray-900 font-semibold transition">Actualizar contraseña</button>
    </form>
    <?php if ($error_message): ?>
      <div class="mt-6 text-center text-red-400 font-medium"><?php echo $error_message; ?></div>
    <?php elseif ($success_message): ?>
      <div class="mt-6 text-center text-green-400 font-medium"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <a href="login.php" class="block w-full mt-6 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-200 font-medium text-center transition">Volver al login</a>
  </div>
</div>

<script>
function togglePassword(id, btn) {
  const input = document.getElementById(id);
  const icon = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('ph-eye');
    icon.classList.add('ph-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('ph-eye-slash');
    icon.classList.add('ph-eye');
  }
}
</script>

<!-- Tailwind CDN incluido arriba -->
</body>
</html>
