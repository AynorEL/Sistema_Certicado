<?php
ob_start();
require_once('header.php');
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Ver Módulos</h1>
    </div>
    <div class="content-header-right">
        <a href="modulo-add.php" class="btn btn-primary btn-sm">Agregar Nuevo</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible" id="success-alert">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-check"></i> ¡Éxito!</h4>
                    <?php
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Nombre del Módulo</th>
                                <th>Descripción</th>
                                <th>Curso</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $statement = $pdo->prepare("
                                SELECT m.*, c.nombre_curso 
                                FROM modulo m
                                LEFT JOIN curso c ON m.idcurso = c.idcurso
                                ORDER BY m.idmodulo DESC
                            ");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                $i++;
                            ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $row['nombre_modulo']; ?></td>
                                    <td><?php echo $row['descripcion']; ?></td>
                                    <td><?php echo $row['nombre_curso']; ?></td>
                                    <td>
                                        <a href="modulo-edit.php?id=<?php echo $row['idmodulo']; ?>" class="btn btn-primary btn-xs">Editar</a>
                                        <a href="#" class="btn btn-danger btn-xs" data-href="modulo-delete.php?id=<?php echo $row['idmodulo']; ?>" data-toggle="modal" data-target="#confirm-delete">Eliminar</a>
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

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Confirmar Eliminación</h4>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea eliminar este módulo?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <a class="btn btn-danger btn-ok">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                }
            });
        }

        $('#confirm-delete').on('show.bs.modal', function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
        });
    });
</script>

<?php require_once('footer.php'); ?> 