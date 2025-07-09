<?php
session_start();
require_once('inc/config.php');

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $paypal_email = trim($_POST['paypal_email']);
        $paypal_sandbox = isset($_POST['paypal_sandbox']) ? 1 : 0;
        $banco_nombre = trim($_POST['banco_nombre']);
        $banco_cuenta = trim($_POST['banco_cuenta']);
        $banco_titular = trim($_POST['banco_titular']);
        $yape_numero = trim($_POST['yape_numero']);
        $plin_numero = trim($_POST['plin_numero']);

        // Validar número de Yape
        if (!empty($yape_numero) && (!preg_match('/^9\d{8}$/', $yape_numero))) {
            throw new Exception('El número de Yape debe tener 9 dígitos y comenzar con 9');
        }

        // Validar número de Plin
        if (!empty($plin_numero) && (!preg_match('/^9\d{8}$/', $plin_numero))) {
            throw new Exception('El número de Plin debe tener 9 dígitos y comenzar con 9');
        }

        // Verificar si ya existe una configuración
        $stmt = $pdo->prepare("SELECT id FROM configuracion_pago WHERE id = 1");
        $stmt->execute();
        $exists = $stmt->fetch();

        if ($exists) {
            // Actualizar configuración existente
            $stmt = $pdo->prepare("UPDATE configuracion_pago SET 
                                  paypal_email = ?, paypal_sandbox = ?, banco_nombre = ?, 
                                  banco_cuenta = ?, banco_titular = ?, yape_numero = ?, plin_numero = ? 
                                  WHERE id = 1");
        } else {
            // Insertar nueva configuración
            $stmt = $pdo->prepare("INSERT INTO configuracion_pago 
                                  (paypal_email, paypal_sandbox, banco_nombre, banco_cuenta, banco_titular, yape_numero, plin_numero) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
        }

        $stmt->execute([$paypal_email, $paypal_sandbox, $banco_nombre, $banco_cuenta, $banco_titular, $yape_numero, $plin_numero]);
        
        $_SESSION['success'] = 'Configuración de pagos actualizada correctamente';
        header('Location: configuracion-pago.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
}

// Obtener configuración actual
$stmt = $pdo->prepare("SELECT * FROM configuracion_pago WHERE id = 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no hay configuración, crear valores por defecto
if (!$config) {
    $config = [
        'paypal_email' => '',
        'paypal_sandbox' => 1,
        'banco_nombre' => '',
        'banco_cuenta' => '',
        'banco_titular' => '',
        'yape_numero' => '',
        'plin_numero' => ''
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Pagos - Panel de Administración</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/AdminLTE.min.css">
    <link rel="stylesheet" href="css/_all-skins.min.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php include('header.php'); ?>
        <?php include('footer.php'); ?>

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Configuración de Pagos
                    <small>Gestionar métodos de pago</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="index.php"><i class="fa fa-dashboard"></i> Inicio</a></li>
                    <li class="active">Configuración de Pagos</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Configurar Métodos de Pago</h3>
                            </div>
                            
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" class="form-horizontal">
                                <div class="box-body">
                                    
                                    <!-- PayPal -->
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">PayPal</label>
                                        <div class="col-sm-9">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="paypal_sandbox" value="1" <?php echo $config['paypal_sandbox'] ? 'checked' : ''; ?>>
                                                    Usar modo Sandbox (pruebas)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Email de PayPal</label>
                                        <div class="col-sm-9">
                                            <input type="email" class="form-control" name="paypal_email" 
                                                   value="<?php echo htmlspecialchars($config['paypal_email']); ?>" 
                                                   placeholder="tu-email@paypal.com">
                                        </div>
                                    </div>

                                    <hr>

                                    <!-- Transferencia Bancaria -->
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Banco</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="banco_nombre" 
                                                   value="<?php echo htmlspecialchars($config['banco_nombre']); ?>" 
                                                   placeholder="Nombre del banco">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Número de Cuenta</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="banco_cuenta" 
                                                   value="<?php echo htmlspecialchars($config['banco_cuenta']); ?>" 
                                                   placeholder="Número de cuenta bancaria">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Titular de la Cuenta</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="banco_titular" 
                                                   value="<?php echo htmlspecialchars($config['banco_titular']); ?>" 
                                                   placeholder="Nombre del titular">
                                        </div>
                                    </div>

                                    <hr>

                                    <!-- Yape -->
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Número de Yape</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="yape_numero" 
                                                   value="<?php echo htmlspecialchars($config['yape_numero']); ?>" 
                                                   placeholder="9XXXXXXXX" maxlength="9">
                                            <span class="help-block">Debe tener 9 dígitos y comenzar con 9</span>
                                        </div>
                                    </div>

                                    <!-- Plin -->
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Número de Plin</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="plin_numero" 
                                                   value="<?php echo htmlspecialchars($config['plin_numero']); ?>" 
                                                   placeholder="9XXXXXXXX" maxlength="9">
                                            <span class="help-block">Debe tener 9 dígitos y comenzar con 9</span>
                                        </div>
                                    </div>

                                </div>

                                <div class="box-footer">
                                    <div class="col-sm-offset-3 col-sm-9">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Guardar Configuración
                                        </button>
                                        <a href="index.php" class="btn btn-default">
                                            <i class="fa fa-arrow-left"></i> Volver
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="js/jquery-2.2.3.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/app.min.js"></script>
</body>
</html> 