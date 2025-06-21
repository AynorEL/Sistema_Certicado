<?php
// Archivo de prueba temporal para forget-password
session_start();
include "admin/inc/config.php";
include "phpmailer-config.php";

echo "<h1>🧪 Prueba de Forget Password</h1>";

// Mostrar información del POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>📤 POST Recibido:</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    if(isset($_POST['form1'])) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<strong>✅ Campo form1 encontrado</strong><br>";
        echo "</div>";
        
        $email = trim($_POST['email']);
        echo "<h3>📧 Email procesado: {$email}</h3>";
        
        // Verificar si el email existe
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE nombre_usuario = ? AND estado = 'Activo'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if($user) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<strong>✅ Usuario encontrado</strong><br>";
            echo "ID: {$user['idusuario']}<br>";
            echo "Email: {$user['nombre_usuario']}<br>";
            echo "</div>";
            
            // Generar token
            $token = bin2hex(random_bytes(32));
            echo "<h3>🔑 Token generado: " . substr($token, 0, 20) . "...</h3>";
            
            // Intentar enviar email
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $token;
            $subject = "Prueba - Restablecimiento de Contraseña";
            $message = "<h1>Prueba de Email</h1><p>Este es un email de prueba. Enlace: {$reset_link}</p>";
            
            $email_sent = sendEmailWithPHPMailer($email, $subject, $message);
            
            if ($email_sent) {
                echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
                echo "<strong>✅ Email enviado correctamente</strong><br>";
                echo "Revisa tu Gmail: {$email}<br>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
                echo "<strong>❌ Error al enviar email</strong><br>";
                echo "</div>";
            }
            
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<strong>❌ Usuario no encontrado</strong><br>";
            echo "El email {$email} no existe en la base de datos.<br>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<strong>❌ Campo form1 no encontrado</strong><br>";
        echo "</div>";
    }
} else {
    echo "<div style='background: #e6f3ff; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<strong>📝 No se ha enviado POST</strong><br>";
    echo "Usa el formulario de abajo para probar.<br>";
    echo "</div>";
}

// Mostrar formulario de prueba
echo "<h3>📝 Formulario de Prueba:</h3>";
?>

<form method="post" style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
    <input type="hidden" name="form1" value="1">
    <div style="margin-bottom: 15px;">
        <label>Email:</label><br>
        <input type="email" name="email" placeholder="Ingresa un email de la base de datos" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
    </div>
    <button type="submit" style="background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        Probar Envío
    </button>
</form>

<div style="text-align: center; margin: 30px 0;">
    <a href="forget-password.php" style="background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px;">🔐 Ir a Forget Password Real</a>
</div> 