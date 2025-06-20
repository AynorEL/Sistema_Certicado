<?php
ob_start();
require_once('header.php');
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Agregar Categoría</h1>
    </div>
    <div class="content-header-right">
        <a href="categoria.php" class="btn btn-primary btn-sm">Ver Todos</a>
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
                            <label for="" class="col-sm-2 control-label">Nombre de la Categoría <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="nombre_categoria" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Descripción <span>*</span></label>
                            <div class="col-sm-4">
                                <textarea name="descripcion" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Guardar</button>
                                <a href="categoria.php" class="btn btn-danger pull-left" style="margin-left: 10px;">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
if (isset($_POST['form1'])) {
    $valid = 1;

    if (empty($_POST['nombre_categoria'])) {
        $valid = 0;
        $_SESSION['error'] = "El nombre de la categoría es requerido";
    }

    if (empty($_POST['descripcion'])) {
        $valid = 0;
        $_SESSION['error'] = "La descripción es requerida";
    }

    if ($valid == 1) {
        $statement = $pdo->prepare("INSERT INTO categoria (nombre_categoria, descripcion) VALUES (?, ?)");
        $statement->execute(array(
            $_POST['nombre_categoria'],
            $_POST['descripcion']
        ));

        $_SESSION['success'] = "Categoría agregada exitosamente";
        header('location: categoria.php');
        exit();
    }
}
?>

<?php require_once('footer.php'); ?> 