<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';
if (isset($_POST['form1'])) {

    $valid = 1;

    if (empty($_POST['nombre_pagina'])) {
        $valid = 0;
        $error_message .= 'El nombre de la página no puede estar vacío<br>';
    }

    if (empty($_POST['contenido_pagina'])) {
        $valid = 0;
        $error_message .= 'El contenido de la página no puede estar vacío<br>';
    }

    $path = $_FILES['banner_pagina']['name'];
    $path_tmp = $_FILES['banner_pagina']['tmp_name'];

    if ($path != '') {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $file_name = basename($path, '.' . $ext);
        if ($ext != 'jpg' && $ext != 'png' && $ext != 'jpeg' && $ext != 'gif') {
            $valid = 0;
            $error_message .= 'Debe subir un archivo jpg, jpeg, gif o png<br>';
        }
    }

    if ($valid == 1) {

        if ($path != '') {
            // eliminando la foto existente
            $statement = $pdo->prepare("SELECT * FROM paginas WHERE id=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                $banner_pagina = $row['banner_pagina'];
                unlink('../assets/uploads/' . $banner_pagina);
            }

            // actualizando los datos
            $final_name = 'banner' . '.' . $ext;
            move_uploaded_file($path_tmp, '../assets/uploads/' . $final_name);

            // actualizando la base de datos
            $statement = $pdo->prepare("UPDATE paginas SET nombre_pagina=?, contenido_pagina=?, banner_pagina=?, meta_titulo=?, meta_palabras_clave=?, meta_descripcion=? WHERE id=1");
            $statement->execute(array($_POST['nombre_pagina'], $_POST['contenido_pagina'], $final_name, $_POST['meta_titulo'], $_POST['meta_palabras_clave'], $_POST['meta_descripcion']));
        } else {
            // actualizando la base de datos
            $statement = $pdo->prepare("UPDATE paginas SET nombre_pagina=?, contenido_pagina=?, meta_titulo=?, meta_palabras_clave=?, meta_descripcion=? WHERE id=1");
            $statement->execute(array($_POST['nombre_pagina'], $_POST['contenido_pagina'], $_POST['meta_titulo'], $_POST['meta_palabras_clave'], $_POST['meta_descripcion']));
        }

        $_SESSION['success'] = 'La página se ha actualizado correctamente.';
        header('location: page.php');
        exit();
    }
}

$statement = $pdo->prepare("SELECT * FROM paginas WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $nombre_pagina = $row['nombre_pagina'];
    $contenido_pagina = $row['contenido_pagina'];
    $banner_pagina = $row['banner_pagina'];
    $meta_titulo = $row['meta_titulo'];
    $meta_palabras_clave = $row['meta_palabras_clave'];
    $meta_descripcion = $row['meta_descripcion'];
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Editar Página</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <?php if ($error_message): ?>
                                <div class="alert alert-danger">
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success_message): ?>
                                <div class="alert alert-success">
                                    <?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="nombre_pagina">Nombre de la Página</label>
                                    <input type="text" name="nombre_pagina" class="form-control" value="<?php echo $nombre_pagina; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="contenido_pagina">Contenido</label>
                                    <textarea name="contenido_pagina" class="form-control editor" rows="10"><?php echo $contenido_pagina; ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="banner_pagina">Banner</label>
                                    <input type="file" name="banner_pagina" class="form-control">
                                    <?php if ($banner_pagina != ''): ?>
                                        <img src="../assets/uploads/<?php echo $banner_pagina; ?>" alt="Banner" class="mt-2" style="max-width: 200px;">
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="meta_titulo">Meta Título</label>
                                    <input type="text" name="meta_titulo" class="form-control" value="<?php echo $meta_titulo; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="meta_palabras_clave">Meta Palabras Clave</label>
                                    <input type="text" name="meta_palabras_clave" class="form-control" value="<?php echo $meta_palabras_clave; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="meta_descripcion">Meta Descripción</label>
                                    <textarea name="meta_descripcion" class="form-control" rows="3"><?php echo $meta_descripcion; ?></textarea>
                                </div>

                                <button type="submit" name="form1" class="btn btn-primary">Actualizar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once('footer.php'); ?>