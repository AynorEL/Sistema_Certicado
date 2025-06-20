<?php
ob_start();
require_once('header.php');

if (!isset($_REQUEST['idcurso'])) {
	$_SESSION['error'] = "ID de curso no válido";
	header('location: curso.php');
	exit;
}

// Verificar si existe el curso
$statement = $pdo->prepare("SELECT * FROM curso WHERE idcurso=?");
$statement->execute(array($_REQUEST['idcurso']));
if ($statement->rowCount() == 0) {
	$_SESSION['error'] = "Curso no encontrado";
	header('location: curso.php');
	exit;
}

try {
	// Iniciar transacción
	$pdo->beginTransaction();

	// Eliminar registros de la base de datos
	$statement = $pdo->prepare("DELETE FROM curso WHERE idcurso=?");
	$statement->execute(array($_REQUEST['idcurso']));

	$pdo->commit();
	$_SESSION['success'] = "Curso eliminado correctamente";
} catch (Exception $e) {
	$pdo->rollBack();
	$_SESSION['error'] = "Error al eliminar el curso: " . $e->getMessage();
}

header('location: curso.php');
exit;
