<?php
ob_start();
require_once('header.php');

if (!isset($_GET['id'])) {
    header('location: categoria.php');
    exit();
}

// Verificar si la categoría está siendo usada en algún curso
$statement = $pdo->prepare("SELECT COUNT(*) as total FROM curso WHERE idcategoria=?");
$statement->execute(array($_GET['id']));
$result = $statement->fetch(PDO::FETCH_ASSOC);

if ($result['total'] > 0) {
    $_SESSION['error'] = "No se puede eliminar esta categoría porque está siendo utilizada en uno o más cursos";
    header('location: categoria.php');
    exit();
}

$statement = $pdo->prepare("DELETE FROM categoria WHERE idcategoria=?");
$statement->execute(array($_GET['id']));

$_SESSION['success'] = "Categoría eliminada exitosamente";
header('location: categoria.php');
exit();
?> 