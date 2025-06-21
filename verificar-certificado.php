<?php
require_once('admin/inc/config.php');
header('Content-Type: text/html; charset=utf-8');

// Verificar si se proporcionó un código QR
if (!isset($_GET['qr'])) {
    echo "<div style='text-align: center; padding: 50px; font-family: Arial, sans-serif;'>
            <h2>❌ Código QR no válido</h2>
            <p>No se proporcionó un código QR válido para verificar el certificado.</p>
            <a href='index.php' style='color: #007bff; text-decoration: none;'>← Volver al inicio</a>
          </div>";
    exit;
}

$qr_code = $_GET['qr'];

try {
    // Decodificar el código QR (asumiendo que contiene datos en formato JSON o similar)
    // Por ahora, vamos a usar un formato simple: idcurso-idalumno-fecha
    $qr_parts = explode('-', $qr_code);
    
    if (count($qr_parts) < 3) {
        throw new Exception("Formato de código QR inválido");
    }
    
    $idcurso = (int) $qr_parts[0];
    $idalumno = (int) $qr_parts[1];
    $fecha = $qr_parts[2];
    
    // Obtener datos del curso y configuración
    $stmt = $pdo->prepare("
        SELECT c.*, i.nombre as nombre_instructor, i.apellido as apellido_instructor,
               i.firma_digital as firma_instructor
        FROM curso c
        LEFT JOIN instructor i ON c.idinstructor = i.idinstructor
        WHERE c.idcurso = ?
    ");
    $stmt->execute([$idcurso]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$curso) {
        throw new Exception("Curso no encontrado");
    }

    // Obtener datos del alumno
    $stmt = $pdo->prepare("
        SELECT c.idcliente, c.nombre, c.apellido, c.dni, c.email
        FROM cliente c
        WHERE c.idcliente = ?
    ");
    $stmt->execute([$idalumno]);
    $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$alumno) {
        throw new Exception("Alumno no encontrado");
    }

    // Verificar que el alumno esté inscrito y aprobado en el curso
    $stmt = $pdo->prepare("
        SELECT estado, fecha_aprobacion, nota_final
        FROM inscripcion
        WHERE idcliente = ? AND idcurso = ?
    ");
    $stmt->execute([$idalumno, $idcurso]);
    $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscripcion) {
        throw new Exception("El alumno no está inscrito en este curso");
    }

    if ($inscripcion['estado'] !== 'Aprobado') {
        throw new Exception("El alumno no ha aprobado este curso");
    }

    // Obtener configuración guardada del certificado
    $config_certificado = null;
    if (!empty($curso['config_certificado'])) {
        $config_certificado = json_decode($curso['config_certificado'], true);
    }

    if (!$config_certificado) {
        throw new Exception("Este curso no tiene configuración de certificado");
    }

    // Obtener datos del instructor del curso
    $instructor = null;
    if ($curso['idinstructor']) {
        $stmt = $pdo->prepare("
            SELECT idinstructor, nombre, apellido, especialidad, firma_digital
            FROM instructor
            WHERE idinstructor = ?
        ");
        $stmt->execute([$curso['idinstructor']]);
        $instructor = $stmt->fetch(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    echo "<div style='text-align: center; padding: 50px; font-family: Arial, sans-serif;'>
            <h2>❌ Error al verificar certificado</h2>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <a href='index.php' style='color: #007bff; text-decoration: none;'>← Volver al inicio</a>
          </div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado Verificado - <?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <style>
        body { 
            margin: 0; 
            padding: 0; 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .certificado-container { 
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            min-height: 100vh;
        }
        .certificado-preview { 
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border-radius: 10px;
            overflow: hidden;
            max-width: 90%;
            max-height: 90vh;
        }
        .certificado-header {
            background: #28a745;
            color: white;
            padding: 15px 20px;
            text-align: center;
        }
        .certificado-header h1 {
            margin: 0;
            font-size: 1.5em;
        }
        .certificado-body {
            padding: 20px;
            text-align: center;
        }
        .campo-preview {
            position: absolute;
            padding: 0;
            margin: 0;
            min-width: 0;
            min-height: 0;
            word-wrap: break-word;
            overflow: hidden;
            box-sizing: border-box;
            border: none !important;
            background: transparent !important;
            outline: none !important;
        }
        .firma-preview {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .firma-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .qr-preview img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .verification-badge {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 25px;
            font-size: 14px;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .verification-badge i {
            margin-right: 5px;
        }
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.9);
            color: #333;
            border: none;
            padding: 10px 15px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .back-btn:hover {
            background: white;
            text-decoration: none;
            color: #333;
        }
        .certificado-info {
            background: rgba(255,255,255,0.95);
            border-radius: 10px;
            padding: 20px;
            margin: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .certificado-info h3 {
            color: #28a745;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="verification-badge">
        <i class="fa fa-check-circle"></i> Certificado Verificado
    </div>
    
    <a href="index.php" class="back-btn">
        <i class="fa fa-arrow-left"></i> Volver al inicio
    </a>
    
    <div class="certificado-container">
        <div class="certificado-preview">
            <div class="certificado-header">
                <h1><i class="fa fa-certificate"></i> Certificado Verificado</h1>
                <p>Este certificado ha sido verificado y es válido</p>
            </div>
            
            <div class="certificado-body">
                <div id="certificado-content" style="position: relative; display: inline-block;">
                    <?php if (!empty($curso['diseño'])): ?>
                        <img id="fondo-certificado" src="assets/uploads/cursos/<?php echo htmlspecialchars($curso['diseño']); ?>" 
                             alt="Diseño del Certificado" style="display: block; max-width: 100%; height: auto;">
                    <?php endif; ?>
                    
                    <?php if ($config_certificado && isset($config_certificado['campos'])): ?>
                        <?php foreach ($config_certificado['campos'] as $campo): ?>
                            <?php if (isset($campo['left']) && isset($campo['top']) && isset($campo['tipo'])): ?>
                                <?php
                                // Obtener el valor del campo según el tipo
                                $valor = '';
                                switch ($campo['tipo']) {
                                    case 'nombre_alumno':
                                        $valor = $alumno['nombre'] . ' ' . $alumno['apellido'];
                                        break;
                                    case 'fecha':
                                        $valor = date('d/m/Y', strtotime($fecha));
                                        break;
                                    case 'instructor':
                                        if (isset($campo['idinstructor'])) {
                                            $stmt_instructor = $pdo->prepare("SELECT nombre, apellido FROM instructor WHERE idinstructor = ?");
                                            $stmt_instructor->execute([$campo['idinstructor']]);
                                            $instructor_data = $stmt_instructor->fetch();
                                            if ($instructor_data) {
                                                $valor = $instructor_data['nombre'] . ' ' . $instructor_data['apellido'];
                                            }
                                        }
                                        break;
                                    case 'especialista':
                                        if (isset($campo['idespecialista'])) {
                                            $stmt_especialista = $pdo->prepare("SELECT nombre, apellido FROM especialista WHERE idespecialista = ?");
                                            $stmt_especialista->execute([$campo['idespecialista']]);
                                            $especialista_data = $stmt_especialista->fetch();
                                            if ($especialista_data) {
                                                $valor = $especialista_data['nombre'] . ' ' . $especialista_data['apellido'];
                                            }
                                        }
                                        break;
                                    case 'qr':
                                        $valor = 'QR';
                                        break;
                                    default:
                                        $valor = $campo['texto'] ?? '';
                                }
                                ?>
                                <div class="campo-preview" style="
                                    left: <?php echo $campo['left']; ?>px; 
                                    top: <?php echo $campo['top']; ?>px; 
                                    width: <?php echo isset($campo['width']) ? $campo['width'] . 'px' : 'auto'; ?>; 
                                    height: <?php echo isset($campo['width']) ? $campo['height'] . 'px' : 'auto'; ?>; 
                                    font-size: <?php echo isset($campo['fontSize']) ? $campo['fontSize'] . 'px' : '14px'; ?>; 
                                    font-family: <?php echo isset($campo['fontFamily']) ? $campo['fontFamily'] : 'Arial'; ?>; 
                                    color: <?php echo isset($campo['color']) ? $campo['color'] : '#000000'; ?>; 
                                    text-align: <?php echo isset($campo['textAlign']) ? $campo['textAlign'] : 'left'; ?>; 
                                    font-weight: <?php echo isset($campo['fontWeight']) ? $campo['fontWeight'] : 'normal'; ?>; 
                                    font-style: <?php echo isset($campo['fontStyle']) ? $campo['fontStyle'] : 'normal'; ?>; 
                                    text-decoration: <?php echo isset($campo['textDecoration']) ? $campo['textDecoration'] : 'none'; ?>; 
                                    line-height: <?php echo isset($campo['lineHeight']) ? $campo['lineHeight'] : 'normal'; ?>; 
                                    letter-spacing: <?php echo isset($campo['letterSpacing']) ? $campo['letterSpacing'] . 'px' : 'normal'; ?>; 
                                    transform: <?php echo isset($campo['rotation']) ? 'rotate(' . $campo['rotation'] . 'deg)' : 'none'; ?>; 
                                    transform-origin: center center;
                                    <?php if (isset($campo['opacity'])): ?>
                                        opacity: <?php echo $campo['opacity']; ?>;
                                    <?php endif; ?>
                                    <?php if (isset($campo['shadowColor']) && isset($campo['shadowBlur'])): ?>
                                        text-shadow: <?php echo $campo['shadowOffsetX'] ?? 0; ?>px <?php echo $campo['shadowOffsetY'] ?? 0; ?>px <?php echo $campo['shadowBlur']; ?>px <?php echo $campo['shadowColor']; ?>;
                                    <?php endif; ?>
                                ">
                                    <?php if ($campo['tipo'] === 'qr'): ?>
                                        <div class="qr-preview">
                                            <img src="admin/generar_qr.php?certificado=1&idcurso=<?php echo $idcurso; ?>&idalumno=<?php echo $idalumno; ?>&fecha=<?php echo $fecha; ?>" 
                                                 alt="Código QR del Certificado">
                                        </div>
                                    <?php elseif ($campo['tipo'] === 'firma_instructor' && $instructor && !empty($instructor['firma_digital'])): ?>
                                        <div class="firma-preview">
                                            <img src="admin/assets/uploads/firmas/<?php echo htmlspecialchars($instructor['firma_digital']); ?>" 
                                                 alt="Firma del Instructor">
                                        </div>
                                    <?php elseif ($campo['tipo'] === 'firma_especialista' && isset($campo['idespecialista'])): ?>
                                        <?php
                                        $stmt_especialista = $pdo->prepare("SELECT firma_especialista FROM especialista WHERE idespecialista = ?");
                                        $stmt_especialista->execute([$campo['idespecialista']]);
                                        $especialista = $stmt_especialista->fetch();
                                        if ($especialista && !empty($especialista['firma_especialista'])):
                                        ?>
                                            <div class="firma-preview">
                                                <img src="admin/assets/uploads/firmas/<?php echo htmlspecialchars($especialista['firma_especialista']); ?>" 
                                                     alt="Firma del Especialista">
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($valor); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="certificado-info">
                    <h3><i class="fa fa-info-circle"></i> Información del Certificado</h3>
                    <div class="info-row">
                        <span class="info-label">Estudiante:</span>
                        <span class="info-value"><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">DNI:</span>
                        <span class="info-value"><?php echo htmlspecialchars($alumno['dni']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Curso:</span>
                        <span class="info-value"><?php echo htmlspecialchars($curso['nombre_curso']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha de Aprobación:</span>
                        <span class="info-value"><?php echo $inscripcion['fecha_aprobacion'] ? date('d/m/Y', strtotime($inscripcion['fecha_aprobacion'])) : 'N/A'; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nota Final:</span>
                        <span class="info-value"><?php echo $inscripcion['nota_final'] ? $inscripcion['nota_final'] : 'N/A'; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Código QR:</span>
                        <span class="info-value"><?php echo htmlspecialchars($qr_code); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-2.2.4.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html> 