<?php
ob_start();
require_once('header.php');
require_once('inc/functions.php');

if(!isset($_REQUEST['id'])) {
    $_SESSION['error'] = "ID de usuario no válido";
    header('location: usuario.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM usuarios_admin WHERE id_usuario=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        $_SESSION['error'] = "Usuario no encontrado";
        header('location: usuario.php');
        exit;
    }
}
$row = $statement->fetch(PDO::FETCH_ASSOC);
$foto = $row['foto'];
if($foto && $foto != 'user-placeholder.png' && file_exists('img/'.$foto)) {
    unlink('img/'.$foto);
}
$statement = $pdo->prepare("DELETE FROM usuarios_admin WHERE id_usuario=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Usuario eliminado exitosamente";
header('location: usuario.php');
exit();
?> 