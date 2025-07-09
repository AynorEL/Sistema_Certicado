<?php
session_start();
require_once('admin/inc/config.php');
require_once('admin/inc/functions.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $response = array();
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['status'] = 'error';
        $response['message'] = "Por favor, ingrese un correo electrónico válido.";
        echo json_encode($response);
        exit();
    }

    try {
        // Verificar si el email ya está suscrito
        $statement = $pdo->prepare("SELECT * FROM suscriptores WHERE correo_suscriptor = ?");
        $statement->execute([$email]);
        
        if ($statement->rowCount() > 0) {
            $response['status'] = 'error';
            $response['message'] = "Este correo ya está suscrito a nuestro boletín.";
            echo json_encode($response);
            exit();
        }

        // Si el correo no existe, proceder con la inserción
        $statement = $pdo->prepare("
            INSERT INTO suscriptores 
            (correo_suscriptor, activo, fecha_suscripcion) 
            VALUES (?, 1, NOW())
        ");
        
        if ($statement->execute([$email])) {
            $response['status'] = 'success';
            $response['message'] = "¡Gracias por suscribirte a nuestro boletín!";
        } else {
            throw new PDOException("Error al insertar el suscriptor");
        }
    } catch (PDOException $e) {
        error_log("Error en newsletter-subscribe.php: " . $e->getMessage());
        $response['status'] = 'error';
        $response['message'] = "Error al procesar la suscripción. Por favor, intente más tarde.";
    }
    
    echo json_encode($response);
    exit();
} else {
    $response['status'] = 'error';
    $response['message'] = "Método de solicitud no válido.";
    echo json_encode($response);
    exit();
}
?> 