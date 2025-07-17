<?php
ob_start();
require_once('header.php');

$error_message = '';
$success_message = '';
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['titulo'])) {
		$valid = 0;
		$error_message .= "El título no puede estar vacío<br>";
	}

	if(empty($_POST['contenido'])) {
		$valid = 0;
		$error_message .= "El contenido no puede estar vacío<br>";
	}

	if(empty($_POST['texto_boton'])) {
		$valid = 0;
		$error_message .= "El texto del botón no puede estar vacío<br>";
	}

	if(empty($_POST['url_boton'])) {
		$valid = 0;
		$error_message .= "La URL del botón no puede estar vacía<br>";
	}

	if(empty($_POST['posicion'])) {
		$valid = 0;
		$error_message .= "La posición no puede estar vacía<br>";
	}

	$path = $_FILES['foto']['name'];
	$path_tmp = $_FILES['foto']['tmp_name'];

	if($path != '') {
		$ext = pathinfo( $path, PATHINFO_EXTENSION );
		$file_name = basename( $path, '.' . $ext );
		if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
			$valid = 0;
			$error_message .= 'Debes subir un archivo jpg, jpeg, gif o png<br>';
		}
	}

	if($valid == 1) {
		// Obtener la foto actual
		$statement = $pdo->prepare("SELECT foto FROM sliders WHERE id=?");
		$statement->execute(array($_REQUEST['id']));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		$current_foto = $result[0]['foto'];

		if($path != '') {
			// Eliminar la foto anterior si existe
			if($current_foto && file_exists('../assets/uploads/'.$current_foto)) {
				unlink('../assets/uploads/'.$current_foto);
			}

			// Generar nombre único para la nueva foto
			$final_name = 'slider-'.time().'.'.$ext;
			
			// Asegurarse de que el directorio existe
			if (!file_exists('../assets/uploads')) {
				mkdir('../assets/uploads', 0777, true);
			}
			
			// Mover la nueva foto
			if(move_uploaded_file($path_tmp, '../assets/uploads/'.$final_name)) {
				// Actualizar la base de datos con la nueva foto
				$statement = $pdo->prepare("UPDATE sliders SET titulo=?, contenido=?, texto_boton=?, url_boton=?, posicion=?, foto=? WHERE id=?");
				$statement->execute(array(
					$_POST['titulo'],
					$_POST['contenido'],
					$_POST['texto_boton'],
					$_POST['url_boton'],
					$_POST['posicion'],
					$final_name,
					$_REQUEST['id']
				));
			} else {
				$error_message .= "Error al subir la imagen. Por favor, intente nuevamente.<br>";
				$valid = 0;
			}
		} else {
			// Actualizar sin cambiar la foto
			$statement = $pdo->prepare("UPDATE sliders SET titulo=?, contenido=?, texto_boton=?, url_boton=?, posicion=? WHERE id=?");
			$statement->execute(array(
				$_POST['titulo'],
				$_POST['contenido'],
				$_POST['texto_boton'],
				$_POST['url_boton'],
				$_POST['posicion'],
				$_REQUEST['id']
			));
		}

		if($valid == 1) {
			$_SESSION['success'] = 'El slider ha sido actualizado exitosamente.';
			header('location: slider.php');
			exit();
		}
	}
} else {
	$statement = $pdo->prepare("SELECT * FROM sliders WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
	foreach ($result as $row) {
		$titulo = $row['titulo'];
		$contenido = $row['contenido'];
		$texto_boton = $row['texto_boton'];
		$url_boton = $row['url_boton'];
		$posicion = $row['posicion'];
		$foto = $row['foto'];
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Editar Slider</h1>
	</div>
	<div class="content-header-right">
		<a href="slider.php" class="btn btn-primary btn-sm">Ver Todos</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php if($error_message): ?>
			<div class="callout callout-danger">
				<p><?php echo $error_message; ?></p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
				<p><?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Título <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="titulo" value="<?php echo $titulo; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Contenido <span>*</span></label>
							<div class="col-sm-6">
								<textarea class="form-control" name="contenido" style="height:140px;"><?php echo $contenido; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Texto del Botón <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="texto_boton" value="<?php echo $texto_boton; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">URL del Botón <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="url_boton" value="<?php echo $url_boton; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Posición <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="posicion" value="<?php echo $posicion; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Foto Actual</label>
							<div class="col-sm-9" style="padding-top:5px">
								<?php if($foto): ?>
								<img src="../assets/uploads/<?php echo $foto; ?>" alt="Slider" style="width:200px;">
								<?php endif; ?>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Nueva Foto</label>
							<div class="col-sm-6" style="padding-top:5px">
								<input type="file" name="foto">
								<small class="text-muted">Formatos permitidos: jpg, jpeg, gif, png. Tamaño recomendado: 1920x600px</small>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
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