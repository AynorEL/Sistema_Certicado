<?php
require_once('inc/config.php');
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Editor Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .firma-test { max-width: 120px; max-height: 60px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>Test Editor Fix - Verificación de Datos</h1>
    
    <?php
    $idcurso = 2; // ID del curso a probar
    
    echo "<div class='test-section'>";
    echo "<h2>1. Verificación de Conexión a Base de Datos</h2>";
    try {
        $stmt = $pdo->query("SELECT 1");
        echo "<div class='success'>✅ Conexión a base de datos exitosa</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    echo "<div class='test-section'>";
    echo "<h2>2. Datos del Curso</h2>";
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, i.nombre as nombre_instructor, i.apellido as apellido_instructor,
                   i.firma_digital as firma_instructor
            FROM curso c
            LEFT JOIN instructor i ON c.idinstructor = i.idinstructor
            WHERE c.idcurso = ?
        ");
        $stmt->execute([$idcurso]);
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($curso) {
            echo "<div class='success'>✅ Curso encontrado: " . htmlspecialchars($curso['nombre_curso']) . "</div>";
            echo "<pre>" . print_r($curso, true) . "</pre>";
        } else {
            echo "<div class='error'>❌ Curso no encontrado</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    echo "<div class='test-section'>";
    echo "<h2>3. Instructores</h2>";
    try {
        $stmt = $pdo->prepare("
            SELECT idinstructor, nombre, apellido, especialidad, firma_digital as firma
            FROM instructor
            ORDER BY nombre, apellido
        ");
        $stmt->execute();
        $instructores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($instructores) {
            echo "<div class='success'>✅ Instructores encontrados: " . count($instructores) . "</div>";
            foreach ($instructores as $instructor) {
                echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #eee;'>";
                echo "<strong>" . htmlspecialchars($instructor['nombre'] . ' ' . $instructor['apellido']) . "</strong><br>";
                echo "Especialidad: " . htmlspecialchars($instructor['especialidad']) . "<br>";
                echo "Firma: " . ($instructor['firma'] ? htmlspecialchars($instructor['firma']) : 'No tiene firma') . "<br>";
                if ($instructor['firma']) {
                    $ruta_firma = "../assets/uploads/firmas/" . $instructor['firma'];
                    if (file_exists($ruta_firma)) {
                        echo "<img src='$ruta_firma' class='firma-test' alt='Firma'> ✅ Archivo existe";
                    } else {
                        echo "<div class='error'>❌ Archivo no encontrado: $ruta_firma</div>";
                    }
                }
                echo "</div>";
            }
        } else {
            echo "<div class='warning'>⚠️ No hay instructores registrados</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    echo "<div class='test-section'>";
    echo "<h2>4. Especialistas</h2>";
    try {
        $stmt = $pdo->prepare("
            SELECT idespecialista, nombre, apellido, especialidad, firma_especialista as firma
            FROM especialista
            ORDER BY nombre, apellido
        ");
        $stmt->execute();
        $especialistas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($especialistas) {
            echo "<div class='success'>✅ Especialistas encontrados: " . count($especialistas) . "</div>";
            foreach ($especialistas as $especialista) {
                echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #eee;'>";
                echo "<strong>" . htmlspecialchars($especialista['nombre'] . ' ' . $especialista['apellido']) . "</strong><br>";
                echo "Especialidad: " . htmlspecialchars($especialista['especialidad']) . "<br>";
                echo "Firma: " . ($especialista['firma'] ? htmlspecialchars($especialista['firma']) : 'No tiene firma') . "<br>";
                if ($especialista['firma']) {
                    $ruta_firma = "../assets/uploads/firmas/" . $especialista['firma'];
                    if (file_exists($ruta_firma)) {
                        echo "<img src='$ruta_firma' class='firma-test' alt='Firma'> ✅ Archivo existe";
                    } else {
                        echo "<div class='error'>❌ Archivo no encontrado: $ruta_firma</div>";
                    }
                }
                echo "</div>";
            }
        } else {
            echo "<div class='warning'>⚠️ No hay especialistas registrados</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    echo "<div class='test-section'>";
    echo "<h2>5. Test API obtener_datos_certificado.php</h2>";
    try {
        $url = "obtener_datos_certificado.php?idcurso=$idcurso";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        
        if ($data && isset($data['success'])) {
            if ($data['success']) {
                echo "<div class='success'>✅ API funcionando correctamente</div>";
                echo "<pre>" . print_r($data, true) . "</pre>";
            } else {
                echo "<div class='error'>❌ API error: " . htmlspecialchars($data['message']) . "</div>";
            }
        } else {
            echo "<div class='error'>❌ Respuesta inválida de la API</div>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error al llamar API: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    echo "<div class='test-section'>";
    echo "<h2>6. Verificación de Rutas de Archivos</h2>";
    $rutas_a_verificar = [
        '../assets/uploads/firmas/',
        '../assets/uploads/cursos/',
        'assets/img/'
    ];
    
    foreach ($rutas_a_verificar as $ruta) {
        if (is_dir($ruta)) {
            echo "<div class='success'>✅ Directorio existe: $ruta</div>";
            $archivos = scandir($ruta);
            echo "<small>Archivos: " . implode(', ', array_filter($archivos, function($f) { return $f != '.' && $f != '..'; })) . "</small><br>";
        } else {
            echo "<div class='error'>❌ Directorio no existe: $ruta</div>";
        }
    }
    echo "</div>";
    ?>
    
    <div class='test-section'>
        <h2>7. Test JavaScript</h2>
        <button onclick="testJavaScript()">Probar JavaScript</button>
        <div id="js-result"></div>
    </div>
    
    <script>
    function testJavaScript() {
        const result = document.getElementById('js-result');
        result.innerHTML = '';
        
        try {
            // Test 1: Verificar que los elementos existen
            const testElements = ['selectAlumno', 'selectInstructor', 'selectEspecialista'];
            testElements.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    result.innerHTML += `<div class='success'>✅ Elemento ${id} existe</div>`;
                } else {
                    result.innerHTML += `<div class='error'>❌ Elemento ${id} no existe</div>`;
                }
            });
            
            // Test 2: Verificar fetch
            fetch('obtener_datos_certificado.php?idcurso=<?php echo $idcurso; ?>')
                .then(response => response.json())
                .then(data => {
                    result.innerHTML += `<div class='success'>✅ Fetch funcionando - ${data.inscritos ? data.inscritos.length : 0} inscritos</div>`;
                })
                .catch(error => {
                    result.innerHTML += `<div class='error'>❌ Error en fetch: ${error.message}</div>`;
                });
                
        } catch (error) {
            result.innerHTML += `<div class='error'>❌ Error JavaScript: ${error.message}</div>`;
        }
    }
    </script>
</body>
</html> 