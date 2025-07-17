<?php
require_once('header.php');

if (isset($_GET['id'])) {
	$id = $_GET['id'];
	
	// Validar si se puede eliminar
	$validacion = validarEliminacionCliente($pdo, $id);
	
	if (!$validacion['puede_eliminar']) {
		$_SESSION['error'] = $validacion['mensaje'];
		header('location: cliente.php');
		exit;
	}
	
	// Si pasa la validación, proceder con la eliminación
	try {
		$stmt = $pdo->prepare("DELETE FROM cliente WHERE idcliente = ?");
		$stmt->execute([$id]);
		
		$_SESSION['success'] = "✅ Cliente eliminado exitosamente.";
	} catch (PDOException $e) {
		$_SESSION['error'] = "❌ Error al eliminar el cliente: " . $e->getMessage();
	}
} else {
	$_SESSION['error'] = "❌ ID de cliente no proporcionado.";
}

header('location: cliente.php');
exit;
?> 