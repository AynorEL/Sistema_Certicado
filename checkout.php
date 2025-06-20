<?php
session_start();
require_once('admin/inc/config.php');

// Verificar si hay cursos en el carrito
$cart_items = $_SESSION['cart_certificados'] ?? [];
if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Obtener configuración de pagos
$statement = $pdo->prepare("SELECT * FROM configuracion_pago WHERE id = 1");
$statement->execute();
$config_pago = $statement->fetch(PDO::FETCH_ASSOC);

// Si no hay configuración de pago, crear una por defecto
if (!$config_pago) {
    $statement = $pdo->prepare("INSERT INTO configuracion_pago (paypal_email, paypal_sandbox, banco_nombre, banco_cuenta, banco_titular, yape_numero, plin_numero) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $statement->execute([
        'paypal@ejemplo.com',
        1,
        'Banco de Ejemplo',
        '1234567890',
        'Nombre del Titular',
        '987654321',
        '987654321'
    ]);
    // Volver a obtener la configuración insertada
    $statement = $pdo->prepare("SELECT * FROM configuracion_pago WHERE id = 1");
    $statement->execute();
    $config_pago = $statement->fetch(PDO::FETCH_ASSOC);
}

// Calcular totales
$subtotal = 0;
foreach ($cart_items as $item) {
    if (isset($item['precio'])) {
        $subtotal += $item['precio'];
    }
}
$igv = $subtotal * 0.18;
$total = $subtotal + $igv;

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar campos requeridos
        $campos_requeridos = ['nombre', 'apellido', 'dni', 'telefono', 'email', 'direccion', 'metodo_pago'];
        foreach ($campos_requeridos as $campo) {
            if (empty($_POST[$campo])) {
                throw new Exception("El campo '$campo' es obligatorio.");
            }
        }

        // Validaciones específicas
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El formato del email no es válido');
        }
        if (!preg_match('/^\d{8}$/', $_POST['dni'])) {
            throw new Exception('El DNI debe tener 8 dígitos');
        }
        if (!preg_match('/^\d{9}$/', $_POST['telefono'])) {
            throw new Exception('El teléfono debe tener 9 dígitos');
        }

        // Iniciar transacción
        $pdo->beginTransaction();

        // Insertar o actualizar cliente
        $stmt = $pdo->prepare("SELECT idcliente FROM cliente WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cliente) {
            $idcliente = $cliente['idcliente'];
            $stmt = $pdo->prepare("UPDATE cliente SET nombre = ?, apellido = ?, dni = ?, telefono = ?, direccion = ? WHERE idcliente = ?");
            $stmt->execute([
                $_POST['nombre'],
                $_POST['apellido'],
                $_POST['dni'],
                $_POST['telefono'],
                $_POST['direccion'],
                $idcliente
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cliente (nombre, apellido, dni, telefono, email, direccion) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['nombre'],
                $_POST['apellido'],
                $_POST['dni'],
                $_POST['telefono'],
                $_POST['email'],
                $_POST['direccion']
            ]);
            $idcliente = $pdo->lastInsertId();
        }

        // Insertar inscripciones y pagos
        $inscripciones_ids = [];
        foreach ($cart_items as $item) {
            if (!isset($item['idcurso'])) continue;

            // Verificar cupos
            $stmt = $pdo->prepare("SELECT cupos_disponibles FROM curso WHERE idcurso = ?");
            $stmt->execute([$item['idcurso']]);
            $curso = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$curso || $curso['cupos_disponibles'] <= 0) {
                throw new Exception("El curso '{$item['nombre']}' no tiene cupos disponibles.");
            }

            // Insertar inscripción
            $stmt = $pdo->prepare("INSERT INTO inscripcion (idcliente, idcurso, fecha_inscripcion, estado, monto_pago, estado_pago, metodo_pago) VALUES (?, ?, CURDATE(), 'Pendiente', ?, 'Pendiente', ?)");
            $stmt->execute([
                $idcliente,
                $item['idcurso'],
                $item['precio'],
                $_POST['metodo_pago']
            ]);
            $idinscripcion = $pdo->lastInsertId();
            $inscripciones_ids[] = $idinscripcion;

            // Insertar pago
            $stmt = $pdo->prepare("INSERT INTO pago (idinscripcion, monto, fecha_pago, metodo_pago, estado) VALUES (?, ?, NOW(), ?, 'Pendiente')");
            $stmt->execute([
                $idinscripcion,
                $item['precio'],
                $_POST['metodo_pago']
            ]);

            // Restar cupo
            $stmt = $pdo->prepare("UPDATE curso SET cupos_disponibles = cupos_disponibles - 1 WHERE idcurso = ?");
            $stmt->execute([$item['idcurso']]);
        }

        // Redirigir según método de pago
        $idinscripcion = $inscripciones_ids[0];
        switch ($_POST['metodo_pago']) {
            case 'PayPal':
                if (empty($config_pago['paypal_email']) || !filter_var($config_pago['paypal_email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Configuración de PayPal no válida');
                }
                $_SESSION['paypal_details'] = [
                    'email' => $config_pago['paypal_email'],
                    'sandbox' => (bool)$config_pago['paypal_sandbox'],
                    'monto' => number_format($total, 2, '.', ''),
                    'order_id' => $idinscripcion
                ];
                $pdo->commit();
                header("Location: payment/paypal.php");
                exit;

            case 'Transferencia Bancaria':
                if (empty($config_pago['banco_cuenta']) || empty($config_pago['banco_nombre']) || empty($config_pago['banco_titular'])) {
                    throw new Exception('Configuración bancaria incompleta');
                }
                $_SESSION['bank_details'] = [
                    'banco' => htmlspecialchars($config_pago['banco_nombre']),
                    'cuenta' => htmlspecialchars($config_pago['banco_cuenta']),
                    'titular' => htmlspecialchars($config_pago['banco_titular']),
                    'monto' => number_format($total, 2, '.', ''),
                    'order_id' => $idinscripcion
                ];
                $pdo->commit();
                header("Location: payment/bank-transfer.php");
                exit;

            case 'Yape':
                if (empty($config_pago['yape_numero'])) {
                    throw new Exception('Número de Yape no configurado');
                }
                $_SESSION['yape_details'] = [
                    'numero' => htmlspecialchars($config_pago['yape_numero']),
                    'monto' => number_format($total, 2, '.', ''),
                    'order_id' => $idinscripcion
                ];
                $pdo->commit();
                header("Location: payment/yape.php");
                exit;

            case 'Plin':
                if (empty($config_pago['plin_numero'])) {
                    throw new Exception('Número de Plin no configurado');
                }
                $_SESSION['plin_details'] = [
                    'numero' => htmlspecialchars($config_pago['plin_numero']),
                    'monto' => number_format($total, 2, '.', ''),
                    'order_id' => $idinscripcion
                ];
                $pdo->commit();
                header("Location: payment/plin.php");
                exit;

            default:
                throw new Exception('Método de pago no válido');
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
        error_log("Error en checkout: " . $error);
        // Opcionalmente puedes mostrar un mensaje de error en la vista
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Sistema de Certificados</title>
    <style>
        .checkout-form label {
            font-weight: 500;
            color: #495057;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .payment-method {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method:hover {
            border-color: #80bdff;
            background-color: #f8f9fa;
        }
        .payment-method.selected {
            border-color: #007bff;
            background-color: #e7f1ff;
        }
        .payment-method i {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: #6c757d;
        }
        .summary-card {
            position: sticky;
            top: 20px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        .step {
            text-align: center;
            flex: 1;
            position: relative;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-weight: bold;
        }
        .step.active .step-number {
            background-color: #007bff;
            color: white;
        }
        .step-line {
            position: absolute;
            top: 15px;
            left: 50%;
            right: 50%;
            height: 2px;
            background-color: #e9ecef;
            z-index: -1;
        }
        .payment-details {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            background-color: #f8f9fa;
        }
        .payment-details.active {
            display: block;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <div class="container my-5">
        <div class="step-indicator">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-title">Carrito</div>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-title">Checkout</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-title">Confirmación</div>
            </div>
        </div>

        <h2 class="mb-4"><i class="fas fa-credit-card me-2"></i>Checkout</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_POST['metodo_pago'])): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Método de pago seleccionado: <?php echo htmlspecialchars($_POST['metodo_pago']); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-4"><i class="fas fa-user me-2"></i>Información Personal</h4>
                        <form method="POST" action="" class="checkout-form" id="checkoutForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" class="form-control" name="nombre" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Apellido</label>
                                    <input type="text" class="form-control" name="apellido" required value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">DNI</label>
                                    <input type="text" class="form-control" name="dni" required pattern="\d{8}" title="El DNI debe tener 8 dígitos" value="<?php echo isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" name="telefono" required pattern="\d{9}" title="El teléfono debe tener 9 dígitos" value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dirección</label>
                                <textarea class="form-control" name="direccion" rows="3" required><?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?></textarea>
                            </div>

                            <h4 class="card-title mb-4 mt-5"><i class="fas fa-money-bill-wave me-2"></i>Método de Pago</h4>
                            <div class="payment-methods">
                                <?php if ($config_pago['paypal_email']): ?>
                                    <div class="payment-method" data-method="PayPal">
                                        <i class="fab fa-paypal"></i>
                                        <span>PayPal</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($config_pago['banco_cuenta']): ?>
                                    <div class="payment-method" data-method="Transferencia Bancaria">
                                        <i class="fas fa-university"></i>
                                        <span>Transferencia Bancaria</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($config_pago['yape_numero']): ?>
                                    <div class="payment-method" data-method="Yape">
                                        <i class="fas fa-mobile-alt"></i>
                                        <span>Yape</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($config_pago['plin_numero']): ?>
                                    <div class="payment-method" data-method="Plin">
                                        <i class="fas fa-mobile-alt"></i>
                                        <span>Plin</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="metodo_pago" id="metodo_pago" required>

                            <!-- Detalles de pago según método -->
                            <div id="paypalDetails" class="payment-details">
                                <h5>Detalles de PayPal</h5>
                                <p>Serás redirigido a PayPal para completar el pago.</p>
                            </div>

                            <div id="bancoDetails" class="payment-details">
                                <h5>Detalles de Transferencia Bancaria</h5>
                                <p><strong>Banco:</strong> <?php echo htmlspecialchars($config_pago['banco_nombre']); ?></p>
                                <p><strong>Cuenta:</strong> <?php echo htmlspecialchars($config_pago['banco_cuenta']); ?></p>
                                <p><strong>Titular:</strong> <?php echo htmlspecialchars($config_pago['banco_titular']); ?></p>
                            </div>

                            <div id="yapeDetails" class="payment-details">
                                <h5>Detalles de Yape</h5>
                                <p><strong>Número:</strong> <?php echo htmlspecialchars($config_pago['yape_numero']); ?></p>
                            </div>

                            <div id="plinDetails" class="payment-details">
                                <h5>Detalles de Plin</h5>
                                <p><strong>Número:</strong> <?php echo htmlspecialchars($config_pago['plin_numero']); ?></p>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="cart.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver al Carrito
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-credit-card me-2"></i>Proceder al Pago
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm summary-card">
                    <div class="card-header bg-white">
                        <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>Resumen del Pedido</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Curso</th>
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $idcurso => $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-graduation-cap text-primary me-2"></i>
                                                    <?php echo htmlspecialchars($item['nombre']); ?>
                                                </div>
                                            </td>
                                            <td>S/ <?php echo number_format($item['precio'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Subtotal:</th>
                                        <td>S/ <?php echo number_format($subtotal, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>IGV (18%):</th>
                                        <td>S/ <?php echo number_format($igv, 2); ?></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <th>Total:</th>
                                        <td><strong>S/ <?php echo number_format($total, 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethods = document.querySelectorAll('.payment-method');
        const metodoPagoInput = document.getElementById('metodo_pago');
        const paymentDetails = document.querySelectorAll('.payment-details');
        const form = document.getElementById('checkoutForm');

        paymentMethods.forEach(method => {
            method.addEventListener('click', function() {
                paymentMethods.forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
                metodoPagoInput.value = this.dataset.method;
                paymentDetails.forEach(detail => detail.classList.remove('active'));
                const methodName = this.dataset.method.toLowerCase().replace(' ', '');
                const detailsElement = document.getElementById(methodName + 'Details');
                if (detailsElement) {
                    detailsElement.classList.add('active');
                }
            });
        });

        form.addEventListener('submit', function(e) {
            if (!metodoPagoInput.value) {
                e.preventDefault();
                alert('Por favor, selecciona un método de pago');
                return false;
            }
        });
    });
    </script>
</body>
</html>