<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

// Función para verificar firmas
function verificarFirmas() {
    $firmasDir = __DIR__ . '/../assets/uploads/firmas/';
    $firmas = [];
    
    echo "<h3>🔍 Verificación de Firmas</h3>";
    echo "<p><strong>Directorio:</strong> $firmasDir</p>";
    
    if (!is_dir($firmasDir)) {
        echo "<p style='color: red;'>❌ El directorio no existe</p>";
        return $firmas;
    }
    
    echo "<p style='color: green;'>✅ El directorio existe</p>";
    
    $archivos = scandir($firmasDir);
    echo "<p><strong>Archivos encontrados:</strong></p>";
    echo "<ul>";
    
    foreach ($archivos as $archivo) {
        if ($archivo !== '.' && $archivo !== '..') {
            $extension = pathinfo($archivo, PATHINFO_EXTENSION);
            $rutaCompleta = $firmasDir . $archivo;
            $url = '../assets/uploads/firmas/' . $archivo;
            
            echo "<li>";
            echo "<strong>$archivo</strong> (extensión: $extension)";
            
            if (is_file($rutaCompleta)) {
                echo " - ✅ Archivo existe";
                if ($extension === 'png') {
                    echo " - ✅ Es PNG";
                    $firmas[] = [
                        'nombre' => pathinfo($archivo, PATHINFO_FILENAME),
                        'archivo' => $archivo,
                        'url' => $url,
                        'ruta_completa' => $rutaCompleta
                    ];
                } else {
                    echo " - ❌ No es PNG";
                }
            } else {
                echo " - ❌ Archivo no existe";
            }
            echo "</li>";
        }
    }
    echo "</ul>";
    
    return $firmas;
}

// Verificar firmas de instructores en la BD
function verificarFirmasBD() {
    global $pdo;
    
    echo "<h3>🗄️ Firmas en Base de Datos</h3>";
    
    // Verificar instructores
    $stmt = $pdo->prepare("SELECT idinstructor, nombre, apellido, firma_digital FROM instructor");
    $stmt->execute();
    $instructores = $stmt->fetchAll();
    
    echo "<h4>Instructores:</h4>";
    echo "<ul>";
    foreach ($instructores as $instructor) {
        echo "<li>";
        echo "<strong>{$instructor['nombre']} {$instructor['apellido']}</strong>";
        if (!empty($instructor['firma_digital'])) {
            echo " - Firma: {$instructor['firma_digital']}";
            $rutaFirma = __DIR__ . '/../assets/uploads/firmas/' . $instructor['firma_digital'];
            if (file_exists($rutaFirma)) {
                echo " - ✅ Archivo existe";
            } else {
                echo " - ❌ Archivo no existe";
            }
        } else {
            echo " - ❌ Sin firma";
        }
        echo "</li>";
    }
    echo "</ul>";
    
    // Verificar especialistas
    $stmt = $pdo->prepare("SELECT idespecialista, nombre, apellido, firma_especialista FROM especialista");
    $stmt->execute();
    $especialistas = $stmt->fetchAll();
    
    echo "<h4>Especialistas:</h4>";
    echo "<ul>";
    foreach ($especialistas as $especialista) {
        echo "<li>";
        echo "<strong>{$especialista['nombre']} {$especialista['apellido']}</strong>";
        if (!empty($especialista['firma_especialista'])) {
            echo " - Firma: {$especialista['firma_especialista']}";
            $rutaFirma = __DIR__ . '/../assets/uploads/firmas/' . $especialista['firma_especialista'];
            if (file_exists($rutaFirma)) {
                echo " - ✅ Archivo existe";
            } else {
                echo " - ❌ Archivo no existe";
            }
        } else {
            echo " - ❌ Sin firma";
        }
        echo "</li>";
    }
    echo "</ul>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Firmas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        ul { margin: 10px 0; }
        li { margin: 5px 0; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Verificación de Firmas - Editor de Certificados</h1>
    
    <div class="section">
        <?php 
        $firmas = verificarFirmas();
        ?>
        
        <h3>📋 Resumen de Firmas Encontradas</h3>
        <?php if (empty($firmas)): ?>
            <p class="error">❌ No se encontraron firmas PNG</p>
        <?php else: ?>
            <p class="success">✅ Se encontraron <?php echo count($firmas); ?> firmas</p>
            <pre><?php print_r($firmas); ?></pre>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <?php verificarFirmasBD(); ?>
    </div>
    
    <div class="section">
        <h3>🧪 Prueba de Carga de Datos</h3>
        <button onclick="probarCargaDatos()">Probar Carga de Datos</button>
        <div id="resultado"></div>
    </div>
    
    <script>
        async function probarCargaDatos() {
            const resultado = document.getElementById('resultado');
            resultado.innerHTML = '<p>🔄 Probando carga de datos...</p>';
            
            try {
                const response = await fetch('cargar_datos_prueba.php?idcurso=1');
                const data = await response.json();
                
                if (data.success) {
                    resultado.innerHTML = `
                        <div style="color: green; margin: 10px 0;">
                            ✅ Carga exitosa
                            <h4>Firmas cargadas:</h4>
                            <pre>${JSON.stringify(data.datos.firmas, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    resultado.innerHTML = `
                        <div style="color: red; margin: 10px 0;">
                            ❌ Error: ${data.message}
                        </div>
                    `;
                }
            } catch (error) {
                resultado.innerHTML = `
                    <div style="color: red; margin: 10px 0;">
                        ❌ Error de conexión: ${error.message}
                    </div>
                `;
            }
        }
    </script>
</body>
</html> 