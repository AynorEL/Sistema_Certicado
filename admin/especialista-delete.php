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
	
	// Obtener información del especialista antes de eliminar
	$firma_especialista = $result[0]['firma_especialista'];
	
	// Eliminar el archivo de firma especialista si existe
	if (!empty($firma_especialista) && file_exists(FIRMAS_PATH . $firma_especialista)) {
		unlink(FIRMAS_PATH . $firma_especialista);
	}
}

// Delete from especialista
$statement = $pdo->prepare("DELETE FROM especialista WHERE idespecialista=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Especialista eliminado exitosamente";
header('location: especialista.php');
exit();
?> 