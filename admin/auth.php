<?php
// Iniciar la sesión si no se ha iniciado
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once('inc/config.php'); // Asegúrate de que la ruta es correcta

function checkAuth()
{
    // Si no hay sesión de usuario, redirigir al login
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }

    // Verificar si el usuario está activo
    global $pdo;
    $stmt = $pdo->prepare("SELECT estado FROM usuarios_admin WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['user']['id_usuario']]);
    $user = $stmt->fetch();

    if (!$user || $user['estado'] !== 'Activo') {
        session_destroy();
        header("Location: login.php");
        exit;
    }
}

// Si estamos en login.php, no necesitamos verificar la autenticación
$current_page = basename($_SERVER['PHP_SELF']);
if($current_page != 'login.php') {
    checkAuth();
}
