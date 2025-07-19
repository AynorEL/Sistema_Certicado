<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

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

// Inicializar variables para evitar warnings
$firma_recortada = null;
$firma_instructor = null;
$firma_especialista = null;
$nombre_archivo_qr = null;

// Buscar archivos de firmas en la carpeta
$dir_firmas = $_SERVER['DOCUMENT_ROOT'] . '/certificado/assets/uploads/firmas/';
if (is_dir($dir_firmas)) {
    foreach (scandir($dir_firmas) as $f) {
        if (!$firma_recortada && $f === "firma_recortada_{$idcurso}.png") $firma_recortada = $f;
        if (!$firma_instructor && strpos($f, 'instructor_firma_') === 0) $firma_instructor = $f;
        if (!$firma_especialista && strpos($f, 'especialista_firma_') === 0) $firma_especialista = $f;
    }
}

// Cargar datos usando el mismo sistema que el editor
try {
    // Cargar datos del alumno
    $stmt = $pdo->prepare("SELECT idcliente, nombre, apellido, email FROM cliente WHERE idcliente = ?");
    $stmt->execute([$idalumno]);
    $alumnoData = $stmt->fetch();
    
    if (!$alumnoData) {
        // Si no existe el alumno, usar datos de prueba
        $alumno = 'Juan Carlos García López';
        $email = 'juan.garcia@email.com';
    } else {
        $alumno = $alumnoData['nombre'] . ' ' . $alumnoData['apellido'];
        $email = $alumnoData['email'];
    }
    
    // Cargar instructor del curso
    $stmt = $pdo->prepare("
        SELECT i.idinstructor, i.nombre, i.apellido, i.email, i.firma_digital 
        FROM instructor i 
        INNER JOIN curso c ON i.idinstructor = c.idinstructor 
        WHERE c.idcurso = ?
    ");
    $stmt->execute([$idcurso]);
    $instructorData = $stmt->fetch();
    
    if ($instructorData) {
        $instructor = $instructorData['nombre'] . ' ' . $instructorData['apellido'];
        $firmaInstructor = $instructorData['firma_digital'] ? '../assets/uploads/firmas/' . $instructorData['firma_digital'] : '../assets/img/qr_placeholder.png';
    } else {
        $instructor = 'Dr. Roberto Sánchez Mendoza';
        $firmaInstructor = '../assets/img/qr_placeholder.png';
    }
    
    // Cargar especialista del curso
    $stmt = $pdo->prepare("
        SELECT e.idespecialista, e.nombre, e.apellido, e.email, e.firma_especialista 
        FROM especialista e 
        INNER JOIN curso c ON e.idespecialista = c.idespecialista 
        WHERE c.idcurso = ?
    ");
    $stmt->execute([$idcurso]);
    $especialistaData = $stmt->fetch();
    
    if ($especialistaData) {
        $especialista = $especialistaData['nombre'] . ' ' . $especialistaData['apellido'];
        $firmaEspecialista = $especialistaData['firma_especialista'] ? '../assets/uploads/firmas/' . $especialistaData['firma_especialista'] : '../assets/img/qr_placeholder.png';
    } else {
        $especialista = 'Mg. Patricia González Castro';
        $firmaEspecialista = '../assets/img/qr_placeholder.png';
    }
    
    // Obtener datos de inscripción
    $stmt = $pdo->prepare("
        SELECT nota_final, fecha_aprobacion, estado 
        FROM inscripcion 
        WHERE idcliente = ? AND idcurso = ?
    ");
    $stmt->execute([$idalumno, $idcurso]);
    $inscripcion = $stmt->fetch();
    
    // Verificar si se encontró la inscripción antes de acceder a sus propiedades
    if ($inscripcion) {
        $nota_final = $inscripcion['nota_final'] ?? '85';
        $fecha_aprobacion = $inscripcion['fecha_aprobacion'] ? date('d/m/Y', strtotime($inscripcion['fecha_aprobacion'])) : date('d/m/Y');
    } else {
        $nota_final = '85';
        $fecha_aprobacion = date('d/m/Y');
    }
    
} catch (Exception $e) {
    // Si hay error, usar datos por defecto
    $alumno = 'Juan Carlos García López';
    $instructor = 'Dr. Roberto Sánchez Mendoza';
    $especialista = 'Mg. Patricia González Castro';
    $firmaInstructor = '../assets/img/qr_placeholder.png';
    $firmaEspecialista = '../assets/img/qr_placeholder.png';
    $nota_final = '85';
    $fecha_aprobacion = date('d/m/Y');
}

// Generar código de validación para preview o final
$codigo_validacion = '';
$enlace_verificacion = '';
if ($modo === 'final') {
    $stmt = $pdo->prepare("SELECT codigo_validacion FROM certificado_generado WHERE idcliente = ? AND idcurso = ? AND estado = 'Activo' LIMIT 1");
    $stmt->execute([$idalumno, $idcurso]);
    $row = $stmt->fetch();
    if ($row && !empty($row['codigo_validacion'])) {
        $codigo_validacion = $row['codigo_validacion'];
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $enlace_verificacion = $protocolo . '://' . $host . '/certificado/verificar-certificado.php?codigo=' . urlencode($codigo_validacion);
    } else {
        $codigo_validacion = 'NO-CERTIFICADO';
        $enlace_verificacion = '';
    }
} else {
    $codigo_validacion = 'PREVIEW-' . $idcurso . '-' . $idalumno . '-' . date('YmdHis');
    $enlace_verificacion = '';
}

// Para mostrar en el certificado:
$base_url = '/certificado/';
$base_url_admin = '/certificado/admin/';

function url_si_existe($web_path) {
    $abs_path = $_SERVER['DOCUMENT_ROOT'] . $web_path;
    return file_exists($abs_path) ? $web_path : false;
}

// Lógica para la firma del instructor
$firmaInstructorUrl =
    ($firma_recortada && url_si_existe($base_url . 'assets/uploads/firmas/' . $firma_recortada)) ? $base_url . 'assets/uploads/firmas/' . $firma_recortada :
    (($firma_instructor && url_si_existe($base_url . 'assets/uploads/firmas/' . $firma_instructor)) ? $base_url . 'assets/uploads/firmas/' . $firma_instructor : $base_url . 'assets/img/qr_placeholder.png');

// Lógica para la firma del especialista
$firmaEspecialistaUrl =
    ($firma_especialista && url_si_existe($base_url . 'assets/uploads/firmas/' . $firma_especialista)) ? $base_url . 'assets/uploads/firmas/' . $firma_especialista : $base_url . 'assets/img/qr_placeholder.png';

// Lógica para el QR
$qr_url = false;
$qr_real_faltante = false;
if ($modo === 'final') {
    $stmt = $pdo->prepare("SELECT codigo_qr FROM certificado_generado WHERE idcliente = ? AND idcurso = ? AND estado = 'Activo' LIMIT 1");
    $stmt->execute([$idalumno, $idcurso]);
    $row = $stmt->fetch();
    if ($row && !empty($row['codigo_qr'])) {
        $nombre_archivo_qr = $row['codigo_qr'];
        if (url_si_existe($base_url . 'assets/img/qr/' . $nombre_archivo_qr)) {
            $qr_url = $base_url . 'assets/img/qr/' . $nombre_archivo_qr;
        } elseif (url_si_existe($base_url . 'admin/img/qr/' . $nombre_archivo_qr)) {
            $qr_url = $base_url . 'admin/img/qr/' . $nombre_archivo_qr;
        }
    }
    if (!$qr_url) {
        $qr_real_faltante = true;
    }
}
if (!$qr_url && $modo !== 'final') {
    // Generar QR temporal para previsualización
    $qr_config = $config['qr_config'] ?? [
        'size' => 300,
        'color' => '#000000',
        'bgColor' => '#FFFFFF',
        'margin' => 0,
        'logoEnabled' => false
    ];
    $qr_size = $qr_config['size'];
    $qr_color = urlencode($qr_config['color']);
    $qr_bg = urlencode($qr_config['bgColor']);
    $qr_margin = $qr_config['margin'];
    $qr_data = 'PREVIEW-' . $idcurso . '-' . $idalumno . '-' . date('YmdHis');
    $qr_url = "generar_qr_svg.php?size=$qr_size&color=$qr_color&bgColor=$qr_bg&margin=$qr_margin&data=$qr_data";
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
<?php if ($modo === 'final'): ?>
    <div class="controls">
        <button class="btn" onclick="window.print()">
            <i class="fa fa-print"></i> Imprimir
        </button>
        <button class="btn btn-success" onclick="window.close()">
            <i class="fa fa-times"></i> Cerrar
        </button>
        <button class="btn btn-warning" onclick="window.location.href='editor_certificado.php?id=<?php echo $idcurso; ?>'">
            <i class="fa fa-edit"></i> Volver al Editor
        </button>
        <button class="btn btn-primary" onclick="abrirModalEnvio()">
            <i class="fa fa-envelope"></i> Enviar Certificado
        </button>
    </div>
    <?php endif; ?>
    
    <?php if ($modo === 'final'): ?>
    <div class="info" style="text-align:center; margin: 30px auto; max-width: 700px;">
        <h2 style="margin-bottom: 10px;">✅ Certificado Final Generado</h2>
        <p><strong>Alumno:</strong> <?php echo htmlspecialchars($alumno); ?></p>
        <p><strong>Curso:</strong> <?php echo htmlspecialchars($curso['nombre_curso']); ?></p>
        <p><strong>Instructor:</strong> <?php echo htmlspecialchars($instructor); ?></p>
        <p><strong>Especialista:</strong> <?php echo htmlspecialchars($especialista); ?></p>
        <p><strong>Fecha de Aprobación:</strong> <?php echo htmlspecialchars($fecha_aprobacion); ?></p>
        <p><strong>Nota Final:</strong> <?php echo htmlspecialchars($nota_final); ?></p>
        <p style="font-size:18px;"><strong>Código de Validación:</strong> <span style="color:#007bff;letter-spacing:1px;"> <?php echo htmlspecialchars($codigo_validacion); ?> </span></p>
        <?php if ($enlace_verificacion): ?>
            <p><strong>Enlace de Verificación:</strong><br>
                <a href="<?php echo htmlspecialchars($enlace_verificacion); ?>" target="_blank" style="word-break:break-all; color:#28a745; font-size:16px;">
                    <?php echo htmlspecialchars($enlace_verificacion); ?>
                </a>
            </p>
            <a href="<?php echo htmlspecialchars($enlace_verificacion); ?>" target="_blank" class="btn btn-success" style="font-size:18px; padding:12px 32px; margin: 18px auto 0 auto; display:inline-block;">
                <i class="fa fa-qrcode"></i> Verificar Certificado
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="info" style="text-align:center; margin: 30px auto; max-width: 700px;">
        <h2 style="margin-bottom: 10px;">👁️ Previsualización de Certificado</h2>
        <p><strong>Alumno:</strong> <?php echo htmlspecialchars($alumno); ?></p>
        <p><strong>Curso:</strong> <?php echo htmlspecialchars($curso['nombre_curso']); ?></p>
        <p><strong>Instructor:</strong> <?php echo htmlspecialchars($instructor); ?></p>
        <p><strong>Especialista:</strong> <?php echo htmlspecialchars($especialista); ?></p>
        <p><strong>Fecha de Aprobación:</strong> <?php echo htmlspecialchars($fecha_aprobacion); ?></p>
        <p><strong>Nota Final:</strong> <?php echo htmlspecialchars($nota_final); ?></p>
        <p style="font-size:18px;"><strong>Código de Validación:</strong> <span style="color:#007bff;letter-spacing:1px;"> <?php echo htmlspecialchars($codigo_validacion); ?> </span></p>
        <p style="color:#888;">Este QR y código son solo para previsualización, no son válidos para verificación real.</p>
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
                        <img src="<?php echo htmlspecialchars($firmaInstructorUrl); ?>" alt="Firma Instructor" style="width:100%;height:100%;object-fit:contain;max-width:100%;max-height:100%;display:block;pointer-events:none;">
                    <?php elseif ($campo['tipo'] === 'firma_especialista'): ?>
                        <img src="<?php echo htmlspecialchars($firmaEspecialistaUrl); ?>" alt="Firma Especialista" style="width:100%;height:100%;object-fit:contain;max-width:100%;max-height:100%;display:block;pointer-events:none;">
                    <?php elseif ($campo['tipo'] === 'qr'): ?>
                        <?php 
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
                            <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="QR" style="width: 100%; height: 100%; object-fit: contain;">
                            <?php if ($qr_config['logoEnabled'] && file_exists(__DIR__ . '/img/logo.png')): ?>
                                <img src="/certificado/admin/img/logo.png" alt="Logo" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: <?php echo $qr_size * 0.2; ?>px; pointer-events: none; z-index: 10;">
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php 
                        $texto = $campo['texto'] ?? '';
                        
                        // Reemplazar placeholders con datos reales
                        $texto = str_replace('NOMBRE DEL ALUMNO', $alumno, $texto);
                        $texto = str_replace('FECHA DE EMISIÓN', $fecha_aprobacion, $texto);
                        $texto = str_replace('NOMBRE DEL INSTRUCTOR', $instructor, $texto);
                        $texto = str_replace('NOMBRE DEL ESPECIALISTA', $especialista, $texto);
                        $texto = str_replace('NOTA_FINAL', $nota_final, $texto);
                        
                        echo htmlspecialchars($texto); 
                        ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php if ($qr_real_faltante): ?>
    <div style="color:red;text-align:center;font-size:18px;margin:20px 0;">
        <strong>El QR real aún no ha sido generado para este certificado.<br>Por favor, genera el certificado primero.</strong>
    </div>
<?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Función para cerrar ventana
        function cerrar() {
            window.close();
        }
        
        // Función para volver al editor
        function editar() {
            window.location.href = 'editor_certificado.php?id=<?php echo $idcurso; ?>';
        }
        
        function abrirModalEnvio() {
            const mensajeDefecto = `Hola <?php echo addslashes($alumno); ?>,\n\nTe adjuntamos tu certificado de <?php echo addslashes($curso['nombre_curso']); ?>.\n\nSaludos cordiales,\nEl equipo de certificación`;
            Swal.fire({
                title: 'Enviar certificado',
                html: `<div style='text-align:left;'>
                    <b>Correo destino:</b><br>
                    <input id='swal-input-email' class='swal2-input' type='email' value='<?php echo htmlspecialchars($email); ?>' readonly style='margin-bottom:10px;'>
                    <b>Dedicatoria (opcional):</b><br>
                    <textarea id='swal-input-dedicatoria' class='swal2-textarea' placeholder='Puedes agregar un mensaje personalizado'></textarea>
                    <b>Mensaje:</b><br>
                    <textarea id='swal-input-mensaje' class='swal2-textarea' rows='4' style='min-height:80px;'>${mensajeDefecto}</textarea>
                </div>`,
                showCancelButton: true,
                confirmButtonText: 'Enviar',
                cancelButtonText: 'Cancelar',
                focusConfirm: false,
                preConfirm: () => {
                    const dedicatoria = document.getElementById('swal-input-dedicatoria').value;
                    const mensaje = document.getElementById('swal-input-mensaje').value;
                    return { dedicatoria, mensaje };
                },
                didOpen: () => {
                    setTimeout(() => {
                        document.getElementById('swal-input-dedicatoria').focus();
                    }, 300);
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Paso 1: Generando PDF...
                    Swal.fire({
                        title: 'Generando PDF...',
                        html: `<div class='swal2-progress-bar' style='height:8px;background:#eee;border-radius:4px;overflow:hidden;margin-top:20px;'>
                            <div id='swal-bar-pdf' style='width:0%;height:100%;background:#0d6efd;transition:width 0.7s;'></div>
                        </div>
                        <div style='margin-top:10px;font-size:14px;color:#888;'>Por favor espera...</div>`,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            setTimeout(() => {
                                document.getElementById('swal-bar-pdf').style.width = '60%';
                            }, 400);
                        }
                    });
                    // Simular progreso de PDF antes de enviar
                    setTimeout(() => {
                        // Paso 2: Enviando correo...
                        Swal.update({
                            title: 'Enviando correo...',
                            html: `<div class='swal2-progress-bar' style='height:8px;background:#eee;border-radius:4px;overflow:hidden;margin-top:20px;'>
                                <div id='swal-bar-mail' style='width:60%;height:100%;background:#0d6efd;transition:width 0.7s;'></div>
                            </div>
                            <div style='margin-top:10px;font-size:14px;color:#888;'>Enviando certificado al correo...</div>`
                        });
                        setTimeout(() => {
                            document.getElementById('swal-bar-mail').style.width = '100%';
                        }, 400);
                        // Enviar petición AJAX
                        fetch('enviar_certificado.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `idcliente=<?php echo $idalumno; ?>&idcurso=<?php echo $idcurso; ?>&dedicatoria=${encodeURIComponent(result.value.dedicatoria)}&mensaje=${encodeURIComponent(result.value.mensaje)}`
                        })
                        .then(async response => {
                            try {
                                const text = await response.text();
                                let data = null;
                                try {
                                    data = JSON.parse(text);
                                } catch (e) {
                                    throw new Error('Respuesta inesperada del servidor: ' + text);
                                }
                                if (data && (data.status === 'ok' || data.status === 'success')) {
                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'success',
                                        title: '¡Certificado enviado correctamente!',
                                        showConfirmButton: false,
                                        timer: 2500
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        html: `<div style='text-align:left;'>${data && data.mensaje ? data.mensaje : 'No se pudo enviar el certificado.'}</div>`
                                    });
                                }
                            } catch (error) {
                                console.error('Error de conexión o backend:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de conexión',
                                    html: `<div style='text-align:left;'>No se pudo enviar el certificado.<br><small>${error.message}</small><br><br>Revisa la configuración SMTP, la consola y el archivo .env.</div>`
                                });
                            }
                        })
                        .catch((error) => {
                            console.error('Error de conexión o backend:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de conexión',
                                html: `<div style='text-align:left;'>No se pudo enviar el certificado.<br><small>${error.message}</small><br><br>Revisa la configuración SMTP, la consola y el archivo .env.</div>`
                            });
                        });
                    }, 1200); // Simula tiempo de generación de PDF
                }
            });
        }
    </script>
</body>
</html>