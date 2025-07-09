<?php
ob_start();
require_once('header.php');

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM modulo WHERE idmodulo=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        header('location: logout.php');
        exit;
    }
}

$statement = $pdo->prepare("SELECT * FROM modulo WHERE idmodulo=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $nombre_modulo = $row['nombre_modulo'];
    $descripcion = $row['descripcion'];
    $idcurso = $row['idcurso'];
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Editar Módulo</h1>
    </div>
    <div class="content-header-right">
        <a href="modulo.php" class="btn btn-primary btn-sm">Ver Todos</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible" id="error-alert">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-ban"></i> ¡Error!</h4>
                    <?php
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <form class="form-horizontal" action="" method="post">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Nombre del Módulo <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="nombre_modulo" value="<?php echo $nombre_modulo; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Descripción <span>*</span></label>
                            <div class="col-sm-8">
                                <textarea class="form-control" name="descripcion" rows="5" required><?php echo $descripcion; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Curso <span>*</span></label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="idcurso" required>
                                    <option value="">Seleccione un curso</option>
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM curso ORDER BY nombre_curso ASC");
                                    $statement->execute();
                                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($result as $row) {
                                        if($row['idcurso'] == $idcurso) {
                                            echo '<option value="'.$row['idcurso'].'" selected>'.$row['nombre_curso'].'</option>';
                                        } else {
                                            echo '<option value="'.$row['idcurso'].'">'.$row['nombre_curso'].'</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Actualizar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
if(isset($_POST['form1'])) {
    $valid = 1;

    if(empty($_POST['nombre_modulo'])) {
        $valid = 0;
        $_SESSION['error'] = "El nombre del módulo es requerido";
    }

    if(empty($_POST['descripcion'])) {
        $valid = 0;
        $_SESSION['error'] = "La descripción es requerida";
    }

    if(empty($_POST['idcurso'])) {
        $valid = 0;
        $_SESSION['error'] = "Debe seleccionar un curso";
    }

    if($valid == 1) {
        $statement = $pdo->prepare("UPDATE modulo SET nombre_modulo=?, descripcion=?, idcurso=? WHERE idmodulo=?");
        $statement->execute(array($_POST['nombre_modulo'], $_POST['descripcion'], $_POST['idcurso'], $_REQUEST['id']));
        
        $_SESSION['success'] = "Módulo actualizado exitosamente";
        header('location: modulo.php');
        exit();
    }
}
?>

<?php require_once('footer.php'); ?> 