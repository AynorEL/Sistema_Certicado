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

// Si el pago se marca como completado, actualizar también el estado de la inscripción
if ($estado == 'Completado') {
    $statement = $pdo->prepare("
        UPDATE inscripcion i 
        JOIN pago p ON i.idinscripcion = p.idinscripcion 
        SET i.estado_pago = 'Pagado' 
        WHERE p.idpago = ?
    ");
    $statement->execute(array($id));
}

header('location: pago.php');
?> 