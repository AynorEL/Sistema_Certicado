<?php
ob_start();
require_once('header.php');
require_once('inc/functions.php');

if(!isset($_REQUEST['id'])) {
    $_SESSION['error'] = "ID de módulo no válido";
    header('location: modulo.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM modulo WHERE idmodulo=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        $_SESSION['error'] = "Módulo no encontrado";
        header('location: modulo.php');
        exit;
    }
}

// Validar si se puede eliminar el módulo
$validacion = validarEliminacionModulo($pdo, $_REQUEST['id']);

if (!$validacion['puede_eliminar']) {
    $_SESSION['error'] = $validacion['mensaje'];
    header('location: modulo.php');
    exit;
}

$statement = $pdo->prepare("DELETE FROM modulo WHERE idmodulo=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Módulo eliminado exitosamente";
header('location: modulo.php');
exit();
?> 