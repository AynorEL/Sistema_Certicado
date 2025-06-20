<?php
ob_start();
require_once('header.php');

if (!isset($_REQUEST['id'])) {
	header('location: cliente.php');
	exit();
} else {
	// Check the idcliente is valid or not
	$statement = $pdo->prepare("SELECT * FROM cliente WHERE idcliente=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	if ($total == 0) {
		header('location: cliente.php');
		exit();
	}
}

// Delete from cliente
$statement = $pdo->prepare("DELETE FROM cliente WHERE idcliente=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Cliente eliminado exitosamente";
header('location: cliente.php');
exit();
?> 