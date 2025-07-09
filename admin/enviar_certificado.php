<?php
require_once 'inc/config.php';
require_once __DIR__ . '/../vendor/autoload.php'; 
require_once __DIR__ . '/inc/utilidades.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Cargar variables de entorno usando Dotenv
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} else {
    die('Error: El archivo .env no existe. Por favor, copia env.example a .env y configura tus variables.');
}

// Configuración temporal de email (si no existe .env)
if (!isset($_ENV['MAIL_USER']) || empty($_ENV['MAIL_USER']) || $_ENV['MAIL_USER'] === 'tu_email@gmail.com') {
    die('Error: Configuración de correo no válida. Por favor, edita el archivo .env con tus credenciales de Gmail.');
}

// Función que genera el HTML del certificado
function generarHTMLCertificado($datos, $config, $certificado) {
    $base_path = dirname(__DIR__) . '/';

    $html = '
    <!DOCTYPE html>
    <html><head>
        <meta charset="UTF-8">
        <style>
            body { margin: 0; padding: 0; font-family: Arial, sans-serif; background: white; }
            .certificado-container { width: 2000px; height: 1414px; position: relative; overflow: hidden; background: white; }
            .certificate-background { position: absolute; top: 0; left: 0; width: 2000px; height: 1414px; object-fit: cover; z-index: 1; }
            .certificate-field { position: absolute; z-index: 10; word-wrap: break-word; overflow: hidden; }
            .certificate-field img { max-width: 100%; max-height: 100%; object-fit: contain; }
        </style>
    </head><body><div class="certificado-container">';

    // Fondo del certificado (convertir a base64)
    if (!empty($certificado['diseño'])) {
        $ruta_fondo = $base_path . 'assets/uploads/cursos/' . $certificado['diseño'];
        $fondo_base64 = convertirImagenABase64($ruta_fondo);
        
        if ($fondo_base64) {
            $html .= '<img class="certificate-background" src="' . $fondo_base64 . '" alt="Fondo del Certificado">';
        } else {
            // Si no se puede cargar la imagen, usar un fondo de color
            $html .= '<div class="certificate-background" style="background-color: #f8f9fa; border: 2px solid #dee2e6;"></div>';
        }
    }

    if ($config && isset($config['campos'])) {
        foreach ($config['campos'] as $campo) {
            $html .= '<div class="certificate-field" style="
                left: ' . $campo['left'] . 'px;
                top: ' . $campo['top'] . 'px;
                width: ' . $campo['width'] . 'px;
                height: ' . $campo['height'] . 'px;
                font-family: ' . ($campo['fontFamily'] ?? 'Arial') . ';
                font-size: ' . ($campo['fontSize'] ?? 14) . 'px;
                color: ' . ($campo['color'] ?? '#000000') . ';
                text-align: ' . ($campo['textAlign'] ?? 'left') . ';
                font-weight: ' . ($campo['fontWeight'] ?? 'normal') . ';
                font-style: ' . ($campo['fontStyle'] ?? 'normal') . ';
            ">';

            if ($campo['tipo'] === 'firma_instructor' && isset($datos['firma_instructor'])) {
                $ruta_firma = $base_path . $datos['firma_instructor'];
                $firma_base64 = convertirImagenABase64($ruta_firma);
                
                if ($firma_base64) {
                    $html .= '<img src="' . $firma_base64 . '" alt="Firma Instructor">';
                } else {
                    $html .= '<div style="border: 1px solid #ccc; padding: 10px; text-align: center; color: #666;">Firma Instructor</div>';
                }
            } elseif ($campo['tipo'] === 'firma_especialista' && isset($datos['firma_especialista'])) {
                $ruta_firma = $base_path . $datos['firma_especialista'];
                $firma_base64 = convertirImagenABase64($ruta_firma);
                
                if ($firma_base64) {
                    $html .= '<img src="' . $firma_base64 . '" alt="Firma Especialista">';
                } else {
                    $html .= '<div style="border: 1px solid #ccc; padding: 10px; text-align: center; color: #666;">Firma Especialista</div>';
                }
            } elseif ($campo['tipo'] === 'qr') {
                $ruta_qr = __DIR__ . '/img/qr/' . $datos['nombre_archivo_qr'];
                $qr_base64 = convertirImagenABase64($ruta_qr);
                
                if ($qr_base64) {
                    $html .= '<img src="' . $qr_base64 . '" alt="Código QR">';
                } else {
                    $html .= '<div style="border: 1px solid #ccc; padding: 10px; text-align: center; color: #666;">QR Code</div>';
                }
            } else {
                $texto = str_replace(
                    ['NOMBRE DEL ALUMNO', 'FECHA DE EMISIÓN', 'NOMBRE DEL INSTRUCTOR', 'NOMBRE DEL ESPECIALISTA', 'NOTA_FINAL'],
                    [$datos['nombre_cliente'], $datos['fecha_aprobacion'], $datos['instructor'] ?? '', $datos['especialista'] ?? '', $datos['nota_final']],
                    $campo['texto'] ?? ''
                );
                $html .= htmlspecialchars($texto);
            }

            $html .= '</div>';
        }
    }

    $html .= '</div></body></html>';
    return $html;
}

