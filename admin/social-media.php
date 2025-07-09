<?php
ob_start();
require_once('header.php');
$timeout = 3;

// Verificar si el usuario está logueado
if(!isset($_SESSION['user']['id_usuario'])) {
    header('Location: login.php');
    exit();
}

// Procesar formulario
if(isset($_POST['form1'])) {
    $valid = 1;

    // Actualizar cada red social
    $redes = [
        'Facebook' => ['url' => $_POST['facebook'], 'icon' => 'fab fa-facebook'],
        'Twitter' => ['url' => $_POST['twitter'], 'icon' => 'fab fa-twitter'],
        'LinkedIn' => ['url' => $_POST['linkedin'], 'icon' => 'fab fa-linkedin'],
        'YouTube' => ['url' => $_POST['youtube'], 'icon' => 'fab fa-youtube'],
        'Instagram' => ['url' => $_POST['instagram'], 'icon' => 'fab fa-instagram'],
        'WhatsApp' => ['url' => $_POST['whatsapp'], 'icon' => 'fab fa-whatsapp'],
        'Pinterest' => ['url' => $_POST['pinterest'], 'icon' => 'fab fa-pinterest'],
        'TikTok' => ['url' => $_POST['tiktok'], 'icon' => 'fab fa-tiktok']
    ];

    foreach($redes as $nombre => $datos) {
        // Verificar si existe la red social
        $statement = $pdo->prepare("SELECT * FROM redes_sociales WHERE nombre_red=?");
        $statement->execute(array($nombre));
        $total = $statement->rowCount();

        if($total == 0) {
            // Si no existe, insertar
            $statement = $pdo->prepare("INSERT INTO redes_sociales (nombre_red, url_red, icono_red) VALUES (?, ?, ?)");
            $statement->execute(array($nombre, $datos['url'], $datos['icon']));
        } else {
            // Si existe, actualizar
            $statement = $pdo->prepare("UPDATE redes_sociales SET url_red=?, icono_red=? WHERE nombre_red=?");
            $statement->execute(array($datos['url'], $datos['icon'], $nombre));
        }
    }

    $_SESSION['success'] = '¡Las URL de las redes sociales se han actualizado con éxito!';
}

// Obtener todas las redes sociales
$statement = $pdo->prepare("SELECT * FROM redes_sociales");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

// Inicializar variables
$facebook = '';
$twitter = '';
$linkedin = '';
$youtube = '';
$instagram = '';
$whatsapp = '';
$pinterest = '';
$tiktok = '';

// Asignar valores
foreach ($result as $row) {
    switch($row['nombre_red']) {
        case 'Facebook':
            $facebook = $row['url_red'];
            break;
        case 'Twitter':
            $twitter = $row['url_red'];
            break;
        case 'LinkedIn':
            $linkedin = $row['url_red'];
            break;
        case 'YouTube':
            $youtube = $row['url_red'];
            break;
        case 'Instagram':
            $instagram = $row['url_red'];
            break;
        case 'WhatsApp':
            $whatsapp = $row['url_red'];
            break;
        case 'Pinterest':
            $pinterest = $row['url_red'];
            break;
        case 'TikTok':
            $tiktok = $row['url_red'];
            break;
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible" id="success-alert">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-check"></i> ¡Éxito!</h4>
                <?php
                echo $_SESSION['success'];
                header("refresh:$timeout");
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible" id="error-alert">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-ban"></i> ¡Error!</h4>
                <?php
                echo $_SESSION['error'];
                header("refresh:$timeout");
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Redes Sociales</h3>
            </div>
            <div class="box-body">
                <form class="form-horizontal" action="" method="post">
                    <p style="padding-bottom: 20px;">Si no desea mostrar una red social en su página, simplemente deje el campo de entrada en blanco.</p>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Facebook</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="facebook" value="<?php echo $facebook; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Twitter</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="twitter" value="<?php echo $twitter; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">LinkedIn</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="linkedin" value="<?php echo $linkedin; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">YouTube</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="youtube" value="<?php echo $youtube; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Instagram</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="instagram" value="<?php echo $instagram; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">WhatsApp</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="whatsapp" value="<?php echo $whatsapp; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Pinterest</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="pinterest" value="<?php echo $pinterest; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">TikTok</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="tiktok" value="<?php echo $tiktok; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-4">
                            <button type="submit" class="btn btn-success" name="form1">Actualizar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cerrar alertas automáticamente
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if(alert) {
                alert.style.display = 'none';
            }
        }, 3000);
    });

    // Cerrar alertas al hacer clic en el botón de cerrar
    var closeButtons = document.querySelectorAll('.alert .close');
    closeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var alert = this.closest('.alert');
            if(alert) {
                alert.style.display = 'none';
            }
        });
    });
});
</script> 