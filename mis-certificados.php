<?php
require_once('header.php');

// Redirigir si no está logueado
if (!isset($_SESSION['cliente'])) {
    header('location: login.php');
    exit;
}

// Obtener ID del cliente
$idCliente = $_SESSION['cliente']['idcliente'];

// Obtener las inscripciones aprobadas (certificados)
$statement = $pdo->prepare("
    SELECT c.nombre_curso, c.duracion, i.fecha_finalizacion
    FROM inscripcion i
    JOIN curso c ON i.idcurso = c.idcurso
    WHERE i.idcliente = ? AND i.estado = 'Aprobado'
");
$statement->execute([$idCliente]);
$certificados = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-banner" style="background-color:#444;">
    <div class="inner">
        <h1>Mis Certificados</h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">
                    <h3>Certificados Obtenidos</h3>
                    <?php if (count($certificados) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre del Curso</th>
                                        <th>Duración (horas)</th>
                                        <th>Fecha de Finalización</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certificados as $certificado): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($certificado['nombre_curso']); ?></td>
                                            <td><?php echo htmlspecialchars($certificado['duracion']); ?></td>
                                            <td><?php echo date("d/m/Y", strtotime($certificado['fecha_finalizacion'])); ?></td>
                                            <td>
                                                <a href="generar-certificado.php?curso=<?php echo urlencode($certificado['nombre_curso']); ?>" class="btn btn-success btn-sm" target="_blank">Descargar Certificado</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Aún no has obtenido ningún certificado.</p>
                        <p>Sigue estudiando para conseguir tus certificados. ¡Mucho éxito!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?> 