<?php
ob_start();
include('header.php');

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit();
} else {
    $statement = $pdo->prepare("SELECT * FROM cargo WHERE idcargo=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        header('location: logout.php');
        exit();
    }
}

$statement = $pdo->prepare("SELECT * FROM cargo WHERE idcargo=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $nombre_cargo = $row['nombre_cargo'];
}
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Editar Cargo
        <small>Modificar cargo existente</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Editar Cargo</h3>
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
                            <label for="nombre_cargo" class="col-sm-2 control-label">Nombre del Cargo <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="nombre_cargo" id="nombre_cargo" value="<?php echo $nombre_cargo; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-offset-2 col-sm-6">
                            <button type="submit" class="btn btn-success" name="form1">Actualizar</button>
                            <a href="cargo.php" class="btn btn-default">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
if(isset($_POST['form1'])) {
    $nombre_cargo = trim($_POST['nombre_cargo']);
    
    if(empty($nombre_cargo)) {
        $_SESSION['error'] = "El nombre del cargo es requerido";
        header("location: cargo-edit.php?id=".$_REQUEST['id']);
        exit();
    }

    $statement = $pdo->prepare("UPDATE cargo SET nombre_cargo=? WHERE idcargo=?");
    $statement->execute(array($nombre_cargo, $_REQUEST['id']));
    
    $_SESSION['success'] = "Cargo actualizado exitosamente";
    header("location: cargo.php");
    exit();
}
?>

<?php include('footer.php'); ?> 