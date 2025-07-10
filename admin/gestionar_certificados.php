<?php $cur_page = basename(__FILE__); ?>
<?php
require_once('inc/config.php');
require_once('auth.php');
// require_once('generar_certificado_automatico.php'); // Comentado temporalmente

// La verificación de sesión ya se hace en auth.php
// No necesitamos verificar sesión aquí nuevamente

$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Comentado temporalmente hasta crear la tabla certificados_generados
        /*
        $generador = new GeneradorCertificadosAutomatico($pdo);
        
        switch ($_POST['action']) {
            case 'generar_individual':
                if (isset($_POST['idinscripcion'])) {
                    $resultado = $generador->generarCertificadoAutomatico($_POST['idinscripcion']);
                    if ($resultado['success']) {
                        $mensaje = "✅ " . $resultado['message'];
                        $tipo_mensaje = 'success';
                    } else {
                        $mensaje = "❌ Error: " . $resultado['message'];
                        $tipo_mensaje = 'error';
                    }
                }
                break;
                
            case 'generar_masivo':
                $resultado = $generador->procesarCertificadosPendientes();
                $mensaje = "✅ Se procesaron " . $resultado['procesados'] . " certificados. " . $resultado['mensaje'];
                $tipo_mensaje = 'success';
                break;
        }
        */
        $mensaje = "⚠️ Funcionalidad de generación automática temporalmente deshabilitada. Se requiere crear la tabla certificados_generados.";
        $tipo_mensaje = 'warning';
    }
}

