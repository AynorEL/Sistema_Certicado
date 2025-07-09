<?php
require_once('inc/config.php');
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Función para obtener el HTML desde la URL del certificado
function obtenerHTMLCertificado($idcliente, $idcurso) {
    global $pdo; // IMPORTANTE: añadir esto

    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];

    $stmt = $pdo->prepare("SELECT codigo_validacion FROM certificado_generado WHERE idcliente = ? AND idcurso = ? AND estado = 'Activo'");
    $stmt->execute([$idcliente, $idcurso]);
    $certificado = $stmt->fetch();

    if (!$certificado) {
        throw new Exception("Certificado no encontrado.");
    }

    $codigo = $certificado['codigo_validacion'];
    $url = $protocolo . '://' . $host . '/certificado/admin/previsualizar_certificado_final.php?modo=final&idcurso=' . $idcurso . '&idalumno=' . $idcliente;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
    ]);
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$html) {
        throw new Exception("No se pudo obtener el HTML del certificado. Código HTTP: $httpCode");
    }

    // Extraer solo el contenido del certificado (sin controles ni información del QR)
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    
    // Buscar el contenedor del certificado
    $certificateContainer = $xpath->query('//div[@class="certificate-container"]')->item(0);
    
    if (!$certificateContainer) {
        throw new Exception("No se encontró el contenedor del certificado en el HTML.");
    }
    
    // Crear un nuevo documento HTML solo con el certificado
    $cleanHTML = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: white;
            font-family: Arial, sans-serif;
        }
        
        .certificate-container {
            width: 2000px;
            height: 1414px;
            position: relative;
            background: white;
            margin: 0;
            border-radius: 0;
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
            overflow: hidden;
            word-wrap: break-word;
        }
        
        .no-background {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            color: #6c757d;
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
<body>' . $dom->saveHTML($certificateContainer) . '</body></html>';

    return $cleanHTML;
}

// Función para generar el PDF desde el HTML
function generarPDFCertificado($idcliente, $idcurso) {
    $html = obtenerHTMLCertificado($idcliente, $idcurso);

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);

    $width = 2000 * 0.75;
    $height = 1414 * 0.75;
    $dompdf->setPaper([0, 0, $width, $height], 'portrait');

    $dompdf->render();

    return $dompdf->output();
}

// Si se accede directamente por GET
if (isset($_GET['idcliente']) && isset($_GET['idcurso'])) {
    $idcliente = (int)$_GET['idcliente'];
    $idcurso = (int)$_GET['idcurso'];

    try {
        $pdf = generarPDFCertificado($idcliente, $idcurso);

        // Descargar o mostrar el PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="certificado_' . $idcliente . '_' . $idcurso . '.pdf"');
        echo $pdf;
    } catch (Exception $e) {
        echo 'Error generando PDF: ' . $e->getMessage();
    }
}
?>
 