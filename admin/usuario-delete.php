<?php
ob_start();
require_once('header.php');

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM usuario WHERE idusuario=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        header('location: logout.php');
        exit;
    }
}

$statement = $pdo->prepare("DELETE FROM usuario WHERE idusuario=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Usuario eliminado exitosamente";
header('location: usuario.php');
exit();
?> 