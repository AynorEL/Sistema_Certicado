<?php
ob_start();
require_once('header.php');
require_once('inc/functions.php');

if (!isset($_REQUEST['idrol'])) {
    $_SESSION['error'] = "ID de rol no válido";
    header('location: rol.php');
    exit;
}

// Verificar si existe el rol
$statement = $pdo->prepare("SELECT * FROM rol WHERE idrol=?");
$statement->execute(array($_REQUEST['idrol']));
if ($statement->rowCount() == 0) {
    $_SESSION['error'] = "Rol no encontrado";
    header('location: rol.php');
    exit;
}

// Validar si se puede eliminar el rol
$validacion = validarEliminacionRol($pdo, $_REQUEST['idrol']);

if (!$validacion['puede_eliminar']) {
    $_SESSION['error'] = $validacion['mensaje'];
    header('location: rol.php');
    exit;
}

try {
    // Iniciar transacción
    $pdo->beginTransaction();

    // Eliminar el rol
    $statement = $pdo->prepare("DELETE FROM rol WHERE idrol=?");
    $statement->execute(array($_REQUEST['idrol']));

    $pdo->commit();
    $_SESSION['success'] = "Rol eliminado correctamente";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error al eliminar el rol: " . $e->getMessage();
}

header('location: rol.php');
exit; 