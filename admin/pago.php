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

        $statement = $pdo->prepare("SELECT * FROM cliente WHERE idcliente=?");
        $statement->execute(array($_POST['idcliente']));
        $cliente_email = $statement->fetchColumn(3);

        $statement = $pdo->prepare("SELECT email_contacto FROM configuraciones WHERE id=1");
        $statement->execute();
        $admin_email = $statement->fetchColumn();

        $pago_detail = '';
        $statement = $pdo->prepare("SELECT p.*, c.nombre, c.apellido, c.email, cur.nombre_curso 
                                    FROM pago p 
                                    JOIN inscripcion i ON p.idinscripcion = i.idinscripcion
                                    JOIN cliente c ON i.idcliente = c.idcliente
                                    JOIN curso cur ON i.idcurso = cur.idcurso
                                    WHERE p.idpago=?");
        $statement->execute(array($_POST['idpago']));
        foreach ($statement as $row) {
            $pago_detail .= 'Nombre del Cliente: '.$row['nombre'].' '.$row['apellido'].'<br>
Correo Electrónico: '.$row['email'].'<br>
Curso: '.$row['nombre_curso'].'<br>
Método de Pago: '.$row['metodo_pago'].'<br>
Fecha de Pago: '.$row['fecha_pago'].'<br>
Monto: S/ '.$row['monto'].'<br>
Estado: '.$row['estado'].'<br>
ID de Transacción: '.$row['txn_id'].'<br>';
        }

        $to_customer = $cliente_email;
        $message = '<html><body><h3>Mensaje: </h3>'.$message_text.'<h3>Detalles del Pago: </h3>'.$pago_detail.'</body></html>';

        $headers = 'From: ' . $admin_email . "\r\n" .
                   'Reply-To: ' . $admin_email . "\r\n" .
                   'X-Mailer: PHP/' . phpversion() . "\r\n" . 
                   "MIME-Version: 1.0\r\n" . 
                   "Content-Type: text/html; charset=UTF-8\r\n";

        mail($to_customer, $subject_text, $message, $headers);
        $success_message = 'El correo electrónico se envió con éxito.';
    }
}

if($error_message != '') echo "<script>alert('".$error_message."')</script>";
if($success_message != '') echo "<script>alert('".$success_message."')</script>";
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Gestíon de Pagos</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header">
                    <h3 class="box-title">Lista de Pagos</h3>
                </div>
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
                                <th>Estado Inscripción</th>
                                <th>Comprobante</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT p.*, i.idinscripcion, i.estado_pago as estado_pago_inscripcion, c.nombre as nombre_cliente, c.apellido as apellido_cliente, cur.nombre_curso FROM pago p JOIN inscripcion i ON p.idinscripcion = i.idinscripcion JOIN cliente c ON i.idcliente = c.idcliente JOIN curso cur ON i.idcurso = cur.idcurso ORDER BY p.fecha_pago DESC");
                            $stmt->execute();
                            $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($pagos as $pago): ?>
                            <tr>
                                <td><?= $pago['idpago']; ?></td>
                                <td><?= $pago['nombre_cliente'].' '.$pago['apellido_cliente']; ?></td>
                                <td><?= $pago['nombre_curso']; ?></td>
                                <td>S/ <?= number_format($pago['monto'], 2); ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])); ?></td>
                                <td><?= $pago['metodo_pago']; ?></td>
                                <td>
                                    <select class="form-select estado-select" data-idpago="<?= $pago['idpago'] ?>">
                                        <option value="Pendiente" <?= $pago['estado'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="Completado" <?= $pago['estado'] == 'Completado' ? 'selected' : '' ?>>Completado</option>
                                        <option value="Reembolsado" <?= $pago['estado'] == 'Reembolsado' ? 'selected' : '' ?>>Reembolsado</option>
                                        <option value="Cancelado" <?= $pago['estado'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                    </select>
                                </td>
                                <td>
                                    <?php
                                    $estado = strtolower($pago['estado_pago_inscripcion']);
                                    $clase = match($estado) {
                                        'pagado' => 'success',
                                        'pendiente' => 'warning',
                                        'reembolsado' => 'info',
                                        'cancelado' => 'danger',
                                        default => 'secondary'
                                    };
                                    echo "<span class='badge badge-$clase'>".ucfirst($estado)."</span>";
                                    ?>
                                </td>
                                <td>
                                    <?php if($pago['comprobante']): ?>
                                        <a href="../assets/uploads/comprobantes/<?= $pago['comprobante']; ?>" class="btn btn-info btn-xs" target="_blank">Ver</a>
                                    <?php else: ?>
                                        <span class="text-muted">No disponible</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(in_array($pago['estado'], ['Pendiente', 'Cancelado'])): ?>
                                        <a href="#" class="btn btn-danger btn-xs" data-href="pago-delete.php?id=<?= $pago['idpago']; ?>" data-toggle="modal" data-target="#confirm-delete"><i class="fa fa-trash"></i></a>
                                    <?php else: ?>
                                        <button class="btn btn-danger btn-xs" disabled><i class="fa fa-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirmar Eliminación</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">¿Está seguro que desea eliminar este pago?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <a class="btn btn-danger btn-ok">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.estado-select').forEach(select => {
    select.addEventListener('change', function () {
        const idpago = this.dataset.idpago;
        const nuevo_estado = this.value;

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas cambiar el estado a "${nuevo_estado}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`pago-cambiar-estado.php?id=${idpago}&estado=${nuevo_estado}&ajax=1`, {
                    method: 'GET'
                })
                .then(res => res.text())
                .then(response => {
                    if (response.trim() === 'ok' || response.includes('pago.php')) {
                        // Mostrar un Toast de éxito
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });

                        Toast.fire({
                            icon: 'success',
                            title: 'Estado actualizado correctamente'
                        });

                        // Recargar la tabla luego de 3 segundos
                        setTimeout(() => location.reload(), 3000);
                    } else {
                        Swal.fire('Error', response, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                    console.error(error);
                });
            } else {
                // Si canceló, vuelve al estado original del select
                location.reload();
            }
        });
    });
});
</script>

<?php require_once('footer.php'); ?>