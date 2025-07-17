<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if (isset($_POST['form1'])) {
	$valid = 1;

	if (empty($_POST['titulo_pregunta'])) {
		$valid = 0;
		$error_message .= 'El título no puede estar vacío<br>';
	}

	if (empty($_POST['contenido_pregunta'])) {
		$valid = 0;
		$error_message .= 'El contenido no puede estar vacío<br>';
	}

	if ($valid == 1) {
		// Inserta en la tabla correcta con los campos correctos
		$statement = $pdo->prepare("INSERT INTO preguntas_frecuentes (titulo_pregunta, contenido_pregunta, orden_pregunta) VALUES (?, ?, ?)");
		$statement->execute([
			$_POST['titulo_pregunta'],
			$_POST['contenido_pregunta'],
			$_POST['orden_pregunta'] ?? 0 // por defecto 0 si no se envía
		]);

		$_SESSION['success'] = '¡Pregunta frecuente añadida con éxito!';
		header('location: faq.php');
		exit();
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Agregar Pregunta Frecuente</h1>
	</div>
	<div class="content-header-right">
		<a href="faq.php" class="btn btn-primary btn-sm">Ver todas</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">

			<?php if ($error_message): ?>
				<div class="callout callout-danger"><p><?php echo $error_message; ?></p></div>
			<?php endif; ?>

			<?php if ($success_message): ?>
				<div class="callout callout-success"><p><?php echo $success_message; ?></p></div>
			<?php endif; ?>

			<form class="form-horizontal" action="" method="post">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label class="col-sm-2 control-label">Título <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="titulo_pregunta" value="<?php echo $_POST['titulo_pregunta'] ?? ''; ?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">Contenido <span>*</span></label>
							<div class="col-sm-9">
								<textarea class="form-control" name="contenido_pregunta" id="editor1" style="height:200px;"><?php echo $_POST['contenido_pregunta'] ?? ''; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">Orden</label>
							<div class="col-sm-2">
								<input type="number" class="form-control" name="orden_pregunta" value="<?php echo $_POST['orden_pregunta'] ?? '0'; ?>">
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-6">
								<button type="submit" class="btn btn-success" name="form1">Enviar</button>
							</div>
						</div>
					</div>
				</div>
			</form>

		</div>
	</div>
</section>

<script src="admin/ckeditor/ckeditor.js"></script>
<script>
  CKEDITOR.replace('editor1', {
    removePlugins: 'elementspath',
    resize_enabled: false,
    allowedContent: true
  });
</script>

<?php require_once('footer.php'); ?>
