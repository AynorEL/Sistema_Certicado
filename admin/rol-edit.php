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
        $statement = $pdo->prepare("UPDATE rol SET nombre_rol = ?, descripcion = ? WHERE idrol = ?");
        $statement->execute(array($_POST['nombre_rol'], $_POST['descripcion'], $_REQUEST['idrol']));

        $_SESSION['success'] = 'El rol se ha actualizado correctamente.';
        header('location: rol.php');
        exit();
    }
}
?>

<?php
if (!isset($_REQUEST['idrol'])) {
    $_SESSION['error'] = "ID de rol no válido";
    header('location: rol.php');
    exit;
} else {
    // Verificar si el ID es válido
    $statement = $pdo->prepare("SELECT * FROM rol WHERE idrol=?");
    $statement->execute(array($_REQUEST['idrol']));
    $total = $statement->rowCount();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($total == 0) {
        $_SESSION['error'] = "Rol no encontrado";
        header('location: rol.php');
        exit;
    }
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Editar Rol</h1>
	</div>
	<div class="content-header-right">
		<a href="rol.php" class="btn btn-primary btn-sm">Ver Todos</a>
	</div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM rol WHERE idrol=?");
$statement->execute(array($_REQUEST['idrol']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $nombre_rol = $row['nombre_rol'];
    $descripcion = $row['descripcion'];
}
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
							<label class="col-sm-3 control-label">Nombre del Rol <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="nombre_rol" class="form-control" value="<?php echo $nombre_rol; ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-3 control-label">Descripción <span>*</span></label>
							<div class="col-sm-8">
								<textarea name="descripcion" class="form-control" cols="30" rows="10"><?php echo $descripcion; ?></textarea>
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