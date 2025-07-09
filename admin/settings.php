<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

//Cambiar Logo
if (isset($_POST['form1'])) {
    $valid = 1;

    $path = $_FILES['photo_logo']['name'];
    $path_tmp = $_FILES['photo_logo']['tmp_name'];

    if ($path == '') {
        $valid = 0;
        $error_message .= 'Debe seleccionar una foto<br>';
    } else {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $file_name = basename($path, '.' . $ext);
        if ($ext != 'jpg' && $ext != 'png' && $ext != 'jpeg' && $ext != 'gif') {
            $valid = 0;
            $error_message .= 'Debe subir un archivo jpg, jpeg, gif o png<br>';
        }
    }

    if ($valid == 1) {
        $statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $logo = $row['logo'];
            if ($logo != '') {
                $logo_path = '../assets/uploads/' . $logo;
                if (file_exists($logo_path)) {
                    unlink($logo_path);
                }
            }
        }

        $final_name = 'logo_' . time() . '.' . $ext;

        if (move_uploaded_file($path_tmp, '../assets/uploads/' . $final_name)) {
            $statement = $pdo->prepare("UPDATE configuraciones SET logo=? WHERE id=1");
            $statement->execute(array($final_name));
            $success_message = 'El logo se ha actualizado con éxito.';
        } else {
            $error_message = 'Error al subir la imagen. Por favor, intente nuevamente.';
        }
    }
}

// Cambiar Favicon
if (isset($_POST['form2'])) {
    $valid = 1;

    $path = $_FILES['photo_favicon']['name'];
    $path_tmp = $_FILES['photo_favicon']['tmp_name'];

    if ($path == '') {
        $valid = 0;
        $error_message .= 'Debe seleccionar una foto<br>';
    } else {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $file_name = basename($path, '.' . $ext);
        if ($ext != 'jpg' && $ext != 'png' && $ext != 'jpeg' && $ext != 'gif') {
            $valid = 0;
            $error_message .= 'Debe subir un archivo jpg, jpeg, gif o png<br>';
        }
    }

    if ($valid == 1) {
        $statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $favicon = $row['favicon'];
            if ($favicon != '') {
                $favicon_path = '../assets/uploads/' . $favicon;
                if (file_exists($favicon_path)) {
                    unlink($favicon_path);
                }
            }
        }

        $final_name = 'favicon_' . time() . '.' . $ext;

        if (move_uploaded_file($path_tmp, '../assets/uploads/' . $final_name)) {
            $statement = $pdo->prepare("UPDATE configuraciones SET favicon=? WHERE id=1");
            $statement->execute(array($final_name));
            $success_message = 'El favicon se ha actualizado con éxito.';
        } else {
            $error_message = 'Error al subir la imagen. Por favor, intente nuevamente.';
        }
    }
}

