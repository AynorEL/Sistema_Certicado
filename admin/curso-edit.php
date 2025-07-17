<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

// Validar ID del curso al inicio
if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
	$_SESSION['error'] = "ID de curso no válido";
	header('location: curso.php');
	exit;
}

// Verificar si el curso existe
$statement = $pdo->prepare("SELECT * FROM curso WHERE idcurso=?");
$statement->execute(array($_REQUEST['id']));
$total = $statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

if ($total == 0) {
	$_SESSION['error'] = "Curso no encontrado";
	header('location: curso.php');
	exit;
}

// Obtener datos del curso
$curso = $result[0];
$nombre_curso = $curso['nombre_curso'];
$descripcion = $curso['descripcion'];
$duracion = $curso['duracion'];
$precio = $curso['precio'];
$cupos_disponibles = $curso['cupos_disponibles'];
$fecha_inicio = $curso['fecha_inicio'];
$fecha_fin = $curso['fecha_fin'];
$requisitos = $curso['requisitos'];
$objetivos = $curso['objetivos'];
$estado = $curso['estado'];
$idcategoria = $curso['idcategoria'];
$idinstructor = $curso['idinstructor'];
$dias_semana = $curso['dias_semana'];
$hora_inicio = $curso['hora_inicio'];
$hora_fin = $curso['hora_fin'];
$diseno = $curso['diseño'];

// Convertir días de la semana a array
$dias_array = !empty($dias_semana) ? explode(',', $dias_semana) : array();

// Obtener datos de la categoría
$statement = $pdo->prepare("SELECT * FROM categoria WHERE idcategoria=?");
$statement->execute(array($idcategoria));
$categoria = $statement->fetch(PDO::FETCH_ASSOC);
$nombre_categoria = $categoria ? $categoria['nombre_categoria'] : '';

// Obtener datos del instructor
$statement = $pdo->prepare("SELECT * FROM instructor WHERE idinstructor=?");
$statement->execute(array($idinstructor));
$instructor = $statement->fetch(PDO::FETCH_ASSOC);
$nombre_instructor = $instructor ? $instructor['nombre'] . ' ' . $instructor['apellido'] : '';

