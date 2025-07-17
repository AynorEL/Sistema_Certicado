<?php require_once('header.php'); ?>
<?php require_once('inc/functions.php'); ?>

<?php
$error_message = '';
$success_message = '';

if (!isset($_REQUEST['id'])) {
    $_SESSION['error'] = "ID de pago no válido";
    header('location: pago.php');
    exit;
}

$id = $_REQUEST['id'];

// Validar si se puede eliminar el pago
$validacion = validarEliminacionPago($pdo, $id);

if (!$validacion['puede_eliminar']) {
    $_SESSION['error'] = $validacion['mensaje'];
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

$_SESSION['success'] = "Pago eliminado exitosamente";
header('location: pago.php');
exit;
?> 