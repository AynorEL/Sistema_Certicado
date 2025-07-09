<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if (isset($_POST['form1'])) {
    $valid = 1;

    if (empty($_POST['nombre_rol'])) {
        $valid = 0;
        $error_message .= "El nombre del rol es obligatorio<br>";
    }

    if (empty($_POST['descripcion'])) {
        $valid = 0;
        $error_message .= "La descripción es obligatoria<br>";
    }

    if ($valid == 1) {
        $statement = $pdo->prepare("INSERT INTO rol (nombre_rol, descripcion) VALUES (?,?)");
        $statement->execute(array($_POST['nombre_rol'], $_POST['descripcion']));

        $success_message = 'El rol se ha añadido correctamente.';
    }
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Añadir Rol</h1>
	</div>
	<div class="content-header-right">
		<a href="rol.php" class="btn btn-primary btn-sm">Ver Todos</a>
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

			<form class="form-horizontal" action="" method="post">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label class="col-sm-3 control-label">Nombre del Rol <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="nombre_rol" class="form-control">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Descripción <span>*</span></label>
							<div class="col-sm-8">
								<textarea name="descripcion" class="form-control" cols="30" rows="10"></textarea>
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

<?php require_once('footer.php'); ?> 