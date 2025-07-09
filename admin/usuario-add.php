<?php
ob_start();
require_once('header.php');
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Agregar Usuario</h1>
    </div>
    <div class="content-header-right">
        <a href="usuario.php" class="btn btn-primary btn-sm">Ver Todos</a>
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
                            <label for="" class="col-sm-2 control-label">Nombre de Usuario <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="nombre_usuario" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Contraseña <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Confirmar Contraseña <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Rol <span>*</span></label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="idrol" required>
                                    <option value="">Seleccione un rol</option>
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM rol ORDER BY nombre_rol ASC");
                                    $statement->execute();
                                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($result as $row) {
                                        echo '<option value="'.$row['idrol'].'">'.$row['nombre_rol'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Estado <span>*</span></label>
                            <div class="col-sm-4">
                                <select class="form-control" name="estado" required>
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Guardar</button>
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

    if(empty($_POST['nombre_usuario'])) {
        $valid = 0;
        $_SESSION['error'] = "El nombre de usuario es requerido";
    }

    if(empty($_POST['password'])) {
        $valid = 0;
        $_SESSION['error'] = "La contraseña es requerida";
    }

    if(empty($_POST['confirm_password'])) {
        $valid = 0;
        $_SESSION['error'] = "La confirmación de contraseña es requerida";
    }

    if($_POST['password'] != $_POST['confirm_password']) {
        $valid = 0;
        $_SESSION['error'] = "Las contraseñas no coinciden";
    }

    if(empty($_POST['idrol'])) {
        $valid = 0;
        $_SESSION['error'] = "Debe seleccionar un rol";
    }

    if($valid == 1) {
        $statement = $pdo->prepare("INSERT INTO usuario (nombre_usuario, password, idrol, estado) VALUES (?, ?, ?, ?)");
        $statement->execute(array(
            $_POST['nombre_usuario'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['idrol'],
            $_POST['estado']
        ));
        
        $_SESSION['success'] = "Usuario agregado exitosamente";
        header('location: usuario.php');
        exit();
    }
}
?>

<?php require_once('footer.php'); ?> 