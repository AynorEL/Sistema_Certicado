<?php
require_once('inc/config.php');
require_once('inc/functions.php');

if(!isset($_REQUEST['id'])) {
    $_SESSION['error'] = "ID de administrador no válido";
    header('location: admin.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM usuarios_admin WHERE id_usuario=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        $_SESSION['error'] = "Administrador no encontrado";
        header('location: admin.php');
        exit;
    }
}

// Validar si se puede eliminar el admin
$validacion = validarEliminacionAdmin($pdo, $_REQUEST['id']);

if (!$validacion['puede_eliminar']) {
    $_SESSION['error'] = $validacion['mensaje'];
    header('location: admin.php');
    exit;
}

$statement = $pdo->prepare("DELETE FROM usuarios_admin WHERE id_usuario=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Administrador eliminado exitosamente";
header('location: admin.php');
exit();
?> 