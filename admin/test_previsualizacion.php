<?php
session_start();

if (!isset($_SESSION['user'])) {
    echo "❌ No hay sesión activa. <a href='login.php'>Iniciar sesión</a>";
    exit;
}

require_once 'inc/config.php';

echo "<h2>Prueba de Previsualización de Certificados</h2>";

// Obtener cursos disponibles
try {
    $stmt = $pdo->query("SELECT idcurso, nombre_curso, diseño, config_certificado FROM curso LIMIT 5");
    $cursos = $stmt->fetchAll();
    
    if ($cursos) {
        echo "<h3>Cursos disponibles para previsualización:</h3>";
        foreach ($cursos as $curso) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
            echo "<strong>ID:</strong> {$curso['idcurso']}<br>";
            echo "<strong>Curso:</strong> {$curso['nombre_curso']}<br>";
            echo "<strong>Diseño:</strong> " . ($curso['diseño'] ? $curso['diseño'] : 'No asignado') . "<br>";
            echo "<strong>Configuración:</strong> " . ($curso['config_certificado'] ? 'Guardada' : 'No guardada') . "<br>";
            
            // Obtener alumnos aprobados para este curso
            $stmt_alumnos = $pdo->prepare("
                SELECT c.idcliente, c.nombre, c.apellido 
                FROM inscripcion i 
                JOIN cliente c ON i.idcliente = c.idcliente 
                WHERE i.idcurso = ? AND i.estado = 'Aprobado' 
                LIMIT 1
            ");
            $stmt_alumnos->execute([$curso['idcurso']]);
            $alumno = $stmt_alumnos->fetch();
            
            if ($alumno) {
                echo "<strong>Alumno de prueba:</strong> {$alumno['nombre']} {$alumno['apellido']}<br>";
                echo "<a href='previsualizar_certificado.php?idcurso={$curso['idcurso']}&idalumno={$alumno['idcliente']}&fecha=" . date('Y-m-d') . "' target='_blank'>Previsualizar Certificado</a>";
            } else {
                echo "<strong>Alumno:</strong> No hay alumnos aprobados<br>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p>No hay cursos en la base de datos.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Información de configuración:</h3>";
echo "<p>La previsualización requiere:</p>";
echo "<ul>";
echo "<li>Un curso con diseño asignado</li>";
echo "<li>Configuración guardada del certificado</li>";
echo "<li>Al menos un alumno aprobado</li>";
echo "<li>Instructor y especialista (opcional)</li>";
echo "</ul>";

echo "<h3>Navegación:</h3>";
echo "<a href='curso.php'>Ver todos los cursos</a><br>";
echo "<a href='index.php'>Ir al dashboard</a><br>";
?> 