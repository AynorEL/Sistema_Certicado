<?php
echo "Iniciando prueba...<br>";

// Verificar si podemos incluir config.php
if (file_exists('inc/config.php')) {
    echo "✅ Archivo config.php encontrado<br>";
    require_once 'inc/config.php';
    
    if (isset($pdo)) {
        echo "✅ Variable \$pdo disponible<br>";
        
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM curso");
            $row = $stmt->fetch();
            echo "✅ Total de cursos: " . $row['total'] . "<br>";
        } catch (Exception $e) {
            echo "❌ Error en consulta: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Variable \$pdo no disponible<br>";
    }
} else {
    echo "❌ Archivo config.php no encontrado<br>";
}

echo "Prueba completada.<br>";
?> 