if (isset($_POST['form1'])) {
	$valid = 1;

	if (empty($_POST['nombre_curso'])) {
		$valid = 0;
		$error_message .= "El nombre del curso no puede estar vacío<br>";
	}

	if (empty($_POST['descripcion'])) {
		$valid = 0;
		$error_message .= "La descripción del curso no puede estar vacía<br>";
	}

	if (empty($_POST['duracion'])) {
		$valid = 0;
		$error_message .= "La duración del curso no puede estar vacía<br>";
	}

	if (empty($_POST['precio'])) {
		$valid = 0;
		$error_message .= "El precio no puede estar vacío<br>";
	}

	if (empty($_POST['cupos_disponibles'])) {
		$valid = 0;
		$error_message .= "Los cupos disponibles no pueden estar vacíos<br>";
	}

	if (empty($_POST['fecha_inicio'])) {
		$valid = 0;
		$error_message .= "La fecha de inicio no puede estar vacía<br>";
	}

	if (empty($_POST['fecha_fin'])) {
		$valid = 0;
		$error_message .= "La fecha de fin no puede estar vacía<br>";
	}

	if (empty($_POST['requisitos'])) {
		$valid = 0;
		$error_message .= "Los requisitos no pueden estar vacíos<br>";
	}

	if (empty($_POST['objetivos'])) {
		$valid = 0;
		$error_message .= "Los objetivos no pueden estar vacíos<br>";
	}

	if (empty($_POST['estado'])) {
		$valid = 0;
		$error_message .= "El estado del curso no puede estar vacío<br>";
	}

	if (empty($_POST['idcategoria'])) {
		$valid = 0;
		$error_message .= "Debe seleccionar una categoría<br>";
	}

	if (empty($_POST['idinstructor'])) {
		$valid = 0;
		$error_message .= "Debe seleccionar un instructor<br>";
	}

	if (empty($_POST['dias_semana'])) {
		$valid = 0;
		$error_message .= "Debe seleccionar al menos un día de la semana<br>";
	}

	if (empty($_POST['hora_inicio'])) {
		$valid = 0;
		$error_message .= "La hora de inicio no puede estar vacía<br>";
	}

	if (empty($_POST['hora_fin'])) {
		$valid = 0;
		$error_message .= "La hora de fin no puede estar vacía<br>";
	}

	// Validar nuevo diseño si se subió
	$new_diseno = '';
	if (isset($_FILES['diseno']) && $_FILES['diseno']['error'] === UPLOAD_ERR_OK) {
		$diseno_file = $_FILES['diseno'];
		$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
		$max_size = 5 * 1024 * 1024; // 5MB

		if (!in_array($diseno_file['type'], $allowed_types)) {
			$valid = 0;
			$error_message .= "Solo se permiten archivos de imagen (JPG, PNG, GIF)<br>";
		}

		if ($diseno_file['size'] > $max_size) {
			$valid = 0;
			$error_message .= "El archivo es demasiado grande. Máximo 5MB<br>";
		}

		if ($valid == 1) {
			$file_extension = pathinfo($_FILES['diseno']['name'], PATHINFO_EXTENSION);
			$new_diseno = 'curso_diseno_' . time() . '.' . $file_extension;
			$upload_path = CURSOS_PATH . $new_diseno;
			
			if (!move_uploaded_file($_FILES['diseno']['tmp_name'], $upload_path)) {
				$valid = 0;
				$error_message .= "Error al subir el nuevo archivo de diseño<br>";
			}
		}
	}

	if ($valid == 1) {
		// Determinar qué diseño usar
		$diseno_to_save = $diseno; // Mantener el actual por defecto
		
		if (!empty($new_diseno)) {
			// Si se subió un nuevo diseño, eliminar el anterior y usar el nuevo
			if (!empty($diseno) && file_exists(CURSOS_PATH . $diseno)) {
				unlink(CURSOS_PATH . $diseno);
			}
			$diseno_to_save = $new_diseno;
		}

		// Actualizar curso
		$statement = $pdo->prepare("UPDATE curso SET 
			nombre_curso = ?,
			descripcion = ?,
			duracion = ?,
			precio = ?,
			cupos_disponibles = ?,
			fecha_inicio = ?,
			fecha_fin = ?,
			requisitos = ?,
			objetivos = ?,
			idcategoria = ?,
			idinstructor = ?,
			estado = ?,
			dias_semana = ?,
			hora_inicio = ?,
			hora_fin = ?,
			diseño = ?
			WHERE idcurso = ?");

		$statement->execute(array(
			$_POST['nombre_curso'],
			$_POST['descripcion'],
			$_POST['duracion'],
			$_POST['precio'],
			$_POST['cupos_disponibles'],
			$_POST['fecha_inicio'],
			$_POST['fecha_fin'],
			$_POST['requisitos'],
			$_POST['objetivos'],
			$_POST['idcategoria'],
			$_POST['idinstructor'],
			$_POST['estado'],
			implode(',', $_POST['dias_semana']),
			$_POST['hora_inicio'],
			$_POST['hora_fin'],
			$diseno_to_save,
			$_REQUEST['id']
		));

		$_SESSION['success'] = 'El curso se actualizó correctamente.';
		header('location: curso.php');
		exit();
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Editar Curso</h1>
	</div>
	<div class="content-header-right">
		<a href="curso.php" class="btn btn-primary btn-sm">Ver Todos</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php if ($error_message): ?>
				<div class="callout callout-danger">
					<p><?php echo $error_message; ?></p>
				</div>
			<?php endif; ?>

			<?php if ($success_message): ?>
				<div class="callout callout-success">
					<p><?php echo $success_message; ?></p>
				</div>
			<?php endif; ?>

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label class="col-sm-3 control-label">Nombre del Curso <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="nombre_curso" class="form-control" value="<?php echo $nombre_curso; ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Descripción <span>*</span></label>
							<div class="col-sm-8">
								<textarea name="descripcion" class="form-control" cols="30" rows="10"><?php echo $descripcion; ?></textarea>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Duración (semanas) <span>*</span></label>
							<div class="col-sm-4">
								<input type="number" name="duracion" class="form-control" min="1" value="<?php echo $duracion; ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Días de la Semana <span>*</span></label>
							<div class="col-sm-8">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="dias_semana[]" value="Lunes" <?php echo in_array('Lunes', $dias_array) ? 'checked' : ''; ?>> Lunes
									</label>
									&nbsp;&nbsp;
									<label>
										<input type="checkbox" name="dias_semana[]" value="Martes" <?php echo in_array('Martes', $dias_array) ? 'checked' : ''; ?>> Martes
									</label>
									&nbsp;&nbsp;
									<label>
										<input type="checkbox" name="dias_semana[]" value="Miércoles" <?php echo in_array('Miércoles', $dias_array) ? 'checked' : ''; ?>> Miércoles
									</label>
									&nbsp;&nbsp;
									<label>
										<input type="checkbox" name="dias_semana[]" value="Jueves" <?php echo in_array('Jueves', $dias_array) ? 'checked' : ''; ?>> Jueves
									</label>
									&nbsp;&nbsp;
									<label>
										<input type="checkbox" name="dias_semana[]" value="Viernes" <?php echo in_array('Viernes', $dias_array) ? 'checked' : ''; ?>> Viernes
									</label>
									&nbsp;&nbsp;
									<label>
										<input type="checkbox" name="dias_semana[]" value="Sábado" <?php echo in_array('Sábado', $dias_array) ? 'checked' : ''; ?>> Sábado
									</label>
									&nbsp;&nbsp;
									<label>
										<input type="checkbox" name="dias_semana[]" value="Domingo" <?php echo in_array('Domingo', $dias_array) ? 'checked' : ''; ?>> Domingo
									</label>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Hora de Inicio <span>*</span></label>
							<div class="col-sm-4">
								<input type="time" name="hora_inicio" class="form-control" value="<?php echo $hora_inicio; ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Hora de Fin <span>*</span></label>
							<div class="col-sm-4">
								<input type="time" name="hora_fin" class="form-control" value="<?php echo $hora_fin; ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Categoría <span>*</span></label>
							<div class="col-sm-4">
								<select name="idcategoria" class="form-control select2">
									<option value="">Seleccionar Categoría</option>
									<?php
									$statement = $pdo->prepare("SELECT * FROM categoria ORDER BY nombre_categoria ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);
									foreach ($result as $row) {
									?>
										<option value="<?php echo $row['idcategoria']; ?>" <?php if ($row['idcategoria'] == $idcategoria) {
																							echo 'selected';
																						} ?>><?php echo $row['nombre_categoria']; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Instructor <span>*</span></label>
							<div class="col-sm-4">
								<select name="idinstructor" class="form-control select2">
									<option value="">Seleccionar Instructor</option>
									<?php
									$statement = $pdo->prepare("SELECT * FROM instructor ORDER BY nombre ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);
									foreach ($result as $row) {
									?>
										<option value="<?php echo $row['idinstructor']; ?>" <?php if ($row['idinstructor'] == $idinstructor) {
																							echo 'selected';
																						} ?>><?php echo $row['nombre'] . ' ' . $row['apellido']; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Estado <span>*</span></label>
							<div class="col-sm-4">
								<select name="estado" class="form-control">
									<option value="">Seleccionar estado</option>
									<option value="Activo" <?php if ($estado == 'Activo') echo 'selected'; ?>>Activo</option>
									<option value="Inactivo" <?php if ($estado == 'Inactivo') echo 'selected'; ?>>Inactivo</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Diseño Actual</label>
							<div class="col-sm-4">
								<?php if (!empty($diseno)): ?>
									<img src="<?php echo BASE_URL . 'assets/uploads/cursos/' . $diseno; ?>" 
										 alt="Diseño del curso" 
										 class="img-responsive" 
										 style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; padding: 5px;">
									<br><small class="text-muted"><?php echo $diseno; ?></small>
								<?php else: ?>
									<span class="text-muted">No hay diseño</span>
								<?php endif; ?>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Nuevo Diseño (Imagen)</label>
							<div class="col-sm-4">
								<input type="file" name="diseno" class="form-control" accept="image/*">
								<small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB. Deje vacío para mantener el actual.</small>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Precio (S/) <span>*</span></label>
							<div class="col-sm-4">
								<input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $precio; ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Cupos Disponibles <span>*</span></label>
							<div class="col-sm-4">
								<input type="number" name="cupos_disponibles" class="form-control" value="<?php echo $cupos_disponibles; ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Fecha de Inicio <span>*</span></label>
							<div class="col-sm-4">
								<input type="date" name="fecha_inicio" class="form-control" value="<?php echo $fecha_inicio; ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Fecha de Fin <span>*</span></label>
							<div class="col-sm-4">
								<input type="date" name="fecha_fin" class="form-control" value="<?php echo $fecha_fin; ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Requisitos</label>
							<div class="col-sm-8">
								<textarea name="requisitos" class="form-control" cols="30" rows="10"><?php echo $requisitos; ?></textarea>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Objetivos</label>
							<div class="col-sm-8">
								<textarea name="objetivos" class="form-control" cols="30" rows="10"><?php echo $objetivos; ?></textarea>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Actualizar</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</section>

<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%'
    });
});
</script>

<?php require_once('footer.php'); ?>