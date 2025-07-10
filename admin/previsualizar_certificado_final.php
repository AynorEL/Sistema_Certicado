<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Color\Color;

$idcurso = isset($_GET['idcurso']) ? (int)$_GET['idcurso'] : 0;
$idalumno = isset($_GET['idalumno']) ? (int)$_GET['idalumno'] : 1; // Por defecto alumno 1
$modo = isset($_GET['modo']) ? $_GET['modo'] : 'preview'; // 'preview' o 'final'

if ($idcurso <= 0) {
    die('ID de curso inválido');
}

// Obtener información del curso
$stmt = $pdo->prepare("SELECT nombre_curso, diseño, config_certificado FROM curso WHERE idcurso = ?");
$stmt->execute([$idcurso]);
$curso = $stmt->fetch();

if (!$curso) {
    die('Curso no encontrado');
}

$diseño = $curso['diseño'];
$configGuardada = $curso['config_certificado'];
$config = json_decode($configGuardada, true);

// Obtener datos del alumno específico
$alumno = 'NOMBRE DEL ALUMNO';
$fecha = date('d/m/Y');
$instructor = 'NOMBRE DEL INSTRUCTOR';
$especialista = 'NOMBRE DEL ESPECIALISTA';
$codigo_validacion = '';
$nombre_archivo_qr = '';
$nota_final = '';
$fecha_aprobacion = '';

