<?php
$page_title = "Agregar Género";
$page_subtitle = "Nuevo Género";
include("header.php");
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?php echo $page_title; ?></h1>
            </div>
            <div class="col-sm-6">
                <a href="genero.php" class="btn btn-primary float-right">
                    <i class="fa fa-list"></i> Ver Todos
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php
                        if(isset($_SESSION['error_message'])) {
                            echo '<div class="alert alert-danger">'.$_SESSION['error_message'].'</div>';
                            unset($_SESSION['error_message']);
                        }
                        ?>
                        <form action="" method="post">
                            <div class="form-group">
                                <label for="nombre_genero">Nombre del Género <span class="text-danger">*</span></label>
                                <input type="text" name="nombre_genero" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <input type="submit" name="form1" class="btn btn-success" value="Guardar">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
if(isset($_POST['form1'])) {
    $nombre_genero = trim($_POST['nombre_genero']);
    
    if(empty($nombre_genero)) {
        $_SESSION['error_message'] = "El nombre del género es requerido.";
        header("location: genero-add.php");
        exit();
    }

    $statement = $pdo->prepare("SELECT * FROM genero WHERE nombre_genero = ?");
    $statement->execute(array($nombre_genero));
    $total = $statement->rowCount();

    if($total) {
        $_SESSION['error_message'] = "Este género ya existe.";
        header("location: genero-add.php");
        exit();
    }

    $statement = $pdo->prepare("INSERT INTO genero (nombre_genero) VALUES (?)");
    $statement->execute(array($nombre_genero));

    $_SESSION['success_message'] = "Género agregado exitosamente.";
    header("location: genero.php");
    exit();
}
?>

<?php include("footer.php"); ?> 