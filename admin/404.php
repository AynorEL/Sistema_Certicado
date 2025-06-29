<?php
// Detecta automáticamente el subdirectorio del proyecto
$projectBase = dirname($_SERVER['SCRIPT_NAME'], 2); 
$base = $projectBase . '/admin/';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error 404 - Página no encontrada</title>
  <link rel="stylesheet" href="<?= $base ?>css/404.css">
</head>
<body>
  <div class="content">
    <img src="<?= $base ?>img/404.gif" alt="Error 404">
    <div class="text">
      <h2>¡Parece que estás perdido!</h2>
      <p>La página que buscas no está disponible o fue movida.</p>
      <a href="<?= $base ?>index.php" class="btn">Ir al inicio</a>
    </div>
    <p class="firma">Aynor <span style="color: red;">EL</span></p>
  </div>
</body>
</html>
