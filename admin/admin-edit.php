<?php
ob_start();
require_once('header.php');

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM usuarios_admin WHERE id_usuario=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        header('location: logout.php');
        exit;
    }
}

$statement = $pdo->prepare("SELECT * FROM usuarios_admin WHERE id_usuario=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $nombre_completo = $row['nombre_completo'];
    $correo = $row['correo'];
    $telefono = $row['telefono'];
    $foto = $row['foto'];
    $rol = $row['rol'];
    $estado = $row['estado'];
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Editar Administrador</h1>
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
                                <input type="text" class="form-control" name="nombre_completo" value="<?php echo $nombre_completo; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Correo <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="email" class="form-control" name="correo" value="<?php echo $correo; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Teléfono <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="telefono" value="<?php echo $telefono; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Contraseña</label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" name="contrasena">
                                <small class="text-muted">Dejar en blanco para mantener la contraseña actual</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Confirmar Contraseña</label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" name="confirm_contrasena">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Foto</label>
                            <div class="col-sm-4">
                                <input type="file" name="foto" class="form-control">
                                <?php if($foto != ''): ?>
                                    <img src="../img/<?php echo $foto; ?>" alt="" style="width:100px;margin-top:5px;">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Rol <span>*</span></label>
                            <div class="col-sm-4">
                                <select class="form-control" name="rol" required>
                                    <option value="Super Admin" <?php if($rol == 'Super Admin') { echo 'selected'; } ?>>Super Admin</option>
                                    <option value="Admin" <?php if($rol == 'Admin') { echo 'selected'; } ?>>Admin</option>
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

    if(!empty($_POST['contrasena']) || !empty($_POST['confirm_contrasena'])) {
        if($_POST['contrasena'] != $_POST['confirm_contrasena']) {
            $valid = 0;
            $_SESSION['error'] = "Las contraseñas no coinciden";
        }
    }

    if($valid == 1) {
        $foto = $foto;
        if($_FILES['foto']['name'] != '') {
            $foto = time().'_'.$_FILES['foto']['name'];
            move_uploaded_file($_FILES['foto']['tmp_name'], '../img/'.$foto);
        }

        if(!empty($_POST['contrasena'])) {
            $statement = $pdo->prepare("UPDATE usuarios_admin SET nombre_completo=?, correo=?, telefono=?, contrasena=?, foto=?, rol=?, estado=? WHERE id_usuario=?");
            $statement->execute(array(
                $_POST['nombre_completo'],
                $_POST['correo'],
                $_POST['telefono'],
                password_hash($_POST['contrasena'], PASSWORD_DEFAULT),
                $foto,
                $_POST['rol'],
                $_POST['estado'],
                $_REQUEST['id']
            ));
        } else {
            $statement = $pdo->prepare("UPDATE usuarios_admin SET nombre_completo=?, correo=?, telefono=?, foto=?, rol=?, estado=? WHERE id_usuario=?");
            $statement->execute(array(
                $_POST['nombre_completo'],
                $_POST['correo'],
                $_POST['telefono'],
                $foto,
                $_POST['rol'],
                $_POST['estado'],
                $_REQUEST['id']
            ));
        }
        
        $_SESSION['success'] = "Administrador actualizado exitosamente";
        header('location: admin.php');
        exit();
    }
}
?>

<?php require_once('footer.php'); ?> 