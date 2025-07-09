<?php
session_start();
session_destroy();

// Eliminar la cookie de recordar sesión
if(isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

header("Location: login.php");
exit;
?>
