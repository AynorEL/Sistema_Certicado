<?php
require_once('admin/inc/config.php');
header('Content-Type: text/html; charset=utf-8');

// Verificar si se proporcionó un código de validación
$codigo_validacion = null;

// Verificar si viene del parámetro 'codigo' (enlace directo)
if (isset($_GET['codigo'])) {
    $codigo_validacion = $_GET['codigo'];
}
// Verificar si viene del parámetro 'qr' (escaneo de QR)
elseif (isset($_GET['qr'])) {
    $qr_data = $_GET['qr'];
    
    // Si el QR contiene una URL completa, extraer el código
    if (strpos($qr_data, 'verificar-certificado.php?codigo=') !== false) {
        // Extraer el código de la URL
        $parts = parse_url($qr_data);
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query_params);
            if (isset($query_params['codigo'])) {
                $codigo_validacion = $query_params['codigo'];
            }
        }
    } else {
        // Si es solo el código directo
        $codigo_validacion = $qr_data;
    }
}

// Si no se pudo obtener el código de validación
if (!$codigo_validacion) {
    echo "<div style='text-align: center; padding: 50px; font-family: Arial, sans-serif;'>
            <h2>❌ Código de validación no válido</h2>
            <p>No se proporcionó un código de validación válido para verificar el certificado.</p>
            <p><strong>URL recibida:</strong> " . htmlspecialchars($_SERVER['REQUEST_URI']) . "</p>
            <a href='index.php' style='color: #007bff; text-decoration: none;'>← Volver al inicio</a>
          </div>";
    exit;
}

