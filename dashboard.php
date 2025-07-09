<?php
session_start();
require_once('admin/inc/config.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['customer'])) {
    header('Location: login.php');
    exit;
}

// Verificar si el usuario está activo
$statement = $pdo->prepare("SELECT * FROM cliente WHERE idcliente=? AND estado=?");
$statement->execute([$_SESSION['customer']['idcliente'], 'Activo']);
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

// Obtener cursos inscritos
$statement = $pdo->prepare("SELECT i.*, c.nombre_curso, c.descripcion, c.duracion, c.estado, i.fecha_inscripcion, i.nota_final
                            FROM inscripcion i
                            JOIN curso c ON i.idcurso = c.idcurso
                            WHERE i.idcliente=?
                            ORDER BY i.fecha_inscripcion DESC");
$statement->execute([$_SESSION['customer']['idcliente']]);
$cursos_inscritos = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once('header.php'); ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<div class="container my-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="dashboard-sidebar bg-light rounded shadow-sm p-3">
                <div class="user-info text-center mb-4">
                    <div class="mb-2">
                        <i class="bi bi-person-circle" style="font-size: 2.5rem; color: #0d6efd;"></i>
                    </div>
                    <h5 class="mb-0 text-primary"><?php echo $nombre; ?></h5>
                    <small class="text-muted"><?php echo $email; ?></small>
                </div>
                <ul class="nav flex-column dashboard-menu">
                    <li class="nav-item">
                        <a class="nav-link active d-flex align-items-center gap-2" href="dashboard.php">
                            <i class="bi bi-house-door-fill"></i> Mi Cuenta
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="customer-profile-update.php">
                            <i class="bi bi-pencil-square"></i> Actualizar Perfil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="customer-password-update.php">
                            <i class="bi bi-lock-fill"></i> Cambiar Contraseña
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger d-flex align-items-center gap-2" href="logout.php">
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
                        <p><strong>Teléfono:</strong> <?php echo $telefono; ?></p>
                        <p><strong>Dirección:</strong> <?php echo $direccion; ?></p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Mis Cursos</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($cursos_inscritos): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Curso</th>
                                            <th>Fecha de Inscripción</th>
                                            <th>Estado</th>
                                            <th>Nota Final</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cursos_inscritos as $curso): ?>
                                            <tr>
                                                <td>
                                                    <a href="curso.php?id=<?php echo $curso['idcurso']; ?>">
                                                        <?php echo $curso['nombre_curso']; ?>
                                                    </a>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($curso['fecha_inscripcion'])); ?></td>
                                                <td><?php echo $curso['estado']; ?></td>
                                                <td><?php echo $curso['nota_final'] ? $curso['nota_final'] : 'Pendiente'; ?></td>
                                                <td>
                                                    <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-sm btn-outline-primary">Ver Curso</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No estás inscrito en ningún curso todavía.</p>
                            <a href="curso.php" class="btn btn-primary">Ver Cursos Disponibles</a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
