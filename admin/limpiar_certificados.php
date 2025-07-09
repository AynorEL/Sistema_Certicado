<?php
require_once 'inc/config.php';

// Script para limpiar certificados antiguos y archivos QR no utilizados
// Se puede ejecutar manualmente o programar con cron

echo "<h1>🧹 Limpieza de Certificados y Archivos QR</h1>";

// Configuración
$dias_limpiar = 365; // Limpiar certificados más antiguos de 1 año
$limpiar_archivos = true; // También limpiar archivos QR
$modo_prueba = isset($_GET['test']); // Modo prueba (no elimina realmente)

if ($modo_prueba) {
    echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "🔍 <strong>MODO PRUEBA</strong> - No se eliminarán archivos realmente";
    echo "</div>";
}

// 1. Limpiar certificados antiguos de la BD
echo "<h2>1. Limpieza de Base de Datos</h2>";

try {
    $fecha_limite = date('Y-m-d H:i:s', strtotime("-{$dias_limpiar} days"));
    
    // Contar certificados a eliminar
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM certificado_generado 
        WHERE fecha_generacion < ? AND estado = 'Inactivo'
    ");
    $stmt->execute([$fecha_limite]);
    $total_eliminar = $stmt->fetch()['total'];
    
    echo "📊 Certificados inactivos más antiguos de {$dias_limpiar} días: {$total_eliminar}<br>";
    
    if ($total_eliminar > 0) {
        if (!$modo_prueba) {
            // Obtener archivos QR antes de eliminar
            $stmt = $pdo->prepare("
                SELECT codigo_qr 
                FROM certificado_generado 
                WHERE fecha_generacion < ? AND estado = 'Inactivo'
            ");
            $stmt->execute([$fecha_limite]);
            $archivos_qr = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Eliminar de la BD
            $stmt = $pdo->prepare("
                DELETE FROM certificado_generado 
                WHERE fecha_generacion < ? AND estado = 'Inactivo'
            ");
            $stmt->execute([$fecha_limite]);
            
            echo "✅ Se eliminaron {$total_eliminar} certificados de la BD<br>";
            
            // Eliminar archivos QR correspondientes
            if ($limpiar_archivos) {
                $qr_dir = __DIR__ . '/img/qr/';
                $eliminados = 0;
                
                foreach ($archivos_qr as $archivo) {
                    $ruta_completa = $qr_dir . $archivo;
                    if (file_exists($ruta_completa)) {
                        unlink($ruta_completa);
                        $eliminados++;
                    }
                }
                
                echo "✅ Se eliminaron {$eliminados} archivos QR<br>";
            }
        } else {
            echo "🔍 <strong>MODO PRUEBA:</strong> Se habrían eliminado {$total_eliminar} certificados<br>";
        }
    } else {
        echo "✅ No hay certificados antiguos para eliminar<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error al limpiar BD: " . $e->getMessage() . "<br>";
}

// 2. Limpiar archivos QR huérfanos
echo "<h2>2. Limpieza de Archivos QR Huérfanos</h2>";

try {
    $qr_dir = __DIR__ . '/img/qr/';
    
    if (is_dir($qr_dir)) {
        $archivos_qr = glob($qr_dir . '*.png');
        $archivos_huérfanos = 0;
        
        foreach ($archivos_qr as $archivo) {
            $nombre_archivo = basename($archivo);
            
            // Verificar si existe en la BD
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM certificado_generado WHERE codigo_qr = ?");
            $stmt->execute([$nombre_archivo]);
            $existe = $stmt->fetch()['total'];
            
            if ($existe == 0) {
                $archivos_huérfanos++;
                echo "📄 Archivo huérfano encontrado: {$nombre_archivo}<br>";
                
                if (!$modo_prueba && $limpiar_archivos) {
                    unlink($archivo);
                    echo "✅ Eliminado: {$nombre_archivo}<br>";
                }
            }
        }
        
        if ($archivos_huérfanos == 0) {
            echo "✅ No se encontraron archivos QR huérfanos<br>";
        } else {
            if ($modo_prueba) {
                echo "🔍 <strong>MODO PRUEBA:</strong> Se habrían eliminado {$archivos_huérfanos} archivos huérfanos<br>";
            } else {
                echo "✅ Se eliminaron {$archivos_huérfanos} archivos huérfanos<br>";
            }
        }
    } else {
        echo "❌ Directorio QR no existe<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error al limpiar archivos: " . $e->getMessage() . "<br>";
}

// 3. Estadísticas finales
echo "<h2>3. Estadísticas Finales</h2>";

try {
    // Total de certificados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM certificado_generado");
    $total_certificados = $stmt->fetch()['total'];
    
    // Certificados por estado
    $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM certificado_generado GROUP BY estado");
    $estados = $stmt->fetchAll();
    
    // Certificados verificados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM certificado_generado WHERE fecha_verificacion IS NOT NULL");
    $verificados = $stmt->fetch()['total'];
    
    // Tamaño total de archivos QR
    $qr_dir = __DIR__ . '/img/qr/';
    $tamaño_total = 0;
    if (is_dir($qr_dir)) {
        $archivos_qr = glob($qr_dir . '*.png');
        foreach ($archivos_qr as $archivo) {
            $tamaño_total += filesize($archivo);
        }
    }
    
    echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
    echo "<h3>📊 Resumen del Sistema</h3>";
    echo "<ul>";
    echo "<li><strong>Total de certificados:</strong> {$total_certificados}</li>";
    echo "<li><strong>Certificados verificados:</strong> {$verificados}</li>";
    echo "<li><strong>Tamaño total de archivos QR:</strong> " . number_format($tamaño_total) . " bytes (" . number_format($tamaño_total / 1024 / 1024, 2) . " MB)</li>";
    echo "</ul>";
    
    echo "<h4>Por Estado:</h4>";
    echo "<ul>";
    foreach ($estados as $estado) {
        echo "<li><strong>{$estado['estado']}:</strong> {$estado['total']}</li>";
    }
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ Error al obtener estadísticas: " . $e->getMessage() . "<br>";
}

// 4. Recomendaciones
echo "<h2>4. Recomendaciones</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px;'>";
echo "<h3>💡 Configuración Recomendada</h3>";
echo "<ul>";
echo "<li><strong>Cron Job:</strong> Ejecutar este script semanalmente</li>";
echo "<li><strong>Backup:</strong> Hacer backup antes de ejecutar limpieza</li>";
echo "<li><strong>Monitoreo:</strong> Revisar logs de limpieza regularmente</li>";
echo "<li><strong>Retención:</strong> Considerar aumentar días de retención según necesidades</li>";
echo "</ul>";

echo "<h3>🔧 Comandos Útiles</h3>";
echo "<code># Ejecutar limpieza automática (cron)<br>";
echo "0 2 * * 0 /usr/bin/php /ruta/a/certificado/admin/limpiar_certificados.php<br><br>";
echo "# Ejecutar en modo prueba<br>";
echo "php limpiar_certificados.php?test=1</code>";
echo "</div>";

echo "<p><a href='certificados_generados.php' class='btn btn-primary'>Ver Certificados</a> ";
echo "<a href='limpiar_certificados.php?test=1' class='btn btn-warning'>Modo Prueba</a></p>";

?>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #007bff; }
h2 { color: #28a745; margin-top: 30px; }
h3 { color: #6c757d; }
.btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
.btn:hover { background: #0056b3; color: white; text-decoration: none; }
.btn-warning { background: #ffc107; color: #212529; }
.btn-warning:hover { background: #e0a800; color: #212529; }
code { background: #f8f9fa; padding: 10px; border-radius: 5px; display: block; margin: 10px 0; }
</style> 