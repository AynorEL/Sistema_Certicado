<?php
require_once('inc/config.php');

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM paginas WHERE id=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        header('location: logout.php');
        exit;
    }
}

$statement = $pdo->prepare("SELECT * FROM paginas WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $banner_pagina = $row['banner_pagina'];
}

if($banner_pagina != '') {
    unlink('img/' . $banner_pagina);
}

$statement = $pdo->prepare("DELETE FROM paginas WHERE id=?");
$statement->execute(array($_REQUEST['id']));

$_SESSION['success'] = "Página eliminada exitosamente";
header('location: pagina.php');
exit();
?> 