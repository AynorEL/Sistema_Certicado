<?php
ob_start();
require_once('header.php');
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Ver Especialistas</h1>
	</div>
	<div class="content-header-right">
		<a href="especialista-add.php" class="btn btn-primary btn-sm">Agregar Nuevo</a>
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
								<th>Nombre</th>
								<th>Apellido</th>
								<th>Especialidad</th>
								<th>Experiencia</th>
								<th>Email</th>
								<th>Teléfono</th>
								<th>Firma Especialista</th>
								<th>Acción</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							$statement = $pdo->prepare("SELECT * FROM especialista ORDER BY idespecialista DESC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								$i++;
							?>
								<tr>
									<td><?php echo $i; ?></td>
									<td><?php echo $row['nombre']; ?></td>
									<td><?php echo $row['apellido']; ?></td>
									<td><?php echo $row['especialidad']; ?></td>
									<td><?php echo $row['experiencia']; ?> años</td>
									<td><?php echo $row['email']; ?></td>
									<td><?php echo $row['telefono']; ?></td>
									<td>
										<?php if (!empty($row['firma_especialista'])): ?>
											<img src="<?php echo BASE_URL . 'assets/uploads/firmas/' . $row['firma_especialista']; ?>" alt="Firma Especialista" style="max-width: 100px; max-height: 50px; border: 1px solid #ddd;">
											<br><small class="text-muted"><?php echo $row['firma_especialista']; ?></small>
										<?php else: ?>
											<span class="text-muted">Sin firma</span>
										<?php endif; ?>
									</td>
									<td>
										<a href="especialista-edit.php?id=<?php echo $row['idespecialista']; ?>" class="btn btn-primary btn-xs">Editar</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="especialista-delete.php?id=<?php echo $row['idespecialista']; ?>" data-toggle="modal" data-target="#confirm-delete">Eliminar</a>
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
				<h4 class="modal-title" id="myModalLabel">Eliminar Confirmación</h4>
			</div>
			<div class="modal-body">
				¿Estás seguro de que quieres eliminar este especialista?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
				<a class="btn btn-danger btn-ok">Eliminar</a>
			</div>
		</div>
	</div>
</div>

<?php require_once('footer.php'); ?>
<script>
	$(document).ready(function() {
		if ($.fn.DataTable.isDataTable('#example1')) {
			$('#example1').DataTable().destroy();
		}
		$('#example1').DataTable({
			"language": {
				"url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
			},
			"columnDefs": [{
				"targets": [8],
				"orderable": false
			}]
		});
	});
</script> 