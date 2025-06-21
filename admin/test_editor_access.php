<?php
session_start();

echo "<h2>Estado de la Sesión</h2>";
if (isset($_SESSION['user'])) {
    echo "✅ Usuario logueado: " . $_SESSION['user']['nombre_completo'] . "<br>";
    echo "Email: " . $_SESSION['user']['correo'] . "<br>";
    echo "Rol: " . $_SESSION['user']['rol'] . "<br>";
} else {
    echo "❌ No hay usuario logueado<br>";
    echo '<a href="login.php">Ir al login</a><br>';
}

echo "<h2>Prueba de acceso al editor</h2>";
echo '<a href="editor_certificado.php?id=1">Probar editor curso ID=1</a><br>';
echo '<a href="editor_certificado.php?id=2">Probar editor curso ID=2</a><br>';

echo "<h2>Verificar cursos disponibles</h2>";
if (file_exists('inc/config.php')) {
    require_once 'inc/config.php';
    
    try {
        $stmt = $pdo->query("SELECT idcurso, nombre_curso FROM curso LIMIT 5");
        $cursos = $stmt->fetchAll();
        
        if ($cursos) {
            echo "Cursos disponibles:<br>";
            foreach ($cursos as $curso) {
                echo "- ID: {$curso['idcurso']} - {$curso['nombre_curso']} ";
                echo '<a href="editor_certificado.php?id=' . $curso['idcurso'] . '">[Editor]</a><br>';
            }
        } else {
            echo "No hay cursos en la base de datos<br>";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}

echo "<h2>Navegación</h2>";
echo '<a href="curso.php">Ver todos los cursos</a><br>';
echo '<a href="index.php">Ir al dashboard</a><br>';
?> 