<?php
require_once('inc/config.php');

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM usuarios_admin WHERE id_usuario=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        header('location: logout.php');
        exit;
    }
}

$statement = $pdo->prepare("DELETE FROM usuarios_admin WHERE id_usuario=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Administrador eliminado exitosamente";
header('location: admin.php');
exit();
?> 