// Obtener cursos con alumnos inscritos
$stmt = $pdo->prepare("
    SELECT 
        cur.idcurso,
        cur.nombre_curso,
        cur.duracion,
        cur.fecha_inicio,
        cur.fecha_fin,
        cur.cupos_disponibles,
        COUNT(i.idinscripcion) as total_inscritos,
        SUM(CASE WHEN i.estado = 'Aprobado' THEN 1 ELSE 0 END) as aprobados,
        SUM(CASE WHEN i.estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN i.estado = 'Cancelado' THEN 1 ELSE 0 END) as cancelados,
        0 as certificados_generados,
        cur.config_certificado
    FROM curso cur
    LEFT JOIN inscripcion i ON cur.idcurso = i.idcurso
    WHERE cur.estado = 'Activo'
    GROUP BY cur.idcurso
    ORDER BY cur.fecha_inicio DESC
");
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener detalles de alumnos por curso
$alumnos_por_curso = [];
foreach ($cursos as $curso) {
    $stmt = $pdo->prepare("
        SELECT 
            i.idinscripcion,
            i.estado,
            i.fecha_inscripcion,
            i.fecha_aprobacion,
            i.nota_final,
            i.observaciones,
            c.idcliente,
            c.nombre,
            c.apellido,
            c.email,
            c.telefono,
            CASE 
                WHEN i.estado = 'Aprobado' THEN 'Pendiente Certificado'
                WHEN i.estado = 'Pendiente' THEN 'En Curso'
                WHEN i.estado = 'Cancelado' THEN 'Cancelado'
                ELSE 'Desconocido'
            END as estado_certificado,
            NULL as fecha_generacion,
            NULL as archivo_path,
            CASE 
                WHEN i.estado = 'Aprobado' THEN 90
                WHEN i.nota_final IS NOT NULL AND i.nota_final >= 10.5 THEN 80
                WHEN i.nota_final IS NOT NULL THEN 70
                WHEN i.fecha_aprobacion IS NOT NULL THEN 60
                WHEN i.estado = 'Pendiente' THEN 30
                ELSE 10
            END as progreso_certificacion
        FROM inscripcion i
        JOIN cliente c ON i.idcliente = c.idcliente
        WHERE i.idcurso = ?
        ORDER BY i.fecha_inscripcion DESC
    ");
    $stmt->execute([$curso['idcurso']]);
    $alumnos_por_curso[$curso['idcurso']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Incluir el header
require_once('header.php');
?>

<style>
    .curso-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .curso-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 8px 8px 0 0;
    }
    .curso-body {
        padding: 15px;
    }
    .stats-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    .stat-item {
        text-align: center;
        flex: 1;
        margin: 0 5px;
    }
    .stat-number {
        font-size: 1.5em;
        font-weight: bold;
        display: block;
    }
    .stat-label {
        font-size: 0.9em;
        color: #666;
    }
    .alumno-row {
        border: 1px solid #eee;
        border-radius: 5px;
        margin-bottom: 10px;
        padding: 10px;
        background: #f9f9f9;
    }
    .alumno-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .alumno-details {
        flex: 1;
    }
    .alumno-actions {
        text-align: right;
    }
    .progreso-bar {
        width: 100%;
        height: 8px;
        background: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 5px;
    }
    .progreso-fill {
        height: 100%;
        transition: width 0.3s ease;
    }
    .estado-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.8em;
        font-weight: bold;
    }
    .estado-aprobado { background: #d4edda; color: #155724; }
    .estado-pendiente { background: #fff3cd; color: #856404; }
    .estado-cancelado { background: #f8d7da; color: #721c24; }
    .estado-generado { background: #d1ecf1; color: #0c5460; }
    .btn-generar {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
    }
    .btn-ver {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
        color: white;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
        text-decoration: none;
        display: inline-block;
        margin-top: 5px;
    }
    .btn-deshabilitado {
        background: #6c757d !important;
        cursor: not-allowed !important;
    }
    .curso-sin-config {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 15px;
        color: #856404;
    }
</style>

<!-- Contenido de la Página -->
<section class="content-header">
    <h1>
        📊 Dashboard de Certificados
        <small>Progreso detallado por curso</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Dashboard Certificados</li>
    </ol>
</section>

<section class="content">
    <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje === 'success' ? 'success' : ($tipo_mensaje === 'warning' ? 'warning' : 'danger'); ?> alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <!-- Resumen General -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">📈 Resumen General</h3>
                </div>
                <div class="box-body">
                    <?php
                    $total_cursos = count($cursos);
                    $total_inscritos = array_sum(array_column($cursos, 'total_inscritos'));
                    $total_aprobados = array_sum(array_column($cursos, 'aprobados'));
                    $total_certificados = array_sum(array_column($cursos, 'certificados_generados'));
                    ?>
                    <div class="stats-row">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $total_cursos; ?></span>
                            <span class="stat-label">Cursos Activos</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $total_inscritos; ?></span>
                            <span class="stat-label">Total Inscritos</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $total_aprobados; ?></span>
                            <span class="stat-label">Aprobados</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $total_certificados; ?></span>
                            <span class="stat-label">Certificados</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cursos Detallados -->
    <?php foreach ($cursos as $curso): ?>
        <div class="curso-card">
            <div class="curso-header">
                <h4>
                    <i class="fa fa-graduation-cap"></i> 
                    <?php echo htmlspecialchars($curso['nombre_curso']); ?>
                </h4>
                <small>
                    Duración: <?php echo $curso['duracion']; ?> horas | 
                    Fecha: <?php echo date('d/m/Y', strtotime($curso['fecha_inicio'])); ?> - 
                    <?php echo date('d/m/Y', strtotime($curso['fecha_fin'])); ?>
                </small>
            </div>
            
            <div class="curso-body">
                <!-- Estadísticas del curso -->
                <div class="stats-row">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $curso['total_inscritos']; ?></span>
                        <span class="stat-label">Inscritos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $curso['aprobados']; ?></span>
                        <span class="stat-label">Aprobados</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $curso['pendientes']; ?></span>
                        <span class="stat-label">En Curso</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $curso['certificados_generados']; ?></span>
                        <span class="stat-label">Certificados</span>
                    </div>
                </div>

                <!-- Advertencia si no hay configuración -->
                <?php if (empty($curso['config_certificado'])): ?>
                    <div class="curso-sin-config">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>⚠️ Curso sin configuración de certificado</strong>
                        <a href="editor_certificado.php?idcurso=<?php echo $curso['idcurso']; ?>" class="btn btn-xs btn-warning">
                            <i class="fa fa-cog"></i> Configurar Certificado
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Lista de alumnos -->
                <h5><i class="fa fa-users"></i> Alumnos del Curso</h5>
                <?php if (empty($alumnos_por_curso[$curso['idcurso']])): ?>
                    <p class="text-muted">No hay alumnos inscritos en este curso.</p>
                <?php else: ?>
                    <?php foreach ($alumnos_por_curso[$curso['idcurso']] as $alumno): ?>
                        <div class="alumno-row">
                            <div class="alumno-info">
                                <div class="alumno-details">
                                    <strong><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?></strong>
                                    <br>
                                    <small><?php echo htmlspecialchars($alumno['email']); ?> | 
                                    Tel: <?php echo htmlspecialchars($alumno['telefono']); ?></small>
                                    <br>
                                    <span class="estado-badge estado-<?php echo strtolower($alumno['estado']); ?>">
                                        <?php echo $alumno['estado']; ?>
                                    </span>
                                    <?php if ($alumno['estado'] === 'Aprobado'): ?>
                                        <span class="estado-badge estado-generado">
                                            <i class="fa fa-certificate"></i> Listo para Certificar
                                        </span>
                                    <?php endif; ?>
                                    <br>
                                    <small>
                                        Inscripción: <?php echo date('d/m/Y', strtotime($alumno['fecha_inscripcion'])); ?>
                                        <?php if ($alumno['fecha_aprobacion']): ?>
                                            | Aprobación: <?php echo date('d/m/Y', strtotime($alumno['fecha_aprobacion'])); ?>
                                        <?php endif; ?>
                                        <?php if ($alumno['nota_final']): ?>
                                            | Nota: <?php echo $alumno['nota_final']; ?>
                                        <?php endif; ?>
                                    </small>
                                    <br>
                                    <strong>Progreso hacia certificación: <?php echo $alumno['progreso_certificacion']; ?>%</strong>
                                    <div class="progreso-bar">
                                        <div class="progreso-fill" style="width: <?php echo $alumno['progreso_certificacion']; ?>%; 
                                            background: <?php echo $alumno['progreso_certificacion'] >= 100 ? '#28a745' : 
                                            ($alumno['progreso_certificacion'] >= 80 ? '#ffc107' : '#dc3545'); ?>;">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alumno-actions">
                                    <?php if ($alumno['estado'] === 'Aprobado' && !empty($curso['config_certificado'])): ?>
                                        <button class="btn-generar btn-deshabilitado" disabled>
                                            <i class="fa fa-cog"></i> En Configuración
                                        </button>
                                        <br>
                                        <small>Funcionalidad en desarrollo</small>
                                    <?php else: ?>
                                        <button class="btn-generar btn-deshabilitado" disabled>
                                            <i class="fa fa-ban"></i> No Disponible
                                        </button>
                                        <br>
                                        <small>
                                            <?php if ($alumno['estado'] !== 'Aprobado'): ?>
                                                Falta aprobar al alumno
                                            <?php elseif (empty($curso['config_certificado'])): ?>
                                                Falta configurar certificado
                                            <?php endif; ?>
                                        </small>
                                    <?php endif; ?>
                                    <br>
                                    <!-- Botón para Previsualizar (corregido) -->
                                    <button class="btn-ver" onclick="previsualizarCertificadoDesdeBoton(<?php echo $curso['idcurso']; ?>, <?php echo $alumno['idcliente']; ?>)">
                                      <i class="fa fa-eye"></i> Previsualizar
                                    </button>
                                    <!-- Miniatura mejorada -->
                                    <?php if (!empty($curso['config_certificado'])): ?>
                                        <br>
                                        <div class="certificado-preview-mini" style="max-width:220px; margin:auto;">
                                            <?php 
                                            $config_certificado = json_decode($curso['config_certificado'], true);
                                            if ($config_certificado && isset($config_certificado['campos'])): 
                                                // Obtener tamaño original del fondo
                                                $imgOriginalW = isset($config_certificado['imagenOriginal']['width']) ? $config_certificado['imagenOriginal']['width'] : 800;
                                                $imgOriginalH = isset($config_certificado['imagenOriginal']['height']) ? $config_certificado['imagenOriginal']['height'] : 600;
                                                $miniW = 200;
                                                $miniH = intval($imgOriginalH * ($miniW / $imgOriginalW)); // Mantener proporción
                                                $scale_x = $miniW / $imgOriginalW;
                                                $scale_y = $miniH / $imgOriginalH;
                                            ?>
                                                <div class="certificado-container" style="
                                                    position: relative; 
                                                    width: <?php echo $miniW; ?>px; 
                                                    height: <?php echo $miniH; ?>px; 
                                                    border: 1px solid #ddd; 
                                                    border-radius: 5px; 
                                                    overflow: hidden;
                                                    background: white;
                                                    margin-top: 10px;
                                                ">
                                                    <?php if (!empty($curso['diseño'])): ?>
                                                        <img src="../assets/uploads/cursos/<?php echo htmlspecialchars($curso['diseño']); ?>" 
                                                             alt="Diseño del Certificado" 
                                                             style="width: 100%; height: 100%; object-fit: cover; position:absolute; left:0; top:0; z-index:1;">
                                                    <?php endif; ?>
                                                    <?php 
                                                    foreach ($config_certificado['campos'] as $campo): 
                                                        if (isset($campo['left']) && isset($campo['top']) && isset($campo['tipo'])): 
                                                            $left_mini = $campo['left'] * $scale_x;
                                                            $top_mini = $campo['top'] * $scale_y;
                                                            $width_mini = isset($campo['width']) ? $campo['width'] * $scale_x : 'auto';
                                                            $height_mini = isset($campo['height']) ? $campo['height'] * $scale_y : 'auto';
                                                            $font_size_mini = (isset($campo['fontSize']) ? $campo['fontSize'] : 14) * min($scale_x, $scale_y);
                                                            $valor = '';
                                                            switch ($campo['tipo']) {
                                                                case 'alumno':
                                                                    $valor = $alumno['nombre'] . ' ' . $alumno['apellido'];
                                                                    break;
                                                                case 'fecha':
                                                                    $valor = date('d/m/Y');
                                                                    break;
                                                                case 'instructor':
                                                                    $valor = 'Instructor';
                                                                    break;
                                                                case 'especialista':
                                                                    $valor = 'Especialista';
                                                                    break;
                                                                case 'firma_instructor':
                                                                    $valor = '<img src="../assets/img/qr_placeholder.png" alt="Firma Instructor" style="max-width:40px; max-height:20px;">';
                                                                    break;
                                                                case 'firma_especialista':
                                                                    $valor = '<img src="../assets/img/qr_placeholder.png" alt="Firma Especialista" style="max-width:40px; max-height:20px;">';
                                                                    break;
                                                                case 'qr':
                                                                    $valor = '<img src="../admin/generar_qr.php?certificado=1&idcurso=' . $curso['idcurso'] . '&idalumno=' . $alumno['idcliente'] . '" alt="QR" style="width:40px; height:40px;">';
                                                                    break;
                                                                default:
                                                                    $valor = htmlspecialchars($campo['texto'] ?? '');
                                                            }
                                                    ?>
                                                        <div style="
                                                            position: absolute;
                                                            left: <?php echo $left_mini; ?>px;
                                                            top: <?php echo $top_mini; ?>px;
                                                            <?php if($width_mini !== 'auto'): ?>width: <?php echo $width_mini; ?>px;<?php endif; ?>
                                                            <?php if($height_mini !== 'auto'): ?>height: <?php echo $height_mini; ?>px;<?php endif; ?>
                                                            font-size: <?php echo $font_size_mini; ?>px;
                                                            font-family: <?php echo isset($campo['fontFamily']) ? $campo['fontFamily'] : 'Arial'; ?>;
                                                            color: <?php echo isset($campo['color']) ? $campo['color'] : '#000000'; ?>;
                                                            text-align: <?php echo isset($campo['textAlign']) ? $campo['textAlign'] : 'left'; ?>;
                                                            font-weight: <?php echo isset($campo['fontWeight']) ? $campo['fontWeight'] : 'normal'; ?>;
                                                            font-style: <?php echo isset($campo['fontStyle']) ? $campo['fontStyle'] : 'normal'; ?>;
                                                            transform: <?php echo isset($campo['rotation']) ? 'rotate(' . $campo['rotation'] . 'deg)' : 'none'; ?>;
                                                            opacity: <?php echo isset($campo['opacity']) ? $campo['opacity'] : 1; ?>;
                                                            max-width: 80%;
                                                            overflow: hidden;
                                                            text-overflow: ellipsis;
                                                            white-space: nowrap;
                                                            z-index:2;
                                                        ">
                                                            <?php echo $valor; ?>
                                                        </div>
                                                    <?php endif; endforeach; ?>
                                                </div>
                                                <small style="display: block; margin-top: 5px; color: #666;">
                                                    Vista previa del certificado
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($cursos)): ?>
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            No hay cursos activos con alumnos inscritos.
        </div>
    <?php endif; ?>

    <script>
function previsualizarCertificadoDesdeBoton(idcurso, idalumno) {
  const previewWindow = window.open('', '_blank', 'width=900,height=600,scrollbars=yes');
  previewWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Previsualización de Certificado</title>
      <style>
        body { font-family: Arial; text-align: center; padding: 50px; }
        .loading { font-size: 18px; color: #555; }
      </style>
    </head>
    <body>
      <div class="loading">⏳ Cargando previsualización...</div>
    </body>
    </html>
  `);
  fetch(`previsualizar_certificado_final.php?idcurso=${idcurso}&idalumno=${idalumno}&modo=preview`)
    .then(response => response.text())
    .then(html => {
      previewWindow.document.open();
      previewWindow.document.write(html);
      previewWindow.document.close();
    })
    .catch(error => {
      previewWindow.document.body.innerHTML = `
        <div style="color:red; padding:20px;">❌ Error al cargar: ${error.message}</div>
      `;
    });
}
</script>

</section>

<?php require_once('footer.php'); ?> 