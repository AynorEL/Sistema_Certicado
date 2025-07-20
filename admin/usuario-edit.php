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
                                <div style="position:relative;">
                                    <input type="password" class="form-control" name="password" id="edit_password">
                                    <span class="toggle-password" onclick="togglePassword('edit_password', this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;font-size:1.2em;color:#888;z-index:2;"><i class="ph ph-eye"></i></span>
                                </div>
                                <small class="text-muted">Dejar en blanco para mantener la contraseña actual</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Confirmar Contraseña</label>
                            <div class="col-sm-4">
                                <div style="position:relative;">
                                    <input type="password" class="form-control" name="confirm_password" id="edit_confirm_password">
                                    <span class="toggle-password" onclick="togglePassword('edit_confirm_password', this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;font-size:1.2em;color:#888;z-index:2;"><i class="ph ph-eye"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Foto</label>
                            <div class="col-sm-4">
                                <?php if(!empty($foto) && file_exists('img/'.$foto)) { ?>
                                    <img src="img/<?php echo $foto; ?>" alt="Foto actual" style="max-width:100px;max-height:100px;display:block;margin-bottom:10px;">
                                <?php } ?>
                                <input type="file" class="form-control" name="foto" accept="image/*">
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
    } elseif(!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}$/u', $_POST['nombre_completo'])) {
        $valid = 0;
        $_SESSION['error'] = "El nombre solo puede contener letras y espacios (3-100 caracteres).";
    }
    if(empty($_POST['correo'])) {
        $valid = 0;
        $_SESSION['error'] = "El correo es requerido";
    } elseif(!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
        $valid = 0;
        $_SESSION['error'] = "El correo no tiene un formato válido.";
    }
    if(empty($_POST['telefono'])) {
        $valid = 0;
        $_SESSION['error'] = "El teléfono es requerido";
    } elseif(!preg_match('/^[0-9]{9}$/', $_POST['telefono'])) {
        $valid = 0;
        $_SESSION['error'] = "El teléfono debe tener exactamente 9 dígitos numéricos.";
    }
    // Validar correo único excepto el propio
    $statement = $pdo->prepare("SELECT * FROM usuarios_admin WHERE correo = ? AND id_usuario != ?");
    $statement->execute([$_POST['correo'], $_REQUEST['id']]);
    if($statement->rowCount() > 0) {
        $valid = 0;
        $_SESSION['error'] = "El correo ya está registrado";
    }
    if(!empty($_POST['password'])) {
        if(strlen($_POST['password']) < 6) {
            $valid = 0;
            $_SESSION['error'] = "La contraseña debe tener al menos 6 caracteres.";
        }
        if($_POST['password'] != $_POST['confirm_password']) {
            $valid = 0;
            $_SESSION['error'] = "Las contraseñas no coinciden";
        }
    }
    // Procesar imagen
    $foto_nombre = $foto;
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $permitidas = ['jpg','jpeg','png','gif','webp'];
        if(in_array(strtolower($ext), $permitidas)) {
            if($_FILES['foto']['size'] > 2*1024*1024) {
                $valid = 0;
                $_SESSION['error'] = "La imagen no debe superar los 2MB.";
            } else {
                $foto_nombre = 'admin_'.time().'.'.$ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], 'img/'.$foto_nombre);
            }
        } else {
            $valid = 0;
            $_SESSION['error'] = "Solo se permiten imágenes (jpg, jpeg, png, gif, webp)";
        }
    }
    if($valid == 1) {
        if(!empty($_POST['password'])) {
            $statement = $pdo->prepare("UPDATE usuarios_admin SET nombre_completo=?, correo=?, telefono=?, contrasena=?, foto=?, estado=? WHERE id_usuario=?");
            $statement->execute([
                $_POST['nombre_completo'],
                $_POST['correo'],
                $_POST['telefono'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $foto_nombre,
                $_POST['estado'],
                $_REQUEST['id']
            ]);
        } else {
            $statement = $pdo->prepare("UPDATE usuarios_admin SET nombre_completo=?, correo=?, telefono=?, foto=?, estado=? WHERE id_usuario=?");
            $statement->execute([
                $_POST['nombre_completo'],
                $_POST['correo'],
                $_POST['telefono'],
                $foto_nombre,
                $_POST['estado'],
                $_REQUEST['id']
            ]);
        }
        $_SESSION['success'] = "Usuario actualizado exitosamente";
        header('location: usuario.php');
        exit();
    }
}
?>

<?php require_once('footer.php'); ?> 

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación en tiempo real para cada campo
    const nombre = document.querySelector('input[name="nombre_completo"]');
    const correo = document.querySelector('input[name="correo"]');
    const telefono = document.querySelector('input[name="telefono"]');
    const password = document.querySelector('input[name="password"]');
    const confirmPassword = document.querySelector('input[name="confirm_password"]');

    function showError(input, message) {
        let error = input.parentElement.querySelector('.text-danger');
        if (!error) {
            error = document.createElement('div');
            error.className = 'text-danger';
            input.parentElement.appendChild(error);
        }
        error.textContent = message;
    }
    function clearError(input) {
        let error = input.parentElement.querySelector('.text-danger');
        if (error) error.textContent = '';
    }
    nombre.addEventListener('input', function() {
        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}$/.test(nombre.value)) {
            showError(nombre, 'El nombre solo puede contener letras y espacios (3-100 caracteres).');
        } else {
            clearError(nombre);
        }
    });
    correo.addEventListener('input', function() {
        if (!/^\S+@\S+\.\S+$/.test(correo.value)) {
            showError(correo, 'El correo no tiene un formato válido.');
        } else {
            clearError(correo);
        }
    });
    telefono.addEventListener('input', function() {
        if (!/^[0-9]{9}$/.test(telefono.value)) {
            showError(telefono, 'El teléfono debe tener exactamente 9 dígitos numéricos.');
        } else {
            clearError(telefono);
        }
    });
    password.addEventListener('input', function() {
        if (password.value.length > 0 && password.value.length < 6) {
            showError(password, 'La contraseña debe tener al menos 6 caracteres.');
        } else {
            clearError(password);
        }
    });
    confirmPassword.addEventListener('input', function() {
        if (confirmPassword.value !== password.value) {
            showError(confirmPassword, 'Las contraseñas no coinciden.');
        } else {
            clearError(confirmPassword);
        }
    });
});
</script> 
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<script>
function togglePassword(id, el) {
  const input = document.getElementById(id);
  const icon = el.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('ph-eye');
    icon.classList.add('ph-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('ph-eye-slash');
    icon.classList.add('ph-eye');
  }
}
</script> 