<?php
session_start();
require_once('admin/inc/config.php');
require_once('admin/inc/functions.php');

header('Content-Type: application/json');

// Verificar si el usuario está logueado
$clienteId = $_SESSION['customer']['idcliente'] ?? null;
if (!$clienteId) {
    echo json_encode(['status'=>'error','message'=>'Debes iniciar sesión para agregar cursos al carrito']);
    exit;
}

if(isset($_POST['idcurso'])) {
    $idcurso = $_POST['idcurso'];
    
    // Obtener información del curso
    $statement = $pdo->prepare("SELECT * FROM curso WHERE idcurso=?");
    $statement->execute(array($idcurso));
    $curso = $statement->fetch(PDO::FETCH_ASSOC);
    
    if($curso) {
        // Verificar si hay cupos disponibles
        if($curso['cupos_disponibles'] > 0) {
            // Inicializar carrito si no existe
            if (!isset($_SESSION['carritos'][$clienteId])) {
                $_SESSION['carritos'][$clienteId] = [];
            }
            
            // Verificar si el curso ya está en el carrito
            if (isset($_SESSION['carritos'][$clienteId][$idcurso])) {
                echo json_encode(array(
                    'status' => 'info',
                    'message' => 'Este curso ya está en tu carrito'
                ));
                exit;
            }
            
            // Agregar al carrito
            $_SESSION['carritos'][$clienteId][$idcurso] = array(
                'idcurso' => $idcurso,
                'nombre' => $curso['nombre_curso'],
                'precio' => $curso['precio']
            );
            
            echo json_encode(array(
                'status' => 'success',
                'message' => '✅ ¡Curso agregado al carrito correctamente!',
                'cart_count' => count($_SESSION['carritos'][$clienteId])
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