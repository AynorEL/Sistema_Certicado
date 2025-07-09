<?php
ob_start();
require_once('header.php');

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM usuario WHERE idusuario=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        header('location: logout.php');
        exit;
    }
}

$statement = $pdo->prepare("SELECT * FROM usuario WHERE idusuario=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $nombre_usuario = $row['nombre_usuario'];
    $idrol = $row['idrol'];
    $estado = $row['estado'];
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Editar Usuario</h1>
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
                                <input type="text" class="form-control" name="nombre_usuario" value="<?php echo $nombre_usuario; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Contraseña</label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" name="password">
                                <small class="text-muted">Dejar en blanco para mantener la contraseña actual</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Confirmar Contraseña</label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" name="confirm_password">
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
                                        if($row['idrol'] == $idrol) {
                                            echo '<option value="'.$row['idrol'].'" selected>'.$row['nombre_rol'].'</option>';
                                        } else {
                                            echo '<option value="'.$row['idrol'].'">'.$row['nombre_rol'].'</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Estado <span>*</span></label>
                            <div class="col-sm-4">
                                <select class="form-control" name="estado" required>
                                    <option value="Activo" <?php if($estado == 'Activo') { echo 'selected'; } ?>>Activo</option>
                                    <option value="Inactivo" <?php if($estado == 'Inactivo') { echo 'selected'; } ?>>Inactivo</option>
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

    if(empty($_POST['nombre_usuario'])) {
        $valid = 0;
        $_SESSION['error'] = "El nombre de usuario es requerido";
    }

    if(empty($_POST['idrol'])) {
        $valid = 0;
        $_SESSION['error'] = "Debe seleccionar un rol";
    }

    if(!empty($_POST['password']) || !empty($_POST['confirm_password'])) {
        if($_POST['password'] != $_POST['confirm_password']) {
            $valid = 0;
            $_SESSION['error'] = "Las contraseñas no coinciden";
        }
    }

    if($valid == 1) {
        if(!empty($_POST['password'])) {
            $statement = $pdo->prepare("UPDATE usuario SET nombre_usuario=?, password=?, idrol=?, estado=? WHERE idusuario=?");
            $statement->execute(array(
                $_POST['nombre_usuario'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['idrol'],
                $_POST['estado'],
                $_REQUEST['id']
            ));
        } else {
            $statement = $pdo->prepare("UPDATE usuario SET nombre_usuario=?, idrol=?, estado=? WHERE idusuario=?");
            $statement->execute(array(
                $_POST['nombre_usuario'],
                $_POST['idrol'],
                $_POST['estado'],
                $_REQUEST['id']
            ));
        }
        
        $_SESSION['success'] = "Usuario actualizado exitosamente";
        header('location: usuario.php');
        exit();
    }
}
?>

<?php require_once('footer.php'); ?> 