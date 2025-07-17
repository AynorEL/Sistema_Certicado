<?php
ob_start();
require_once('header.php');
require_once('inc/functions.php');

if (!isset($_GET['id'])) {
    header('location: instructor.php');
    exit();
}

// Validar si se puede eliminar el instructor
$validacion = validarEliminacionInstructor($pdo, $_GET['id']);

if (!$validacion['puede_eliminar']) {
    $_SESSION['error'] = $validacion['mensaje'];
    header('location: instructor.php');
    exit();
}

// Obtener información del instructor antes de eliminar
$statement = $pdo->prepare("SELECT firma_digital FROM instructor WHERE idinstructor=?");
$statement->execute(array($_GET['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

if (!empty($result)) {
    $firma_digital = $result[0]['firma_digital'];
    
    // Eliminar el archivo de firma digital si existe
    if (!empty($firma_digital) && file_exists(FIRMAS_PATH . $firma_digital)) {
        unlink(FIRMAS_PATH . $firma_digital);
    }
}

// Eliminar el instructor de la base de datos
$statement = $pdo->prepare("DELETE FROM instructor WHERE idinstructor=?");
$statement->execute(array($_GET['id']));

$_SESSION['success'] = "Instructor eliminado exitosamente";
header('location: instructor.php');
exit();
?> 