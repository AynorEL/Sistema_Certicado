<?php require_once('header.php'); ?>

<?php
// Eliminar suscriptor
if(isset($_POST['delete'])) {
    $statement = $pdo->prepare("DELETE FROM suscriptores WHERE id_suscriptor=?");
    $statement->execute(array($_POST['id_suscriptor']));
    $_SESSION['success'] = 'El suscriptor ha sido eliminado con éxito.';
    header('location: suscriptor.php');
    exit();
}

// Cambiar estado del suscriptor
if(isset($_POST['toggle_status'])) {
    $statement = $pdo->prepare("UPDATE suscriptores SET activo=? WHERE id_suscriptor=?");
    $statement->execute(array($_POST['activo'], $_POST['id_suscriptor']));
    $_SESSION['success'] = 'El estado del suscriptor ha sido actualizado con éxito.';
    header('location: suscriptor.php');
    exit();
}
?>

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

        <ul class="nav nav-tabs" role="tablist">
            <li class="active"><a href="#contacto" role="tab" data-toggle="tab">📝 Mensajes de Contacto</a></li>
            <li><a href="#suscriptores" role="tab" data-toggle="tab">📰 Suscriptores</a></li>
        </ul>
        <div class="tab-content" style="background:#fff; border:1px solid #ddd; border-top:0; border-radius:0 0 6px 6px; padding:24px 18px;">
            <div class="tab-pane active" id="contacto">
                <?php
                $stmt = $pdo->query('SELECT * FROM mensajes_contacto ORDER BY fecha DESC');
                $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <h4>Mensajes de Contacto Recibidos</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Mensaje</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($mensajes as $i => $m): ?>
                        <tr>
                            <td><?php echo $i+1; ?></td>
                            <td><?php echo htmlspecialchars($m['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($m['email']); ?></td>
                            <td><?php echo htmlspecialchars($m['telefono']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($m['mensaje'])); ?></td>
                            <td><?php echo htmlspecialchars($m['fecha']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane" id="suscriptores">
                <div class="box box-info" style="box-shadow:none; border:0;">
                    <div class="box-header with-border">
                        <h3 class="box-title">Gestionar Suscriptores</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Correo Electrónico</th>
                                    <th>Estado</th>
                                    <th>Fecha de Suscripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $statement = $pdo->prepare("SELECT * FROM suscriptores ORDER BY fecha_suscripcion DESC");
                                $statement->execute();
                                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($result as $row) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id_suscriptor']; ?></td>
                                        <td><?php echo $row['correo_suscriptor']; ?></td>
                                        <td>
                                            <?php if($row['activo'] == 1): ?>
                                                <span class="label label-success">Activo</span>
                                            <?php else: ?>
                                                <span class="label label-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_suscripcion'])); ?></td>
                                        <td>
                                            <form action="" method="post" style="display:inline;">
                                                <input type="hidden" name="id_suscriptor" value="<?php echo $row['id_suscriptor']; ?>">
                                                <input type="hidden" name="activo" value="<?php echo $row['activo'] == 1 ? 0 : 1; ?>">
                                                <button type="submit" class="btn btn-primary btn-xs" name="toggle_status">
                                                    <?php echo $row['activo'] == 1 ? 'Desactivar' : 'Activar'; ?>
                                                </button>
                                            </form>
                                            <form action="" method="post" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este suscriptor?');">
                                                <input type="hidden" name="id_suscriptor" value="<?php echo $row['id_suscriptor']; ?>">
                                                <button type="submit" class="btn btn-danger btn-xs" name="delete">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?> 