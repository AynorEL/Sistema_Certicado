<?php
require_once('inc/config.php');
require_once('inc/functions.php');

if(!isset($_REQUEST['id'])) {
    $_SESSION['error'] = "ID de página no válido";
    header('location: pagina.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM paginas WHERE id=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        $_SESSION['error'] = "Página no encontrada";
        header('location: pagina.php');
        exit;
    }
}

$statement = $pdo->prepare("SELECT * FROM paginas WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $banner_pagina = $row['banner_pagina'];
}

// Eliminar archivo de banner si existe
if($banner_pagina != '') {
    $ruta_archivo = 'img/' . $banner_pagina;
    if (file_exists($ruta_archivo)) {
        unlink($ruta_archivo);
    }
}

$statement = $pdo->prepare("DELETE FROM paginas WHERE id=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Página eliminada exitosamente";
header('location: pagina.php');
exit();
?> 