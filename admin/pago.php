<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if(isset($_POST['form1'])) {
    $valid = 1;
    if(empty($_POST['subject_text'])) {
        $valid = 0;
        $error_message .= 'El asunto no puede estar vacío\n';
    }
    if(empty($_POST['message_text'])) {
        $valid = 0;
        $error_message .= 'El mensaje no puede estar vacío\n';
    }
    if($valid == 1) {
        $subject_text = strip_tags($_POST['subject_text']);
        $message_text = strip_tags($_POST['message_text']);

        // Obteniendo la dirección de correo electrónico del cliente
        $statement = $pdo->prepare("SELECT * FROM cliente WHERE idcliente=?");
        $statement->execute(array($_POST['idcliente']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $cliente_email = $row['email'];
        }

        // Obteniendo la dirección de correo electrónico del administrador
        $statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $admin_email = $row['email_contacto'];
        }

        $pago_detail = '';
        $statement = $pdo->prepare("SELECT p.*, c.nombre, c.apellido, c.email, cur.nombre_curso 
                                  FROM pago p 
                                  JOIN inscripcion i ON p.idinscripcion = i.idinscripcion
                                  JOIN cliente c ON i.idcliente = c.idcliente
                                  JOIN curso cur ON i.idcurso = cur.idcurso
                                  WHERE p.idpago=?");
        $statement->execute(array($_POST['idpago']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $pago_detail .= '
Nombre del Cliente: '.$row['nombre'].' '.$row['apellido'].'<br>
Correo Electrónico: '.$row['email'].'<br>
Curso: '.$row['nombre_curso'].'<br>
Método de Pago: '.$row['metodo_pago'].'<br>
Fecha de Pago: '.$row['fecha_pago'].'<br>
Monto: S/ '.$row['monto'].'<br>
Estado: '.$row['estado'].'<br>
ID de Transacción: '.$row['txn_id'].'<br>
            ';
        }

        // Enviando correo electrónico
        $to_customer = $cliente_email;
        $message = '
<html><body>
<h3>Mensaje: </h3>
'.$message_text.'
<h3>Detalles del Pago: </h3>
'.$pago_detail.'
</body></html>
';
        $headers = 'From: ' . $admin_email . "\r\n" .
                   'Reply-To: ' . $admin_email . "\r\n" .
                   'X-Mailer: PHP/' . phpversion() . "\r\n" . 
                   "MIME-Version: 1.0\r\n" . 
                   "Content-Type: text/html; charset=UTF-8\r\n";

        mail($to_customer, $subject_text, $message, $headers);
        
        $success_message = 'El correo electrónico se envió con éxito.';
    }
}
?>

<?php
if($error_message != '') {
    echo "<script>alert('".$error_message."')</script>";
}
if($success_message != '') {
    echo "<script>alert('".$success_message."')</script>";
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Gestión de Pagos</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Estudiante</th>
                                <th>Curso</th>
                                <th>Monto</th>
                                <th>Fecha</th>
                                <th>Método</th>
                                <th>Estado</th>
                                <th>Comprobante</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $statement = $pdo->prepare("
                                SELECT p.*, 
                                       i.idinscripcion,
                                       c.nombre as nombre_cliente,
                                       c.apellido as apellido_cliente,
                                       cur.nombre_curso
                                FROM pago p
                                JOIN inscripcion i ON p.idinscripcion = i.idinscripcion
                                JOIN cliente c ON i.idcliente = c.idcliente
                                JOIN curso cur ON i.idcurso = cur.idcurso
                                ORDER BY p.fecha_pago DESC
                            ");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                ?>
                                <tr>
                                    <td><?php echo $row['idpago']; ?></td>
                                    <td><?php echo $row['nombre_cliente'] . ' ' . $row['apellido_cliente']; ?></td>
                                    <td><?php echo $row['nombre_curso']; ?></td>
                                    <td>S/ <?php echo number_format($row['monto'], 2); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_pago'])); ?></td>
                                    <td><?php echo $row['metodo_pago']; ?></td>
                                    <td>
                                        <?php if($row['estado'] == 'Completado'): ?>
                                            <span class="badge badge-success">Completado</span>
                                        <?php elseif($row['estado'] == 'Pendiente'): ?>
                                            <span class="badge badge-warning">Pendiente</span>
                                        <?php elseif($row['estado'] == 'Reembolsado'): ?>
                                            <span class="badge badge-info">Reembolsado</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Cancelado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['comprobante']): ?>
                                            <a href="../assets/uploads/comprobantes/<?php echo $row['comprobante']; ?>" class="btn btn-info btn-xs" target="_blank">
                                                <i class="fa fa-eye"></i> Ver
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No disponible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['estado'] == 'Pendiente'): ?>
                                            <a href="pago-cambiar-estado.php?id=<?php echo $row['idpago']; ?>&estado=Completado" class="btn btn-success btn-xs" title="Marcar como Completado">
                                                <i class="fa fa-check"></i>
                                            </a>
                                            <a href="pago-cambiar-estado.php?id=<?php echo $row['idpago']; ?>&estado=Cancelado" class="btn btn-danger btn-xs" title="Cancelar Pago">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        <?php elseif($row['estado'] == 'Completado'): ?>
                                            <a href="pago-cambiar-estado.php?id=<?php echo $row['idpago']; ?>&estado=Reembolsado" class="btn btn-info btn-xs" title="Marcar como Reembolsado">
                                                <i class="fa fa-undo"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="#" class="btn btn-danger btn-xs" data-href="pago-delete.php?id=<?php echo $row['idpago']; ?>" data-toggle="modal" data-target="#confirm-delete" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Confirmar Eliminación</h4>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea eliminar este pago?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <a class="btn btn-danger btn-ok">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?> 