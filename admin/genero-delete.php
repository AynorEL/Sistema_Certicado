<?php
include("inc/config.php");
require_once('inc/functions.php');

if(!isset($_REQUEST['id'])) {
    $_SESSION['error'] = "ID de género no válido";
    header('location: genero.php');
    exit();
}

$statement = $pdo->prepare("SELECT * FROM genero WHERE idgenero=?");
$statement->execute(array($_REQUEST['id']));
$total = $statement->rowCount();
if($total==0) {
    $_SESSION['error'] = "Género no encontrado";
    header('location: genero.php');
    exit();
}

// Validar si se puede eliminar el género
$validacion = validarEliminacionGenero($pdo, $_REQUEST['id']);

if (!$validacion['puede_eliminar']) {
    $_SESSION['error'] = $validacion['mensaje'];
    header('location: genero.php');
    exit();
}

$statement = $pdo->prepare("DELETE FROM genero WHERE idgenero=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Género eliminado exitosamente.";
header("location: genero.php");
exit();
?> 