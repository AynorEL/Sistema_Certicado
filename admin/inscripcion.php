<?php require_once('header.php'); ?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Gestión de Inscripciones</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header">
                    <h3 class="box-title">Lista de Inscripciones</h3>
                </div>
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Curso</th>
                                <th>Fecha Inscripción</th>
                                <th>Estado</th>
                                <th>Nota Final</th>
                                <th>Fecha Aprobación</th>
                                <th>Monto</th>
                                <th>Estado Pago</th>
                                <th>Método Pago</th>
                                <th>Comprobante</th>
                                <th>Observaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i=0;
                            $statement = $pdo->prepare("SELECT i.*, c.nombre, c.apellido, c.email, cur.nombre_curso 
                                                      FROM inscripcion i 
                                                      JOIN cliente c ON i.idcliente = c.idcliente
                                                      JOIN curso cur ON i.idcurso = cur.idcurso
                                                      ORDER BY i.idinscripcion DESC");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
                            foreach ($result as $row) {
                                $i++;
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td>
                                        <?php echo $row['nombre'].' '.$row['apellido']; ?><br>
                                        <small><?php echo $row['email']; ?></small>
                                    </td>
                                    <td><?php echo $row['nombre_curso']; ?></td>
                                    <td><?php echo $row['fecha_inscripcion']; ?></td>
                                    <td>
                                        <?php if($row['estado'] == 'Pendiente'): ?>
                                            <span class="badge badge-warning">Pendiente</span>
                                        <?php elseif($row['estado'] == 'Aprobado'): ?>
                                            <span class="badge badge-success">Aprobado</span>
                                        <?php elseif($row['estado'] == 'Rechazado'): ?>
                                            <span class="badge badge-danger">Rechazado</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Cancelado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row['nota_final'] ? $row['nota_final'] : '-'; ?></td>
                                    <td><?php echo $row['fecha_aprobacion'] ? $row['fecha_aprobacion'] : '-'; ?></td>
                                    <td>S/ <?php echo number_format($row['monto_pago'], 2); ?></td>
                                    <td>
                                        <?php if($row['estado_pago'] == 'Pendiente'): ?>
                                            <span class="badge badge-warning">Pendiente</span>
                                        <?php elseif($row['estado_pago'] == 'Pagado'): ?>
                                            <span class="badge badge-success">Pagado</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Reembolsado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row['metodo_pago'] ? $row['metodo_pago'] : '-'; ?></td>
                                    <td>
                                        <?php if($row['comprobante_pago']): ?>
                                            <a href="../uploads/<?php echo $row['comprobante_pago']; ?>" target="_blank" class="btn btn-info btn-xs">Ver</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['observaciones']): ?>
                                            <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#observaciones<?php echo $i; ?>">
                                                Ver
                                            </button>
                                            <div class="modal fade" id="observaciones<?php echo $i; ?>" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                            <h4 class="modal-title">Observaciones</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php echo nl2br($row['observaciones']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="inscripcion-edit.php?id=<?php echo $row['idinscripcion']; ?>" 
                                           class="btn btn-primary btn-xs">Editar</a>
                                        <a href="#" class="btn btn-danger btn-xs" 
                                           data-href="inscripcion-delete.php?id=<?php echo $row['idinscripcion']; ?>" 
                                           data-toggle="modal" data-target="#confirm-delete">Eliminar</a>
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
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">Confirmación de Eliminación</h4>
        </div>
        <div class="modal-body">
            ¿Estás seguro de que deseas eliminar esta inscripción?
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <a class="btn btn-danger btn-ok">Eliminar</a>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?> 