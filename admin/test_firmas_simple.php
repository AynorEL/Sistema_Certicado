<?php
require_once('inc/config.php');
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Firmas Simple</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .firma-container { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .firma-img { max-width: 120px; max-height: 60px; border: 1px solid #ccc; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Test Firmas Simple</h1>
    
    <?php
    // Test 1: Verificar instructores y sus firmas
    echo "<h2>Instructores y Firmas</h2>";
    try {
        $stmt = $pdo->prepare("
            SELECT idinstructor, nombre, apellido, firma_digital
            FROM instructor
            ORDER BY nombre, apellido
        ");
        $stmt->execute();
        $instructores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($instructores as $instructor) {
            echo "<div class='firma-container'>";
            echo "<h3>" . htmlspecialchars($instructor['nombre'] . ' ' . $instructor['apellido']) . "</h3>";
            echo "<p>Firma: " . ($instructor['firma_digital'] ? htmlspecialchars($instructor['firma_digital']) : 'No tiene firma') . "</p>";
            
            if ($instructor['firma_digital']) {
                $ruta_firma = "../assets/uploads/firmas/" . $instructor['firma_digital'];
                if (file_exists($ruta_firma)) {
                    echo "<img src='$ruta_firma' class='firma-img' alt='Firma'>";
                    echo "<p class='success'>✅ Archivo existe</p>";
                } else {
                    echo "<p class='error'>❌ Archivo no encontrado: $ruta_firma</p>";
                }
            }
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    }
    
    // Test 2: Verificar especialistas y sus firmas
    echo "<h2>Especialistas y Firmas</h2>";
    try {
        $stmt = $pdo->prepare("
            SELECT idespecialista, nombre, apellido, firma_especialista
            FROM especialista
            ORDER BY nombre, apellido
        ");
        $stmt->execute();
        $especialistas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($especialistas as $especialista) {
            echo "<div class='firma-container'>";
            echo "<h3>" . htmlspecialchars($especialista['nombre'] . ' ' . $especialista['apellido']) . "</h3>";
            echo "<p>Firma: " . ($especialista['firma_especialista'] ? htmlspecialchars($especialista['firma_especialista']) : 'No tiene firma') . "</p>";
            
            if ($especialista['firma_especialista']) {
                $ruta_firma = "../assets/uploads/firmas/" . $especialista['firma_especialista'];
                if (file_exists($ruta_firma)) {
                    echo "<img src='$ruta_firma' class='firma-img' alt='Firma'>";
                    echo "<p class='success'>✅ Archivo existe</p>";
                } else {
                    echo "<p class='error'>❌ Archivo no encontrado: $ruta_firma</p>";
                }
            }
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    }
    
    // Test 3: Verificar directorio de firmas
    echo "<h2>Verificación de Directorio</h2>";
    $directorio_firmas = "../assets/uploads/firmas/";
    if (is_dir($directorio_firmas)) {
        echo "<p class='success'>✅ Directorio existe: $directorio_firmas</p>";
        $archivos = scandir($directorio_firmas);
        $archivos_firmas = array_filter($archivos, function($f) { return $f != '.' && $f != '..'; });
        echo "<p>Archivos en el directorio: " . implode(', ', $archivos_firmas) . "</p>";
    } else {
        echo "<p class='error'>❌ Directorio no existe: $directorio_firmas</p>";
    }
    ?>
    
    <h2>Test JavaScript</h2>
    <button onclick="testFirmasJS()">Probar Carga de Firmas con JavaScript</button>
    <div id="resultado-js"></div>
    
    <script>
    function testFirmasJS() {
        const resultado = document.getElementById('resultado-js');
        resultado.innerHTML = '<p>Probando carga de datos...</p>';
        
        fetch('obtener_datos_certificado.php?idcurso=2')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '<h3>Datos cargados correctamente:</h3>';
                    
                    if (data.instructores && data.instructores.length > 0) {
                        html += '<h4>Instructores:</h4>';
                        data.instructores.forEach(instructor => {
                            html += `<p>${instructor.nombre} ${instructor.apellido} - Firma: ${instructor.firma || 'No tiene'}</p>`;
                        });
                    }
                    
                    if (data.especialistas && data.especialistas.length > 0) {
                        html += '<h4>Especialistas:</h4>';
                        data.especialistas.forEach(especialista => {
                            html += `<p>${especialista.nombre} ${especialista.apellido} - Firma: ${especialista.firma || 'No tiene'}</p>`;
                        });
                    }
                    
                    resultado.innerHTML = html;
                } else {
                    resultado.innerHTML = `<p class='error'>Error: ${data.message}</p>`;
                }
            })
            .catch(error => {
                resultado.innerHTML = `<p class='error'>Error en fetch: ${error.message}</p>`;
            });
    }
    </script>
</body>
</html> 