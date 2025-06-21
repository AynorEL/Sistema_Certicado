<?php
require_once('inc/config.php');
header('Content-Type: text/html; charset=utf-8');

$idcurso = isset($_GET['id']) ? (int)$_GET['id'] : 2;

echo "<h2>Prueba Simple de API - Curso ID: $idcurso</h2>";

try {
    // Simular la llamada a la API
    $url = "obtener_datos_certificado.php?idcurso=$idcurso";
    echo "<p><strong>URL de la API:</strong> $url</p>";
    
    // Hacer la llamada usando cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>Código HTTP:</strong> $httpCode</p>";
    
    if ($response) {
        echo "<h3>Respuesta de la API:</h3>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        // Intentar decodificar JSON
        $data = json_decode($response, true);
        if ($data) {
            echo "<h3>Datos Decodificados:</h3>";
            echo "<p><strong>Success:</strong> " . ($data['success'] ? 'true' : 'false') . "</p>";
            
            if (isset($data['inscritos'])) {
                echo "<p><strong>Inscritos:</strong> " . count($data['inscritos']) . "</p>";
            }
            
            if (isset($data['instructores'])) {
                echo "<p><strong>Instructores:</strong> " . count($data['instructores']) . "</p>";
            }
            
            if (isset($data['especialistas'])) {
                echo "<p><strong>Especialistas:</strong> " . count($data['especialistas']) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Error al decodificar JSON</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se recibió respuesta de la API</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
h2, h3 { color: #333; }
</style> 