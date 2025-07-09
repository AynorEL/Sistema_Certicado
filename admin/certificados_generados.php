<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

// Verificar si el usuario está logueado como admin
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
    
    if ($id > 0) {
        try {
            switch ($accion) {
                case 'activar':
                    $stmt = $pdo->prepare("UPDATE certificado_generado SET estado = 'Activo' WHERE id = ?");
                    $stmt->execute([$id]);
                    $mensaje = "Certificado activado exitosamente";
                    $tipo = "success";
                    break;
                    
                case 'desactivar':
                    $stmt = $pdo->prepare("UPDATE certificado_generado SET estado = 'Inactivo' WHERE id = ?");
                    $stmt->execute([$id]);
                    $mensaje = "Certificado desactivado exitosamente";
                    $tipo = "warning";
                    break;
                    
                case 'eliminar':
                    // Obtener información del certificado antes de eliminar
                    $stmt = $pdo->prepare("SELECT codigo_qr FROM certificado_generado WHERE id = ?");
                    $stmt->execute([$id]);
                    $certificado = $stmt->fetch();
                    
                    if ($certificado) {
                        // Eliminar archivo QR
                        $ruta_qr = __DIR__ . '/img/qr/' . $certificado['codigo_qr'];
                        if (file_exists($ruta_qr)) {
                            unlink($ruta_qr);
                        }
                        
                        // Eliminar de la base de datos
                        $stmt = $pdo->prepare("DELETE FROM certificado_generado WHERE id = ?");
                        $stmt->execute([$id]);
                        
                        $mensaje = "Certificado eliminado exitosamente";
                        $tipo = "danger";
                    }
                    break;
            }
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
            $tipo = "danger";
        }
    }
}

