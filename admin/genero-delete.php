<?php
include("inc/config.php");

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit();
}

$statement = $pdo->prepare("SELECT * FROM genero WHERE idgenero=?");
$statement->execute(array($_REQUEST['id']));
$total = $statement->rowCount();
if($total==0) {
    header('location: logout.php');
    exit();
}

$statement = $pdo->prepare("DELETE FROM genero WHERE idgenero=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success_message'] = "Género eliminado exitosamente.";
header("location: genero.php");
exit();
?> 