<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

// Validaciones del formulario
if (isset($_POST['form1'])) {
    $valid = 1;
    
    // Validación nombre curso
    if(empty($_POST['nombre_curso'])) {
        $valid = 0; 
        $error_message .= "El nombre del curso es requerido<br>";
    } elseif(strlen($_POST['nombre_curso']) < 3) {
        $valid = 0;
        $error_message .= "El nombre debe tener al menos 3 caracteres<br>";
    } elseif(strlen($_POST['nombre_curso']) > 100) {
        $valid = 0;
        $error_message .= "El nombre no puede exceder 100 caracteres<br>";
    }

    // Validación descripción
    if(empty($_POST['descripcion'])) {
        $valid = 0; 
        $error_message .= "La descripción es requerida<br>";
    }

    // Validación duración
    if(empty($_POST['duracion'])) {
        $valid = 0; 
        $error_message .= "La duración es requerida<br>";
    } elseif(!is_numeric($_POST['duracion'])) {
        $valid = 0;
        $error_message .= "La duración debe ser un número<br>";
    } elseif($_POST['duracion'] <= 0) {
        $valid = 0;
        $error_message .= "La duración debe ser mayor a 0<br>";
    }

    // Validación categoría
    if(empty($_POST['idcategoria'])) {
        $valid = 0; 
        $error_message .= "La categoría es requerida<br>";
    }

    // Validación instructor
    if(empty($_POST['idinstructor'])) {
        $valid = 0; 
        $error_message .= "El instructor es requerido<br>";
    }

    // Validación días de la semana
    if(empty($_POST['dias_semana'])) {
        $valid = 0; 
        $error_message .= "Los días de la semana son requeridos<br>";
    }

    // Validación hora inicio
    if(empty($_POST['hora_inicio'])) {
        $valid = 0; 
        $error_message .= "La hora de inicio es requerida<br>";
    }

    // Validación hora fin
    if(empty($_POST['hora_fin'])) {
        $valid = 0; 
        $error_message .= "La hora de fin es requerida<br>";
    }

    // Validación estado
    if(empty($_POST['estado'])) {
        $valid = 0; 
        $error_message .= "El estado es requerido<br>";
    }

    // Validación precio
    if(empty($_POST['precio'])) {
        $valid = 0;
        $error_message .= "El precio es requerido<br>";
    }

    // Validación cupos disponibles
    if(empty($_POST['cupos_disponibles'])) {
        $valid = 0;
        $error_message .= "Los cupos disponibles son requeridos<br>";
    }

    // Validación fecha inicio
    if(empty($_POST['fecha_inicio'])) {
        $valid = 0;
        $error_message .= "La fecha de inicio es requerida<br>";
    }

    // Validación fecha fin
    if(empty($_POST['fecha_fin'])) {
        $valid = 0;
        $error_message .= "La fecha de fin es requerida<br>";
    }

    // Validación requisitos
    if(empty($_POST['requisitos'])) {
        $valid = 0;
        $error_message .= "Los requisitos son requeridos<br>";
    }

    // Validación objetivos
    if(empty($_POST['objetivos'])) {
        $valid = 0;
        $error_message .= "Los objetivos son requeridos<br>";
    }

    // Validación diseño (opcional)
    $diseno_name = '';
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
            $diseno_name = 'curso_diseno_' . time() . '.' . $file_extension;
            $upload_path = CURSOS_PATH . $diseno_name;
            
            if (!move_uploaded_file($_FILES['diseno']['tmp_name'], $upload_path)) {
                $valid = 0;
                $error_message .= "Error al subir el archivo de diseño<br>";
            }
        }
    }

	if ($valid == 1) {
		// Insertar curso
		$statement = $pdo->prepare("INSERT INTO curso(
            nombre_curso,
            descripcion,
            duracion,
            idcategoria,
            idinstructor,
            dias_semana,
            hora_inicio,
            hora_fin,
            estado,
            diseño,
            precio,
            cupos_disponibles,
            fecha_inicio,
            fecha_fin,
            requisitos,
            objetivos
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

		$statement->execute(array(
			$_POST['nombre_curso'],
			$_POST['descripcion'],
			$_POST['duracion'],
			$_POST['idcategoria'],
			$_POST['idinstructor'],
			implode(',', $_POST['dias_semana']),
			$_POST['hora_inicio'],
			$_POST['hora_fin'],
			$_POST['estado'],
			$diseno_name,
			$_POST['precio'],
			$_POST['cupos_disponibles'],
			$_POST['fecha_inicio'],
			$_POST['fecha_fin'],
			$_POST['requisitos'],
			$_POST['objetivos']
		));

		$_SESSION['success'] = 'El curso se ha añadido correctamente.';
		header('location: curso.php');
		exit();
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Añadir Curso</h1>
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
							<label class="col-sm-3 control-label">Nombre Curso <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="nombre_curso" class="form-control">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Descripción <span>*</span></label>
							<div class="col-sm-8">
								<textarea name="descripcion" class="form-control" cols="30" rows="10"></textarea>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Duración (semanas) <span>*</span></label>
							<div class="col-sm-4">
								<input type="number" name="duracion" class="form-control" min="1">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Días de la Semana <span>*</span></label>
							<div class="col-sm-8">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="dias_semana[]" value="Lunes"> Lunes
									</label>
									&nbsp;&nbsp;
									<label>
										<input type="checkbox" name="dias_semana[]" value="Martes"> Martes
									</label>
									&nbsp;&nbsp;
									<label>
										<input type="checkbox" name="dias_semana[]" value="Miércoles"> Miércoles
									</label>
									&nbsp;&nbsp;
									<label>
										<input type="checkbox" name="dias_semana[]" value="Jueves"> Jueves
									</label>
									&nbsp;&nbsp;
									<label>
										<input type="checkbox" name="dias_semana[]" value="Viernes"> Viernes
									</label>
									&nbsp;&nbsp;
									<label>
										<input type="checkbox" name="dias_semana[]" value="Sábado"> Sábado
									</label>
									&nbsp;&nbsp;
									<label>
										<input type="checkbox" name="dias_semana[]" value="Domingo"> Domingo
									</label>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Hora de Inicio <span>*</span></label>
							<div class="col-sm-4">
								<input type="time" name="hora_inicio" class="form-control">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Hora de Fin <span>*</span></label>
							<div class="col-sm-4">
								<input type="time" name="hora_fin" class="form-control">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Categoría <span>*</span></label>
							<div class="col-sm-4">
								<select name="idcategoria" class="form-control">
									<option value="">Seleccionar categoría</option>
									<?php
									$statement = $pdo->prepare("SELECT * FROM categoria ORDER BY idcategoria ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);
									foreach ($result as $row) {
									?>
										<option value="<?php echo $row['idcategoria']; ?>"><?php echo $row['nombre_categoria']; ?></option>
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
									<option value="">Seleccionar instructor</option>
									<?php
									$statement = $pdo->prepare("SELECT * FROM instructor ORDER BY nombre ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);
									foreach ($result as $row) {
									?>
										<option value="<?php echo $row['idinstructor']; ?>">
											<?php echo $row['nombre'] . ' ' . $row['apellido']; ?>
										</option>
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
									<option value="Activo">Activo</option>
									<option value="Inactivo">Inactivo</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Diseño del Curso (Imagen)</label>
							<div class="col-sm-4">
								<input type="file" name="diseno" class="form-control" accept="image/*">
								<small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Precio (S/)</label>
							<div class="col-sm-4">
								<input type="number" step="0.01" name="precio" class="form-control">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Cupos Disponibles</label>
							<div class="col-sm-4">
								<input type="number" name="cupos_disponibles" class="form-control">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Fecha de Inicio</label>
							<div class="col-sm-4">
								<input type="date" name="fecha_inicio" class="form-control">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Fecha de Fin</label>
							<div class="col-sm-4">
								<input type="date" name="fecha_fin" class="form-control">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Requisitos</label>
							<div class="col-sm-8">
								<textarea name="requisitos" class="form-control" cols="30" rows="5"></textarea>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Objetivos</label>
							<div class="col-sm-8">
								<textarea name="objetivos" class="form-control" cols="30" rows="5"></textarea>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Guardar</button>
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