<?php
require_once('admin/inc/config.php');
require_once('admin/inc/functions.php');

// Verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit();
}

$cliente_id = $_SESSION['cliente_id'];

// Obtener el historial de pagos del cliente
$statement = $pdo->prepare("
    SELECT p.*, i.nombre_curso, i.fecha_inscripcion
    FROM pago p
    JOIN inscripcion i ON p.idinscripcion = i.idinscripcion
    JOIN curso c ON i.idcurso = c.idcurso
    WHERE i.idcliente = ?
    ORDER BY p.fecha_pago DESC
");
$statement->execute([$cliente_id]);
$pagos = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pagos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include('header.php'); ?>

    <div class="container my-5">
        <h2 class="mb-4">Mis Pagos</h2>

        <?php if (empty($pagos)): ?>
            <div class="alert alert-info">
                No tienes pagos registrados.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Curso</th>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Estado</th>
                            <th>Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td><?php echo $pago['idpago']; ?></td>
                                <td><?php echo htmlspecialchars($pago['nombre_curso']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pago['fecha_pago'])); ?></td>
                                <td>S/ <?php echo number_format($pago['monto'], 2); ?></td>
                                <td><?php echo htmlspecialchars($pago['metodo_pago']); ?></td>
                                <td>
                                    <?php
                                    $estado_class = [
                                        'Pendiente' => 'warning',
                                        'Completado' => 'success',
                                        'Reembolsado' => 'info',
                                        'Cancelado' => 'danger'
                                    ][$pago['estado']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $estado_class; ?>">
                                        <?php echo $pago['estado']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($pago['comprobante']): ?>
                                        <a href="uploads/comprobantes/<?php echo $pago['comprobante']; ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-file-alt"></i> Ver
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No disponible</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    <?php include('footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 