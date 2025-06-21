<?php
header('Content-Type: application/json');

// Función para generar QR con logo en el centro usando API externa
function generarQRConLogo($texto, $tamaño = 200, $logo_url = null) {
    $base_url = "https://api.qrserver.com/v1/create-qr-code/";
    $params = [
        'size' => $tamaño . 'x' . $tamaño,
        'data' => urlencode($texto),
        'format' => 'png',
        'margin' => '2',
        'ecc' => 'M' // Error correction level para permitir logo
    ];
    
    // Si hay logo, agregar parámetros para el logo
    if ($logo_url) {
        $params['logo'] = urlencode($logo_url);
        $params['logo_size'] = '30%'; // Tamaño del logo (30% del QR)
        $params['logo_bg'] = 'FFFFFF'; // Fondo blanco para el logo
        $params['logo_radius'] = '10'; // Bordes redondeados del logo
    }
    
    $url = $base_url . '?' . http_build_query($params);
    return $url;
}

// Función para generar QR con datos del certificado
function generarQRCertificado($idcurso, $idalumno, $fecha, $logo_url = null) {
    $datos = json_encode([
        'curso_id' => $idcurso,
        'alumno_id' => $idalumno,
        'fecha_emision' => $fecha,
        'verificacion_url' => 'http://localhost/certificado/verificar-certificado.php'
    ]);
    
    return generarQRConLogo($datos, 200, $logo_url);
}

// Función para obtener la URL del logo
function obtenerLogoURL() {
    $logo_path = 'img/logo.png';
    $logo_url = 'http://localhost/certificado/admin/' . $logo_path;
    
    // Verificar si el archivo existe
    if (file_exists($logo_path)) {
        return $logo_url;
    }
    
    return null;
}

// Si se llama con parámetros de certificado, generar imagen QR directamente
if (isset($_GET['certificado'])) {
    $idcurso = (int)$_GET['idcurso'];
    $idalumno = (int)$_GET['idalumno'];
    $fecha = $_GET['fecha'] ?? date('Y-m-d');
    $logo_url = isset($_GET['logo']) ? $_GET['logo'] : obtenerLogoURL();
    
    $qr_url = generarQRCertificado($idcurso, $idalumno, $fecha, $logo_url);
    
    // Redirigir a la imagen QR
    header('Location: ' . $qr_url);
    exit;
}

// Para otras llamadas, devolver JSON
header('Content-Type: application/json');

// Si se llama directamente
if (isset($_GET['texto'])) {
    $texto = $_GET['texto'];
    $tamaño = isset($_GET['tamaño']) ? (int)$_GET['tamaño'] : 200;
    $logo_url = isset($_GET['logo']) ? $_GET['logo'] : obtenerLogoURL();
    
    echo json_encode([
        'success' => true,
        'qr_url' => generarQRConLogo($texto, $tamaño, $logo_url),
        'logo_url' => $logo_url
    ]);
} elseif (isset($_GET['test'])) {
    // Función de prueba para verificar que el logo funciona
    $logo_url = obtenerLogoURL();
    $test_data = "Test QR con logo - " . date('Y-m-d H:i:s');
    
    echo json_encode([
        'success' => true,
        'qr_url' => generarQRConLogo($test_data, 200, $logo_url),
        'logo_url' => $logo_url,
        'test_data' => $test_data
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Parámetros requeridos: texto, certificado o test',
        'ejemplos' => [
            'texto' => '?texto=Hola Mundo&tamaño=200',
            'certificado' => '?certificado=1&idcurso=1&idalumno=1&fecha=2024-01-15',
            'test' => '?test=1'
        ]
    ]);
}
?> 