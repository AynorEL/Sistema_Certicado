<?php
ob_start();
require_once('header.php');

if (!isset($_REQUEST['id'])) {
	header('location: especialista.php');
	exit();
} else {
	// Check the idespecialista is valid or not
	$statement = $pdo->prepare("SELECT * FROM especialista WHERE idespecialista=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if ($total == 0) {
		header('location: especialista.php');
		exit();
	}
}

// Delete from especialista
$statement = $pdo->prepare("DELETE FROM especialista WHERE idespecialista=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Especialista eliminado exitosamente";
header('location: especialista.php');
exit();
?> 