// Obtener datos reales de la base de datos
try {
    // Obtener alumno específico
    $stmt = $pdo->prepare("SELECT idcliente, nombre, apellido FROM cliente WHERE idcliente = ?");
    $stmt->execute([$idalumno]);
    $alumnoData = $stmt->fetch();
    if ($alumnoData) {
        $alumno = $alumnoData['nombre'] . ' ' . $alumnoData['apellido'];
    }
    
    // Verificar si ya existe un certificado para este alumno y curso
    $stmt = $pdo->prepare("
        SELECT codigo_validacion, codigo_qr 
        FROM certificado_generado 
        WHERE idcliente = ? AND idcurso = ?
    ");
    $stmt->execute([$idalumno, $idcurso]);
    $certificado_existente = $stmt->fetch();
    
    // Obtener datos de la inscripción (nota, fecha de aprobación)
    $stmt = $pdo->prepare("
        SELECT nota_final, fecha_aprobacion, estado 
        FROM inscripcion 
        WHERE idcliente = ? AND idcurso = ?
    ");
    $stmt->execute([$idalumno, $idcurso]);
    $inscripcion = $stmt->fetch();
    
    if ($modo === 'final' && $certificado_existente) {
        // MODO FINAL: Usar certificado real existente
        $codigo_validacion = $certificado_existente['codigo_validacion'];
        $nombre_archivo_qr = $certificado_existente['codigo_qr'];
        $nota_final = $inscripcion['nota_final'] ?? '';
        $fecha_aprobacion = $inscripcion['fecha_aprobacion'] ? date('d/m/Y', strtotime($inscripcion['fecha_aprobacion'])) : date('d/m/Y');
        
    } elseif ($modo === 'final' && !$certificado_existente) {
        // MODO FINAL pero no existe certificado
        die('Error: No se encontró un certificado generado para este alumno y curso.');
        
    } else {
        // MODO PREVIEW: Generar datos de prueba
        $codigo_validacion = 'PREVIEW-' . $idcurso . '-' . $idalumno . '-' . date('YmdHis');
        $nombre_archivo_qr = $codigo_validacion . '.png';
        $nota_final = '85'; // Nota de ejemplo
        $fecha_aprobacion = date('d/m/Y');
        
        // Generar QR para previsualización usando la configuración guardada
        $url_validacion = "https://" . $_SERVER['HTTP_HOST'] . "/certificado/verificar-certificado.php?codigo=" . $codigo_validacion;
        
        // Obtener configuración del QR del curso
        $qr_config = $config['qr_config'] ?? [
            'size' => 300,
            'color' => '#000000',
            'bgColor' => '#FFFFFF',
            'margin' => 0,
            'logoEnabled' => false
        ];
        
        // Generar QR con configuración personalizada (versión 6)
        $qrCode = new QrCode(
            $url_validacion,
            new Encoding('UTF-8'),
            ErrorCorrectionLevel::High,
            $qr_config['size'],
            $qr_config['margin'],
            RoundBlockSizeMode::Margin,
            new Color(
                hexdec(substr($qr_config['color'], 1, 2)),
                hexdec(substr($qr_config['color'], 3, 2)),
                hexdec(substr($qr_config['color'], 5, 2))
            ),
            new Color(
                hexdec(substr($qr_config['bgColor'], 1, 2)),
                hexdec(substr($qr_config['bgColor'], 3, 2)),
                hexdec(substr($qr_config['bgColor'], 5, 2))
            )
        );
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        // Guardar QR temporal para previsualización
        $ruta_qr = __DIR__ . '/img/qr/' . $nombre_archivo_qr;
        $result->saveToFile($ruta_qr);
    }
    
    // Obtener instructor del curso específico
    $stmt = $pdo->prepare("SELECT i.idinstructor, i.nombre, i.apellido, i.firma_digital 
                          FROM instructor i 
                          JOIN curso c ON i.idinstructor = c.idinstructor 
                          WHERE c.idcurso = ?");
    $stmt->execute([$idcurso]);
    $instructorData = $stmt->fetch();
    if ($instructorData) {
        $instructor = $instructorData['nombre'] . ' ' . $instructorData['apellido'];
        $firmaInstructor = $instructorData['firma_digital'] ? '../assets/uploads/firmas/' . $instructorData['firma_digital'] : '../assets/img/qr_placeholder.png';
    } else {
        $firmaInstructor = '../assets/img/qr_placeholder.png';
    }
    
    // Obtener especialista del curso específico
    $stmt = $pdo->prepare("SELECT e.idespecialista, e.nombre, e.apellido, e.firma_especialista 
                          FROM especialista e 
                          JOIN curso c ON e.idespecialista = c.idespecialista 
                          WHERE c.idcurso = ?");
    $stmt->execute([$idcurso]);
    $especialistaData = $stmt->fetch();
    if ($especialistaData) {
        $especialista = $especialistaData['nombre'] . ' ' . $especialistaData['apellido'];
        $firmaEspecialista = $especialistaData['firma_especialista'] ? '../assets/uploads/firmas/' . $especialistaData['firma_especialista'] : '../assets/img/qr_placeholder.png';
    } else {
        $firmaEspecialista = '../assets/img/qr_placeholder.png';
    }
    
} catch (Exception $e) {
    // Si hay error, usar datos por defecto
    $firmaInstructor = '../assets/img/qr_placeholder.png';
    $firmaEspecialista = '../assets/img/qr_placeholder.png';
}

// Solo usar datos de prueba en modo preview, no en modo final
if ($modo === 'preview' && $config && isset($config['campos'])) {
    foreach ($config['campos'] as $campo) {
        switch ($campo['tipo']) {
            case 'alumno':
                $alumno = $campo['texto'] ?? $alumno;
                break;
            case 'fecha':
                $fecha = $campo['texto'] ?? $fecha;
                break;
            case 'instructor':
                $instructor = $campo['texto'] ?? $instructor;
                break;
            case 'especialista':
                $especialista = $campo['texto'] ?? $especialista;
                break;
        }
    }
}

// Si se solicita el PDF directamente (?pdf=1 en la URL)
if (isset($_GET['pdf']) && $_GET['pdf'] == '1' && isset($_GET['idcurso']) && isset($_GET['idalumno'])) {
    require_once __DIR__ . '/generar_pdf_directo.php';
    $idcliente = (int)$_GET['idalumno'];
    $idcurso = (int)$_GET['idcurso'];
    try {
        $pdf = generarPDFDirecto($idcliente, $idcurso);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="certificado_' . $idcliente . '_' . $idcurso . '.pdf"');
        echo $pdf;
        exit;
    } catch (Exception $e) {
        echo 'Error generando PDF: ' . $e->getMessage();
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $modo === 'final' ? 'Certificado Final' : 'Previsualización'; ?> - <?php echo htmlspecialchars($curso['nombre_curso']); ?></title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        
        .certificate-container {
            width: 2000px;
            height: 1414px;
            position: relative;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin: 0 auto;
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
        
        .no-background {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #f8f9fa;
            color: #6c757d;
            font-size: 24px;
        }
        
        .controls {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px auto;
            max-width: 800px;
            text-align: center;
        }
        
        .qr-info {
            background: #d1ecf1;
            padding: 15px;
            border-radius: 5px;
            margin: 20px auto;
            max-width: 800px;
            text-align: center;
        }
        
        .qr-info h3 {
            color: #0c5460;
            margin-bottom: 10px;
        }
        
        .qr-code {
            font-family: monospace;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            word-break: break-all;
        }
        
        @media print {
    html, body {
        width: 2000px;
        height: 1414px;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        overflow: hidden !important;
    }
    .controls, .info, .qr-info {
        display: none !important;
    }
    @page {
        size: 2000px 1414px;
        margin: 0;
    }
    .certificate-container {
        width: 2000px !important;
        height: 1414px !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        page-break-inside: avoid !important;
        page-break-before: avoid !important;
        page-break-after: avoid !important;
        overflow: hidden !important;
        position: relative !important;
    }
    * {
        box-sizing: border-box !important;
        max-height: 1414px !important;
        max-width: 2000px !important;
    }
}

    </style>
</head>
<body>
    
    <div class="controls">
        <button class="btn" onclick="window.print()">
            <i class="fa fa-print"></i> Imprimir
        </button>
        <button class="btn btn-success" onclick="window.close()">
            <i class="fa fa-times"></i> Cerrar
        </button>
        <?php if ($modo === 'preview'): ?>
        <button class="btn btn-warning" onclick="window.location.href='editor_certificado.php?idcurso=<?php echo $idcurso; ?>'">
            <i class="fa fa-edit"></i> Editar Diseño
        </button>
        <?php else: ?>
        <?php 
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $ruta_cert = $protocolo . '://' . $host . '/certificado/verificar-certificado.php?codigo=' . urlencode($codigo_validacion);
        ?>
        <button class="btn btn-info" onclick="window.open('<?php echo $ruta_cert; ?>', '_blank')">
            <i class="fa fa-search"></i> Verificar Certificado
        </button>
        <button class="btn btn-primary" onclick="abrirModalEnvio()">
            <i class="fa fa-envelope"></i> Enviar Certificado
        </button>
        <button class="btn btn-primary" onclick="window.location.href='aprobar_alumno.php'">
            <i class="fa fa-list"></i> Volver a Lista
        </button>
        <?php endif; ?>
    </div>
    
    
    <?php if ($modo === 'preview'): ?>
    <div class="qr-info">
        <h3>🔍 Información del QR de Previsualización</h3>
        <p><strong>Alumno:</strong> <?php echo htmlspecialchars($alumno); ?></p>
        <p><strong>Código de Validación:</strong></p>
        <div class="qr-code"><?php echo htmlspecialchars($codigo_validacion); ?></div>
        <p><strong>URL de Verificación:</strong></p>
        <div class="qr-code">https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificado/verificar-certificado.php?codigo=<?php echo urlencode($codigo_validacion); ?></div>
        <p><em>Este QR es específico para la previsualización. Al generar el certificado real, se creará un QR único.</em></p>
    </div>
    <?php else: ?>
    <div class="qr-info" style="background: #d4edda; border-color: #c3e6cb;">
        <h3>🏆 Certificado Final Generado</h3>
        <p><strong>Alumno:</strong> <?php echo htmlspecialchars($alumno); ?></p>
        <p><strong>Nota Final:</strong> <span class="badge bg-success"><?php echo htmlspecialchars($nota_final); ?></span></p>
        <p><strong>Fecha de Aprobación:</strong> <?php echo htmlspecialchars($fecha_aprobacion); ?></p>
        <p><strong>Código de Validación:</strong></p>
        <div class="qr-code"><?php echo htmlspecialchars($codigo_validacion); ?></div>
        <p><strong>URL de Verificación:</strong></p>
        <div class="qr-code">https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificado/verificar-certificado.php?codigo=<?php echo urlencode($codigo_validacion); ?></div>
        <p><em>✅ Este es el certificado final con código único y QR válido.</em></p>
    </div>
    <?php endif; ?>
    
    <div class="certificate-container">
        <?php if (!empty($diseño)): ?>
            <img class="certificate-background" src="../assets/uploads/cursos/<?php echo htmlspecialchars($diseño); ?>" alt="Diseño del Certificado">
        <?php else: ?>
            <div class="no-background">
                <i class="fa fa-image fa-3x"></i>
                <p>No hay imagen de fondo asignada</p>
            </div>
        <?php endif; ?>
        
        <?php if ($config && isset($config['campos'])): ?>
            <?php foreach ($config['campos'] as $campo): ?>
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
                    <?php if ($campo['tipo'] === 'firma_instructor'): ?>
                        <img src="<?php echo $firmaInstructor; ?>" alt="Firma Instructor" style="max-width: 120px; max-height: 60px; object-fit: contain;">
                    <?php elseif ($campo['tipo'] === 'firma_especialista'): ?>
                        <img src="<?php echo $firmaEspecialista; ?>" alt="Firma Especialista" style="max-width: 120px; max-height: 60px; object-fit: contain;">
                    <?php elseif ($campo['tipo'] === 'qr'): ?>
                        <?php 
                        // Obtener configuración del QR
                        $qr_config = $config['qr_config'] ?? [
                            'size' => 300,
                            'color' => '#000000',
                            'bgColor' => '#FFFFFF',
                            'margin' => 0,
                            'logoEnabled' => false
                        ];
                        $qr_size = $qr_config['size'];
                        ?>
                        <div style="position: relative; width: <?php echo $qr_size; ?>px; height: <?php echo $qr_size; ?>px;">
                            <img src="img/qr/<?php echo htmlspecialchars($nombre_archivo_qr); ?>" alt="QR" style="width: 100%; height: 100%; object-fit: contain;">
                            <?php if ($qr_config['logoEnabled'] && file_exists(__DIR__ . '/img/logo.png')): ?>
                                <img src="img/logo.png" alt="Logo" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: <?php echo $qr_size * 0.2; ?>px; pointer-events: none; z-index: 10;">
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php 
                        $texto = $campo['texto'] ?? '';
                        
                        // En modo final, usar datos reales del alumno
                        if ($modo === 'final') {
                            if ($campo['tipo'] === 'alumno') {
                                $texto = $alumno;
                            } elseif ($campo['tipo'] === 'fecha') {
                                $texto = $fecha_aprobacion;
                            } elseif ($campo['tipo'] === 'instructor') {
                                $texto = $instructor;
                            } elseif ($campo['tipo'] === 'especialista') {
                                $texto = $especialista;
                            } elseif ($campo['tipo'] === 'nota') {
                                $texto = $nota_final;
                            } else {
                                // Para otros tipos, reemplazar placeholders
                                $texto = str_replace('NOMBRE DEL ALUMNO', $alumno, $texto);
                                $texto = str_replace('FECHA DE EMISIÓN', $fecha_aprobacion, $texto);
                                $texto = str_replace('NOMBRE DEL INSTRUCTOR', $instructor, $texto);
                                $texto = str_replace('NOMBRE DEL ESPECIALISTA', $especialista, $texto);
                                $texto = str_replace('NOTA_FINAL', $nota_final, $texto);
                            }
                        } else {
                            // En modo preview, reemplazar placeholders
                            $texto = str_replace('NOMBRE DEL ALUMNO', $alumno, $texto);
                            $texto = str_replace('FECHA DE EMISIÓN', $fecha_aprobacion, $texto);
                            $texto = str_replace('NOMBRE DEL INSTRUCTOR', $instructor, $texto);
                            $texto = str_replace('NOMBRE DEL ESPECIALISTA', $especialista, $texto);
                            $texto = str_replace('NOTA_FINAL', $nota_final, $texto);
                        }
                        
                        echo htmlspecialchars($texto); 
                        ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    
    <!-- Modal para envío de certificado -->
    <div id="modalEnvio" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
        <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 5px;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0;">📧 Enviar Certificado</h3>
                <span class="close" onclick="cerrarModalEnvio()" style="color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            </div>
            
            <form id="formEnvio" method="post" action="enviar_certificado.php">
                <div style="margin-bottom: 10px; color: #007bff; font-weight: bold;">
                    ID Alumno: <?php echo $idalumno; ?> | ID Curso: <?php echo $idcurso; ?>
                </div>
                <input type="hidden" name="idcliente" value="<?php echo $idalumno; ?>">
                <input type="hidden" name="idcurso" value="<?php echo $idcurso; ?>">
                
                <div style="margin-bottom: 15px;">
                    <label for="email" style="display: block; margin-bottom: 5px; font-weight: bold;">Email del alumno:</label>
                    <input type="email" id="email" name="email" value="<?php 
                        // Obtener email del alumno desde la base de datos
                        $stmt = $pdo->prepare("SELECT email FROM cliente WHERE idcliente = ?");
                        $stmt->execute([$idalumno]);
                        $alumnoData = $stmt->fetch();
                        echo htmlspecialchars($alumnoData['email'] ?? '');
                    ?>" readonly style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; background-color: #f8f9fa;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="asunto" style="display: block; margin-bottom: 5px; font-weight: bold;">Asunto del email:</label>
                    <input type="text" id="asunto" name="asunto" value="Tu certificado de <?php echo htmlspecialchars($curso['nombre_curso']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="mensaje" style="display: block; margin-bottom: 5px; font-weight: bold;">Mensaje personalizado:</label>
                    <textarea id="mensaje" name="mensaje" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">Hola <?php echo htmlspecialchars($alumno); ?>,

Te adjuntamos tu certificado de <?php echo htmlspecialchars($curso['nombre_curso']); ?>.

Saludos cordiales,
El equipo de certificación</textarea>
                </div>
                
                <div style="text-align: right;">
                    <button type="button" onclick="cerrarModalEnvio()" style="background-color: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">Cancelar</button>
                    <button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fa fa-paper-plane"></i> Enviar Certificado
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Función para abrir modal de envío
        function abrirModalEnvio() {
            document.getElementById('modalEnvio').style.display = 'block';
        }
        
        // Función para cerrar modal de envío
        function cerrarModalEnvio() {
            document.getElementById('modalEnvio').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            var modal = document.getElementById('modalEnvio');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        // Función para cerrar ventana
        function cerrar() {
            window.close();
        }
        
        // Función para volver al editor
        function editar() {
            window.location.href = 'editor_certificado.php?id=<?php echo $idcurso; ?>';
        }

        // Adaptar envío AJAX del formulario de certificado
        const formEnvio = document.getElementById('formEnvio');
        if (formEnvio) {
            formEnvio.addEventListener('submit', function(e) {
                e.preventDefault();
                const btnEnviar = formEnvio.querySelector('button[type="submit"]');
                const btnOriginal = btnEnviar.innerHTML;
                // Mostrar spinner y deshabilitar botón
                btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';
                btnEnviar.disabled = true;
                const formData = new FormData(formEnvio);
                fetch('enviar_certificado.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        localStorage.setItem('toastSuccess', data.mensaje);
                        window.location.href = 'aprobar_alumno.php';
                    } else {
                        alert(data.mensaje || 'Error al enviar el certificado');
                    }
                })
                .catch(err => {
                    alert('Error de conexión al enviar el certificado');
                })
                .finally(() => {
                    // Restaurar botón
                    btnEnviar.innerHTML = btnOriginal;
                    btnEnviar.disabled = false;
                });
            });
        }
    </script>
</body>
</html>
<?php include('footer.php'); ?>