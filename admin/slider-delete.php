<?php 
require_once('header.php');
require_once('inc/config.php');
require_once('inc/functions.php');

if (!isset($_REQUEST['id'])) {
	$_SESSION['error'] = "ID de slider no válido";
	header('location: slider.php');
	exit;
}

try {
	// Obtener la foto antes de eliminar
	$statement = $pdo->prepare("SELECT foto FROM sliders WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$result = $statement->fetch(PDO::FETCH_ASSOC);
	
	if ($result) {
		// Eliminar la foto si existe
		if ($result['foto'] && file_exists('../assets/uploads/' . $result['foto'])) {
			unlink('../assets/uploads/' . $result['foto']);
		}

		// Eliminar el slider
		$statement = $pdo->prepare("DELETE FROM sliders WHERE id=?");
		$statement->execute(array($_REQUEST['id']));

		$_SESSION['success'] = "Slider eliminado exitosamente";
	} else {
		$_SESSION['error'] = "Slider no encontrado";
	}
} catch (Exception $e) {
	$_SESSION['error'] = "Error al eliminar el slider: " . $e->getMessage();
}

header('location: slider.php');
exit();
?>