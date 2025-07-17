<?php
ob_start();
require_once('header.php');
require_once('inc/functions.php');

if (!isset($_REQUEST['id'])) {
	$_SESSION['error'] = "ID de entidad no válido";
	header('location: entidad.php');
	exit();
} else {
	// Check the identidad is valid or not
	$statement = $pdo->prepare("SELECT * FROM entidad WHERE identidad=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if ($total == 0) {
		$_SESSION['error'] = "Entidad no encontrada";
		header('location: entidad.php');
		exit();
	}
}

// Validar si se puede eliminar la entidad
$validacion = validarEliminacionEntidad($pdo, $_REQUEST['id']);

if (!$validacion['puede_eliminar']) {
    $_SESSION['error'] = $validacion['mensaje'];
    header('location: entidad.php');
    exit();
}

// Delete from entidad
$statement = $pdo->prepare("DELETE FROM entidad WHERE identidad=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Entidad eliminada exitosamente";
header('location: entidad.php');
exit();
?> 