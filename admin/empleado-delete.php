<?php
require_once('inc/config.php');

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM empleado WHERE idempleado=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        header('location: logout.php');
        exit;
    }
}

$statement = $pdo->prepare("DELETE FROM empleado WHERE idempleado=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Empleado eliminado exitosamente";
header('location: empleado.php');
exit();
?> 