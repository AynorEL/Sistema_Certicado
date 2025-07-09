<?php 
ob_start();
session_start();
include 'admin/inc/config.php';

// Si existe una cookie de "Recordarme", eliminarla
if(isset($_COOKIE['customer_remember'])) {
    // Eliminar la cookie
    setcookie('customer_remember', '', time() - 3600, '/');
}

unset($_SESSION['customer']);
header("location: ".BASE_URL.'login.php'); 
?>