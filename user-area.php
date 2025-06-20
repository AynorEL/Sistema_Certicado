<?php
require_once('header.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Obtener información del usuario
$statement = $pdo->prepare("
    SELECT c.*, u.nombre_usuario, u.estado 
    FROM cliente c 
    JOIN usuario u ON c.idcliente = u.idusuario 
    WHERE u.idusuario = ?
");
$statement->execute([$_SESSION['user_id']]);
$user = $statement->fetch(PDO::FETCH_ASSOC);

// Obtener cursos inscritos
$statement = $pdo->prepare("
    SELECT c.*, i.fecha_inscripcion 
    FROM curso c 
    JOIN inscripcion i ON c.idcurso = i.idcurso 
    WHERE i.idcliente = ?
    ORDER BY i.fecha_inscripcion DESC
");
$statement->execute([$_SESSION['user_id']]);
$cursos_inscritos = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- User Area Section -->
<section class="user-area-section py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="user-sidebar">
                    <div class="user-profile text-center mb-4">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle fa-5x"></i>
                        </div>
                        <h4 class="user-name mt-3"><?php echo $user['nombre'] . ' ' . $user['apellido']; ?></h4>
                        <p class="user-email text-muted"><?php echo $user['email']; ?></p>
                    </div>
                    <div class="list-group">
                        <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="fas fa-user me-2"></i> Mi Perfil
                        </a>
                        <a href="#courses" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-graduation-cap me-2"></i> Mis Cursos
                        </a>
                        <a href="#certificates" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-certificate me-2"></i> Mis Certificados
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Información Personal</h5>
                            </div>
                            <div class="card-body">
                                <form action="update-profile.php" method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nombre</label>
                                            <input type="text" class="form-control" name="nombre" value="<?php echo $user['nombre']; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Apellido</label>
                                            <input type="text" class="form-control" name="apellido" value="<?php echo $user['apellido']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">DNI</label>
                                            <input type="text" class="form-control" name="dni" value="<?php echo $user['dni']; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Teléfono</label>
                                            <input type="tel" class="form-control" name="telefono" value="<?php echo $user['telefono']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo $user['email']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Dirección</label>
                                        <textarea class="form-control" name="direccion" rows="3" required><?php echo $user['direccion']; ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Actualizar Perfil</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Courses Tab -->
                    <div class="tab-pane fade" id="courses">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Mis Cursos</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($cursos_inscritos) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Curso</th>
                                                    <th>Duración</th>
                                                    <th>Días</th>
                                                    <th>Horario</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($cursos_inscritos as $curso): ?>
                                                <tr>
                                                    <td><?php echo $curso['nombre_curso']; ?></td>
                                                    <td><?php echo $curso['duracion']; ?> horas</td>
                                                    <td><?php echo $curso['dias_semana']; ?></td>
                                                    <td><?php echo date('H:i', strtotime($curso['hora_inicio'])) . ' - ' . date('H:i', strtotime($curso['hora_fin'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $curso['estado'] == 'Activo' ? 'success' : 'secondary'; ?>">
                                                            <?php echo $curso['estado']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        No estás inscrito en ningún curso aún.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Certificates Tab -->
                    <div class="tab-pane fade" id="certificates">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Mis Certificados</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    Los certificados estarán disponibles una vez que completes los cursos.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.user-area-section {
    background-color: #f8f9fa;
    min-height: calc(100vh - 200px);
}

.user-sidebar {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
}

.user-profile {
    padding: 20px 0;
    border-bottom: 1px solid #eee;
}

.user-avatar {
    color: #007bff;
}

.user-name {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.user-email {
    font-size: 0.9rem;
}

.list-group-item {
    border: none;
    padding: 12px 20px;
    font-weight: 500;
    color: #333;
    transition: all 0.3s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
    color: #007bff;
}

.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid #eee;
    padding: 20px;
}

.card-title {
    font-weight: 600;
    color: #333;
}

.form-label {
    font-weight: 500;
    color: #555;
}

.table th {
    font-weight: 600;
    color: #333;
}

.badge {
    padding: 6px 12px;
    font-weight: 500;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
}

@media (max-width: 768px) {
    .user-sidebar {
        margin-bottom: 30px;
    }
    
    .card-header {
        padding: 15px;
    }
    
    .table-responsive {
        margin-bottom: 0;
    }
}
</style>

<?php require_once('footer.php'); ?> 