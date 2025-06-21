<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if (!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Verificar si el ID es válido
	$statement = $pdo->prepare("SELECT * FROM preguntas_frecuentes WHERE id=?");
	$statement->execute([$_REQUEST['id']]);
	$total = $statement->rowCount();
	if ($total == 0) {
		header('location: logout.php');
		exit;
	}
}

// Eliminar la pregunta frecuente
$statement = $pdo->prepare("DELETE FROM preguntas_frecuentes WHERE id=?");
$statement->execute([$_REQUEST['id']]);

header('location: faq.php');
exit;
?>
