<?php
ob_start();
include('header.php');
?>
<section class="content-header">
    <h1>
        Ver Géneros
        <small>Lista de todos los géneros</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Lista de Géneros</h3>
                    <div class="box-tools">
                        <a href="genero-add.php" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Agregar Nuevo
                        </a>
                    </div>
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
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Nombre del Género</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i=0;
                            $statement = $pdo->prepare("SELECT * FROM genero ORDER BY idgenero DESC");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                $i++;
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $row['nombre_genero']; ?></td>
                                    <td>
                                        <a href="genero-edit.php?id=<?php echo $row['idgenero']; ?>" class="btn btn-primary btn-xs">Editar</a>
                                        <a href="#" class="btn btn-danger btn-xs" data-href="genero-delete.php?id=<?php echo $row['idgenero']; ?>" data-toggle="modal" data-target="#confirm-delete">Eliminar</a>
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
                <h4 class="modal-title" id="myModalLabel">Eliminar Género</h4>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar este género?
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
                },
                "columnDefs": [
                    { "orderable": false, "targets": 2 }
                ]
            });
        }
    });
</script>

<?php include('footer.php'); ?> 