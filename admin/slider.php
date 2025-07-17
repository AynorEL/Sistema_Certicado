<?php
ob_start();
require_once('header.php');
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Ver Sliders</h1>
	</div>
	<div class="content-header-right">
		<a href="slider-add.php" class="btn btn-primary btn-sm">Agregar Slider</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php if(isset($_SESSION['success'])): ?>
				<div class="alert alert-success alert-dismissible" id="success-alert">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<h4><i class="icon fa fa-check"></i> ¡Éxito!</h4>
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

			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>SL</th>
								<th>Foto</th>
								<th>Título</th>
								<th>Contenido</th>
								<th>Botón</th>
								<th>URL</th>
								<th>Posición</th>
								<th>Acción</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT * FROM sliders ORDER BY posicion ASC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td style="width:150px;">
										<?php if($row['foto']): ?>
											<img src="../assets/uploads/<?php echo $row['foto']; ?>" 
												 alt="<?php echo $row['titulo']; ?>" 
												 style="width:140px; height:80px; object-fit:cover; border-radius:5px;">
										<?php endif; ?>
									</td>
									<td><?php echo $row['titulo']; ?></td>
									<td><?php echo substr($row['contenido'], 0, 100) . '...'; ?></td>
									<td><?php echo $row['texto_boton']; ?></td>
									<td><?php echo $row['url_boton']; ?></td>
									<td><?php echo $row['posicion']; ?></td>
									<td>
										<a href="slider-edit.php?id=<?php echo $row['id']; ?>" 
										   class="btn btn-primary btn-xs">
										   <i class="fa fa-pencil"></i> Editar
										</a>
										<a href="#" 
										   class="btn btn-danger btn-xs" 
										   data-href="slider-delete.php?id=<?php echo $row['id']; ?>" 
										   data-toggle="modal" 
										   data-target="#confirm-delete">
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
				<p>¿Está seguro de que desea eliminar este elemento?</p>
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
					{ "orderable": false, "targets": [1, 7] }
				]
			});
		}
	});
</script>

<?php require_once('footer.php'); ?>