try {
    // Buscar el certificado en la base de datos
    $stmt = $pdo->prepare("
        SELECT cg.*, 
               cl.nombre as nombre_cliente, cl.apellido as apellido_cliente, cl.dni, cl.email,
               cu.nombre_curso, cu.duracion, cu.diseño, cu.config_certificado,
               i.nombre as nombre_instructor, i.apellido as apellido_instructor, i.firma_digital as firma_instructor,
               e.nombre as nombre_especialista, e.apellido as apellido_especialista, e.firma_especialista as firma_especialista,
               ins.fecha_aprobacion, ins.nota_final
        FROM certificado_generado cg
        JOIN cliente cl ON cg.idcliente = cl.idcliente
        JOIN curso cu ON cg.idcurso = cu.idcurso
        LEFT JOIN instructor i ON cu.idinstructor = i.idinstructor
        LEFT JOIN especialista e ON cu.idespecialista = e.idespecialista
        LEFT JOIN inscripcion ins ON cg.idcliente = ins.idcliente AND cg.idcurso = ins.idcurso
        WHERE cg.codigo_validacion = ? AND cg.estado = 'Activo'
    ");
    $stmt->execute([$codigo_validacion]);
    $certificado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$certificado) {
        throw new Exception("Certificado no encontrado o inválido");
    }

    // Actualizar fecha de verificación
    $stmt = $pdo->prepare("
        UPDATE certificado_generado 
        SET fecha_verificacion = NOW(), 
            ip_verificacion = ?, 
            user_agent_verificacion = ?
        WHERE codigo_validacion = ?
    ");
    $stmt->execute([
        $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        $codigo_validacion
    ]);

    // Preparar datos para la visualización
    $datos_certificado = [
        'nombre_cliente' => $certificado['nombre_cliente'] . ' ' . $certificado['apellido_cliente'],
        'dni' => $certificado['dni'],
        'email' => $certificado['email'],
        'nombre_curso' => $certificado['nombre_curso'],
        'duracion' => $certificado['duracion'],
        'fecha_aprobacion' => $certificado['fecha_aprobacion'] ? date('d/m/Y', strtotime($certificado['fecha_aprobacion'])) : 'N/A',
        'nota_final' => $certificado['nota_final'] ?? 'N/A',
        'fecha_generacion' => date('d/m/Y H:i', strtotime($certificado['fecha_generacion'])),
        'codigo_validacion' => $certificado['codigo_validacion'],
        'nombre_archivo_qr' => $certificado['codigo_qr']
    ];

    // Datos del instructor
    if ($certificado['nombre_instructor']) {
        $datos_certificado['instructor'] = $certificado['nombre_instructor'] . ' ' . $certificado['apellido_instructor'];
        if ($certificado['firma_instructor']) {
            $datos_certificado['firma_instructor'] = 'assets/uploads/firmas/' . $certificado['firma_instructor'];
        }
    }

    // Datos del especialista (solo si existen todos los datos necesarios)
    if (!empty($certificado['nombre_especialista']) && !empty($certificado['apellido_especialista'])) {
        $datos_certificado['especialista'] = $certificado['nombre_especialista'] . ' ' . $certificado['apellido_especialista'];
        if (!empty($certificado['firma_especialista'])) {
            $datos_certificado['firma_especialista'] = 'assets/uploads/firmas/' . $certificado['firma_especialista'];
        }
    }

    // Configuración del certificado
    $config_certificado = null;
    if (!empty($certificado['config_certificado'])) {
        $config_certificado = json_decode($certificado['config_certificado'], true);
    }

    // Obtener configuración del QR
    $qr_config = $config_certificado['qr_config'] ?? [
        'size' => 300,
        'color' => '#000000',
        'bgColor' => '#FFFFFF',
        'margin' => 0,
        'logoEnabled' => false
    ];

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
    <title>Certificado Verificado - <?php echo htmlspecialchars($datos_certificado['nombre_cliente']); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #fff;
        }
        .certificado-container {
            width: 2000px;
            height: 1414px;
            position: relative;
            background: white;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .certificate-background {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
        }
        .certificate-field {
            position: absolute;
            z-index: 10;
            word-wrap: break-word;
            overflow: hidden;
        }
        .certificate-field img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .qr-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 20;
        }
        @media print {
            body {
                background: white !important;
            }
            .no-print {
                display: none !important;
            }
            .certificado-container {
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin: 20px 0;">
        <a href="index.php" style="color: #007bff; text-decoration: none; font-size: 18px;">
            ← Volver al inicio
        </a>
        <a href="generar-certificado.php?codigo=<?php echo urlencode($datos_certificado['codigo_validacion']); ?>" 
           style="margin-left: 30px; color: #28a745; text-decoration: none; font-size: 18px;">
            <i class="fa fa-download"></i> Descargar PDF
        </a>
    </div>
    <div class="certificado-container">
        <?php if (!empty($certificado['diseño'])): ?>
            <img class="certificate-background" src="assets/uploads/cursos/<?php echo htmlspecialchars($certificado['diseño']); ?>" alt="Diseño del Certificado">
        <?php else: ?>
            <div class="no-background">
                <i class="fa fa-image fa-3x"></i>
                <p>No hay imagen de fondo asignada</p>
            </div>
        <?php endif; ?>
        <?php if ($config_certificado && isset($config_certificado['campos'])): ?>
            <?php foreach ($config_certificado['campos'] as $campo): ?>
                <div class="certificate-field" style="
                    left: <?php echo $campo['left']; ?>px;
                    top: <?php echo $campo['top']; ?>px;
                    width: <?php echo $campo['width']; ?>px;
                    height: <?php echo $campo['height']; ?>px;
                    font-family: <?php echo $campo['fontFamily'] ?? 'Arial'; ?>;
                    font-size: <?php echo $campo['fontSize'] ?? 14; ?>px;
                    color: <?php echo $campo['color'] ?? '#000000'; ?>;
                    text-align: <?php echo $campo['textAlign'] ?? 'left'; ?>;
                    font-weight: <?php echo $campo['fontWeight'] ?? 'normal'; ?>;
                    font-style: <?php echo $campo['fontStyle'] ?? 'normal'; ?>;
                ">
                    <?php if ($campo['tipo'] === 'firma_instructor' && isset($datos_certificado['firma_instructor'])): ?>
                        <img src="<?php echo htmlspecialchars($datos_certificado['firma_instructor']); ?>" alt="Firma Instructor" style="max-width: 120px; max-height: 60px; object-fit: contain;">
                    <?php elseif ($campo['tipo'] === 'firma_especialista' && isset($datos_certificado['firma_especialista'])): ?>
                        <img src="<?php echo htmlspecialchars($datos_certificado['firma_especialista']); ?>" alt="Firma Especialista" style="max-width: 120px; max-height: 60px; object-fit: contain;">
                    <?php elseif ($campo['tipo'] === 'qr'): ?>
                        <div style="position: relative; width: 100%; height: 100%;">
                            <img src="admin/img/qr/<?php echo htmlspecialchars($datos_certificado['nombre_archivo_qr']); ?>" alt="QR" style="width: 100%; height: 100%; object-fit: contain;">
                            <?php if ($qr_config['logoEnabled'] && file_exists(__DIR__ . '/admin/img/logo.png')): ?>
                                <img src="admin/img/logo.png" alt="Logo" class="qr-logo" style="width: <?php echo $qr_config['size'] * 0.2; ?>px;">
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php 
                        $texto = $campo['texto'] ?? '';
                        $texto = str_replace('NOMBRE DEL ALUMNO', $datos_certificado['nombre_cliente'], $texto);
                        $texto = str_replace('FECHA DE EMISIÓN', $datos_certificado['fecha_aprobacion'], $texto);
                        $texto = str_replace('NOMBRE DEL INSTRUCTOR', $datos_certificado['instructor'] ?? '', $texto);
                        $texto = str_replace('NOMBRE DEL ESPECIALISTA', $datos_certificado['especialista'] ?? '', $texto);
                        $texto = str_replace('NOTA_FINAL', $datos_certificado['nota_final'], $texto);
                        echo htmlspecialchars($texto); 
                        ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html> 
