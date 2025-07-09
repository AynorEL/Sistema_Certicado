<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Ver Preguntas Frecuentes</h1>
	</div>
	<div class="content-header-right">
		<a href="faq-add.php" class="btn btn-primary btn-sm">Agregar Pregunta Frecuente</a>
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
								<th width="30">N°</th>
								<th>Título</th>
								<th>Contenido</th>
								<th width="80">Acción</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							$statement = $pdo->prepare("SELECT * FROM preguntas_frecuentes ORDER BY orden_pregunta ASC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								$i++;
							?>
								<tr>
									<td><?php echo $i; ?></td>
									<td><?php echo htmlspecialchars($row['titulo_pregunta']); ?></td>
									<td><?php echo nl2br(htmlspecialchars($row['contenido_pregunta'])); ?></td>
									<td>
										<a href="faq-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-xs">Editar</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="faq-delete.php?id=<?php echo $row['id']; ?>" data-toggle="modal" data-target="#confirm-delete">Eliminar</a>
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
				<p>¿Está seguro de que desea eliminar esta pregunta frecuente?</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
				<a class="btn btn-danger btn-ok">Eliminar</a>
			</div>
		</div>
	</div>
</div>

<?php require_once('footer.php'); ?>
