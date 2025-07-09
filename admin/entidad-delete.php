<?php
ob_start();
require_once('header.php');

if (!isset($_REQUEST['id'])) {
	header('location: entidad.php');
	exit();
} else {
	// Check the identidad is valid or not
	$statement = $pdo->prepare("SELECT * FROM entidad WHERE identidad=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if ($total == 0) {
		header('location: entidad.php');
		exit();
	}
}

// Delete from entidad
$statement = $pdo->prepare("DELETE FROM entidad WHERE identidad=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Entidad eliminada exitosamente";
header('location: entidad.php');
exit();
?> 