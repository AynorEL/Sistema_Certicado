<?php
ob_start();
require_once('header.php');
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Agregar Administrador</h1>
    </div>
    <div class="content-header-right">
        <a href="admin.php" class="btn btn-primary btn-sm">Ver Todos</a>
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

            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Nombre Completo <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="nombre_completo" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Correo <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="email" class="form-control" name="correo" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Teléfono <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="telefono" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Contraseña <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" name="contrasena" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Confirmar Contraseña <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" name="confirm_contrasena" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Foto</label>
                            <div class="col-sm-4">
                                <input type="file" name="foto" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Rol <span>*</span></label>
                            <div class="col-sm-4">
                                <select class="form-control" name="rol" required>
                                    <option value="Super Admin">Super Admin</option>
                                    <option value="Admin">Admin</option>
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

    if(empty($_POST['nombre_completo'])) {
        $valid = 0;
        $_SESSION['error'] = "El nombre completo es requerido";
    }

    if(empty($_POST['correo'])) {
        $valid = 0;
        $_SESSION['error'] = "El correo es requerido";
    }

    if(empty($_POST['telefono'])) {
        $valid = 0;
        $_SESSION['error'] = "El teléfono es requerido";
    }

    if(empty($_POST['contrasena'])) {
        $valid = 0;
        $_SESSION['error'] = "La contraseña es requerida";
    }

    if(empty($_POST['confirm_contrasena'])) {
        $valid = 0;
        $_SESSION['error'] = "La confirmación de contraseña es requerida";
    }

    if($_POST['contrasena'] != $_POST['confirm_contrasena']) {
        $valid = 0;
        $_SESSION['error'] = "Las contraseñas no coinciden";
    }

    if($valid == 1) {
        $foto = '';
        if($_FILES['foto']['name'] != '') {
            $foto = time().'_'.$_FILES['foto']['name'];
            move_uploaded_file($_FILES['foto']['tmp_name'], '../img/'.$foto);
        }

        $statement = $pdo->prepare("INSERT INTO usuarios_admin (nombre_completo, correo, telefono, contrasena, foto, rol, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $statement->execute(array(
            $_POST['nombre_completo'],
            $_POST['correo'],
            $_POST['telefono'],
            password_hash($_POST['contrasena'], PASSWORD_DEFAULT),
            $foto,
            $_POST['rol'],
            $_POST['estado']
        ));
        
        $_SESSION['success'] = "Administrador agregado exitosamente";
        header('location: admin.php');
        exit();
    }
}
?>

<?php require_once('footer.php'); ?> 