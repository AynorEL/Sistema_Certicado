<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if (!isset($_REQUEST['id'])) {
    header('location: pago.php');
    exit;
}

$id = $_REQUEST['id'];

// Verificar si el pago existe
$statement = $pdo->prepare("SELECT * FROM pago WHERE idpago=?");
$statement->execute(array($id));
$total = $statement->rowCount();

if ($total == 0) {
    header('location: pago.php');
    exit;
}

// Eliminar el comprobante si existe
$statement = $pdo->prepare("SELECT comprobante FROM pago WHERE idpago=?");
$statement->execute(array($id));
$result = $statement->fetch(PDO::FETCH_ASSOC);
if ($result && $result['comprobante']) {
    $comprobante = $result['comprobante'];
    $ruta = '../assets/uploads/comprobantes/' . $comprobante;
    if (file_exists($ruta)) {
        unlink($ruta);
    }
}

// Eliminar el pago
$statement = $pdo->prepare("DELETE FROM pago WHERE idpago=?");
$statement->execute(array($id));

header('location: pago.php');
exit;
?> 