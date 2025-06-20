<?php
require_once('admin/inc/config.php');

echo "<h2>Cursos Disponibles en la Base de Datos</h2>";

// Consultar todos los cursos
$statement = $pdo->prepare("SELECT * FROM curso WHERE estado = 'Activo'");
$statement->execute();
$cursos = $statement->fetchAll(PDO::FETCH_ASSOC);

if ($cursos) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th>";
    echo "<th>Nombre del Curso</th>";
    echo "<th>Precio</th>";
    echo "<th>Cupos Disponibles</th>";
    echo "<th>Estado</th>";
    echo "</tr>";
    
    foreach ($cursos as $curso) {
        echo "<tr>";
        echo "<td>" . $curso['idcurso'] . "</td>";
        echo "<td>" . $curso['nombre_curso'] . "</td>";
        echo "<td>S/ " . number_format($curso['precio'], 2) . "</td>";
        echo "<td>" . $curso['cupos_disponibles'] . "</td>";
        echo "<td>" . $curso['estado'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay cursos disponibles en la base de datos.</p>";
} 