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
		$statement = $pdo->prepare("UPDATE preguntas_frecuentes SET titulo_pregunta=?, contenido_pregunta=? WHERE id=?");
		$statement->execute([
			$_POST['titulo_pregunta'],
			$_POST['contenido_pregunta'],
			$_REQUEST['id']
		]);

		$_SESSION['success'] = '¡Pregunta frecuente actualizada con éxito!';
		header('location: faq.php');
		exit();
	}
}
?>

<?php
if (!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	$statement = $pdo->prepare("SELECT * FROM preguntas_frecuentes WHERE id=?");
	$statement->execute([$_REQUEST['id']]);
	$total = $statement->rowCount();
	if ($total == 0) {
		header('location: logout.php');
		exit;
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Editar Pregunta Frecuente</h1>
	</div>
	<div class="content-header-right">
		<a href="faq.php" class="btn btn-primary btn-sm">Ver todas</a>
	</div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM preguntas_frecuentes WHERE id=?");
$statement->execute([$_REQUEST['id']]);
$result = $statement->fetch(PDO::FETCH_ASSOC);
$titulo_pregunta = $result['titulo_pregunta'];
$contenido_pregunta = $result['contenido_pregunta'];
?>

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
							<label class="col-sm-2 control-label">Título <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="titulo_pregunta" value="<?php echo htmlspecialchars($titulo_pregunta); ?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">Contenido <span>*</span></label>
							<div class="col-sm-9">
								<textarea class="form-control" name="contenido_pregunta" id="editor1" style="height:140px;"><?php echo $contenido_pregunta; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-6">
								<button type="submit" class="btn btn-success" name="form1">Actualizar</button>
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
