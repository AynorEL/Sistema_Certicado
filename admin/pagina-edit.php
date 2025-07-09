<?php
require_once('header.php');

// Validación del ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">ID no válido</div>';
    exit;
}

$id = (int)$_GET['id'];

// Procesar formulario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_pagina']);
    $slug = trim($_POST['slug_pagina']);
    $contenido = $_POST['contenido_pagina'];
    $meta_titulo = trim($_POST['meta_titulo']);
    $meta_palabras_clave = trim($_POST['meta_palabras_clave']);
    $meta_descripcion = trim($_POST['meta_descripcion']);

    // Obtener banner anterior
    $stmtOld = $pdo->prepare("SELECT banner_pagina FROM paginas WHERE id = ?");
    $stmtOld->execute([$id]);
    $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);
    $banner = $oldData['banner_pagina'];

    // Procesar nuevo banner
    if (!empty($_FILES['banner_pagina']['name'])) {
        $ext = pathinfo($_FILES['banner_pagina']['name'], PATHINFO_EXTENSION);
        $nuevo_banner = uniqid('pagina_') . '.' . $ext;
        move_uploaded_file($_FILES['banner_pagina']['tmp_name'], 'img/' . $nuevo_banner);
        if (!empty($banner) && file_exists("img/" . $banner)) {
            unlink('img/' . $banner);
        }
        $banner = $nuevo_banner;
    }

    // Actualizar base de datos
    $stmt = $pdo->prepare("UPDATE paginas SET nombre_pagina=?, slug_pagina=?, contenido_pagina=?, banner_pagina=?, meta_titulo=?, meta_palabras_clave=?, meta_descripcion=? WHERE id=?");
    $stmt->execute([$nombre, $slug, $contenido, $banner, $meta_titulo, $meta_palabras_clave, $meta_descripcion, $id]);

    $_SESSION['success'] = 'La página fue actualizada correctamente.';
    header("Location: pagina.php");
    exit;
}

// Cargar datos actualizados desde la base de datos
$stmt = $pdo->prepare("SELECT * FROM paginas WHERE id = ?");
$stmt->execute([$id]);
$pagina = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pagina) {
    echo '<div class="alert alert-danger">Página no encontrada</div>';
    exit;
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Editar Página</h1>
    </div>
</section>

<section class="content">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
        <div class="box box-info">
            <div class="box-body">
                <!-- Nombre -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Nombre *</label>
                    <div class="col-sm-6">
                        <input type="text" name="nombre_pagina" class="form-control" required value="<?php echo htmlspecialchars($pagina['nombre_pagina']); ?>">
                    </div>
                </div>

                <!-- Slug -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Slug *</label>
                    <div class="col-sm-6">
                        <input type="text" name="slug_pagina" class="form-control" required value="<?php echo htmlspecialchars($pagina['slug_pagina']); ?>">
                    </div>
                </div>

                <!-- Contenido -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Contenido *</label>
                    <div class="col-sm-9">
                        <textarea name="contenido_pagina" id="editor1" class="form-control" rows="10"><?php echo htmlentities($pagina['contenido_pagina']); ?></textarea>
                    </div>
                </div>

                <!-- Banner actual -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Banner Actual</label>
                    <div class="col-sm-6">
                        <?php if (!empty($pagina['banner_pagina'])): ?>
                            <img src="img/<?php echo $pagina['banner_pagina']; ?>" style="width:120px;">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Nuevo banner -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Nuevo Banner</label>
                    <div class="col-sm-6">
                        <input type="file" name="banner_pagina" class="form-control">
                    </div>
                </div>

                <!-- Meta Título -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Meta Título</label>
                    <div class="col-sm-8">
                        <input type="text" name="meta_titulo" class="form-control" value="<?php echo htmlspecialchars($pagina['meta_titulo']); ?>">
                    </div>
                </div>

                <!-- Meta Palabras Clave -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Meta Palabras Clave</label>
                    <div class="col-sm-8">
                        <textarea name="meta_palabras_clave" class="form-control" rows="3"><?php echo htmlspecialchars($pagina['meta_palabras_clave']); ?></textarea>
                    </div>
                </div>

                <!-- Meta Descripción -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">Meta Descripción</label>
                    <div class="col-sm-8">
                        <textarea name="meta_descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($pagina['meta_descripcion']); ?></textarea>
                    </div>
                </div>

                <!-- Botón Actualizar -->
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-6">
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>


<?php require_once('footer.php'); ?>
