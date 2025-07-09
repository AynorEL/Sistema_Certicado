<?php
// ---------------- plin-verify.php ----------------
session_start();
require_once('../admin/inc/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['plin_details'])) {
    $_SESSION['error'] = 'Acceso no autorizado.';
    header('Location: ../cart.php');
    exit;
}

$plin = $_SESSION['plin_details'];
$order_id = $_POST['order_id'] ?? '';

if ($order_id != $plin['order_id']) {
    $_SESSION['error'] = 'Orden inválida.';
    header('Location: plin.php');
    exit;
}

if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Debe subir un comprobante válido.';
    header('Location: plin.php');
    exit;
}

$file = $_FILES['comprobante'];
$allowed = ['image/jpeg', 'image/png', 'application/pdf'];
$max_size = 5 * 1024 * 1024;

if (!in_array($file['type'], $allowed) || $file['size'] > $max_size) {
    $_SESSION['error'] = 'Archivo no permitido o demasiado grande.';
    header('Location: plin.php');
    exit;
}

$upload_dir = '../assets/uploads/comprobantes/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'plin_' . $order_id . '_' . time() . '.' . $ext;
$path = $upload_dir . $filename;

if (!move_uploaded_file($file['tmp_name'], $path)) {
    $_SESSION['error'] = 'Error al guardar el comprobante.';
    header('Location: plin.php');
    exit;
}

try {
    $pdo->beginTransaction();
    $check = $pdo->prepare("SELECT idpago FROM pago WHERE idinscripcion = ?");
    $check->execute([$order_id]);

    if ($check->rowCount() === 0) {
        $insert = $pdo->prepare("INSERT INTO pago (idinscripcion, monto, fecha_pago, metodo_pago, estado, comprobante) VALUES (?, ?, NOW(), 'Plin', 'pendiente_verificacion', ?)");
        $insert->execute([$order_id, $plin['monto'], $filename]);
    } else {
        $update = $pdo->prepare("UPDATE pago SET comprobante = ?, estado = 'pendiente_verificacion', fecha_actualizacion = NOW() WHERE idinscripcion = ?");
        $update->execute([$filename, $order_id]);
    }

    $pdo->commit();
    unset($_SESSION['plin_details']);
    $_SESSION['success'] = '¡Pago recibido! Verificaremos tu comprobante.';
    header('Location: ../payment-success.php');
    exit;

} catch (Exception $e) {
    if (file_exists($path)) unlink($path);
    $pdo->rollBack();
    error_log("Error PLIN: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar el pago.';
    header('Location: plin.php');
    exit;
}
