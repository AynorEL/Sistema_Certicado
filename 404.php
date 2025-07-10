<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error 404 - Página no encontrada | Sistema de Certificados</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
      color: #dc3545;
      margin-bottom: 30px;
    }
    .error-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: #333;
      margin-bottom: 20px;
    }
    .error-message {
      font-size: 1.1rem;
      color: #666;
      margin-bottom: 40px;
      line-height: 1.6;
    }
    .btn-home {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    .btn-home:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      color: white;
    }
    .btn-secondary {
      background: #6c757d;
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
      background: #5a6268;
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      color: white;
    }
    .error-code {
      font-size: 1rem;
      color: #999;
      margin-top: 30px;
      font-family: monospace;
    }
  </style>
</head>
<body>
  <div class="error-container">
    <div class="error-card">
      <div class="error-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <h1 class="error-title">¡Oops! Página no encontrada</h1>
      <p class="error-message">
        La página que buscas no existe o ha sido movida. 
        Puedes volver al inicio o explorar nuestros cursos disponibles.
      </p>
      <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
        <a href="index.php" class="btn-home">
          <i class="fas fa-home me-2"></i>Ir al Inicio
        </a>
        <a href="index.php#cursos" class="btn-secondary">
          <i class="fas fa-graduation-cap me-2"></i>Ver Cursos
        </a>
      </div>
      <div class="error-code">
        Error 404 - <?php echo date('Y-m-d H:i:s'); ?>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>