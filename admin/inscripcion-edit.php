<?php
require_once('header.php');

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM inscripcion WHERE idinscripcion=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total==0) {
        header('location: logout.php');
        exit;
    }
}

if(isset($_POST['form1'])) {
    $valid = 1;

    if(empty($_POST['estado'])) {
        $valid = 0;
        $error_message .= "El estado es requerido<br>";
    }

    if(empty($_POST['estado_pago'])) {
        $valid = 0;
        $error_message .= "El estado del pago es requerido<br>";
    }

    if($valid == 1) {
        $statement = $pdo->prepare("UPDATE inscripcion SET 
                                    estado=?,
                                    estado_pago=?,
                                    observaciones=?
                                    WHERE idinscripcion=?");
        $statement->execute(array(
                            $_POST['estado'],
                            $_POST['estado_pago'],
                            $_POST['observaciones'],
                            $_REQUEST['id']
                        ));

        // Sincronizar pagos asociados
        $nuevo_estado_pago = $_POST['estado_pago'];
        $estado_pago_pago = '';
        switch ($nuevo_estado_pago) {
            case 'Pagado':
                $estado_pago_pago = 'Completado';
                break;
            case 'Pendiente':
                $estado_pago_pago = 'Pendiente';
                break;
            case 'Reembolsado':
                $estado_pago_pago = 'Reembolsado';
                break;
            case 'Cancelado':
                $estado_pago_pago = 'Cancelado';
                break;
            default:
                $estado_pago_pago = 'Pendiente';
                break;
        }
        $statement = $pdo->prepare("UPDATE pago SET estado = ? WHERE idinscripcion = ?");
        $statement->execute(array($estado_pago_pago, $_REQUEST['id']));

        $_SESSION['success'] = 'La inscripción ha sido actualizada exitosamente.';
        header('location: inscripcion.php');
        exit();
    }
}

$statement = $pdo->prepare("SELECT i.*, c.nombre, c.apellido, c.email, c.telefono, c.direccion,
                           cur.nombre_curso, cur.precio, cur.fecha_inicio, cur.fecha_fin
                           FROM inscripcion i 
                           JOIN cliente c ON i.idcliente = c.idcliente
                           JOIN curso cur ON i.idcurso = cur.idcurso
                           WHERE i.idinscripcion=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $estado = $row['estado'];
    $estado_pago = $row['estado_pago'];
    $observaciones = $row['observaciones'];
    $nombre_cliente = $row['nombre'].' '.$row['apellido'];
    $email_cliente = $row['email'];
    $telefono_cliente = $row['telefono'];
    $direccion_cliente = $row['direccion'];
    $nombre_curso = $row['nombre_curso'];
    $precio_curso = $row['precio'];
    $fecha_inicio = $row['fecha_inicio'];
    $fecha_fin = $row['fecha_fin'];
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Editar Inscripción</h1>
    </div>
</section>

<section class="content">
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

            <form class="form-horizontal" action="" method="post">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Cliente</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" value="<?php echo $nombre_cliente; ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Email</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" value="<?php echo $email_cliente; ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Teléfono</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" value="<?php echo $telefono_cliente; ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Dirección</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" value="<?php echo $direccion_cliente; ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Curso</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" value="<?php echo $nombre_curso; ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Precio</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" value="S/ <?php echo number_format($precio_curso, 2); ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Fecha Inicio</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" value="<?php echo $fecha_inicio; ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Fecha Fin</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" value="<?php echo $fecha_fin; ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Estado</label>
                            <div class="col-sm-4">
                                <select name="estado" class="form-control">
                                    <option value="Pendiente" <?php if($estado=='Pendiente'){echo 'selected';} ?>>Pendiente</option>
                                    <option value="Aprobado" <?php if($estado=='Aprobado'){echo 'selected';} ?>>Aprobado</option>
                                    <option value="Rechazado" <?php if($estado=='Rechazado'){echo 'selected';} ?>>Rechazado</option>
                                    <option value="Cancelado" <?php if($estado=='Cancelado'){echo 'selected';} ?>>Cancelado</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Estado Pago</label>
                            <div class="col-sm-4">
                                <select name="estado_pago" class="form-control" disabled>
                                    <option value="Pendiente" <?php if($estado_pago=='Pendiente'){echo 'selected';} ?>>Pendiente</option>
                                    <option value="Pagado" <?php if($estado_pago=='Pagado'){echo 'selected';} ?>>Pagado</option>
                                    <option value="Reembolsado" <?php if($estado_pago=='Reembolsado'){echo 'selected';} ?>>Reembolsado</option>
                                    <option value="Cancelado" <?php if($estado_pago=='Cancelado'){echo 'selected';} ?>>Cancelado</option>
                                </select>
                                <input type="hidden" name="estado_pago" value="<?php echo htmlspecialchars($estado_pago); ?>">
                                <div class="alert alert-info" style="margin-top:8px;">
                                    <strong>Nota:</strong> El estado de pago se actualiza automáticamente según los pagos individuales. No se puede editar manualmente.
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Observaciones</label>
                            <div class="col-sm-6">
                                <textarea name="observaciones" class="form-control" rows="5"><?php echo $observaciones; ?></textarea>
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

<?php require_once('footer.php'); ?> 
