<?php
require_once('header.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Actualizar información del cliente
        $statement = $pdo->prepare("
            UPDATE cliente 
            SET nombre = ?, 
                apellido = ?, 
                dni = ?, 
                telefono = ?, 
                email = ?, 
                direccion = ? 
            WHERE idcliente = ?
        ");
        
        $statement->execute([
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['dni'],
            $_POST['telefono'],
            $_POST['email'],
            $_POST['direccion'],
            $_SESSION['user_id']
        ]);

        // Actualizar email en la tabla usuario
        $statement = $pdo->prepare("
            UPDATE usuario 
            SET nombre_usuario = ? 
            WHERE idusuario = ?
        ");
        
        $statement->execute([
            $_POST['email'],
            $_SESSION['user_id']
        ]);

        $_SESSION['success_message'] = "Perfil actualizado correctamente.";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al actualizar el perfil: " . $e->getMessage();
    }

    // Redirigir de vuelta al área de usuario
    header('Location: user-area.php');
    exit();
} else {
    // Si no es POST, redirigir al área de usuario
    header('Location: user-area.php');
    exit();
}
?> 