<?php
ob_start();
session_start();
include_once("inc/config.php");
include_once("inc/functions.php");
include_once("inc/CSRF_Protect.php");
$csrf = new CSRF_Protect();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header('location: login.php');
    exit;
}

$error_message = '';
$success_message = '';

// Obtener datos del usuario actual
$stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE id_usuario = ?");
$stmt->execute([$_SESSION['user']['id_usuario']]);
$user = $stmt->fetch();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!$csrf->isTokenValid($_POST['_csrf'])) {
        $error_message = 'Error de validación del token CSRF';
    } else {
        if (isset($_POST['form1'])) {
            $valid = 1;
            $nombre_completo = clean($_POST['full_name']);
            $correo = clean($_POST['email']);
            $telefono = clean($_POST['phone']);

            if (empty($nombre_completo)) {
                $valid = 0;
                $error_message .= "El nombre no puede estar vacío<br>";
            }

            if (empty($correo)) {
                $valid = 0;
                $error_message .= 'La dirección de correo no puede estar vacía<br>';
            } else {
                if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $valid = 0;
                    $error_message .= 'La dirección de correo debe ser válida<br>';
                } else {
                    // Verificar si el correo ya existe
                    $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE correo = ? AND id_usuario != ?");
                    $stmt->execute([$correo, $_SESSION['user']['id_usuario']]);
                    if ($stmt->rowCount() > 0) {
                        $valid = 0;
                        $error_message .= 'La dirección de correo ya existe<br>';
                    }
                }
            }

            if ($valid == 1) {
                try {
                    $stmt = $pdo->prepare("UPDATE usuarios_admin SET nombre_completo = ?, correo = ?, telefono = ? WHERE id_usuario = ?");
                    $stmt->execute([$nombre_completo, $correo, $telefono, $_SESSION['user']['id_usuario']]);

                    $_SESSION['user']['nombre_completo'] = $nombre_completo;
                    $_SESSION['user']['correo'] = $correo;
                    $_SESSION['user']['telefono'] = $telefono;

                    $success_message = 'La información del usuario se ha actualizado con éxito.';
                } catch (PDOException $e) {
                    $error_message = 'Error al actualizar el perfil: ' . $e->getMessage();
                }
            }
        }

        if (isset($_POST['form2'])) {
            $valid = 1;
            
            // Verificar si se subió un archivo
            if (!isset($_FILES['photo']) || $_FILES['photo']['error'] != 0) {
                $valid = 0;
                $error_message .= 'Por favor selecciona una imagen<br>';
            } else {
                // Verificar el tipo de archivo
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = $_FILES['photo']['type'];
                
                if (!in_array($file_type, $allowed_types)) {
                    $valid = 0;
                    $error_message .= 'Solo se permiten archivos JPG, JPEG, PNG y GIF<br>';
                }
                
                // Verificar el tamaño del archivo (máximo 5MB)
                if ($_FILES['photo']['size'] > 5242880) {
                    $valid = 0;
                    $error_message .= 'El tamaño máximo permitido es 5MB<br>';
                }
            }

            if ($valid == 1) {
                try {
                    // Crear directorio si no existe
                    if (!file_exists('img')) {
                        mkdir('img', 0777, true);
                    }

                    // Eliminar foto existente si existe
                    if (!empty($user['foto']) && file_exists('img/' . $user['foto'])) {
                        unlink('img/' . $user['foto']);
                    }

                    // Generar nombre único para la nueva foto
                    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $new_filename = 'user-' . $_SESSION['user']['id_usuario'] . '-' . time() . '.' . $ext;
                    $target_file = 'img/' . $new_filename;

                    // Mover la nueva foto
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                        $stmt = $pdo->prepare("UPDATE usuarios_admin SET foto = ? WHERE id_usuario = ?");
                        $stmt->execute([$new_filename, $_SESSION['user']['id_usuario']]);
                        
                        // Actualizar la sesión
                        $_SESSION['user']['foto'] = $new_filename;
                        
                        // Actualizar la variable $user para mostrar la nueva foto
                        $user['foto'] = $new_filename;
                        
                        $success_message = 'La foto del usuario se ha actualizado con éxito.';
                    } else {
                        $error_message = 'Error al subir la imagen. Por favor, inténtalo de nuevo.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error al actualizar la foto: ' . $e->getMessage();
                }
            }
        }

        if (isset($_POST['form3'])) {
            $valid = 1;
            $contrasena = $_POST['password'];
            $password_confirm = $_POST['re_password'];

            if (empty($contrasena) || empty($password_confirm)) {
                $valid = 0;
                $error_message .= "La contraseña no puede estar vacía<br>";
            }

            if ($contrasena != $password_confirm) {
                $valid = 0;
                $error_message .= "Las contraseñas no coinciden<br>";
            }

            if ($valid == 1) {
                try {
                    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios_admin SET contrasena = ? WHERE id_usuario = ?");
                    $stmt->execute([$hashed_password, $_SESSION['user']['id_usuario']]);
                    $success_message = 'La contraseña del usuario se ha actualizado con éxito.';
                } catch (PDOException $e) {
                    $error_message = 'Error al actualizar la contraseña: ' . $e->getMessage();
                }
            }
        }
    }
}

include('header.php');
?>

<!-- Content Header -->
<section class="content-header">
    <div class="content-header-left">
        <h1>Editar Perfil</h1>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_1" data-toggle="tab">Actualizar Información</a></li>
                    <li><a href="#tab_2" data-toggle="tab">Actualizar Foto</a></li>
                    <li><a href="#tab_3" data-toggle="tab">Actualizar Contraseña</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <form class="form-horizontal" action="" method="post">
                            <?php $csrf->echoInputField(); ?>
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Nombre <span>*</span></label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['nombre_completo']); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Foto Existente</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <?php if (!empty($user['foto']) && file_exists('img/' . $user['foto'])): ?>
                                                <img src="img/<?php echo htmlspecialchars($user['foto']); ?>" class="existing-photo" width="140" style="border:1px solid #ddd; padding:5px;">
                                            <?php else: ?>
                                                <img src="img/default.jpg" class="existing-photo" width="140" style="border:1px solid #ddd; padding:5px;">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Correo Electrónico <span>*</span></label>
                                        <div class="col-sm-4">
                                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['correo']); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Teléfono</label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['telefono']); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Rol</label>
                                        <div class="col-sm-4" style="padding-top:7px;">
                                            <?php echo htmlspecialchars($user['rol']); ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form1">Actualizar Información</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane" id="tab_2">
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <?php $csrf->echoInputField(); ?>
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Nueva Foto</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <input type="file" name="photo" accept="image/*" required>
                                            <p class="help-block">Formatos permitidos: JPG, JPEG, PNG, GIF (Máximo 5MB)</p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form2">Actualizar Foto</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane" id="tab_3">
                        <form class="form-horizontal" action="" method="post">
                            <?php $csrf->echoInputField(); ?>
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Contraseña</label>
                                        <div class="col-sm-4">
                                            <input type="password" class="form-control" name="password" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Redigitación de Contraseña</label>
                                        <div class="col-sm-4">
                                            <input type="password" class="form-control" name="re_password" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form3">Actualizar Contraseña</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('footer.php'); ?>