<?php
ob_start();
require_once('header.php');

if (!isset($_GET['id'])) {
    header('location: instructor.php');
    exit();
}

$statement = $pdo->prepare("DELETE FROM instructor WHERE idinstructor=?");
$statement->execute(array($_GET['id']));

$_SESSION['success'] = "Instructor eliminado exitosamente";
header('location: instructor.php');
exit();
?> 