//Pie de Página y Página de Contacto
if (isset($_POST['form3'])) {
    $statement = $pdo->prepare("UPDATE configuraciones SET 
        pie_pagina_descripcion=?,
        pie_pagina_derechos=?,
        direccion_contacto=?,
        correo_contacto=?,
        telefono_contacto=?,
        fax_contacto=?,
        mapa_contacto=?,
        boletin_activo=?
        WHERE id=1");
    $statement->execute(array(
        $_POST['pie_pagina_descripcion'],
        $_POST['pie_pagina_derechos'],
        $_POST['direccion_contacto'],
        $_POST['correo_contacto'],
        $_POST['telefono_contacto'],
        $_POST['fax_contacto'],
        $_POST['mapa_contacto'],
        $_POST['boletin_activo']
    ));

    $success_message = 'Los ajustes de contenido general se han actualizado con éxito.';
}

//Ajustes de Secciones
if (isset($_POST['form4'])) {
    $statement = $pdo->prepare("UPDATE configuraciones SET 
        servicios_activos=?,
        bienvenida_activa=?,
        certificados_activos=?,
        total_certificados_recientes=?,
        total_certificados_populares=?
        WHERE id=1");
    $statement->execute(array(
        $_POST['servicios_activos'],
        $_POST['bienvenida_activa'],
        $_POST['certificados_activos'],
        $_POST['total_certificados_recientes'],
        $_POST['total_certificados_populares']
    ));

    $success_message = 'Los ajustes de secciones se han actualizado con éxito.';
}


?>

<div class="row">
    <div class="col-md-12">
        <?php if($error_message): ?>
        <div class="callout callout-danger">
            <p><?php echo $error_message; ?></p>
        </div>
        <?php endif; ?>

        <?php if($success_message): ?>
        <div class="callout callout-success">
            <p><?php echo $success_message; ?></p>
        </div>
        <?php endif; ?>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Ajustes del Sitio Web</h3>
            </div>
            <div class="box-body">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_1" data-toggle="tab">Logo</a></li>
                        <li><a href="#tab_2" data-toggle="tab">Favicon</a></li>
                        <li><a href="#tab_3" data-toggle="tab">Pie de Página & Contacto</a></li>
                        <li><a href="#tab_4" data-toggle="tab">Ajustes de Secciones</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_1">
                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Logo Existente</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <?php
                                            $statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
                                            $statement->execute();
                                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($result as $row) {
                                                echo '<img src="../assets/uploads/'.$row['logo'].'" style="width:200px;">';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Nuevo Logo</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <input type="file" name="photo_logo">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form1">Actualizar Logo</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="tab_2">
                            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Favicon Existente</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <?php
                                            $statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
                                            $statement->execute();
                                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($result as $row) {
                                                echo '<img src="../assets/uploads/'.$row['favicon'].'" style="width:40px;">';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Nuevo Favicon</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <input type="file" name="photo_favicon">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form2">Actualizar Favicon</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="tab_3">
                            <form class="form-horizontal" action="" method="post">
                                <div class="box-body">
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
                                    $statement->execute();
                                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($result as $row) {
                                    ?>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Descripción del Pie de Página</label>
                                        <div class="col-sm-6">
                                            <textarea class="form-control" name="pie_pagina_descripcion" style="height:140px;"><?php echo $row['pie_pagina_descripcion']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Derechos de Autor</label>
                                        <div class="col-sm-6">
                                            <textarea class="form-control" name="pie_pagina_derechos" style="height:140px;"><?php echo $row['pie_pagina_derechos']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Dirección de Contacto</label>
                                        <div class="col-sm-6">
                                            <textarea class="form-control" name="direccion_contacto" style="height:140px;"><?php echo $row['direccion_contacto']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Correo de Contacto</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="correo_contacto" value="<?php echo $row['correo_contacto']; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Teléfono de Contacto</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="telefono_contacto" value="<?php echo $row['telefono_contacto']; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Fax de Contacto</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="fax_contacto" value="<?php echo $row['fax_contacto']; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Mapa de Contacto</label>
                                        <div class="col-sm-6">
                                            <textarea class="form-control" name="mapa_contacto" style="height:140px;"><?php echo $row['mapa_contacto']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Boletín Activo</label>
                                        <div class="col-sm-6">
                                            <select name="boletin_activo" class="form-control">
                                                <option value="1" <?php if($row['boletin_activo'] == 1) {echo 'selected';} ?>>Sí</option>
                                                <option value="0" <?php if($row['boletin_activo'] == 0) {echo 'selected';} ?>>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form3">Actualizar Información</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="tab_4">
                            <form class="form-horizontal" action="" method="post">
                                <div class="box-body">
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
                                    $statement->execute();
                                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($result as $row) {
                                    ?>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Servicios Activos</label>
                                        <div class="col-sm-6">
                                            <select name="servicios_activos" class="form-control">
                                                <option value="1" <?php if($row['servicios_activos'] == 1) {echo 'selected';} ?>>Sí</option>
                                                <option value="0" <?php if($row['servicios_activos'] == 0) {echo 'selected';} ?>>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Bienvenida Activa</label>
                                        <div class="col-sm-6">
                                            <select name="bienvenida_activa" class="form-control">
                                                <option value="1" <?php if($row['bienvenida_activa'] == 1) {echo 'selected';} ?>>Sí</option>
                                                <option value="0" <?php if($row['bienvenida_activa'] == 0) {echo 'selected';} ?>>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Certificados Activos</label>
                                        <div class="col-sm-6">
                                            <select name="certificados_activos" class="form-control">
                                                <option value="1" <?php if($row['certificados_activos'] == 1) {echo 'selected';} ?>>Sí</option>
                                                <option value="0" <?php if($row['certificados_activos'] == 0) {echo 'selected';} ?>>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Total Certificados Recientes</label>
                                        <div class="col-sm-6">
                                            <input type="number" class="form-control" name="total_certificados_recientes" value="<?php echo $row['total_certificados_recientes']; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Total Certificados Populares</label>
                                        <div class="col-sm-6">
                                            <input type="number" class="form-control" name="total_certificados_populares" value="<?php echo $row['total_certificados_populares']; ?>">
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success pull-left" name="form4">Actualizar Secciones</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>