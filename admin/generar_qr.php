<?php
require_once '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

// Obtener datos del certificado
$idcurso = isset($_GET['idcurso']) ? (int)$_GET['idcurso'] : 0;
$idalumno = isset($_GET['idalumno']) ? (int)$_GET['idalumno'] : 0;
$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$test = isset($_GET['test']) ? (bool)$_GET['test'] : false;

// Obtener parámetros de configuración del QR
$size = isset($_GET['size']) ? (int)$_GET['size'] : 120;
$color = isset($_GET['color']) ? $_GET['color'] : '#000000';
$bgColor = isset($_GET['bgColor']) ? $_GET['bgColor'] : '#FFFFFF';
$margin = isset($_GET['margin']) ? (int)$_GET['margin'] : 2;

// Datos para el QR
if ($test) {
    $qrData = "CERTIFICADO-TEST-" . date('YmdHis');
} elseif ($codigo) {
    // Usar código de validación específico
    $qrData = "https://" . $_SERVER['HTTP_HOST'] . "/certificado/verificar-certificado.php?codigo=" . $codigo;
} else {
    // En producción, usar datos reales del certificado
    $qrData = "CERTIFICADO-{$idcurso}-{$idalumno}-" . date('YmdHis');
}

try {
    // Crear el código QR con la API correcta (versión 6)
    $qrCode = new QrCode(
        $qrData,
        new Encoding('UTF-8'),
        ErrorCorrectionLevel::High,
        $size,
        $margin,
        RoundBlockSizeMode::Margin,
        new Color(
            hexdec(substr($color, 1, 2)),
            hexdec(substr($color, 3, 2)),
            hexdec(substr($color, 5, 2))
        ),
        new Color(
            hexdec(substr($bgColor, 1, 2)),
            hexdec(substr($bgColor, 3, 2)),
            hexdec(substr($bgColor, 5, 2))
        )
    );
    
    $writer = new PngWriter();
    $result = $writer->write($qrCode);

    // Enviar como imagen PNG
    header('Content-Type: ' . $result->getMimeType());
    echo $result->getString();
    
} catch (Exception $e) {
    // En caso de error, mostrar imagen de error
    header('Content-Type: image/png');
    $errorImage = imagecreate(300, 300);
    $bgColor = imagecolorallocate($errorImage, 255, 255, 255);
    $textColor = imagecolorallocate($errorImage, 255, 0, 0);
    imagestring($errorImage, 5, 50, 140, 'Error QR', $textColor);
    imagepng($errorImage);
    imagedestroy($errorImage);
}
?> 