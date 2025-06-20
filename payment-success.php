<?php
session_start();
require_once('admin/inc/config.php');

// Verificar si hay mensaje de éxito
if (!isset($_SESSION['success'])) {
    header('Location: cart.php');
    exit;
}

$success_message = $_SESSION['success'];
unset($_SESSION['success']); // Limpiar el mensaje después de mostrarlo

// Recuperar detalles del último pago si existen
$datos_pago = $_SESSION['last_pago'] ?? null;
unset($_SESSION['last_pago']); // Limpiar después de usar
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Exitoso - Sistema de Certificados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .success-container {
            max-width: 800px;
            margin: 50px auto;
        }
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 2rem;
        }
        .success-message {
            font-size: 1.2rem;
            line-height: 1.6;
            color: #495057;
        }
        .next-steps {
            margin-top: 3rem;
        }
        .next-steps .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
            margin-bottom: 1.5rem;
        }
        .next-steps .card:hover {
            transform: translateY(-5px);
        }
        .step-icon {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 1rem;
        }
        .action-buttons {
            margin-top: 2rem;
        }
        .action-buttons .btn {
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }
        .pago-resumen {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 1rem;
            border-radius: .5rem;
            margin-top: 2rem;
            text-align: left;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <div class="container success-container">
        <div class="card shadow">
            <div class="card-body text-center p-5">
                <i class="fas fa-check-circle success-icon"></i>
                <h1 class="mb-4">¡Pago Procesado con Éxito!</h1>
                <div class="success-message mb-4">
                    <?php echo nl2br(htmlspecialchars($success_message)); ?>
                </div>

                <?php if ($datos_pago): ?>
                    <div class="pago-resumen">
                        <h5 class="mb-3">Resumen del Pago:</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Método:</strong> <?= htmlspecialchars($datos_pago['metodo']) ?></li>
                            <li class="list-group-item"><strong>Monto:</strong> S/ <?= number_format($datos_pago['monto'], 2) ?></li>
                            <li class="list-group-item"><strong>Número de orden:</strong> <?= htmlspecialchars($datos_pago['orden']) ?></li>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="next-steps">
                    <h3 class="mb-4 mt-5">Próximos Pasos</h3>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <i class="fas fa-envelope step-icon"></i>
                                    <h4>Revisa tu Email</h4>
                                    <p class="text-muted">Recibirás una confirmación y detalles del curso por correo electrónico.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <i class="fas fa-graduation-cap step-icon"></i>
                                    <h4>Accede a tus Cursos</h4>
                                    <p class="text-muted">Desde tu área personal podrás ver los cursos activos.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <i class="fas fa-certificate step-icon"></i>
                                    <h4>Obtén tu Certificado</h4>
                                    <p class="text-muted">Al terminar el curso, podrás descargar tu certificado digital.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="action-buttons text-center">
                    <a href="user-area.php" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-user me-2"></i>Mi Área Personal
                    </a>
                    <a href="index.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-home me-2"></i>Volver al Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
