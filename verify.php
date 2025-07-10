<?php

require_once 'header.php';

$errorMessage = '';
$successMessage = '';

if ((!isset($_REQUEST['email'])) || (isset($_REQUEST['token']))) {
    $var = 1;

    // Comprobar si el token es correcto y coincide con la base de datos.
    $statement = $pdo->prepare("SELECT * FROM cliente WHERE email=?");
    $statement->execute(array($_REQUEST['email']));
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
        if ($_REQUEST['token'] != $row['token']) {
            header('location: ' . BASE_URL);
            exit;
        }
    }
    // Test de despliegue automático

    // Todo está correcto. Ahora activa al usuario eliminando el valor del token de la base de datos.
    if ($var != 0) {
        $statement = $pdo->prepare("UPDATE cliente SET token=?, estado=? WHERE email=?");
        $statement->execute(array('', 'Activo', $_GET['email']));

        $successMessage = '<p style="color:green;">Tu correo electrónico se ha verificado con éxito. '
            . 'Ahora puedes iniciar sesión en nuestro sitio web.</p>'
            . '<p><a href="' . BASE_URL . 'login.php" style="color:#167ac6;font-weight:bold;">'
            . 'Haz clic aquí para iniciar sesión</a></p>';
    }
}

?>
// Test de despliegue automático

<div class="page-banner" style="background-color:#444;">
    <div class="inner">
        <h1>Registro Exitoso</h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">
                    <?php
                    echo $errorMessage;
                    echo $successMessage;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>