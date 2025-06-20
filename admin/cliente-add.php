<?php
ob_start();
require_once('header.php');
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Agregar Cliente</h1>
	</div>
	<div class="content-header-right">
		<a href="cliente.php" class="btn btn-primary btn-sm">Ver Todos</a>
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
							<label for="" class="col-sm-2 control-label">DNI <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="dni" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Teléfono <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="telefono" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Email <span>*</span></label>
							<div class="col-sm-4">
								<input type="email" name="email" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Dirección <span>*</span></label>
							<div class="col-sm-4">
								<textarea name="direccion" class="form-control" rows="3" required></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
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

	if (empty($_POST['dni'])) {
		$valid = 0;
		$_SESSION['error'] = "El DNI es requerido";
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
		$statement = $pdo->prepare("INSERT INTO cliente (nombre, apellido, dni, telefono, email, direccion) VALUES (?, ?, ?, ?, ?, ?)");
		$statement->execute(array(
			$_POST['nombre'],
			$_POST['apellido'],
			$_POST['dni'],
			$_POST['telefono'],
			$_POST['email'],
			$_POST['direccion']
		));

		$_SESSION['success'] = "Cliente agregado exitosamente";
		header('location: cliente.php');
		exit();
	}
}
?>

<?php require_once('footer.php'); ?> 