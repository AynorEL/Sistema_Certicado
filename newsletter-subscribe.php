<?php
require_once 'admin/inc/config.php';
$mensaje = '';
$mensaje_tipo = '';
if (isset($_SESSION['newsletter_msg'])) {
    $mensaje = $_SESSION['newsletter_msg']['msg'];
    $mensaje_tipo = $_SESSION['newsletter_msg']['type'];
    unset($_SESSION['newsletter_msg']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    $email = trim($_POST['newsletter_email']);
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['newsletter_msg'] = [
            'msg' => 'Por favor, ingresa un correo electrónico válido.',
            'type' => 'danger'
        ];
    } else {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM suscriptores WHERE correo_suscriptor = ?');
        $statement->execute([$email]);
        $existe = $statement->fetchColumn();
        if ($existe) {
            $_SESSION['newsletter_msg'] = [
                'msg' => 'Este correo ya está suscrito.',
                'type' => 'warning'
            ];
        } else {
            $statement = $pdo->prepare('INSERT INTO suscriptores (correo_suscriptor, activo, fecha_suscripcion) VALUES (?, 1, NOW())');
            $statement->execute([$email]);
            $_SESSION['newsletter_msg'] = [
                'msg' => '¡Gracias por suscribirte! Pronto recibirás nuestras novedades.',
                'type' => 'success'
            ];
        }
    }
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '#newsletter-form');
    exit;
}
?>

<!-- NEWSLETTER FORMULARIO (Bootstrap 3) -->
<div class="container" id="newsletter-form" style="max-width: 480px; margin: 40px auto 0 auto;">
  <div class="panel panel-default" style="box-shadow: 0 4px 24px rgba(80,80,120,0.08); border-radius: 16px; border: 1px solid #eee; background: #fafbfc;">
    <div class="panel-body" style="padding: 32px 28px 24px 28px;">
      <h3 class="text-primary text-center" style="font-weight:700; margin-bottom: 8px;">¿Deseas recibir noticias y actualizaciones?</h3>
      <p class="text-center" style="color:#555; margin-bottom: 22px;">Suscríbete al boletín del IESTP San Marcos y sé el primero en enterarte de novedades.</p>
      <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $mensaje_tipo; ?> text-center" style="margin-bottom:18px; border-radius:8px;">
          <?php echo $mensaje; ?>
        </div>
        <script>
          window.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('newsletter-form');
            if (el) {
              setTimeout(function() {
                el.scrollIntoView({behavior: 'smooth', block: 'start'});
              }, 200);
            }
          });
        </script>
      <?php endif; ?>
      <form method="post" class="form-inline text-center" autocomplete="off" style="justify-content:center;">
        <div class="form-group" style="width:70%; max-width:260px; margin-bottom:10px;">
          <input type="email" name="newsletter_email" class="form-control input-lg" style="width:100%; border-radius:8px;" placeholder="Tu correo electrónico..." required>
        </div>
        <button type="submit" class="btn btn-lg btn-primary" style="border-radius:8px; min-width:120px; margin-left:8px;">Suscribirse</button>
      </form>
    </div>
  </div>
</div>