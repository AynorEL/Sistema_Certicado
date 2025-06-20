<?php
require_once('admin/inc/config.php');
require_once('admin/inc/functions.php');

// Obtener el ID del curso de la URL
$idcurso = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener información del curso
$statement = $pdo->prepare("SELECT * FROM curso WHERE idcurso = ?");
$statement->execute([$idcurso]);
$curso = $statement->fetch(PDO::FETCH_ASSOC);

if (!$curso) {
    header('Location: index.php');
    exit();
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Insertar cliente
        $statement = $pdo->prepare("
            INSERT INTO cliente (nombre, apellido, dni, telefono, email, direccion) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $statement->execute([
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['dni'],
            $_POST['telefono'],
            $_POST['email'],
            $_POST['direccion']
        ]);
        
        $idcliente = $pdo->lastInsertId();
        
        // Insertar inscripción
        $statement = $pdo->prepare("
            INSERT INTO inscripcion (idcliente, idcurso, fecha_inscripcion, estado) 
            VALUES (?, ?, CURDATE(), 'Pendiente')
        ");
        
        $statement->execute([$idcliente, $idcurso]);
        
        $mensaje = "¡Inscripción exitosa! Te contactaremos pronto.";
        $tipo = "success";
    } catch (PDOException $e) {
        $mensaje = "Error al procesar la inscripción. Por favor, intente más tarde.";
        $tipo = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción a Curso - <?php echo htmlspecialchars($curso['nombre_curso']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('header.php'); ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-center">Inscripción a: <?php echo htmlspecialchars($curso['nombre_curso']); ?></h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($mensaje)): ?>
                            <div class="alert alert-<?php echo $tipo; ?>">
                                <?php echo $mensaje; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dni" class="form-label">DNI</label>
                                    <input type="text" class="form-control" id="dni" name="dni" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="3" required></textarea>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Enviar Inscripción</button>
                                <a href="curso.php?id=<?php echo $idcurso; ?>" class="btn btn-secondary">Volver al Curso</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 