<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;

// Obtener parámetros
$size = isset($_GET['size']) ? (int)$_GET['size'] : 300;
$color = isset($_GET['color']) ? $_GET['color'] : '#000000';
$bgColor = isset($_GET['bgColor']) ? $_GET['bgColor'] : '#FFFFFF';
$margin = isset($_GET['margin']) ? (int)$_GET['margin'] : 2;
$data = isset($_GET['data']) ? $_GET['data'] : 'CERTIFICADO-TEST-' . date('YmdHis');

// Convertir colores hex a RGB
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    ];
}

$foregroundRgb = hexToRgb($color);
$backgroundRgb = hexToRgb($bgColor);

try {
    // Crear el código QR
    $qrCode = new QrCode(
        $data,
        new Encoding('UTF-8'),
        ErrorCorrectionLevel::High,
        $size,
        $margin,
        RoundBlockSizeMode::Margin,
        new Color($foregroundRgb[0], $foregroundRgb[1], $foregroundRgb[2]),
        new Color($backgroundRgb[0], $backgroundRgb[1], $backgroundRgb[2])
    );

    $writer = new SvgWriter();
    $result = $writer->write($qrCode);
    $svg = $result->getString();

    // Enviar como SVG
    header('Content-Type: image/svg+xml');
    echo $svg;
    
} catch (Exception $e) {
    // En caso de error, mostrar SVG de error
    header('Content-Type: image/svg+xml');
    echo '<svg width="300" height="300" xmlns="http://www.w3.org/2000/svg">
        <rect width="300" height="300" fill="#f0f0f0"/>
        <text x="150" y="150" text-anchor="middle" dy=".3em" fill="#ff0000" font-family="Arial" font-size="16">Error QR</text>
    </svg>';
}
?> 