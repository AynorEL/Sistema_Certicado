<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['idcurso'])) {
    echo json_encode(['status'=>'error','message'=>'Solicitud inválida']);
    exit;
}

$idcurso = (int)$_POST['idcurso'];
$clienteId = $_SESSION['customer']['idcliente'] ?? null;

if (!$clienteId) {
    echo json_encode(['status'=>'error','message'=>'Debes iniciar sesión']);
    exit;
}

// Asegurarse de que la estructura exista
if (!isset($_SESSION['carritos'][$clienteId])) {
    echo json_encode(['status'=>'error','message'=>'El carrito está vacío']);
    exit;
}

// Eliminar el curso del carrito si existe
if (isset($_SESSION['carritos'][$clienteId][$idcurso])) {
    unset($_SESSION['carritos'][$clienteId][$idcurso]);

    $nuevo_total = count($_SESSION['carritos'][$clienteId]);

    echo json_encode([
        'status' => 'success',
        'message' => '✅ Curso eliminado del carrito correctamente',
        'cart_count' => $nuevo_total
    ]);
} else {
    echo json_encode(['status'=>'error','message'=>'Curso no encontrado en el carrito']);
}
?> 