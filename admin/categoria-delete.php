<?php
ob_start();
require_once('header.php');
require_once('inc/functions.php');

if (!isset($_GET['id'])) {
    header('location: categoria.php');
    exit();
}

// Validar si se puede eliminar la categoría
$validacion = validarEliminacionCategoria($pdo, $_GET['id']);

if (!$validacion['puede_eliminar']) {
    $_SESSION['error'] = $validacion['mensaje'];
    header('location: categoria.php');
    exit();
}

$statement = $pdo->prepare("DELETE FROM categoria WHERE idcategoria=?");
$statement->execute(array($_GET['id']));

$_SESSION['success'] = "Categoría eliminada exitosamente";
header('location: categoria.php');
exit();
?> 