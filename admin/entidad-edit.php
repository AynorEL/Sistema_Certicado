<?php
ob_start();
require_once('header.php');

if (!isset($_REQUEST['id'])) {
	header('location: entidad.php');
	exit();
} else {
	// Check the identidad is valid or not
	$statement = $pdo->prepare("SELECT * FROM entidad WHERE identidad=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	foreach ($result as $row) {
		$nombre_entidad = $row['nombre_entidad'];
		$ruc = $row['ruc'];
		$telefono = $row['telefono'];
		$email = $row['email'];
		$direccion = $row['direccion'];
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Editar Entidad</h1>
	</div>
	<div class="content-header-right">
		<a href="entidad.php" class="btn btn-primary btn-sm">Ver Todos</a>
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

			<form class="form-horizontal" action="" method="post">
				<input type="hidden" name="identidad" value="<?php echo $_REQUEST['id']; ?>">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Nombre de la Entidad <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="nombre_entidad" class="form-control" value="<?php echo $nombre_entidad; ?>" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">RUC <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="ruc" class="form-control" value="<?php echo $ruc; ?>" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Teléfono <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="telefono" class="form-control" value="<?php echo $telefono; ?>" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Email <span>*</span></label>
							<div class="col-sm-4">
								<input type="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Dirección <span>*</span></label>
							<div class="col-sm-4">
								<textarea name="direccion" class="form-control" rows="3" required><?php echo $direccion; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Actualizar</button>
								<a href="entidad.php" class="btn btn-danger pull-left" style="margin-left: 10px;">Cancelar</a>
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

	if (empty($_POST['nombre_entidad'])) {
		$valid = 0;
		$_SESSION['error'] = "El nombre de la entidad es requerido";
	}

	if (empty($_POST['ruc'])) {
		$valid = 0;
		$_SESSION['error'] = "El RUC es requerido";
	}

	if (empty($_POST['telefono'])) {
		$valid = 0;
		$_SESSION['error'] = "El teléfono es requerido";
	}

	if (empty($_POST['email'])) {
		$valid = 0;
		$_SESSION['error'] = "El email es requerido";
	}

	if (empty($_POST['direccion'])) {
		$valid = 0;
		$_SESSION['error'] = "La dirección es requerida";
	}

	if ($valid == 1) {
		$statement = $pdo->prepare("UPDATE entidad SET nombre_entidad=?, ruc=?, telefono=?, email=?, direccion=? WHERE identidad=?");
		$statement->execute(array(
			$_POST['nombre_entidad'],
			$_POST['ruc'],
			$_POST['telefono'],
			$_POST['email'],
			$_POST['direccion'],
			$_POST['identidad']
		));

		$_SESSION['success'] = "Entidad actualizada exitosamente";
		header('location: entidad.php');
		exit();
	}
}
?>

<?php require_once('footer.php'); ?> 