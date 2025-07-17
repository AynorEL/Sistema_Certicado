<?php require_once('header.php'); ?>
<?php require_once('inc/functions.php'); ?>

<?php
$error_message = '';
$success_message = '';

if (!isset($_REQUEST['id'])) {
	$_SESSION['error'] = "ID de pregunta frecuente no válido";
	header('location: faq.php');
	exit;
} else {
	// Verificar si el ID es válido
	$statement = $pdo->prepare("SELECT * FROM preguntas_frecuentes WHERE id=?");
	$statement->execute([$_REQUEST['id']]);
	$total = $statement->rowCount();
	if ($total == 0) {
		$_SESSION['error'] = "Pregunta frecuente no encontrada";
		header('location: faq.php');
		exit;
	}
}

// Eliminar la pregunta frecuente
$statement = $pdo->prepare("DELETE FROM preguntas_frecuentes WHERE id=?");
$statement->execute([$_REQUEST['id']]);

$_SESSION['success'] = "Pregunta frecuente eliminada exitosamente";
header('location: faq.php');
exit;
?>
