<?php
ob_start();
require_once('header.php');
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Agregar Slider</h1>
	</div>
	<div class="content-header-right">
		<a href="slider.php" class="btn btn-primary btn-sm">Ver Todos</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php if(isset($_SESSION['error'])): ?>
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
							<label for="" class="col-sm-2 control-label">Foto <span>*</span></label>
							<div class="col-sm-4">
								<input type="file" name="foto" class="form-control" required>
								<small class="text-muted">Formatos permitidos: jpg, jpeg, gif, png. Tamaño recomendado: 1920x600px</small>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Título <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="titulo" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Contenido <span>*</span></label>
							<div class="col-sm-8">
								<textarea class="form-control" name="contenido" rows="5" required></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Texto del Botón</label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="texto_boton">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">URL del Botón</label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="url_boton">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Posición <span>*</span></label>
							<div class="col-sm-4">
								<input type="number" class="form-control" name="posicion" required>
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
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['titulo'])) {
		$valid = 0;
		$_SESSION['error'] = "El título es requerido";
	}

	if(empty($_POST['contenido'])) {
		$valid = 0;
		$_SESSION['error'] = "El contenido es requerido";
	}

	if(empty($_POST['posicion'])) {
		$valid = 0;
		$_SESSION['error'] = "La posición es requerida";
	}

	if($valid == 1) {
		try {
			$foto = '';
			if($_FILES['foto']['name'] != '') {
				$foto = time() . '_' . $_FILES['foto']['name'];
				// Asegurarse de que el directorio existe
				if (!file_exists('../assets/uploads')) {
					mkdir('../assets/uploads', 0777, true);
				}
				move_uploaded_file($_FILES['foto']['tmp_name'], '../assets/uploads/' . $foto);
			}

			$statement = $pdo->prepare("INSERT INTO sliders (foto, titulo, contenido, texto_boton, url_boton, posicion) VALUES (?, ?, ?, ?, ?, ?)");
			$statement->execute(array(
				$foto,
				$_POST['titulo'],
				$_POST['contenido'],
				$_POST['texto_boton'],
				$_POST['url_boton'],
				$_POST['posicion']
			));
			
			$_SESSION['success'] = "Slider agregado exitosamente";
			header('location: slider.php');
			exit();
		} catch (Exception $e) {
			$_SESSION['error'] = "Error al agregar el slider";
			header('location: slider.php');
			exit();
		}
	}
}
?>

<?php require_once('footer.php'); ?>