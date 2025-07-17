<?php
require_once('header.php');

// Redirigir si no está logueado
if (!isset($_SESSION['customer'])) {
    header('location: login.php');
    exit;
}

// Obtener ID del cliente
$idCliente = $_SESSION['customer']['idcliente'];

// Obtener los cursos comprados del cliente
$statement = $pdo->prepare("
    SELECT DISTINCT 
        c.idcurso,
        c.nombre_curso,
        c.descripcion,
        c.precio,
        c.duracion,
        c.estado as estado_curso,
        i.fecha_inscripcion,
        i.nota_final,
        COALESCE(p.estado, 'sin_pago') as estado_pago,
        p.fecha_pago,
        p.monto as monto_pagado
    FROM inscripcion i
    JOIN curso c ON i.idcurso = c.idcurso
    LEFT JOIN pago p ON i.idinscripcion = p.idinscripcion
    WHERE i.idcliente = ? 
    ORDER BY i.fecha_inscripcion DESC
");
$statement->execute([$idCliente]);
$cursos = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-banner" style="background-color:#444;">
    <div class="inner">
        <h1>Mis Cursos</h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">
                    <h3>Cursos en los que estás inscrito</h3>
                    <?php if (count($cursos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Curso</th>
                                        <th>Duración</th>
                                        <th>Estado del Curso</th>
                                        <th>Estado del Pago</th>
                                        <th>Fecha de Compra</th>
                                        <th>Nota Final</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cursos as $curso): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($curso['nombre_curso']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars(substr($curso['descripcion'], 0, 100)) . '...'; ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($curso['duracion']); ?> horas</td>
                                            <td>
                                                <span class="badge bg-<?php echo $curso['estado_curso'] == 'Activo' ? 'success' : 'secondary'; ?>">
                                                    <?php echo htmlspecialchars($curso['estado_curso']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($curso['estado_pago'] == 'aprobado'): ?>
                                                    <span class="badge bg-success">Aprobado</span>
                                                <?php elseif ($curso['estado_pago'] == 'pendiente_verificacion'): ?>
                                                    <span class="badge bg-warning">Pendiente</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sin pago</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($curso['fecha_inscripcion'])); ?></td>
                                            <td>
                                                <?php if ($curso['nota_final']): ?>
                                                    <span class="badge bg-info"><?php echo $curso['nota_final']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                    <?php if ($curso['estado_pago'] == 'aprobado'): ?>
                                                        <a href="generar-certificado.php?curso=<?php echo $curso['idcurso']; ?>" class="btn btn-success btn-sm">
                                                            <i class="fas fa-certificate"></i> Certificado
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-book" style="font-size: 3rem; color: #6c757d;"></i>
                            <h5 class="mt-3">No tienes cursos comprados</h5>
                            <p class="text-muted">Compra tu primer curso para comenzar tu aprendizaje</p>
                                                    <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Ver Cursos Disponibles
                        </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?> 