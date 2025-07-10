<?php
require_once('inc/config.php');
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/inc/utilidades.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Función para generar el PDF directamente desde los datos
function generarPDFDirecto($idcliente, $idcurso) {
    global $pdo;
    
    // Obtener información del curso
    $stmt = $pdo->prepare("SELECT nombre_curso, diseño, config_certificado FROM curso WHERE idcurso = ?");
    $stmt->execute([$idcurso]);
    $curso = $stmt->fetch();

    if (!$curso) {
        throw new Exception("Curso no encontrado");
    }

    $diseño = $curso['diseño'];
    $configGuardada = $curso['config_certificado'];
    $config = json_decode($configGuardada, true);

    // Obtener datos del alumno
    $stmt = $pdo->prepare("SELECT idcliente, nombre, apellido FROM cliente WHERE idcliente = ?");
    $stmt->execute([$idcliente]);
    $alumnoData = $stmt->fetch();
    if (!$alumnoData) {
        throw new Exception("Alumno no encontrado");
    }
    $alumno = $alumnoData['nombre'] . ' ' . $alumnoData['apellido'];

    // Obtener certificado existente
    $stmt = $pdo->prepare("SELECT codigo_validacion, codigo_qr FROM certificado_generado WHERE idcliente = ? AND idcurso = ? AND estado = 'Activo'");
    $stmt->execute([$idcliente, $idcurso]);
    $certificado_existente = $stmt->fetch();

    if (!$certificado_existente) {
        throw new Exception("Certificado no encontrado");
    }

    $codigo_validacion = $certificado_existente['codigo_validacion'];
    $nombre_archivo_qr = $certificado_existente['codigo_qr'];

    // Obtener datos de la inscripción
    $stmt = $pdo->prepare("SELECT nota_final, fecha_aprobacion FROM inscripcion WHERE idcliente = ? AND idcurso = ?");
    $stmt->execute([$idcliente, $idcurso]);
    $inscripcion = $stmt->fetch();
    
    $nota_final = $inscripcion['nota_final'] ?? '';
    $fecha_aprobacion = $inscripcion['fecha_aprobacion'] ? date('d/m/Y', strtotime($inscripcion['fecha_aprobacion'])) : date('d/m/Y');

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
        $instructor = 'NOMBRE DEL INSTRUCTOR';
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
        $especialista = 'NOMBRE DEL ESPECIALISTA';
        $firmaEspecialista = '../assets/img/qr_placeholder.png';
    }

    // Convertir imágenes a base64
    $fondo_base64 = '';
    if (!empty($diseño)) {
        $ruta_fondo = __DIR__ . '/../assets/uploads/cursos/' . $diseño;
        $fondo_base64 = convertirImagenABase64($ruta_fondo);
    }

    $firma_instructor_base64 = convertirImagenABase64(__DIR__ . '/' . $firmaInstructor);
    $firma_especialista_base64 = convertirImagenABase64(__DIR__ . '/' . $firmaEspecialista);
    $qr_base64 = convertirImagenABase64(__DIR__ . '/img/qr/' . $nombre_archivo_qr);

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
                    $html .= '<img src="' . $firma_instructor_base64 . '" alt="Firma Instructor" style="max-width: 120px; max-height: 60px; object-fit: contain;">';
                } else {
                    $html .= '<div style="border: 1px solid #ccc; padding: 10px; text-align: center; color: #666;">Firma Instructor</div>';
                }
            } elseif ($campo['tipo'] === 'firma_especialista') {
                if ($firma_especialista_base64) {
                    $html .= '<img src="' . $firma_especialista_base64 . '" alt="Firma Especialista" style="max-width: 120px; max-height: 60px; object-fit: contain;">';
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
                        $logo_base64 = convertirImagenABase64(__DIR__ . '/img/logo.png');
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

    return $dompdf->output();
}

// Si se accede directamente por GET
if (isset($_GET['idcliente']) && isset($_GET['idcurso'])) {
    $idcliente = (int)$_GET['idcliente'];
    $idcurso = (int)$_GET['idcurso'];

    try {
        $pdf = generarPDFDirecto($idcliente, $idcurso);

        // Descargar o mostrar el PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="certificado_' . $idcliente . '_' . $idcurso . '.pdf"');
        echo $pdf;
    } catch (Exception $e) {
        echo 'Error generando PDF: ' . $e->getMessage();
    }
}
?>
<?php include('footer.php'); ?> 