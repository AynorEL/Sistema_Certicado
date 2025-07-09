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
  <title>Error 404 - Panel de Administración</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .error-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .error-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
      padding: 60px 40px;
      text-align: center;
      max-width: 500px;
      width: 100%;
    }
    .error-icon {
      font-size: 120px;
      color: #e74c3c;
      margin-bottom: 30px;
    }
    .error-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 20px;
    }
    .error-message {
      font-size: 1.1rem;
      color: #7f8c8d;
      margin-bottom: 40px;
      line-height: 1.6;
    }
    .btn-admin {
      background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
      border: none;
      padding: 15px 40px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      color: white;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-block;
      margin: 10px;
    }
    .btn-admin:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      color: white;
    }
    .btn-secondary {
      background: #95a5a6;
      border: none;
      padding: 15px 40px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      color: white;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-block;
      margin: 10px;
    }
    .btn-secondary:hover {
      background: #7f8c8d;
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      color: white;
    }
    .error-code {
      font-size: 1rem;
      color: #bdc3c7;
      margin-top: 30px;
      font-family: monospace;
    }
    .admin-badge {
      background: #e74c3c;
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 20px;
      display: inline-block;
    }
  </style>
</head>
<body>
  <div class="error-container">
    <div class="error-card">
      <div class="admin-badge">
        <i class="fas fa-shield-alt me-2"></i>Panel de Administración
      </div>
      <div class="error-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <h1 class="error-title">¡Oops! Página no encontrada</h1>
      <p class="error-message">
        La página que buscas no existe en el panel de administración. 
        Verifica la URL o regresa al dashboard principal.
      </p>
      <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
        <a href="<?= $base ?>index.php" class="btn-admin">
          <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="<?= $projectBase ?>/index.php" class="btn-secondary">
          <i class="fas fa-home me-2"></i>Ir al Sitio
        </a>
      </div>
      <div class="error-code">
        Error 404 - Panel Admin - <?php echo date('Y-m-d H:i:s'); ?>
      </div>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
