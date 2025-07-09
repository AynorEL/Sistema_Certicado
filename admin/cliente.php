<?php
ob_start();
require_once('header.php');
$timeout = 3;
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Ver Clientes</h1>
	</div>
	<div class="content-header-right">
		<a href="cliente-add.php" class="btn btn-primary btn-sm">Agregar Cliente</a>
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
								<th>Nombre</th>
								<th>Apellido</th>
								<th>DNI</th>
								<th>Teléfono</th>
								<th>Email</th>
								<th>Dirección</th>
								<th width="80">Acción</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							$statement = $pdo->prepare("SELECT * FROM cliente ORDER BY idcliente DESC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								$i++;
							?>
								<tr>
									<td><?php echo $i; ?></td>
									<td><?php echo $row['nombre']; ?></td>
									<td><?php echo $row['apellido']; ?></td>
									<td><?php echo $row['dni']; ?></td>
									<td><?php echo $row['telefono']; ?></td>
									<td><?php echo $row['email']; ?></td>
									<td><?php echo $row['direccion']; ?></td>
									<td>
										<a href="cliente-edit.php?id=<?php echo $row['idcliente']; ?>"
											class="btn btn-primary btn-xs" style="width:60px;margin-bottom:4px;">
											<i class="fa fa-pencil"></i> Editar
										</a>
										<a href="#" class="btn btn-danger btn-xs" style="width:60px;"
											data-href="cliente-delete.php?id=<?php echo $row['idcliente']; ?>"
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
				<p>¿Estás seguro de que deseas eliminar este cliente?</p>
				<p style="color:red;">
					¡Advertencia! Esta acción no se puede deshacer.
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
$(document).ready(function() {
    // Inicializar el modal de eliminación
    $('#confirm-delete').on('show.bs.modal', function(e) {
        $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
    });

    // Destruir la tabla si ya está inicializada
    if ($.fn.DataTable.isDataTable('#example1')) {
        $('#example1').DataTable().destroy();
    }

    // Inicializar DataTable
    $('#example1').DataTable({
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "columnDefs": [
            { "orderable": false, "targets": [7] } // Deshabilitar ordenamiento en columna de acciones
        ]
    });
});
</script>

<?php require_once('footer.php'); ?> 