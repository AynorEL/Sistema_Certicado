<?php
ob_start();
include('header.php');

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit();
} else {
    $statement = $pdo->prepare("SELECT * FROM genero WHERE idgenero=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        header('location: logout.php');
        exit();
    }
}

$statement = $pdo->prepare("SELECT * FROM genero WHERE idgenero=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $nombre_genero = $row['nombre_genero'];
}
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Editar Género
        <small>Modificar género existente</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Editar Género</h3>
                </div>
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
                <form class="form-horizontal" action="" method="post">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="nombre_genero" class="col-sm-2 control-label">Nombre del Género <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="nombre_genero" id="nombre_genero" value="<?php echo $nombre_genero; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-offset-2 col-sm-6">
                            <button type="submit" class="btn btn-success" name="form1">Actualizar</button>
                            <a href="genero.php" class="btn btn-default">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
if(isset($_POST['form1'])) {
    $nombre_genero = trim($_POST['nombre_genero']);
    
    if(empty($nombre_genero)) {
        $_SESSION['error'] = "El nombre del género es requerido";
        header("location: genero-edit.php?id=".$_REQUEST['id']);
        exit();
    }

    $statement = $pdo->prepare("UPDATE genero SET nombre_genero=? WHERE idgenero=?");
    $statement->execute(array($nombre_genero, $_REQUEST['id']));
    
    $_SESSION['success'] = "Género actualizado exitosamente";
    header("location: genero.php");
    exit();
}
?>

<?php include('footer.php'); ?> 