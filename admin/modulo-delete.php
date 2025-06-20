<?php
ob_start();
require_once('header.php');

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM modulo WHERE idmodulo=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        header('location: logout.php');
        exit;
    }
}

$statement = $pdo->prepare("DELETE FROM modulo WHERE idmodulo=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Módulo eliminado exitosamente";
header('location: modulo.php');
exit();
?> 