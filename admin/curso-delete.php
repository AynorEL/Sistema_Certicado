<?php
ob_start();
require_once('header.php');
require_once('inc/functions.php');

if (!isset($_REQUEST['id'])) {
	$_SESSION['error'] = "ID de curso no válido";
	header('location: curso.php');
	exit;
}

// Verificar si existe el curso
$statement = $pdo->prepare("SELECT * FROM curso WHERE idcurso=?");
$statement->execute(array($_REQUEST['id']));
if ($statement->rowCount() == 0) {
	$_SESSION['error'] = "Curso no encontrado";
	header('location: curso.php');
	exit;
}

// Validar si se puede eliminar el curso
$validacion = validarEliminacionCurso($pdo, $_REQUEST['id']);

if (!$validacion['puede_eliminar']) {
    $_SESSION['error'] = $validacion['mensaje'];
    header('location: curso.php');
    exit;
}

// Obtener datos del curso para eliminar el archivo de diseño
$curso = $statement->fetch(PDO::FETCH_ASSOC);
$diseno = $curso['diseño'];

try {
	// Iniciar transacción
	$pdo->beginTransaction();

	// Eliminar registros de la base de datos
	$statement = $pdo->prepare("DELETE FROM curso WHERE idcurso=?");
	$statement->execute(array($_REQUEST['id']));

	// Eliminar archivo de diseño si existe
	if (!empty($diseno) && file_exists(CURSOS_PATH . $diseno)) {
		unlink(CURSOS_PATH . $diseno);
	}

	$pdo->commit();
	$_SESSION['success'] = "Curso eliminado correctamente";
} catch (Exception $e) {
	$pdo->rollBack();
	$_SESSION['error'] = "Error al eliminar el curso: " . $e->getMessage();
}

header('location: curso.php');
exit;
