<?php
session_start();
require_once('admin/inc/config.php');

echo "<h2>Contenido del Carrito</h2>";

// Verificar si existe la sesión del carrito
if (isset($_SESSION['cart_certificados'])) {
    echo "<pre>";
    print_r($_SESSION['cart_certificados']);
    echo "</pre>";
    
    // Contar productos
    $total_productos = count($_SESSION['cart_certificados']);
    echo "<p>Total de productos en el carrito: " . $total_productos . "</p>";
    
    // Mostrar detalles de cada producto
    echo "<h3>Detalles de los Productos:</h3>";
    foreach ($_SESSION['cart_certificados'] as $idcurso => $item) {
        echo "<div style='margin-bottom: 10px; padding: 10px; border: 1px solid #ccc;'>";
        echo "ID Curso: " . $idcurso . "<br>";
        echo "Nombre: " . $item['nombre'] . "<br>";
        echo "Precio: S/ " . number_format($item['precio'], 2) . "<br>";
        echo "</div>";
    }
} else {
    echo "<p>El carrito está vacío (no existe la sesión cart_certificados)</p>";
}

// Mostrar todas las variables de sesión para debug
echo "<h3>Todas las variables de sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>"; 