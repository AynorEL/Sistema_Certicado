<?php
ob_start();
include('header.php');
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Agregar Cargo
        <small>Crear nuevo cargo</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Agregar Nuevo Cargo</h3>
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
                                <input type="text" class="form-control" name="nombre_cargo" id="nombre_cargo" required>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-offset-2 col-sm-6">
                            <button type="submit" class="btn btn-success" name="form1">Guardar</button>
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
        header("location: cargo-add.php");
        exit();
    }

    $statement = $pdo->prepare("INSERT INTO cargo (nombre_cargo) VALUES (?)");
    $statement->execute(array($nombre_cargo));
    
    $_SESSION['success'] = "Cargo agregado exitosamente";
    header("location: cargo.php");
    exit();
}
?>

<?php include('footer.php'); ?> 