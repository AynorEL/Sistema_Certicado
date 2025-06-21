<?php
ob_start();
require_once('header.php');
$timeout = 3;
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Lista de Cursos</h1>
	</div>
	<div class="content-header-right">
		<a href="curso-add.php" class="btn btn-primary btn-sm">Agregar Nuevo Curso</a>
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
								<th>ID</th>
								<th>Nombre del Curso</th>
								<th>Descripción</th>
								<th>Duración</th>
								<th>Categoría</th>
								<th>Instructor</th>
								<th>Estado</th>
								<th>Diseño</th>
								<th>Días</th>
								<th>Horario</th>
								<th>Precio</th>
								<th>Cupos</th>
								<th>Fechas</th>
								<th>Requisitos</th>
								<th>Objetivos</th>
								<th>Diseñar Certificado</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$statement = $pdo->prepare("
								SELECT c.*, 
									   cat.nombre_categoria,
									   i.nombre as nombre_instructor,
									   i.apellido as apellido_instructor
								FROM curso c
								LEFT JOIN categoria cat ON c.idcategoria = cat.idcategoria
								LEFT JOIN instructor i ON c.idinstructor = i.idinstructor
								ORDER BY c.idcurso DESC
							");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								?>
								<tr>
									<td><?php echo $row['idcurso']; ?></td>
									<td><?php echo $row['nombre_curso']; ?></td>
									<td><?php echo substr($row['descripcion'], 0, 100) . '...'; ?></td>
									<td><?php echo $row['duracion']; ?> horas</td>
									<td><?php echo $row['nombre_categoria']; ?></td>
									<td><?php echo $row['nombre_instructor'] . ' ' . $row['apellido_instructor']; ?></td>
									<td>
										<?php if($row['estado'] == 'Activo'): ?>
											<span class="badge badge-success">Activo</span>
										<?php else: ?>
											<span class="badge badge-danger">Inactivo</span>
										<?php endif; ?>
									</td>
									<td>
										<?php if (!empty($row['diseño'])): ?>
											<img src="<?php echo BASE_URL . 'assets/uploads/cursos/' . $row['diseño']; ?>" 
												 alt="Diseño del curso" 
												 class="img-responsive" 
												 style="max-width: 80px; max-height: 60px; border: 1px solid #ddd; cursor: pointer;"
												 onclick="window.open('<?php echo BASE_URL . 'assets/uploads/cursos/' . $row['diseño']; ?>', '_blank')"
												 title="Hacer clic para ver imagen completa">
											<br><small class="text-muted"><?php echo $row['diseño']; ?></small>
										<?php else: ?>
											<span class="text-muted">Sin diseño</span>
										<?php endif; ?>
									</td>
									<td><?php echo $row['dias_semana']; ?></td>
									<td><?php echo date('h:i A', strtotime($row['hora_inicio'])) . ' - ' . date('h:i A', strtotime($row['hora_fin'])); ?></td>
									<td>S/ <?php echo number_format($row['precio'], 2); ?></td>
									<td><?php echo $row['cupos_disponibles']; ?></td>
									<td>
										<?php 
										if($row['fecha_inicio'] && $row['fecha_fin']) {
											echo date('d/m/Y', strtotime($row['fecha_inicio'])) . ' - ' . 
												 date('d/m/Y', strtotime($row['fecha_fin']));
										} else {
											echo '<span class="text-muted">No definido</span>';
										}
										?>
									</td>
									<td>
										<?php if($row['requisitos']): ?>
											<button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#requisitos<?php echo $row['idcurso']; ?>">
												Ver Requisitos
											</button>
											<div class="modal fade" id="requisitos<?php echo $row['idcurso']; ?>" tabindex="-1" role="dialog">
												<div class="modal-dialog" role="document">
													<div class="modal-content">
														<div class="modal-header">
															<h4 class="modal-title">Requisitos del Curso</h4>
															<button type="button" class="close" data-dismiss="modal">&times;</button>
														</div>
														<div class="modal-body">
															<?php echo nl2br($row['requisitos']); ?>
														</div>
													</div>
												</div>
											</div>
										<?php else: ?>
											<span class="text-muted">No definido</span>
										<?php endif; ?>
									</td>
									<td>
										<?php if($row['objetivos']): ?>
											<button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#objetivos<?php echo $row['idcurso']; ?>">
												Ver Objetivos
											</button>
											<div class="modal fade" id="objetivos<?php echo $row['idcurso']; ?>" tabindex="-1" role="dialog">
												<div class="modal-dialog" role="document">
													<div class="modal-content">
														<div class="modal-header">
															<h4 class="modal-title">Objetivos del Curso</h4>
															<button type="button" class="close" data-dismiss="modal">&times;</button>
														</div>
														<div class="modal-body">
															<?php echo nl2br($row['objetivos']); ?>
														</div>
													</div>
												</div>
											</div>
										<?php else: ?>
											<span class="text-muted">No definido</span>
										<?php endif; ?>
									</td>
									<td>
									<a href="editor_certificado.php?id=<?php echo $row['idcurso']; ?>" class="btn btn-warning btn-xs" title="Diseñar certificado">
                                   <i class="fa fa-paint-brush"></i> Diseñar Certificado
									</a>
									</td>
									<td>
										<a href="curso-edit.php?id=<?php echo $row['idcurso']; ?>" class="btn btn-primary btn-xs" title="Editar">
											<i class="fa fa-pencil"></i>
										</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="curso-delete.php?id=<?php echo $row['idcurso']; ?>" data-toggle="modal" data-target="#confirm-delete" title="Eliminar">
											<i class="fa fa-trash"></i>
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
				<h4 class="modal-title" id="myModalLabel">Confirmar Eliminación</h4>
			</div>
			<div class="modal-body">
				¿Está seguro que desea eliminar este curso?
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
            if (button) {
                var href = button.getAttribute('data-href');
                var confirmButton = this.querySelector('.btn-ok');
                if (confirmButton && href) {
                    confirmButton.setAttribute('href', href);
                }
            }
        });
    }

    // Inicializar DataTable con configuración local para evitar CORS
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#example1').DataTable({
            "language": {
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix":    "",
                "sSearch":         "Buscar:",
                "sUrl":            "",
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            },
            "columns": [
                { "data": "0" }, // SL
                { "data": "1" }, // Nombre del Curso
                { "data": "2" }, // Descripción
                { "data": "3" }, // Duración
                { "data": "4" }, // Categoría
                { "data": "5" }, // Instructor
                { "data": "6" }, // Estado
                { "data": "7" }, // Diseño
                { "data": "8" }, // Días de la Semana
                { "data": "9" }, // Horario
                { "data": "10" }, // Precio
                { "data": "11" }, // Cupos
                { "data": "12" }, // Fechas
                { "data": "13" }, // Requisitos
                { "data": "14" }, // Objetivos
                { "data": "15" }, // Diseñar Certificado
                { "data": "16" }  // Acción
            ],
            "columnDefs": [
                { "orderable": false, "targets": [15, 16] } // Deshabilitar ordenamiento en columnas de acciones y diseño de certificado
            ]
        });
    }
});
</script>

<?php require_once('footer.php'); ?>