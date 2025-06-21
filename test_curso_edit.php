<?php
// Archivo de prueba para verificar la edición de cursos
echo "<h2>Prueba de Edición de Cursos</h2>";

// Simular parámetros de URL
$_REQUEST['id'] = '1'; // ID de un curso existente

// Incluir la configuración
require_once('admin/inc/config.php');

// Verificar si el curso existe
$statement = $pdo->prepare("SELECT * FROM curso WHERE idcurso=?");
$statement->execute(array($_REQUEST['id']));
$total = $statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

if ($total == 0) {
    echo "<p style='color: red;'>❌ Error: Curso no encontrado con ID: " . $_REQUEST['id'] . "</p>";
} else {
    echo "<p style='color: green;'>✅ Curso encontrado correctamente</p>";
    echo "<p>Nombre del curso: " . $result[0]['nombre_curso'] . "</p>";
    echo "<p>ID del curso: " . $result[0]['idcurso'] . "</p>";
}

// Verificar que el parámetro 'id' se está recibiendo correctamente
echo "<p>Parámetro 'id' recibido: " . (isset($_REQUEST['id']) ? $_REQUEST['id'] : 'NO RECIBIDO') . "</p>";

// Listar todos los cursos disponibles
echo "<h3>Cursos disponibles en la base de datos:</h3>";
$statement = $pdo->prepare("SELECT idcurso, nombre_curso FROM curso ORDER BY idcurso");
$statement->execute();
$cursos = $statement->fetchAll(PDO::FETCH_ASSOC);

if (empty($cursos)) {
    echo "<p style='color: orange;'>⚠️ No hay cursos en la base de datos</p>";
} else {
    echo "<ul>";
    foreach ($cursos as $curso) {
        echo "<li>ID: " . $curso['idcurso'] . " - " . $curso['nombre_curso'] . "</li>";
    }
    echo "</ul>";
}
?> 