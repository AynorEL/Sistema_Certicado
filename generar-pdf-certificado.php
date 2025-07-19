<?php
require_once('admin/inc/config.php');
require_once 'vendor/autoload.php';
require_once 'admin/inc/utilidades.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Verificar si se proporcionó un código de validación
$codigo_validacion = $_GET['codigo'] ?? null;

if (!$codigo_validacion) {
    die('Código de validación no proporcionado');
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

    // Preparar datos
    $diseño = $certificado['diseño'];
    $configGuardada = $certificado['config_certificado'];
    $config = json_decode($configGuardada, true);
    
    $alumno = $certificado['nombre_cliente'] . ' ' . $certificado['apellido_cliente'];
    $codigo_validacion = $certificado['codigo_validacion'];
    $nombre_archivo_qr = $certificado['codigo_qr'];
    
    $nota_final = $certificado['nota_final'] ?? '';
    $fecha_aprobacion = $certificado['fecha_aprobacion'] ? date('d/m/Y', strtotime($certificado['fecha_aprobacion'])) : date('d/m/Y');

    // Datos del instructor
    if ($certificado['nombre_instructor']) {
        $instructor = $certificado['nombre_instructor'] . ' ' . $certificado['apellido_instructor'];
        $firmaInstructor = $certificado['firma_instructor'] ? 'assets/uploads/firmas/' . $certificado['firma_instructor'] : 'assets/img/qr_placeholder.png';
    } else {
        $instructor = 'NOMBRE DEL INSTRUCTOR';
        $firmaInstructor = 'assets/img/qr_placeholder.png';
    }

    // Datos del especialista
    if (!empty($certificado['nombre_especialista']) && !empty($certificado['apellido_especialista'])) {
        $especialista = $certificado['nombre_especialista'] . ' ' . $certificado['apellido_especialista'];
        $firmaEspecialista = $certificado['firma_especialista'] ? 'assets/uploads/firmas/' . $certificado['firma_especialista'] : 'assets/img/qr_placeholder.png';
    } else {
        $especialista = 'NOMBRE DEL ESPECIALISTA';
        $firmaEspecialista = 'assets/img/qr_placeholder.png';
    }

    // Convertir imágenes a base64
    $fondo_base64 = '';
    if (!empty($diseño)) {
        $ruta_fondo = __DIR__ . '/assets/uploads/cursos/' . $diseño;
        $fondo_base64 = convertirImagenABase64($ruta_fondo);
    }

    $firma_instructor_base64 = convertirImagenABase64(__DIR__ . '/' . $firmaInstructor);
    $firma_especialista_base64 = convertirImagenABase64(__DIR__ . '/' . $firmaEspecialista);
    $qr_base64 = convertirImagenABase64(__DIR__ . '/admin/img/qr/' . $nombre_archivo_qr);

    // Generar el HTML del certificado
    $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado</title>
    <style>
        @page {
            size: 2000px 1414px;
            margin: 0;
            padding: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: white;
            font-family: Arial, sans-serif;
            width: 2000px;
            height: 1414px;
            overflow: hidden;
        }
        
        .certificate-container {
            width: 2000px;
            height: 1414px;
            position: relative;
            background: white;
            margin: 0;
            padding: 0;
            overflow: hidden;
            page-break-inside: avoid;
            page-break-before: avoid;
            page-break-after: avoid;
        }
        
        .certificate-background {
            width: 2000px;
            height: 1414px;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
            object-fit: cover;
        }
        
        .certificate-field {
            position: absolute;
            z-index: 10;
            overflow: hidden;
            word-wrap: break-word;
            box-sizing: border-box;
        }
        
        .no-background {
            width: 2000px;
            height: 1414px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="certificate-container">';

    // Agregar imagen de fondo
    if ($fondo_base64) {
        $html .= '<img class="certificate-background" src="' . $fondo_base64 . '" alt="Diseño del Certificado">';
    } else {
        $html .= '<div class="no-background">
            <p>No hay imagen de fondo asignada</p>
        </div>';
    }

    // Agregar campos del certificado
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

            if ($campo['tipo'] === 'firma_instructor') {
                if ($firma_instructor_base64) {
                    $html .= '<img src="' . $firma_instructor_base64 . '" alt="Firma Instructor" style="width:100%;height:100%;object-fit:contain;display:block;">';
                } else {
                    $html .= '<div style="border: 1px solid #ccc; padding: 10px; text-align: center; color: #666;">Firma Instructor</div>';
                }
            } elseif ($campo['tipo'] === 'firma_especialista') {
                if ($firma_especialista_base64) {
                    $html .= '<img src="' . $firma_especialista_base64 . '" alt="Firma Especialista" style="width:100%;height:100%;object-fit:contain;display:block;">';
                } else {
                    $html .= '<div style="border: 1px solid #ccc; padding: 10px; text-align: center; color: #666;">Firma Especialista</div>';
                }
            } elseif ($campo['tipo'] === 'qr') {
                $qr_config = $config['qr_config'] ?? [
                    'size' => 300,
                    'color' => '#000000',
                    'bgColor' => '#FFFFFF',
                    'margin' => 0,
                    'logoEnabled' => false
                ];
                $qr_size = $qr_config['size'];
                
                if ($qr_base64) {
                    $html .= '<div style="position: relative; width: ' . $qr_size . 'px; height: ' . $qr_size . 'px;">
                        <img src="' . $qr_base64 . '" alt="QR" style="width: 100%; height: 100%; object-fit: contain;">';
                    
                    if ($qr_config['logoEnabled']) {
                        $logo_base64 = convertirImagenABase64(__DIR__ . '/admin/img/logo.png');
                        if ($logo_base64) {
                            $html .= '<img src="' . $logo_base64 . '" alt="Logo" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: ' . ($qr_size * 0.2) . 'px; pointer-events: none; z-index: 10;">';
                        }
                    }
                    
                    $html .= '</div>';
                } else {
                    $html .= '<div style="border: 1px solid #ccc; padding: 10px; text-align: center; color: #666;">QR Code</div>';
                }
            } else {
                $texto = $campo['texto'] ?? '';
                // Reemplazar placeholders con datos reales
                $texto = str_replace('NOMBRE DEL ALUMNO', $alumno, $texto);
                $texto = str_replace('FECHA DE EMISIÓN', $fecha_aprobacion, $texto);
                $texto = str_replace('NOMBRE DEL INSTRUCTOR', $instructor, $texto);
                $texto = str_replace('NOMBRE DEL ESPECIALISTA', $especialista, $texto);
                $texto = str_replace('NOTA_FINAL', $nota_final, $texto);
                $html .= htmlspecialchars($texto);
            }

            $html .= '</div>';
        }
    }

    $html .= '</div>
</body>
</html>';

    // Generar PDF
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'Arial');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);

    // Forzar una sola página con tamaño exacto
    $dompdf->setPaper([0, 0, 2000, 1414], 'portrait');

    $dompdf->render();

    // Generar nombre del archivo
    $filename = 'Certificado_' . $alumno . '_' . date('Y-m-d') . '.pdf';
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);

    // Enviar el PDF al navegador
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    echo $dompdf->output();

} catch (Exception $e) {
    die('Error al generar el PDF: ' . $e->getMessage());
}
?> 