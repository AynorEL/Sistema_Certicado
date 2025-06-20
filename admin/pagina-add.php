<?php
ob_start();
require_once('header.php');
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Agregar Página</h1>
    </div>
    <div class="content-header-right">
        <a href="pagina.php" class="btn btn-primary btn-sm">Ver Todos</a>
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

            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Nombre <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="nombre_pagina" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Slug <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="slug_pagina" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Contenido <span>*</span></label>
                            <div class="col-sm-8">
                                <textarea class="form-control" name="contenido_pagina" id="editor1" rows="10" cols="80" required></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Banner</label>
                            <div class="col-sm-4">
                                <input type="file" name="banner_pagina" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Meta Título <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="meta_titulo" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Meta Palabras Clave</label>
                            <div class="col-sm-4">
                                <textarea class="form-control" name="meta_palabras_clave" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Meta Descripción</label>
                            <div class="col-sm-4">
                                <textarea class="form-control" name="meta_descripcion" rows="3"></textarea>
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

    if(empty($_POST['nombre_pagina'])) {
        $valid = 0;
        $_SESSION['error'] = "El nombre es requerido";
    }

    if(empty($_POST['slug_pagina'])) {
        $valid = 0;
        $_SESSION['error'] = "El slug es requerido";
    }

    if(empty($_POST['contenido_pagina'])) {
        $valid = 0;
        $_SESSION['error'] = "El contenido es requerido";
    }

    if(empty($_POST['meta_titulo'])) {
        $valid = 0;
        $_SESSION['error'] = "El meta título es requerido";
    }

    if($valid == 1) {
        $banner_pagina = '';
        if($_FILES['banner_pagina']['name'] != '') {
            $banner_pagina = time() . '_' . $_FILES['banner_pagina']['name'];
            move_uploaded_file($_FILES['banner_pagina']['tmp_name'], 'img/' . $banner_pagina);
        }

        $statement = $pdo->prepare("INSERT INTO paginas (nombre_pagina, slug_pagina, contenido_pagina, banner_pagina, meta_titulo, meta_palabras_clave, meta_descripcion) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $statement->execute(array(
            $_POST['nombre_pagina'],
            $_POST['slug_pagina'],
            $_POST['contenido_pagina'],
            $banner_pagina,
            $_POST['meta_titulo'],
            $_POST['meta_palabras_clave'],
            $_POST['meta_descripcion']
        ));
        
        $_SESSION['success'] = "Página agregada exitosamente";
        header('location: pagina.php');
        exit();
    }
}
?>

<?php require_once('footer.php'); ?> 