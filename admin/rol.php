<?php
ob_start();
require_once('header.php');
$timeout = 3;
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Ver Roles</h1>
	</div>
	<div class="content-header-right">
		<a href="rol-add.php" class="btn btn-primary btn-sm">Agregar Rol</a>
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
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th width="30">SL</th>
								<th>Nombre del Rol</th>
								<th>Descripción</th>
								<th width="80">Acción</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							$statement = $pdo->prepare("SELECT * FROM rol ORDER BY idrol DESC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								$i++;
							?>
								<tr>
									<td><?php echo $i; ?></td>
									<td><?php echo $row['nombre_rol']; ?></td>
									<td><?php echo $row['descripcion']; ?></td>
									<td>
										<a href="rol-edit.php?idrol=<?php echo $row['idrol']; ?>"
											class="btn btn-primary btn-xs" style="width:60px;margin-bottom:4px;">
											<i class="fa fa-pencil"></i> Editar
										</a>
										<a href="#" class="btn btn-danger btn-xs" style="width:60px;"
											data-href="rol-delete.php?idrol=<?php echo $row['idrol']; ?>"
											data-toggle="modal" data-target="#confirm-delete">
											<i class="fa fa-trash"></i> Eliminar
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
				<h4 class="modal-title" id="myModalLabel">Confirmación de Eliminación</h4>
			</div>
			<div class="modal-body">
				<p>¿Estás seguro de que deseas eliminar este rol?</p>
				<p style="color:red;">
					¡Advertencia! Este rol será eliminado permanentemente.
					Esta acción no se puede deshacer.
				</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
				<a class="btn btn-danger btn-ok">Eliminar</a>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el modal de eliminación
    var deleteModal = document.getElementById('confirm-delete');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(e) {
            var button = e.relatedTarget;
            var href = button.getAttribute('data-href');
            var confirmButton = this.querySelector('.btn-ok');
            if (confirmButton) {
                confirmButton.setAttribute('href', href);
            }
        });
    }

    // Inicializar DataTable
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#example1').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "columns": [
                { "data": "0" }, // SL
                { "data": "1" }, // Nombre del Rol
                { "data": "2" }, // Descripción
                { "data": "3" }  // Acción
            ],
            "columnDefs": [
                { "orderable": false, "targets": [3] } // Deshabilitar ordenamiento en columna de acciones
            ]
        });
    }
});
</script>

<?php require_once('footer.php'); ?> 