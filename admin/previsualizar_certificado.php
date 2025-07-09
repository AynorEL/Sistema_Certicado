<?php
require_once('inc/config.php');
header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['idcurso']) || !isset($_GET['idalumno'])) {
    echo "<div style='color: red; padding: 20px;'>Parámetros requeridos no proporcionados</div>";
    exit;
}

$idcurso = (int) $_GET['idcurso'];
$idalumno = (int) $_GET['idalumno'];
$idinstructor = isset($_GET['idinstructor']) ? (int) $_GET['idinstructor'] : null;
$idespecialista = isset($_GET['idespecialista']) ? (int) $_GET['idespecialista'] : null;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

try {
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
        echo "<div style='color: red; padding: 20px;'>Curso no encontrado</div>";
        exit;
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
        echo "<div style='color: red; padding: 20px;'>Alumno no encontrado</div>";
        exit;
    }

    // Obtener datos del instructor seleccionado o del instructor del curso
    $instructor = null;
    if ($idinstructor) {
        $stmt = $pdo->prepare("
            SELECT idinstructor, nombre, apellido, especialidad, firma_digital
            FROM instructor
            WHERE idinstructor = ?
        ");
        $stmt->execute([$idinstructor]);
        $instructor = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($curso['idinstructor']) {
        // Si no se especifica instructor, usar el del curso
        $stmt = $pdo->prepare("
            SELECT idinstructor, nombre, apellido, especialidad, firma_digital
            FROM instructor
            WHERE idinstructor = ?
        ");
        $stmt->execute([$curso['idinstructor']]);
        $instructor = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener datos del especialista seleccionado
    $especialista = null;
    if ($idespecialista) {
        $stmt = $pdo->prepare("
            SELECT idespecialista, nombre, apellido, especialidad, firma_especialista
            FROM especialista
            WHERE idespecialista = ?
        ");
        $stmt->execute([$idespecialista]);
        $especialista = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener configuración guardada del certificado
    $config_certificado = null;
    if (!empty($curso['config_certificado'])) {
        $config_certificado = json_decode($curso['config_certificado'], true);
    }

    // Generar URL del QR
    $qr_url = "generar_qr.php?certificado=1&idcurso=$idcurso&idalumno=$idalumno&fecha=$fecha";

} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px;'>Error: " . $e->getMessage() . "</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previsualización del Certificado</title>
    <style>
        body { 
            margin: 0; 
            padding: 0; 
            font-family: Arial, sans-serif; 
            background-color: #f5f5f5;
            text-align: center;
        }
        .certificado-preview { 
            display: inline-block;
            position: relative;
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin: 20px auto;
        }
        .campo-preview {
            position: absolute;
            /* En previsualización NO queremos padding ni bordes */
            padding: 0;
            margin: 0;
            min-width: 0;
            min-height: 0;
            word-wrap: break-word;
            overflow: hidden;
            box-sizing: border-box;
            /* Eliminar cualquier borde o fondo */
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
        .close-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            z-index: 1000;
        }
        .close-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <button class="close-btn" onclick="window.close()">✕ Cerrar</button>
    
    <div id="certificado-container" class="certificado-preview">
        <?php if (!empty($curso['diseño'])): ?>
            <img id="fondo-certificado" src="../assets/uploads/cursos/<?php echo htmlspecialchars($curso['diseño']); ?>" 
                 alt="Diseño del Certificado" style="display: block; max-width: 100%; height: auto;">
        <?php endif; ?>
        
        <?php if ($config_certificado && isset($config_certificado['campos'])): ?>
            <?php foreach ($config_certificado['campos'] as $campo): ?>
                <?php if (isset($campo['left']) && isset($campo['top']) && isset($campo['tipo'])): ?>
                    <div class="campo-preview" style="
                        left: <?php echo $campo['left']; ?>px; 
                        top: <?php echo $campo['top']; ?>px; 
                        width: <?php echo isset($campo['width']) ? $campo['width'] . 'px' : 'auto'; ?>; 
                        height: <?php echo isset($campo['height']) ? $campo['height'] . 'px' : 'auto'; ?>; 
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
                        <?php
                        switch ($campo['tipo']) {
                            case 'alumno':
                                echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']);
                                break;
                            case 'fecha':
                                echo date('d/m/Y', strtotime($fecha));
                                break;
                            case 'instructor':
                                if ($instructor) {
                                    echo htmlspecialchars($instructor['nombre'] . ' ' . $instructor['apellido']);
                                } else {
                                    echo 'INSTRUCTOR NO SELECCIONADO';
                                }
                                break;
                            case 'especialista':
                                if ($especialista) {
                                    echo htmlspecialchars($especialista['nombre'] . ' ' . $especialista['apellido']);
                                } else {
                                    echo 'ESPECIALISTA NO SELECCIONADO';
                                }
                                break;
                            case 'firma_instructor':
                                if ($instructor && $instructor['firma_digital']) {
                                    echo '<div class="firma-preview"><img src="../assets/uploads/firmas/' . htmlspecialchars($instructor['firma_digital']) . '" alt="Firma Instructor"></div>';
                                } else {
                                    echo '<div class="firma-preview" style="text-align: center; min-width: 120px; min-height: 40px; display: flex; align-items: center; justify-content: center; font-size: 12px;">Firma Instructor</div>';
                                }
                                break;
                            case 'firma_especialista':
                                if ($especialista && $especialista['firma_especialista']) {
                                    echo '<div class="firma-preview"><img src="../assets/uploads/firmas/' . htmlspecialchars($especialista['firma_especialista']) . '" alt="Firma Especialista"></div>';
                                } else {
                                    echo '<div class="firma-preview" style="text-align: center; min-width: 120px; min-height: 40px; display: flex; align-items: center; justify-content: center; font-size: 12px;">Firma Especialista</div>';
                                }
                                break;
                            case 'qr':
                                echo '<img src="' . $qr_url . '" alt="QR" class="qr-preview">';
                                break;
                            default:
                                echo htmlspecialchars($campo['texto'] ?? 'Campo desconocido');
                        }
                        ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php elseif ($config_certificado && is_array($config_certificado)): ?>
            <!-- Estructura antigua para compatibilidad -->
            <?php foreach ($config_certificado as $campo): ?>
                <?php if (isset($campo['left']) && isset($campo['top']) && isset($campo['tipo'])): ?>
                    <div class="campo-preview" style="left: <?php echo $campo['left']; ?>px; top: <?php echo $campo['top']; ?>px;">
                        <?php
                        switch ($campo['tipo']) {
                            case 'alumno':
                                echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']);
                                break;
                            case 'fecha':
                                echo date('d/m/Y', strtotime($fecha));
                                break;
                            case 'instructor':
                                if ($instructor) {
                                    echo htmlspecialchars($instructor['nombre'] . ' ' . $instructor['apellido']);
                                } else {
                                    echo 'INSTRUCTOR NO SELECCIONADO';
                                }
                                break;
                            case 'especialista':
                                if ($especialista) {
                                    echo htmlspecialchars($especialista['nombre'] . ' ' . $especialista['apellido']);
                                } else {
                                    echo 'ESPECIALISTA NO SELECCIONADO';
                                }
                                break;
                            case 'firma_instructor':
                                if ($instructor && $instructor['firma_digital']) {
                                    echo '<div class="firma-preview"><img src="../assets/uploads/firmas/' . htmlspecialchars($instructor['firma_digital']) . '" alt="Firma Instructor"></div>';
                                } else {
                                    echo '<div class="firma-preview" style="text-align: center; min-width: 120px; min-height: 40px; display: flex; align-items: center; justify-content: center; font-size: 12px;">Firma Instructor</div>';
                                }
                                break;
                            case 'firma_especialista':
                                if ($especialista && $especialista['firma_especialista']) {
                                    echo '<div class="firma-preview"><img src="../assets/uploads/firmas/' . htmlspecialchars($especialista['firma_especialista']) . '" alt="Firma Especialista"></div>';
                                } else {
                                    echo '<div class="firma-preview" style="text-align: center; min-width: 120px; min-height: 40px; display: flex; align-items: center; justify-content: center; font-size: 12px;">Firma Especialista</div>';
                                }
                                break;
                            case 'qr':
                                echo '<img src="' . $qr_url . '" alt="QR" class="qr-preview">';
                                break;
                            default:
                                echo htmlspecialchars($campo['texto'] ?? 'Campo desconocido');
                        }
                        ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #666;">
                <h3>No hay diseño configurado</h3>
                <p>Configura el diseño del certificado en el editor</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Ajustar el tamaño del contenedor según la imagen de fondo
        window.addEventListener('load', function() {
            const fondoImg = document.getElementById('fondo-certificado');
            if (fondoImg) {
                fondoImg.onload = function() {
                    // El contenedor se ajustará automáticamente al tamaño de la imagen
                    document.getElementById('certificado-container').style.width = this.naturalWidth + 'px';
                    document.getElementById('certificado-container').style.height = this.naturalHeight + 'px';
                };
                
                // Si la imagen ya está cargada
                if (fondoImg.complete) {
                    document.getElementById('certificado-container').style.width = fondoImg.naturalWidth + 'px';
                    document.getElementById('certificado-container').style.height = fondoImg.naturalHeight + 'px';
                }
            }
        });
    </script>
</body>
</html> 