// Filtros
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Construir consulta
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(c.nombre LIKE ? OR c.apellido LIKE ? OR cu.nombre_curso LIKE ? OR cg.codigo_validacion LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($filter === 'activos') {
    $where_conditions[] = "cg.estado = 'Activo'";
} elseif ($filter === 'inactivos') {
    $where_conditions[] = "cg.estado = 'Inactivo'";
} elseif ($filter === 'recientes') {
    $where_conditions[] = "cg.fecha_generacion >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Obtener certificados
$stmt = $pdo->prepare("
    SELECT cg.*, c.nombre, c.apellido, c.email,
           cu.nombre_curso, cu.duracion,
           i.fecha_aprobacion, i.nota_final
    FROM certificado_generado cg
    JOIN cliente c ON cg.idcliente = c.idcliente
    JOIN curso cu ON cg.idcurso = cu.idcurso
    LEFT JOIN inscripcion i ON cg.idcliente = i.idcliente AND cg.idcurso = i.idcurso
    $where_clause
    ORDER BY cg.fecha_generacion DESC
");
$stmt->execute($params);
$certificados = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificados Generados - Admin</title>
    <link href="bootstrap-5.3.7-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row bg-dark text-white py-3 mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-certificate"></i> Certificados Generados</h1>
                    <p class="mb-0">Gestión completa de certificados con QR</p>
                </div>
                <div>
                    <a href="menu_certificados.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left"></i> Volver al Menú
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filtros y Búsqueda -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, curso o código..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="filter" class="form-select">
                            <option value="">Todos los certificados</option>
                            <option value="activos" <?php echo $filter === 'activos' ? 'selected' : ''; ?>>Solo activos</option>
                            <option value="inactivos" <?php echo $filter === 'inactivos' ? 'selected' : ''; ?>>Solo inactivos</option>
                            <option value="recientes" <?php echo $filter === 'recientes' ? 'selected' : ''; ?>>Últimos 7 días</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="certificados_generados.php" class="btn btn-secondary w-100">
                            <i class="fas fa-refresh"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Certificados -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Certificados Generados (<?php echo count($certificados); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Alumno</th>
                                <th>Curso</th>
                                <th>Código QR</th>
                                <th>Fecha Generación</th>
                                <th>Estado</th>
                                <th>Verificaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificados as $cert): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($cert['nombre'] . ' ' . $cert['apellido']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($cert['email']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($cert['nombre_curso']); ?></strong><br>
                                        <small class="text-muted"><?php echo $cert['duracion']; ?> horas</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="img/qr/<?php echo htmlspecialchars($cert['codigo_qr']); ?>" 
                                                 alt="QR" style="width: 40px; height: 40px; margin-right: 10px;">
                                            <div>
                                                <small class="text-muted">Código:</small><br>
                                                <code><?php echo htmlspecialchars($cert['codigo_validacion']); ?></code>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($cert['fecha_generacion'])); ?>
                                        <?php if ($cert['fecha_verificacion']): ?>
                                            <br><small class="text-info">Verificado: <?php echo date('d/m/Y H:i', strtotime($cert['fecha_verificacion'])); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $estado_class = '';
                                        $estado_icon = '';
                                        switch ($cert['estado']) {
                                            case 'Activo':
                                                $estado_class = 'success';
                                                $estado_icon = 'fas fa-check-circle';
                                                break;
                                            case 'Inactivo':
                                                $estado_class = 'warning';
                                                $estado_icon = 'fas fa-pause-circle';
                                                break;
                                            case 'Eliminado':
                                                $estado_class = 'danger';
                                                $estado_icon = 'fas fa-trash';
                                                break;
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $estado_class; ?>">
                                            <i class="<?php echo $estado_icon; ?>"></i>
                                            <?php echo $cert['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($cert['fecha_verificacion']): ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-eye"></i> Verificado
                                            </span>
                                            <?php if ($cert['ip_verificacion']): ?>
                                                <br><small class="text-muted">IP: <?php echo htmlspecialchars($cert['ip_verificacion']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-eye-slash"></i> No verificado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- Ver Certificado -->
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                    onclick="verCertificado(<?php echo $cert['idcliente']; ?>, <?php echo $cert['idcurso']; ?>)">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                            
                                            <!-- Verificar QR -->
                                            <?php 
                                            $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
                                            $host = $_SERVER['HTTP_HOST'];
                                            $ruta_cert = $protocolo . '://' . $host . '/certificado/verificar-certificado.php?codigo=' . urlencode($cert['codigo_validacion']);
                                            ?>
                                            <a href="<?php echo $ruta_cert; ?>" 
                                               class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-qrcode"></i> QR
                                            </a>
                                            
                                            <!-- Descargar PDF -->
                                            <a href="../generar-certificado.php?curso=<?php echo urlencode($cert['nombre_curso']); ?>" 
                                               class="btn btn-success btn-sm" target="_blank">
                                                <i class="fas fa-download"></i> PDF
                                            </a>
                                            
                                            <!-- Acciones de Estado -->
                                            <?php if ($cert['estado'] === 'Activo'): ?>
                                                <button type="button" class="btn btn-warning btn-sm" 
                                                        onclick="cambiarEstado(<?php echo $cert['id']; ?>, 'desactivar')">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        onclick="cambiarEstado(<?php echo $cert['id']; ?>, 'activar')">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Eliminar -->
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="eliminarCertificado(<?php echo $cert['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (empty($certificados)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-certificate fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No se encontraron certificados</h4>
                        <p class="text-muted">No hay certificados que coincidan con los filtros aplicados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Formulario oculto para acciones -->
    <form id="accionForm" method="POST" style="display: none;">
        <input type="hidden" name="id" id="certificado_id">
        <input type="hidden" name="accion" id="accion">
    </form>

    <script src="bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verCertificado(idcliente, idcurso) {
            // Abrir previsualización del certificado
            window.open(`previsualizar_certificado_final.php?idcurso=${idcurso}&idalumno=${idcliente}`, '_blank');
        }
        
        function cambiarEstado(id, accion) {
            if (confirm(`¿Estás seguro de que quieres ${accion} este certificado?`)) {
                document.getElementById('certificado_id').value = id;
                document.getElementById('accion').value = accion;
                document.getElementById('accionForm').submit();
            }
        }
        
        function eliminarCertificado(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este certificado? Esta acción no se puede deshacer.')) {
                document.getElementById('certificado_id').value = id;
                document.getElementById('accion').value = 'eliminar';
                document.getElementById('accionForm').submit();
            }
        }
    </script>
</body>
</html> 