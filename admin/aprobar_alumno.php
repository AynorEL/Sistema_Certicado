<?php
require_once 'inc/config.php';
require_once '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Color\Color;

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idcliente = isset($_POST['idcliente']) ? (int)$_POST['idcliente'] : 0;
    $idcurso = isset($_POST['idcurso']) ? (int)$_POST['idcurso'] : 0;
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
    
    if ($idcliente > 0 && $idcurso > 0) {
        try {
            if ($accion === 'aprobar') {
                // Marcar como aprobado
                $stmt = $pdo->prepare("
                    UPDATE inscripcion 
                    SET estado = 'Aprobado', 
                        fecha_aprobacion = NOW(),
                        nota_final = ?
                    WHERE idcliente = ? AND idcurso = ?
                ");
                $nota = isset($_POST['nota']) ? (float)$_POST['nota'] : 10.0;
                // Validar que la nota esté entre 1 y 20
                if ($nota < 1 || $nota > 20) {
                    throw new Exception("La nota debe estar entre 1 y 20");
                }
                $stmt->execute([$nota, $idcliente, $idcurso]);
                
                // Generar certificado automáticamente
                try {
                    // Verificar si ya existe un certificado
                    $stmt = $pdo->prepare("
                        SELECT codigo_validacion, codigo_qr 
                        FROM certificado_generado 
                        WHERE idcliente = ? AND idcurso = ?
                    ");
                    $stmt->execute([$idcliente, $idcurso]);
                    $certificado_existente = $stmt->fetch();
                    
                    if (!$certificado_existente) {
                        // Generar nuevo código único
                        $codigo_validacion = uniqid('CERT-' . $idcurso . '-' . $idcliente . '-', true);
                        $nombre_archivo_qr = $codigo_validacion . '.png';
                        
                        // Generar el código QR
                        $url_validacion = "https://" . $_SERVER['HTTP_HOST'] . "/certificado/verificar-certificado.php?codigo=" . $codigo_validacion;
                        
                        $qrCode = new QrCode(
                            $url_validacion,
                            new Encoding('UTF-8'),
                            ErrorCorrectionLevel::High,
                            300,
                            0,
                            RoundBlockSizeMode::Margin,
                            new Color(0, 0, 0),
                            new Color(255, 255, 255)
                        );
                        $writer = new PngWriter();
                        $result = $writer->write($qrCode);
                        
                        // Guardar el QR en el servidor
                        $ruta_qr = __DIR__ . '/img/qr/' . $nombre_archivo_qr;
                        $result->saveToFile($ruta_qr);
                        
                        // Guardar en la base de datos
                        $stmt = $pdo->prepare("
                            INSERT INTO certificado_generado (idcliente, idcurso, codigo_validacion, codigo_qr, fecha_generacion, estado)
                            VALUES (?, ?, ?, ?, NOW(), 'Activo')
                        ");
                        $stmt->execute([$idcliente, $idcurso, $codigo_validacion, $nombre_archivo_qr]);
                        
                        $mensaje = "Alumno aprobado exitosamente y certificado generado con QR";
                    } else {
                        $mensaje = "Alumno aprobado exitosamente (certificado ya existía)";
                    }
                    $tipo = "success";
                    
                } catch (Exception $e) {
                    $mensaje = "Alumno aprobado pero error al generar certificado: " . $e->getMessage();
                    $tipo = "warning";
                }
                
            } elseif ($accion === 'rechazar') {
                // Marcar como rechazado
                $stmt = $pdo->prepare("
                    UPDATE inscripcion 
                    SET estado = 'Rechazado', 
                        fecha_aprobacion = NOW()
                    WHERE idcliente = ? AND idcurso = ?
                ");
                $stmt->execute([$idcliente, $idcurso]);
                
                $mensaje = "Alumno rechazado";
                $tipo = "warning";
                
            } elseif ($accion === 'pendiente') {
                // Marcar como pendiente
                $stmt = $pdo->prepare("
                    UPDATE inscripcion 
                    SET estado = 'Pendiente', 
                        fecha_aprobacion = NULL
                    WHERE idcliente = ? AND idcurso = ?
                ");
                $stmt->execute([$idcliente, $idcurso]);
                
                $mensaje = "Alumno marcado como pendiente";
                $tipo = "info";
            }
            
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
            $tipo = "danger";
        }
    } else {
        $mensaje = "Datos inválidos";
        $tipo = "danger";
    }
}

// Obtener lista de inscripciones
$stmt = $pdo->prepare("
    SELECT i.idinscripcion, i.idcliente, i.idcurso, i.estado, i.fecha_inscripcion, i.nota_final,
           c.nombre, c.apellido, c.email,
           cu.nombre_curso, cu.duracion
    FROM inscripcion i
    JOIN cliente c ON i.idcliente = c.idcliente
    JOIN curso cu ON i.idcurso = cu.idcurso
    ORDER BY i.fecha_inscripcion DESC
");
$stmt->execute();
$inscripciones = $stmt->fetchAll();
?>

<?php include 'header.php'; ?>



    <!-- Estilos personalizados -->
    <style>
        .btn-group .btn {
            margin-right: 2px;
        }
        .modal-header {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }
        .modal-header .btn-close {
            filter: invert(1);
        }
        .alert {
            border-radius: 8px;
        }
        .table th {
            background-color: #343a40;
            color: white;
            border-color: #454d55;
        }
        .badge {
            font-size: 0.8em;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
    <?php
$toast = null;
if (isset($_SESSION['toast_mensaje'])) {
    $toast = [
        'mensaje' => $_SESSION['toast_mensaje'],
        'tipo' => $_SESSION['toast_tipo'] ?? 'info'
    ];
    unset($_SESSION['toast_mensaje'], $_SESSION['toast_tipo']);
}
?>

<body>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-graduation-cap text-primary"></i> Gestionar Aprobaciones de Alumnos</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Panel
                    </a>
                </div>
                
                <?php if (isset($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipo; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $tipo === 'success' ? 'check-circle' : ($tipo === 'danger' ? 'exclamation-triangle' : 'info-circle'); ?>"></i>
                        <?php echo htmlspecialchars($mensaje); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Lista de Inscripciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user"></i> Alumno</th>
                                        <th><i class="fas fa-envelope"></i> Email</th>
                                        <th><i class="fas fa-book"></i> Curso</th>
                                        <th><i class="fas fa-clock"></i> Duración</th>
                                        <th><i class="fas fa-calendar"></i> Fecha Inscripción</th>
                                        <th><i class="fas fa-info-circle"></i> Estado</th>
                                        <th><i class="fas fa-star"></i> Nota</th>
                                        <th><i class="fas fa-cogs"></i> Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inscripciones as $inscripcion): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($inscripcion['nombre'] . ' ' . $inscripcion['apellido']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($inscripcion['email']); ?></td>
                                            <td><?php echo htmlspecialchars($inscripcion['nombre_curso']); ?></td>
                                            <td><?php echo $inscripcion['duracion']; ?> horas</td>
                                            <td><?php echo date('d/m/Y', strtotime($inscripcion['fecha_inscripcion'])); ?></td>
                                            <td>
                                                <?php
                                                $estado_class = '';
                                                $estado_icon = '';
                                                switch ($inscripcion['estado']) {
                                                    case 'Aprobado':
                                                        $estado_class = 'success';
                                                        $estado_icon = 'fas fa-check-circle';
                                                        break;
                                                    case 'Rechazado':
                                                        $estado_class = 'danger';
                                                        $estado_icon = 'fas fa-times-circle';
                                                        break;
                                                    case 'Pendiente':
                                                        $estado_class = 'warning';
                                                        $estado_icon = 'fas fa-clock';
                                                        break;
                                                    case 'Cancelado':
                                                        $estado_class = 'secondary';
                                                        $estado_icon = 'fas fa-ban';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $estado_class; ?>">
                                                    <i class="<?php echo $estado_icon; ?>"></i>
                                                    <?php echo $inscripcion['estado']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($inscripcion['nota_final']): ?>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-star"></i> <?php echo $inscripcion['nota_final']; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <?php if ($inscripcion['estado'] !== 'Aprobado'): ?>
                                                        <button type="button" class="btn btn-success btn-sm" 
                                                                onclick="aprobarAlumno(<?php echo $inscripcion['idcliente']; ?>, <?php echo $inscripcion['idcurso']; ?>)">
                                                            <i class="fas fa-check"></i> Aprobar
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($inscripcion['estado'] !== 'Rechazado'): ?>
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                onclick="rechazarAlumno(<?php echo $inscripcion['idcliente']; ?>, <?php echo $inscripcion['idcurso']; ?>)">
                                                            <i class="fas fa-times"></i> Rechazar
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($inscripcion['estado'] !== 'Pendiente'): ?>
                                                        <button type="button" class="btn btn-warning btn-sm" 
                                                                onclick="pendienteAlumno(<?php echo $inscripcion['idcliente']; ?>, <?php echo $inscripcion['idcurso']; ?>)">
                                                            <i class="fas fa-clock"></i> Pendiente
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($inscripcion['estado'] === 'Aprobado'): ?>
                                                        <button type="button" class="btn btn-primary btn-sm" 
                                                                onclick="verCertificado(<?php echo $inscripcion['idcliente']; ?>, <?php echo $inscripcion['idcurso']; ?>)">
                                                            <i class="fas fa-certificate"></i> Ver
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (empty($inscripciones)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No hay inscripciones</h4>
                                <p class="text-muted">No se encontraron inscripciones en el sistema.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Formulario oculto para acciones -->
        <form id="accionForm" method="POST" style="display: none;">
            <input type="hidden" name="idcliente" id="idcliente">
            <input type="hidden" name="idcurso" id="idcurso">
            <input type="hidden" name="accion" id="accion">
            <input type="hidden" name="nota" id="nota" value="10.0">
        </form>


    </div>
   <script>
    // Funciones para aprobar, rechazar y poner pendiente a un alumno
    function aprobarAlumno(idcliente, idcurso) {
        if (confirm('¿Estás seguro de que quieres aprobar a este alumno?')) {
            const nota = prompt('Ingresa la nota final (1-20):', '10');
            if (nota !== null) {
                const notaNum = parseFloat(nota);
                if (isNaN(notaNum) || notaNum < 1 || notaNum > 20) {
                    alert('La nota debe ser un número entre 1 y 20');
                    return;
                }
                document.getElementById('idcliente').value = idcliente;
                document.getElementById('idcurso').value = idcurso;
                document.getElementById('accion').value = 'aprobar';
                document.getElementById('nota').value = nota;
                document.getElementById('accionForm').submit();
            }
        }
    }

    function rechazarAlumno(idcliente, idcurso) {
        if (confirm('¿Estás seguro de que quieres rechazar a este alumno?')) {
            document.getElementById('idcliente').value = idcliente;
            document.getElementById('idcurso').value = idcurso;
            document.getElementById('accion').value = 'rechazar';
            document.getElementById('accionForm').submit();
        }
    }

    function pendienteAlumno(idcliente, idcurso) {
        if (confirm('¿Estás seguro de que quieres marcar como pendiente a este alumno?')) {
            document.getElementById('idcliente').value = idcliente;
            document.getElementById('idcurso').value = idcurso;
            document.getElementById('accion').value = 'pendiente';
            document.getElementById('accionForm').submit();
        }
    }

    function verCertificado(idcliente, idcurso) {
        window.open(`previsualizar_certificado_final.php?idcurso=${idcurso}&idalumno=${idcliente}&modo=final`, '_blank');
    }
</script>

<!-- Toast de éxito para mostrar mensajes -->
<div class="toast align-items-center text-bg-success border-0" id="miToast" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 1rem; right: 1rem; z-index: 9999; display:none;">
  <div class="d-flex">
    <div class="toast-body"></div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
</div>
<script>
window.addEventListener('DOMContentLoaded', function() {
  const mensaje = localStorage.getItem('toastSuccess');
  if (mensaje) {
    const toastEl = document.getElementById('miToast');
    toastEl.querySelector('.toast-body').textContent = mensaje;
    toastEl.style.display = 'block';
    if (window.bootstrap && window.bootstrap.Toast) {
      const toast = new bootstrap.Toast(toastEl, { delay: 4000 }); // 4 segundos
      toast.show();
    } else if (typeof $ !== 'undefined' && $(toastEl).toast) {
      $(toastEl).toast({ delay: 4000 }).toast('show');
    } else {
      // Fallback simple
      setTimeout(() => { toastEl.style.display = 'none'; }, 4000);
    }
    localStorage.removeItem('toastSuccess');
  }
});
</script>

</body>
</html> 