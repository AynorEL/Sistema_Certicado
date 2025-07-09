<?php
ob_start();
require_once('header.php');
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Agregar Especialista</h1>
	</div>
	<div class="content-header-right">
		<a href="especialista.php" class="btn btn-primary btn-sm">Ver Todos</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php if (isset($_SESSION['error'])): ?>
				<div class="alert alert-danger alert-dismissible" id="error-alert">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<h4><i class="icon fa fa-ban"></i> ¡Error!</h4>
					<?php
					echo $_SESSION['error'];
					unset($_SESSION['error']);
					?>
				</div>
			<?php endif; ?>

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Nombre <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="nombre" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Apellido <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="apellido" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Especialidad <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="especialidad" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Años de Experiencia <span>*</span></label>
							<div class="col-sm-4">
								<input type="number" name="experiencia" class="form-control" min="0" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Email <span>*</span></label>
							<div class="col-sm-4">
								<input type="email" name="email" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Teléfono <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="telefono" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Firma Especialista <span>*</span></label>
							<div class="col-sm-4">
								<input type="file" name="firma_especialista" class="form-control" accept="image/*" required>
								<small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Guardar</button>
								<a href="especialista.php" class="btn btn-danger pull-left" style="margin-left: 10px;">Cancelar</a>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</section>

<?php
if (isset($_POST['form1'])) {
	$valid = 1;

	if (empty($_POST['nombre'])) {
		$valid = 0;
		$_SESSION['error'] = "El nombre es requerido";
	}

	if (empty($_POST['apellido'])) {
		$valid = 0;
		$_SESSION['error'] = "El apellido es requerido";
	}

	if (empty($_POST['especialidad'])) {
		$valid = 0;
		$_SESSION['error'] = "La especialidad es requerida";
	}

	if (empty($_POST['experiencia'])) {
		$valid = 0;
		$_SESSION['error'] = "Los años de experiencia son requeridos";
	}

	if (empty($_POST['email'])) {
		$valid = 0;
		$_SESSION['error'] = "El email es requerido";
	}

	if (empty($_POST['telefono'])) {
		$valid = 0;
		$_SESSION['error'] = "El teléfono es requerido";
	}

	// Validar firma especialista
	if (!isset($_FILES['firma_especialista']) || $_FILES['firma_especialista']['error'] !== UPLOAD_ERR_OK) {
		$valid = 0;
		$_SESSION['error'] = "La firma especialista es requerida";
	} else {
		$firma_especialista = $_FILES['firma_especialista'];
		$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
		$max_size = 2 * 1024 * 1024; // 2MB

		if (!in_array($firma_especialista['type'], $allowed_types)) {
			$valid = 0;
			$_SESSION['error'] = "Formato de imagen no válido. Use JPG, PNG o GIF";
		}

		if ($firma_especialista['size'] > $max_size) {
			$valid = 0;
			$_SESSION['error'] = "El archivo es demasiado grande. Máximo 2MB";
		}
	}

	if ($valid == 1) {
		// Procesar la firma especialista
		$firma_especialista_name = '';
		if (isset($_FILES['firma_especialista']) && $_FILES['firma_especialista']['error'] === UPLOAD_ERR_OK) {
			$file_extension = pathinfo($_FILES['firma_especialista']['name'], PATHINFO_EXTENSION);
			$firma_especialista_name = 'especialista_firma_' . time() . '.' . $file_extension;
			$upload_path = FIRMAS_PATH . $firma_especialista_name;
			
			if (!move_uploaded_file($_FILES['firma_especialista']['tmp_name'], $upload_path)) {
				$valid = 0;
				$_SESSION['error'] = "Error al subir la firma especialista";
			}
		}

		if ($valid == 1) {
			$statement = $pdo->prepare("INSERT INTO especialista (nombre, apellido, especialidad, experiencia, email, telefono, firma_especialista) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$statement->execute(array(
				$_POST['nombre'],
				$_POST['apellido'],
				$_POST['especialidad'],
				$_POST['experiencia'],
				$_POST['email'],
				$_POST['telefono'],
				$firma_especialista_name
			));

			$_SESSION['success'] = "Especialista agregado exitosamente";
			header('location: especialista.php');
			exit();
		}
	}
}
?>

<?php require_once('footer.php'); ?> 