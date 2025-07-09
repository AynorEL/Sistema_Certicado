<?php
ob_start();
require_once('header.php');

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

// Verificar si el rol está en uso por algún usuario
$statement = $pdo->prepare("SELECT COUNT(*) as total FROM usuario WHERE idrol=?");
$statement->execute(array($_REQUEST['idrol']));
$result = $statement->fetch(PDO::FETCH_ASSOC);

if ($result['total'] > 0) {
    $_SESSION['error'] = "No se puede eliminar el rol porque está siendo utilizado por " . $result['total'] . " usuario(s). Primero debe reasignar estos usuarios a otro rol.";
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