// Función para generar el cuerpo del correo
function generarCuerpoEmail($datos, $dedicatoria = '') {
    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $url = $protocolo . '://' . $host . '/certificado/verificar-certificado.php?codigo=' . $datos['codigo_validacion'];

    $html = '<div style="font-family: Arial; font-size: 16px; color: #333;">';
    $html .= '<p>Hola <strong>' . htmlspecialchars($datos['nombre_cliente']) . '</strong>,</p>';
    $html .= '<p>¡Felicidades por completar el curso <strong>' . htmlspecialchars($datos['nombre_curso']) . '</strong>!</p>';
    
    if ($dedicatoria) {
        $html .= '<blockquote style="background-color: #f9f9f9; padding: 15px; border-left: 4px solid #0d6efd;">' . nl2br(htmlspecialchars($dedicatoria)) . '</blockquote>';
    }

    $html .= '<p>Adjunto encontrarás tu certificado en PDF.</p>';
    $html .= '<p>Puedes verificarlo en este enlace:</p>';
    $html .= '<p><a href="' . $url . '">' . $url . '</a></p>';
    $html .= '<p>Código de Validación: <strong>' . $datos['codigo_validacion'] . '</strong></p>';
    $html .= '<p>Saludos,<br><em>Sistema de Certificados</em></p>';
    $html .= '</div>';
    return $html;
}

header('Content-Type: application/json');
error_log("DEBUG ENVIO: idcliente=" . ($_POST['idcliente'] ?? 'NO') . " idcurso=" . ($_POST['idcurso'] ?? 'NO'));
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Acceso denegado.']);
    exit;
}

$idcliente = isset($_POST['idcliente']) ? (int)$_POST['idcliente'] : 0;
$idcurso = isset($_POST['idcurso']) ? (int)$_POST['idcurso'] : 0;
$dedicatoria = isset($_POST['dedicatoria']) ? trim($_POST['dedicatoria']) : '';


if ($idcliente <= 0 || $idcurso <= 0) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Datos inválidos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT cg.*, 
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
        WHERE cg.idcliente = ? AND cg.idcurso = ? AND cg.estado = 'Activo'");
    $stmt->execute([$idcliente, $idcurso]);
    $certificado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$certificado) {
        throw new Exception("Certificado no encontrado.");
    }

    // Debug: Verificar datos del certificado
    error_log("Enviando certificado - Alumno: " . $certificado['nombre_cliente'] . " " . $certificado['apellido_cliente'] . 
              " - Curso: " . $certificado['nombre_curso'] . " - Código: " . $certificado['codigo_validacion']);

    $datos = [
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

    if ($certificado['nombre_instructor']) {
        $datos['instructor'] = $certificado['nombre_instructor'] . ' ' . $certificado['apellido_instructor'];
        if ($certificado['firma_instructor']) {
            $datos['firma_instructor'] = 'assets/uploads/firmas/' . $certificado['firma_instructor'];
        }
    }

    if (!empty($certificado['nombre_especialista'])) {
        $datos['especialista'] = $certificado['nombre_especialista'] . ' ' . $certificado['apellido_especialista'];
        if (!empty($certificado['firma_especialista'])) {
            $datos['firma_especialista'] = 'assets/uploads/firmas/' . $certificado['firma_especialista'];
        }
    }

    $config_cert = $certificado['config_certificado'] ? json_decode($certificado['config_certificado'], true) : [];

    // Debug: Verificar parámetros para generar PDF
    error_log("Generando PDF - ID Cliente: $idcliente - ID Curso: $idcurso");

    // Generar PDF usando el nuevo generador directo
    require_once 'generar_pdf_directo.php';
    $pdfContent = generarPDFDirecto($idcliente, $idcurso);

    $nombre_pdf = 'certificado_' . $certificado['codigo_validacion'] . '_' . time() . '.pdf';
    $ruta_pdf = __DIR__ . '/temp/' . $nombre_pdf;

    if (!is_dir(__DIR__ . '/temp')) mkdir(__DIR__ . '/temp', 0755, true);
    file_put_contents($ruta_pdf, $pdfContent);

    // Enviar Email
    $mail = new PHPMailer(true);
    
    // Habilitar debug para identificar problemas
    $mail->SMTPDebug = 2; // Cambiar a 0 en producción
    $mail->Debugoutput = 'error_log';
    
    $mail->isSMTP();
    $mail->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['MAIL_USER'];
    $mail->Password = $_ENV['MAIL_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_ENV['MAIL_PORT'] ?? 587;
    $mail->CharSet = 'UTF-8';

    // Configuración adicional para Gmail
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ]
    ];

    $mail->setFrom($_ENV['MAIL_USER'], $_ENV['MAIL_FROM_NAME'] ?? 'Certificados');
    $mail->addAddress($certificado['email'], $datos['nombre_cliente']);
    $mail->addReplyTo($_ENV['MAIL_USER']);

    $mail->Subject = 'Tu Certificado - ' . $datos['nombre_curso'];
    $mail->isHTML(true);
    $mail->Body = generarCuerpoEmail($datos, $dedicatoria);
    $mail->AltBody = 'Adjunto encontrarás tu certificado. Código de validación: ' . $datos['codigo_validacion'];
    $mail->addAttachment($ruta_pdf, 'Certificado_' . $datos['nombre_curso'] . '.pdf');
    $mail->send();

    // Registrar envío en la base de datos
    $stmt = $pdo->prepare("UPDATE certificado_generado SET fecha_envio_email = NOW(), dedicatoria_email = ? WHERE idcliente = ? AND idcurso = ?");
    $stmt->execute([$dedicatoria, $idcliente, $idcurso]);

    // Limpiar archivo temporal
    if (file_exists($ruta_pdf)) {
        unlink($ruta_pdf);
    }

    echo json_encode([
        'status' => 'success',
        'mensaje' => 'Certificado enviado exitosamente a ' . $datos['nombre_cliente']
    ]);
    exit;

} catch (Exception $e) {
    // Log del error para debugging
    error_log("Error enviando certificado: " . $e->getMessage());
    
    // Limpiar archivo temporal si existe
    if (isset($ruta_pdf) && file_exists($ruta_pdf)) {
        unlink($ruta_pdf);
    }
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error al enviar certificado: ' . $e->getMessage()
    ]);
    exit;
}