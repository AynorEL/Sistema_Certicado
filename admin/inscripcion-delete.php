<?php
require_once('header.php');
require_once('inc/functions.php');

if(!isset($_REQUEST['id'])) {
    $_SESSION['error'] = "ID de inscripción no válido";
    header('location: inscripcion.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM inscripcion WHERE idinscripcion=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total==0) {
        $_SESSION['error'] = "Inscripción no encontrada";
        header('location: inscripcion.php');
        exit;
    }
}

// Validar si se puede eliminar la inscripción
$validacion = validarEliminacionInscripcion($pdo, $_REQUEST['id']);

if (!$validacion['puede_eliminar']) {
    $_SESSION['error'] = $validacion['mensaje'];
    header('location: inscripcion.php');
    exit;
}

$statement = $pdo->prepare("DELETE FROM inscripcion WHERE idinscripcion=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Inscripción eliminada exitosamente";
header('location: inscripcion.php');
?> 