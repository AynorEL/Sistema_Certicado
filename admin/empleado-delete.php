<?php
require_once('inc/config.php');
require_once('inc/functions.php');

if(!isset($_REQUEST['id'])) {
    $_SESSION['error'] = "ID de empleado no válido";
    header('location: empleado.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM empleado WHERE idempleado=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        $_SESSION['error'] = "Empleado no encontrado";
        header('location: empleado.php');
        exit;
    }
}

// Validar si se puede eliminar el empleado
$validacion = validarEliminacionEmpleado($pdo, $_REQUEST['id']);

if (!$validacion['puede_eliminar']) {
    $_SESSION['error'] = $validacion['mensaje'];
    header('location: empleado.php');
    exit;
}

$statement = $pdo->prepare("DELETE FROM empleado WHERE idempleado=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Empleado eliminado exitosamente";
header('location: empleado.php');
exit();
?> 