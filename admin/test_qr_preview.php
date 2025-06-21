<?php
// Archivo de prueba para verificar la generación de QR
require_once('inc/config.php');

$idcurso = 1;
$idalumno = 1;
$fecha = date('Y-m-d');

// URL del QR
$qr_url = "generar_qr.php?certificado=1&idcurso=$idcurso&idalumno=$idalumno&fecha=$fecha";

echo "<h2>Prueba de Generación de QR</h2>";
echo "<p><strong>URL del QR:</strong> $qr_url</p>";
echo "<p><strong>Imagen del QR:</strong></p>";
echo "<img src='$qr_url' alt='QR Test' style='border: 1px solid #ccc;'>";

echo "<h3>Datos del QR:</h3>";
$datos = json_encode([
    'curso_id' => $idcurso,
    'alumno_id' => $idalumno,
    'fecha_emision' => $fecha,
    'verificacion_url' => 'http://localhost/certificado/verificar-certificado.php'
]);
echo "<p><strong>Datos codificados:</strong> " . htmlspecialchars($datos) . "</p>";

echo "<h3>Prueba con API externa:</h3>";
$api_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($datos) . "&format=png&margin=2&ecc=M";
echo "<p><strong>URL de la API:</strong> $api_url</p>";
echo "<img src='$api_url' alt='QR API Test' style='border: 1px solid #ccc;'>";
?> 