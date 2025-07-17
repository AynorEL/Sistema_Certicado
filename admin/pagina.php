<?php require_once('header.php'); ?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Ver Páginas</h1>
    </div>
    <div class="content-header-right">
        <a href="pagina-add.php" class="btn btn-primary btn-sm">Agregar Nueva</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible" id="success-alert">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-check"></i> ¡Éxito!</h4>
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Slug</th>
                                <th>Banner</th>
                                <th>Meta Título</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $statement = $pdo->prepare("SELECT * FROM paginas ORDER BY id DESC");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row):
                                $i++;
                            ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= htmlspecialchars($row['nombre_pagina']) ?></td>
                                    <td><?= htmlspecialchars($row['slug_pagina']) ?></td>
                                    <td>
                                        <?php if (!empty($row['banner_pagina'])): ?>
                                            <img src="img/<?= htmlspecialchars($row['banner_pagina']) ?>" alt="Banner" style="width:100px;">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['meta_titulo']) ?></td>
                                    <td>
                                        <a href="pagina-edit.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-xs">Editar</a>
                                        <a href="#" class="btn btn-danger btn-xs" data-href="pagina-delete.php?id=<?= $row['id'] ?>" data-toggle="modal" data-target="#confirm-delete">Eliminar</a>
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

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Confirmar Eliminación</h4>
            </div>
            <div class="modal-body">
                ¿Estás seguro de eliminar esta página?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <a class="btn btn-danger btn-ok">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    $(document).ready(function () {
        if (!$.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable({
                "language": {
                    "url": "/certificado/admin/js/Spanish.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [5] }
                ]
            });
        }

        $('#confirm-delete').on('show.bs.modal', function(e) {
            var href = $(e.relatedTarget).data('href');
            console.log('Modal abierto. data-href encontrado:', href);
            var btn = $(this).find('.btn-ok');
            btn.attr('href', href);
            console.log('Botón .btn-ok actualizado con href:', btn.attr('href'));
        });
    });
</script>
<?php require_once('footer.php'); ?>
