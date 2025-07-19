<?php
$base_url = '/certificado/';
$base_path = $_SERVER['DOCUMENT_ROOT'] . '/certificado/';

// Listar todos los archivos en la carpeta de firmas
$dir_firmas = $base_path . 'assets/uploads/firmas/';
$archivos_firmas = [];
if (is_dir($dir_firmas)) {
    $archivos_firmas = array_values(array_filter(scandir($dir_firmas), function($f) {
        return !in_array($f, ['.', '..']);
    }));
}

// Buscar archivos dinámicos
$idcurso = 1; // o el curso que quieras probar
$firma_recortada = null;
$firma_instructor = null;
$firma_especialista = null;
foreach ($archivos_firmas as $f) {
    if (!$firma_recortada && $f === "firma_recortada_{$idcurso}.png") $firma_recortada = $f;
    if (!$firma_instructor && strpos($f, 'instructor_firma_') === 0) $firma_instructor = $f;
    if (!$firma_especialista && strpos($f, 'especialista_firma_') === 0) $firma_especialista = $f;
}

$imagenes = [
    'Logo QR' => 'admin/img/logo.png',
    'Firma recortada curso 1' => $firma_recortada ? 'assets/uploads/firmas/' . $firma_recortada : '',
    'Firma original instructor' => $firma_instructor ? 'assets/uploads/firmas/' . $firma_instructor : '',
    'Firma original especialista' => $firma_especialista ? 'assets/uploads/firmas/' . $firma_especialista : '',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test de Imágenes - Frontend y Backend</title>
    <style>img { border:1px solid #ccc; margin:4px; }</style>
</head>
<body>
    <h2>Archivos en assets/uploads/firmas/</h2>
    <ul>
        <?php foreach ($archivos_firmas as $archivo): ?>
            <li><?php echo htmlspecialchars($archivo); ?></li>
        <?php endforeach; ?>
    </ul>
    <h3>Ejemplo de uso en backend:</h3>
    <pre style="background:#f8f8f8;padding:10px;">
// Para curso 1:
$idcurso = 1;
$firma_recortada = '/certificado/assets/uploads/firmas/firma_recortada_' . $idcurso . '.png';
$firma_original = '/certificado/assets/uploads/firmas/NOMBRE_EXACTO.png'; // Copia el nombre exacto de la lista
if (file_exists($_SERVER['DOCUMENT_ROOT'] . $firma_recortada)) {
    $firma = $firma_recortada;
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . $firma_original)) {
    $firma = $firma_original;
} else {
    $firma = '/certificado/assets/img/qr_placeholder.png';
}
    </pre>
    <h2>Test de Imágenes - Frontend (Navegador)</h2>
    <ul>
        <?php foreach ($imagenes as $nombre => $ruta): if (!$ruta) continue; ?>
            <li><?php echo $nombre; ?>:<br>
                <img src="<?php echo $base_url . $ruta; ?>" style="width:120px">
                <span style="font-size:12px;">(<?php echo $base_url . $ruta; ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>
    <h2>Test de Imágenes - Backend (PHP)</h2>
    <ul>
        <?php foreach ($imagenes as $nombre => $ruta): if (!$ruta) continue;
            $full_path = $base_path . $ruta;
            if (file_exists($full_path)) {
                echo "<li style='color:green;'>$nombre: OK ($full_path)</li>";
            } else {
                echo "<li style='color:red;'>$nombre: NO ENCONTRADA ($full_path)</li>";
            }
        endforeach; ?>
    </ul>
    <p>Si ves la imagen, está OK en frontend. Si está en verde, está OK en backend. Si ves un ícono roto o está en rojo, la imagen no existe o la ruta es incorrecta.</p>
</body>
</html> 