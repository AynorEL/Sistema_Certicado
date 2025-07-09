<?php
ob_start();
require_once('header.php');
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Agregar Empleado</h1>
    </div>
    <div class="content-header-right">
        <a href="empleado.php" class="btn btn-primary btn-sm">Ver Todos</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if(isset($_SESSION['error'])): ?>
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
                            <label for="" class="col-sm-2 control-label">Nombre <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Apellido <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="apellido" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">DNI <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="dni" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Cargo <span>*</span></label>
                            <div class="col-sm-4">
                                <select class="form-control" name="idcargo" required>
                                    <option value="">Seleccionar Cargo</option>
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM cargo ORDER BY nombre_cargo ASC");
                                    $statement->execute();
                                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($result as $row) {
                                        echo '<option value="'.$row['idcargo'].'">'.$row['nombre_cargo'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Teléfono <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="telefono" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Email <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Agregar</button>
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

    if(empty($_POST['nombre'])) {
        $valid = 0;
        $_SESSION['error'] = "El nombre es requerido";
    }

    if(empty($_POST['apellido'])) {
        $valid = 0;
        $_SESSION['error'] = "El apellido es requerido";
    }

    if(empty($_POST['dni'])) {
        $valid = 0;
        $_SESSION['error'] = "El DNI es requerido";
    }

    if(empty($_POST['idcargo'])) {
        $valid = 0;
        $_SESSION['error'] = "El cargo es requerido";
    }

    if(empty($_POST['telefono'])) {
        $valid = 0;
        $_SESSION['error'] = "El teléfono es requerido";
    }

    if(empty($_POST['email'])) {
        $valid = 0;
        $_SESSION['error'] = "El email es requerido";
    }

    if($valid == 1) {
        $statement = $pdo->prepare("INSERT INTO empleado (nombre, apellido, dni, idcargo, telefono, email) VALUES (?, ?, ?, ?, ?, ?)");
        $statement->execute(array(
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['dni'],
            $_POST['idcargo'],
            $_POST['telefono'],
            $_POST['email']
        ));
        
        $_SESSION['success'] = "Empleado agregado exitosamente";
        header('location: empleado.php');
        exit();
    }
}
?>

<?php require_once('footer.php'); ?> 