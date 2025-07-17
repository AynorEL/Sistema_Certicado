<?php
session_start();
require_once('admin/inc/config.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['customer'])) {
    header('Location: login.php');
    exit;
}

// Verificar si el usuario existe (sin importar el estado)
$statement = $pdo->prepare("SELECT * FROM cliente WHERE idcliente=?");
$statement->execute([$_SESSION['customer']['idcliente']]);
if ($statement->rowCount() == 0) {
    header('Location: logout.php');
    exit;
}

// Obtener información del cliente
$statement = $pdo->prepare("SELECT * FROM cliente WHERE idcliente=?");
$statement->execute([$_SESSION['customer']['idcliente']]);
$row = $statement->fetch(PDO::FETCH_ASSOC);
$nombre = $row['nombre'];
$apellido = $row['apellido'];
$email = $row['email'];
$telefono = $row['telefono'];
$direccion = $row['direccion'];

// Obtener cursos comprados (con pagos aprobados o pendientes)
$cliente_id = $_SESSION['customer']['idcliente'];

// Consulta principal mejorada
$statement = $pdo->prepare("
    SELECT DISTINCT 
        c.idcurso,
        c.nombre_curso,
        c.descripcion,
        c.duracion,
        c.estado as estado_curso,
        i.fecha_inscripcion,
        i.nota_final,
        i.estado_pago,
        p.fecha_pago
    FROM inscripcion i
    JOIN curso c ON i.idcurso = c.idcurso
    LEFT JOIN pago p ON i.idinscripcion = p.idinscripcion
    WHERE i.idcliente = ? 
    ORDER BY i.fecha_inscripcion DESC
");
$statement->execute([$cliente_id]);
$cursos_comprados = $statement->fetchAll(PDO::FETCH_ASSOC);

// Después de obtener $cursos_comprados
foreach ($cursos_comprados as &$curso) {
    $stmtCert = $pdo->prepare("SELECT codigo_validacion FROM certificado_generado WHERE idcliente = ? AND idcurso = ? AND estado = 'Activo'");
    $stmtCert->execute([$cliente_id, $curso['idcurso']]);
    $cert = $stmtCert->fetch(PDO::FETCH_ASSOC);
    $curso['codigo_certificado'] = $cert['codigo_validacion'] ?? null;
}
unset($curso);
?>

<?php require_once('header.php'); ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<div class="container my-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="dashboard-sidebar bg-dark rounded shadow-sm p-3">
                <!-- Menú lateral sin bloque de usuario -->
                <ul class="nav flex-column dashboard-menu">
                    <li class="nav-item">
                        <a class="nav-link active d-flex align-items-center gap-2 text-white" href="dashboard.php">
                            <i class="bi bi-house-door-fill"></i> Mi Cuenta
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 text-white" href="customer-profile-update.php">
                            <i class="bi bi-pencil-square"></i> Actualizar Perfil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 text-white" href="customer-password-update.php">
                            <i class="bi bi-lock-fill"></i> Cambiar Contraseña
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 text-white" href="mis-cursos.php">
                            <i class="bi bi-book-fill"></i> Mis Cursos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 text-white" href="mis-certificados.php">
                            <i class="bi bi-award-fill"></i> Mis Certificados
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger d-flex align-items-center gap-2 text-white" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Contenido -->
        <div class="col-md-9">
            <div class="dashboard-content">
                <h2 class="mb-4">Mi Cuenta</h2>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Información Personal</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Nombre:</strong> <?php echo $nombre . ' ' . $apellido; ?></p>
                        <p><strong>Email:</strong> <?php echo $email; ?></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Mis Cursos Comprados</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($cursos_comprados): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Curso</th>
                                            <th>Fecha de Compra</th>
                                            <th>Estado del Curso</th>
                                            <th>Estado del Pago</th>
                                            <th>Nota Final</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cursos_comprados as $curso): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($curso['nombre_curso']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($curso['descripcion']); ?></small>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($curso['fecha_inscripcion'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $curso['estado_curso'] == 'Activo' ? 'success' : 'secondary'; ?>">
                                                        <?php echo htmlspecialchars($curso['estado_curso']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $estado_pago = strtolower($curso['estado_pago'] ?? '');
                                                    switch ($estado_pago) {
                                                        case 'pagado':
                                                            echo '<span class="badge bg-success">Pagado</span>';
                                                            break;
                                                        case 'pendiente':
                                                            echo '<span class="badge bg-warning text-dark">Pendiente</span>';
                                                            break;
                                                        case 'reembolsado':
                                                            echo '<span class="badge bg-info text-dark">Reembolsado</span>';
                                                            break;
                                                        case 'cancelado':
                                                            echo '<span class="badge bg-danger">Cancelado</span>';
                                                            break;
                                                        case 'sin pago':
                                                        default:
                                                            echo '<span class="badge bg-secondary">Sin pago</span>';
                                                            break;
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($curso['nota_final']): ?>
                                                        <span class="badge bg-info"><?php echo $curso['nota_final']; ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> Ver
                                                        </a>
                                                        <?php if (!empty($curso['codigo_certificado'])): ?>
                                                            <a href="generar-certificado.php?codigo=<?php echo urlencode($curso['codigo_certificado']); ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                                                <i class="bi bi-file-earmark-text"></i> Certificado
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
                                <i class="bi bi-book" style="font-size: 3rem; color: #6c757d;"></i>
                                <h5 class="mt-3">No tienes cursos comprados</h5>
                                <p class="text-muted">Compra tu primer curso para comenzar tu aprendizaje</p>
                                <a href="index.php" class="btn btn-primary">
                                    <i class="bi bi-cart-plus"></i> Ver Cursos Disponibles
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
