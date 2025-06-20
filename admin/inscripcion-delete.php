<?php
require_once('header.php');

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM inscripcion WHERE idinscripcion=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total==0) {
        header('location: logout.php');
        exit;
    }
}

$statement = $pdo->prepare("DELETE FROM inscripcion WHERE idinscripcion=?");
$statement->execute(array($_REQUEST['id']));

header('location: inscripcion.php');
?> 