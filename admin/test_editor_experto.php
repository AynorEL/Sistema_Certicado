<?php
// Archivo de prueba para verificar el editor experto
require_once('inc/config.php');

// Verificar que estamos logueados
session_start();
if (!isset($_SESSION['user'])) {
    echo "<div style='color: red; padding: 20px;'>No estás logueado</div>";
    exit;
}

$idcurso = 1; // Curso de prueba

// Obtener información del curso
$stmt = $pdo->prepare("SELECT nombre_curso, diseño, config_certificado FROM curso WHERE idcurso = ?");
$stmt->execute([$idcurso]);
$curso = $stmt->fetch();

if (!$curso) {
    echo "<div style='color: red; padding: 20px;'>Curso no encontrado</div>";
    exit;
}

echo "<h2>Prueba del Editor Experto</h2>";
echo "<p><strong>Curso:</strong> " . htmlspecialchars($curso['nombre_curso']) . "</p>";
echo "<p><strong>Diseño:</strong> " . ($curso['diseño'] ? htmlspecialchars($curso['diseño']) : 'No asignado') . "</p>";

if ($curso['config_certificado']) {
    $config = json_decode($curso['config_certificado'], true);
    echo "<p><strong>Configuración guardada:</strong></p>";
    echo "<pre>" . htmlspecialchars(json_encode($config, JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "<p><strong>Configuración:</strong> No hay configuración guardada</p>";
}

echo "<h3>Enlaces de prueba:</h3>";
echo "<p><a href='editor_certificado.php?id=$idcurso' target='_blank'>Abrir Editor Experto</a></p>";
echo "<p><a href='test_qr_preview.php' target='_blank'>Probar Generación de QR</a></p>";
echo "<p><a href='test_previsualizacion.php' target='_blank'>Probar Previsualización</a></p>";

echo "<h3>Características del Editor Experto:</h3>";
echo "<ul>";
echo "<li>✅ Selección de campos con clic</li>";
echo "<li>✅ Controles de tipo de fuente (Arial, Times New Roman, etc.)</li>";
echo "<li>✅ Tamaño de fuente ajustable (8-72px)</li>";
echo "<li>✅ Color de texto personalizable</li>";
echo "<li>✅ Alineación de texto (izquierda, centro, derecha, justificado)</li>";
echo "<li>✅ Estilo de fuente (normal, cursiva)</li>";
echo "<li>✅ Peso de fuente (normal, negrita, 100-900)</li>";
echo "<li>✅ Decoración de texto (ninguna, subrayado, tachado, sobrelineado)</li>";
echo "<li>✅ Altura de línea ajustable</li>";
echo "<li>✅ Espaciado entre letras</li>";
echo "<li>✅ Rotación de elementos</li>";
echo "<li>✅ Opacidad ajustable</li>";
echo "<li>✅ Color de fondo con toggle</li>";
echo "<li>✅ Bordes configurables (ancho, color, radio)</li>";
echo "<li>✅ Sombras de texto (color, desenfoque, desplazamiento X/Y)</li>";
echo "<li>✅ Tamaño de firmas ajustable</li>";
echo "<li>✅ Tamaño de QR ajustable</li>";
echo "<li>✅ Arrastre y posicionamiento preciso</li>";
echo "<li>✅ Guardado de configuración completa</li>";
echo "<li>✅ Previsualización en tiempo real</li>";
echo "</ul>";

echo "<h3>Instrucciones de uso:</h3>";
echo "<ol>";
echo "<li>Haz clic en 'Abrir Editor Experto'</li>";
echo "<li>Agrega elementos usando los botones</li>";
echo "<li>Haz clic en un elemento para seleccionarlo</li>";
echo "<li>Usa los controles de la derecha para personalizar el estilo</li>";
echo "<li>Arrastra los elementos para posicionarlos</li>";
echo "<li>Guarda la configuración</li>";
echo "<li>Prueba la previsualización</li>";
echo "</ol>";
?> 