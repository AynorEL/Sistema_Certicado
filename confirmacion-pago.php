<?php
session_start();
require_once('admin/inc/config.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['customer'])) {
    header('Location: login.php');
    exit;
}

// Verificar si se recibió el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

// Validar datos recibidos
if (!isset($_POST['order_id']) || !isset($_POST['payment_method'])) {
    $_SESSION['error'] = 'Datos de pago incompletos';
    header('Location: cart.php');
    exit;
}

$order_id = $_POST['order_id'];
$payment_method = $_POST['payment_method'];

try {
    // Iniciar transacción
    $pdo->beginTransaction();

    // Verificar si la inscripción existe y está pendiente
    $statement = $pdo->prepare("
    SELECT i.*, p.idpago 
    FROM inscripcion i 
    LEFT JOIN pago p ON i.idinscripcion = p.idinscripcion 
    WHERE i.idinscripcion = ? AND i.estado_pago = 'Pendiente'");
    $statement->execute([$order_id]);
    $inscripcion = $statement->fetch();

    if (!$inscripcion) {
        throw new Exception('Inscripción no encontrada o ya procesada');
    }

    // Procesar el comprobante si se subió un archivo
    $comprobante_db = null;
    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $file_type = $_FILES['comprobante']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception('Tipo de archivo no permitido');
        }

        $max_size = 5 * 1024 * 1024; // 5MB
        if ($_FILES['comprobante']['size'] > $max_size) {
            throw new Exception('El archivo es demasiado grande');
        }

        $upload_dir = 'assets/uploads/comprobantes/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
        $file_name = 'comprobante_' . $order_id . '_' . time() . '.' . $file_extension;
        $comprobante_path = $upload_dir . $file_name;
        $comprobante_db = $file_name;

        if (!move_uploaded_file($_FILES['comprobante']['tmp_name'], $comprobante_path)) {
            throw new Exception('Error al subir el archivo');
        }
    }

    // Actualizar estado de la inscripción
    $stmt = $pdo->prepare("UPDATE inscripcion SET 
                          estado_pago = 'En Revisión',
                          fecha_actualizacion = NOW() 
                          WHERE idinscripcion = ?");
    $stmt->execute([$order_id]);

    // Actualizar el pago
    if ($inscripcion['idpago']) {
        $stmt = $pdo->prepare("UPDATE pago SET 
                              estado = 'En Revisión',
                              comprobante = ?,
                              fecha_actualizacion = NOW() 
                              WHERE idpago = ?");
        $stmt->execute([$comprobante_db, $inscripcion['idpago']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO pago 
                              (idinscripcion, monto, fecha_pago, metodo_pago, estado, comprobante) 
                              VALUES (?, ?, NOW(), ?, 'En Revisión', ?)");
        $stmt->execute([$order_id, $inscripcion['monto_pago'], $payment_method, $comprobante_db]);
    }

    // Confirmar transacción
    $pdo->commit();

    // Limpiar datos de sesión
    unset($_SESSION['cart']);
    unset($_SESSION['bank_details']);
    unset($_SESSION['yape_details']);
    unset($_SESSION['plin_details']);
    unset($_SESSION['paypal_details']);

    // Obtener configuración de pago para mostrar en la página
    $statement = $pdo->prepare("SELECT * FROM configuracion_pago WHERE id = 1");
    $statement->execute();
    $config_pago = $statement->fetch(PDO::FETCH_ASSOC);

    // Obtener detalles actualizados de la inscripción
    $statement = $pdo->prepare("SELECT i.*, c.nombre_curso 
                               FROM inscripcion i 
                               JOIN curso c ON i.idcurso = c.idcurso 
                               WHERE i.idinscripcion = ?");
    $statement->execute([$order_id]);
    $inscripcion = $statement->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    $pdo->rollBack();

    // Si se subió un archivo, eliminarlo
    if (!empty($comprobante_path) && file_exists($comprobante_path)) {
        unlink($comprobante_path);
    }

    $_SESSION['error'] = 'Error al procesar el pago: ' . $e->getMessage();
    header('Location: cart.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pago - Sistema de Certificados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 1.5rem;
        }
        .confirmation-card {
            max-width: 600px;
            margin: 0 auto;
        }
        .order-details {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        .order-details h5 {
            color: #495057;
            margin-bottom: 1rem;
        }
        .order-details p {
            margin-bottom: 0.5rem;
        }
        .order-details strong {
            color: #212529;
        }
        .next-steps {
            margin-top: 2rem;
        }
        .next-steps .card {
            transition: transform 0.3s ease;
        }
        .next-steps .card:hover {
            transform: translateY(-5px);
        }
        .step-icon {
            font-size: 2rem;
            color: #007bff;
            margin-bottom: 1rem;
        }
        .payment-instructions {
            background-color: #e7f1ff;
            border: 1px solid #b8daff;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        .payment-instructions h5 {
            color: #004085;
            margin-bottom: 1rem;
        }
        .payment-instructions p {
            color: #004085;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <div class="container my-5">
        <div class="confirmation-card">
            <div class="text-center">
                <i class="fas fa-check-circle success-icon"></i>
                <h2 class="mb-3">¡Pago Procesado con Éxito!</h2>
                <p class="lead text-muted">Gracias por tu compra. Tu pedido ha sido procesado correctamente.</p>
            </div>

            <div class="order-details">
                <h5><i class="fas fa-info-circle me-2"></i>Detalles del Pedido</h5>
                <p><strong>Número de Orden:</strong> #<?php echo str_pad($inscripcion['idinscripcion'], 8, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($inscripcion['fecha_inscripcion'])); ?></p>
                <p><strong>Curso:</strong> <?php echo htmlspecialchars($inscripcion['nombre_curso']); ?></p>
                <p><strong>Método de Pago:</strong> <?php echo htmlspecialchars($inscripcion['metodo_pago']); ?></p>
                <p><strong>Total Pagado:</strong> S/ <?php echo number_format($inscripcion['monto_pago'], 2); ?></p>
            </div>

            <?php if ($inscripcion['metodo_pago'] == 'Transferencia Bancaria'): ?>
                <div class="payment-instructions">
                    <h5><i class="fas fa-university me-2"></i>Instrucciones de Pago</h5>
                    <p><strong>Banco:</strong> <?php echo htmlspecialchars($config_pago['banco_nombre']); ?></p>
                    <p><strong>Cuenta:</strong> <?php echo htmlspecialchars($config_pago['banco_cuenta']); ?></p>
                    <p><strong>Titular:</strong> <?php echo htmlspecialchars($config_pago['banco_titular']); ?></p>
                    <p><strong>Monto a Transferir:</strong> S/ <?php echo number_format($inscripcion['monto_pago'], 2); ?></p>
                    <p class="mt-3"><i class="fas fa-info-circle me-2"></i>Por favor, envía el comprobante de pago a nuestro correo electrónico.</p>
                </div>
            <?php elseif ($inscripcion['metodo_pago'] == 'Yape'): ?>
                <div class="payment-instructions">
                    <h5><i class="fas fa-mobile-alt me-2"></i>Instrucciones de Pago</h5>
                    <p><strong>Número de Yape:</strong> <?php echo htmlspecialchars($config_pago['yape_numero']); ?></p>
                    <p><strong>Monto a Enviar:</strong> S/ <?php echo number_format($inscripcion['monto_pago'], 2); ?></p>
                    <p class="mt-3"><i class="fas fa-info-circle me-2"></i>Por favor, envía el comprobante de pago a nuestro correo electrónico.</p>
                </div>
            <?php elseif ($inscripcion['metodo_pago'] == 'Plin'): ?>
                <div class="payment-instructions">
                    <h5><i class="fas fa-mobile-alt me-2"></i>Instrucciones de Pago</h5>
                    <p><strong>Número de Plin:</strong> <?php echo htmlspecialchars($config_pago['plin_numero']); ?></p>
                    <p><strong>Monto a Enviar:</strong> S/ <?php echo number_format($inscripcion['monto_pago'], 2); ?></p>
                    <p class="mt-3"><i class="fas fa-info-circle me-2"></i>Por favor, envía el comprobante de pago a nuestro correo electrónico.</p>
                </div>
            <?php endif; ?>

            <div class="next-steps">
                <h4 class="mb-4">Próximos Pasos</h4>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope step-icon"></i>
                                <h5>Recibirás un Email</h5>
                                <p class="text-muted">Te enviaremos un correo con los detalles de tu compra y acceso a los cursos.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-graduation-cap step-icon"></i>
                                <h5>Acceso a los Cursos</h5>
                                <p class="text-muted">Podrás acceder a tus cursos desde tu área personal.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="user-area.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-user me-2"></i>Ir a Mi Área
                </a>
                <a href="cursos.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-book me-2"></i>Ver Más Cursos
                </a>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>