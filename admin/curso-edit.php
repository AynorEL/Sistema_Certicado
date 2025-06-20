<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

// Validar ID del curso al inicio
if (!isset($_REQUEST['idcurso']) || empty($_REQUEST['idcurso'])) {
	$_SESSION['error'] = "ID de curso no válido";
	header('location: curso.php');
	exit;
}

// Verificar si el curso existe
$statement = $pdo->prepare("SELECT * FROM curso WHERE idcurso=?");
$statement->execute(array($_REQUEST['idcurso']));
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

	if (empty($_POST['idcategoria'])) {
		$valid = 0;
		$error_message .= "Debe seleccionar una categoría<br>";
	}

	if (empty($_POST['idinstructor'])) {
		$valid = 0;
		$error_message .= "Debe seleccionar un instructor<br>";
	}

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

	if ($valid == 1) {
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
			estado = ?
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
			$_REQUEST['idcurso']
		));

		$success_message = 'El curso se actualizó correctamente.';
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

<?php require_once('footer.php'); ?>