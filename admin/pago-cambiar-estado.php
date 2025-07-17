<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if (!isset($_REQUEST['id']) || !isset($_REQUEST['estado'])) {
    header('location: pago.php');
    exit;
}

$id = $_REQUEST['id'];
$estado = $_REQUEST['estado'];

// Validar que el estado sea válido
$estados_validos = array('Pendiente', 'Completado', 'Reembolsado', 'Cancelado');
if (!in_array($estado, $estados_validos)) {
    header('location: pago.php');
    exit;
}

// Verificar si el ID es válido
$statement = $pdo->prepare("SELECT * FROM pago WHERE idpago=?");
$statement->execute(array($id));
$total = $statement->rowCount();
if($total == 0) {
    header('location: logout.php');
    exit;
}

// Actualizar el estado del pago
$statement = $pdo->prepare("UPDATE pago SET estado = ? WHERE idpago = ?");
$statement->execute(array($estado, $id));

// Obtener la inscripción asociada a este pago
$statement = $pdo->prepare("SELECT idinscripcion FROM pago WHERE idpago = ?");
$statement->execute(array($id));
$idinscripcion = $statement->fetchColumn();

if ($idinscripcion) {
    // Obtener todos los estados de pago para esta inscripción
    $statement = $pdo->prepare("SELECT estado FROM pago WHERE idinscripcion = ?");
    $statement->execute(array($idinscripcion));
    $estados = $statement->fetchAll(PDO::FETCH_COLUMN);

    $nuevo_estado = 'Sin pago';
    if (in_array('Completado', $estados)) {
        $nuevo_estado = 'Pagado';
    } elseif (in_array('Pendiente', $estados)) {
        $nuevo_estado = 'Pendiente';
    } elseif (count($estados) > 0 && count(array_unique($estados)) === 1 && $estados[0] === 'Cancelado') {
        $nuevo_estado = 'Cancelado';
    } elseif (count($estados) > 0 && count(array_unique($estados)) === 1 && $estados[0] === 'Reembolsado') {
        $nuevo_estado = 'Reembolsado';
    }

    // Actualizar el estado_pago de la inscripción
    $statement = $pdo->prepare("UPDATE inscripcion SET estado_pago = ? WHERE idinscripcion = ?");
    $statement->execute(array($nuevo_estado, $idinscripcion));
}

header('location: pago.php');
?> 