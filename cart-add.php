<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json'); // Asegura que el contenido sea JSON

require_once('admin/inc/config.php');

// Verificar si el cliente está logueado
$clienteId = $_SESSION['customer']['idcliente'] ?? null;
if (!$clienteId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Debes iniciar sesión para agregar cursos al carrito.'
    ]);
    exit;
}

// Verificar si se recibió el ID del curso
if (!isset($_POST['idcurso'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se proporcionó el ID del curso.'
    ]);
    exit;
}

$idcurso = (int)$_POST['idcurso'];

// Verificar si el curso existe
$statement = $pdo->prepare("SELECT * FROM curso WHERE idcurso = ?");
$statement->execute([$idcurso]);
$curso = $statement->fetch(PDO::FETCH_ASSOC);

if (!$curso) {
    echo json_encode([
        'status' => 'error',
        'message' => 'El curso no existe.'
    ]);
    exit;
}

$precio = is_numeric($curso['precio']) ? (float)$curso['precio'] : 0.00;

// Inicializar estructura de carritos si no existe
if (!isset($_SESSION['carritos'])) {
    $_SESSION['carritos'] = [];
}
if (!isset($_SESSION['carritos'][$clienteId])) {
    $_SESSION['carritos'][$clienteId] = [];
}

// Verificar si el curso ya está en el carrito del cliente
if (isset($_SESSION['carritos'][$clienteId][$idcurso])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'El curso ya está en tu carrito.',
        'cart_count' => count($_SESSION['carritos'][$clienteId])
    ]);
    exit;
}

// Agregar el curso al carrito del cliente
$_SESSION['carritos'][$clienteId][$idcurso] = [
    'idcurso' => $idcurso,
    'nombre' => $curso['nombre_curso'],
    'precio' => $precio
];

// Calcular totales
$subtotal = array_sum(array_column($_SESSION['carritos'][$clienteId], 'precio'));
$igv = $subtotal * 0.18;
$total = $subtotal + $igv;

// Devolver respuesta exitosa
echo json_encode([
    'status' => 'success',
    'message' => 'Curso agregado al carrito exitosamente.',
    'cart_count' => count($_SESSION['carritos'][$clienteId]),
    'subtotal' => number_format($subtotal, 2),
    'igv' => number_format($igv, 2),
    'total' => number_format($total, 2)
]);
