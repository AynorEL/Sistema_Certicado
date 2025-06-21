<?php
ob_start();
require_once('header.php');

if (!isset($_GET['id'])) {
    header('location: instructor.php');
    exit();
}

$statement = $pdo->prepare("SELECT * FROM instructor WHERE idinstructor=?");
$statement->execute(array($_GET['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $nombre = $row['nombre'];
    $apellido = $row['apellido'];
    $especialidad = $row['especialidad'];
    $experiencia = $row['experiencia'];
    $email = $row['email'];
    $telefono = $row['telefono'];
    $firma_digital = $row['firma_digital'];
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Editar Instructor</h1>
    </div>
    <div class="content-header-right">
        <a href="instructor.php" class="btn btn-primary btn-sm">Ver Todos</a>
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
                            <label for="" class="col-sm-2 control-label">Nombre <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="nombre" class="form-control" value="<?php echo $nombre; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Apellido <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="apellido" class="form-control" value="<?php echo $apellido; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Especialidad <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="especialidad" class="form-control" value="<?php echo $especialidad; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Años de Experiencia <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="number" name="experiencia" class="form-control" min="0" value="<?php echo $experiencia; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Email <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Teléfono <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="telefono" class="form-control" value="<?php echo $telefono; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Firma Digital Actual</label>
                            <div class="col-sm-4">
                                <?php if (!empty($firma_digital)): ?>
                                    <img src="<?php echo BASE_URL . 'assets/uploads/firmas/' . $firma_digital; ?>" alt="Firma Digital" style="max-width: 200px; max-height: 100px; border: 1px solid #ddd;">
                                    <br><small class="text-muted"><?php echo $firma_digital; ?></small>
                                <?php else: ?>
                                    <span class="text-muted">No hay firma digital</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Nueva Firma Digital</label>
                            <div class="col-sm-4">
                                <input type="file" name="firma_digital" class="form-control" accept="image/*">
                                <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB. Deje vacío para mantener la actual.</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Actualizar</button>
                                <a href="instructor.php" class="btn btn-danger pull-left" style="margin-left: 10px;">Cancelar</a>
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

    if (empty($_POST['nombre'])) {
        $valid = 0;
        $_SESSION['error'] = "El nombre es requerido";
    }

    if (empty($_POST['apellido'])) {
        $valid = 0;
        $_SESSION['error'] = "El apellido es requerido";
    }

    if (empty($_POST['especialidad'])) {
        $valid = 0;
        $_SESSION['error'] = "La especialidad es requerida";
    }

    if (empty($_POST['experiencia'])) {
        $valid = 0;
        $_SESSION['error'] = "Los años de experiencia son requeridos";
    }

    if (empty($_POST['email'])) {
        $valid = 0;
        $_SESSION['error'] = "El email es requerido";
    }

    if (empty($_POST['telefono'])) {
        $valid = 0;
        $_SESSION['error'] = "El teléfono es requerido";
    }

    // Validar nueva firma digital si se subió
    $new_firma_digital = '';
    if (isset($_FILES['firma_digital']) && $_FILES['firma_digital']['error'] === UPLOAD_ERR_OK) {
        $firma_digital_file = $_FILES['firma_digital'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($firma_digital_file['type'], $allowed_types)) {
            $valid = 0;
            $_SESSION['error'] = "Formato de imagen no válido. Use JPG, PNG o GIF";
        }

        if ($firma_digital_file['size'] > $max_size) {
            $valid = 0;
            $_SESSION['error'] = "El archivo es demasiado grande. Máximo 2MB";
        }

        if ($valid == 1) {
            $file_extension = pathinfo($_FILES['firma_digital']['name'], PATHINFO_EXTENSION);
            $new_firma_digital = 'instructor_firma_' . time() . '.' . $file_extension;
            $upload_path = FIRMAS_PATH . $new_firma_digital;
            
            if (!move_uploaded_file($_FILES['firma_digital']['tmp_name'], $upload_path)) {
                $valid = 0;
                $_SESSION['error'] = "Error al subir la nueva firma digital";
            }
        }
    }

    if ($valid == 1) {
        // Determinar qué firma digital usar
        $firma_digital_to_save = $firma_digital; // Mantener la actual por defecto
        
        if (!empty($new_firma_digital)) {
            // Si se subió una nueva firma, eliminar la anterior y usar la nueva
            if (!empty($firma_digital) && file_exists(FIRMAS_PATH . $firma_digital)) {
                unlink(FIRMAS_PATH . $firma_digital);
            }
            $firma_digital_to_save = $new_firma_digital;
        }

        $statement = $pdo->prepare("UPDATE instructor SET nombre=?, apellido=?, especialidad=?, experiencia=?, email=?, telefono=?, firma_digital=? WHERE idinstructor=?");
        $statement->execute(array(
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['especialidad'],
            $_POST['experiencia'],
            $_POST['email'],
            $_POST['telefono'],
            $firma_digital_to_save,
            $_GET['id']
        ));

        $_SESSION['success'] = "Instructor actualizado exitosamente";
        header('location: instructor.php');
        exit();
    }
}
?>

<?php require_once('footer.php'); ?> 