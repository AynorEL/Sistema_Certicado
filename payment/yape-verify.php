<?php
session_start();
require_once('../admin/inc/config.php');

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cart.php');
    exit;
}

// Verificar sesión
if (!isset($_SESSION['yape_details'])) {
    $_SESSION['error'] = "No se encontraron los detalles del pago.";
    header('Location: ../cart.php');
    exit;
}

$yape_details = $_SESSION['yape_details'];
$order_id = $_POST['order_id'] ?? null;

if ($order_id != $yape_details['order_id']) {
    $_SESSION['error'] = "Error: ID de orden no coincide.";
    header('Location: yape.php');
    exit;
}

// Comprobante subido
if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = "Error al subir el comprobante.";
    header('Location: yape.php');
    exit;
}

$file = $_FILES['comprobante'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
$max_size = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowed_types)) {
    $_SESSION['error'] = "Formato no permitido.";
    header('Location: yape.php');
    exit;
}

if ($file['size'] > $max_size) {
    $_SESSION['error'] = "Archivo muy grande. Máx: 5MB.";
    header('Location: yape.php');
    exit;
}

// Carpeta destino
$upload_dir = '../assets/uploads/comprobantes/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Nombre único
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_name = 'yape_' . $order_id . '_' . time() . '.' . $ext;
$path_final = $upload_dir . $new_name;

if (!move_uploaded_file($file['tmp_name'], $path_final)) {
    $_SESSION['error'] = "Error al guardar el archivo.";
    header('Location: yape.php');
    exit;
}

// Guardar en BD
try {
    $pdo->beginTransaction();

    // Obtener todas las inscripciones del cliente para esta orden
    $stmt = $pdo->prepare("SELECT i.idinscripcion FROM inscripcion i 
                          JOIN cliente c ON i.idcliente = c.idcliente 
                          WHERE c.idcliente = (SELECT idcliente FROM inscripcion WHERE idinscripcion = ?)");
    $stmt->execute([$order_id]);
    $inscripciones = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Actualizar todos los pagos de las inscripciones
    foreach ($inscripciones as $idinscripcion) {
        $stmt = $pdo->prepare("UPDATE pago SET 
            estado = 'pendiente_verificacion',
            comprobante = ?,
            fecha_pago = NOW()
            WHERE idinscripcion = ?");
        $stmt->execute([$new_name, $idinscripcion]);
    }

    $pdo->commit();
    unset($_SESSION['yape_details']);
    
    // Limpiar el carrito después del pago exitoso
    $clienteId = $_SESSION['customer']['idcliente'] ?? null;
    if ($clienteId && isset($_SESSION['carritos'][$clienteId])) {
        unset($_SESSION['carritos'][$clienteId]);
    }
    if (isset($_SESSION['cart_certificados'])) {
        unset($_SESSION['cart_certificados']);
    }

    $_SESSION['success'] = "¡Pago registrado! Comprobante recibido correctamente.";
    header('Location: ../payment-success.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    if (file_exists($path_final)) {
        unlink($path_final);
    }
    error_log("Error en Yape-Verify: " . $e->getMessage());
    $_SESSION['error'] = "Error al procesar el pago.";
    header('Location: yape.php');
    exit;
}
