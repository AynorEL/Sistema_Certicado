<?php
session_start();
require_once('admin/inc/config.php');
require_once('admin/inc/functions.php');

if(isset($_POST['idcurso'])) {
    $idcurso = $_POST['idcurso'];
    
    // Obtener información del curso
    $statement = $pdo->prepare("SELECT * FROM curso WHERE idcurso=?");
    $statement->execute(array($idcurso));
    $curso = $statement->fetch(PDO::FETCH_ASSOC);
    
    if($curso) {
        // Verificar si hay cupos disponibles
        if($curso['cupos_disponibles'] > 0) {
            // Agregar al carrito
            $_SESSION['cart_certificados'][$idcurso] = array(
                'idcurso' => $idcurso,
                'nombre' => $curso['nombre_curso'],
                'precio' => $curso['precio']
            );
            
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Certificado agregado al carrito',
                'cart_count' => count($_SESSION['cart_certificados'])
            ));
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'No hay cupos disponibles para este curso'
            ));
        }
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Curso no encontrado'
        ));
    }
} else {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'ID de curso no proporcionado'
    ));
} 