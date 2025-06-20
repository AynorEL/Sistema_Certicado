<?php require_once('header.php'); ?>
<?php
$statement = $pdo->prepare("SELECT * FROM paginas WHERE id = 1");
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    echo '<div class="container"><div class="alert alert-danger">Página no encontrada.</div></div>';
    require_once('footer.php');
    exit;
}

$titulo_pagina = $result['nombre_pagina'];
$contenido_pagina = $result['contenido_pagina'];
$banner_pagina = $result['banner_pagina'];
$meta_titulo = $result['meta_titulo'];
$meta_palabras_clave = $result['meta_palabras_clave'];
$meta_descripcion = $result['meta_descripcion'];

// Banner dinámico: si no hay imagen, usar imagen por defecto
$banner_path = 'admin/img/' . $banner_pagina;
$banner_url = (!empty($banner_pagina) && file_exists(__DIR__ . '/' . $banner_path))
    ? $banner_path
    : 'assets/img/banner-default.jpg';
?>

<!-- Meta tags -->
<?php
echo "<title>$meta_titulo</title>";
echo '<meta name="keywords" content="' . htmlspecialchars($meta_palabras_clave) . '">';
echo '<meta name="description" content="' . htmlspecialchars($meta_descripcion) . '">';
?>

<!-- Estilos -->
<style>
    .page-banner {
        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo $banner_url; ?>') no-repeat center center;
        background-size: cover;
        padding: 100px 0;
        color: white;
        text-align: center;
        border-radius: 0.5rem;
        margin-bottom: 40px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }

    .page-banner h1 {
        font-weight: 700;
        font-size: 3rem;
        text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    }

    .page-content {
        font-size: 1.1rem;
        line-height: 1.7;
        color: #444;
        background: #fff;
        padding: 30px;
        border-radius: 0.5rem;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }

    .page-title {
        font-weight: 600;
        margin-bottom: 30px;
        color: #222;
        text-align: center;
    }

    @media (max-width: 768px) {
        .page-banner {
            padding: 60px 20px;
        }

        .page-banner h1 {
            font-size: 2rem;
        }
    }
</style>

<!-- Banner -->
<div class="page-banner">
    <div class="container">
        <h1><?php echo htmlspecialchars($titulo_pagina); ?></h1>
    </div>
</div>

<!-- Contenido -->
<div class="page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <h2 class="page-title"><?php echo htmlspecialchars($titulo_pagina); ?></h2>
                <div class="page-content">
                    <?php echo $contenido_pagina; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
