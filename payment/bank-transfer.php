<?php
session_start();
require_once('../admin/inc/config.php');

// Verificar si hay datos de pago en sesión
if (!isset($_SESSION['bank_details'])) {
    $_SESSION['error'] = 'Error: Datos de pago no encontrados';
    header('Location: ../cart.php');
    exit;
}

$bank_details = $_SESSION['bank_details'];

// Validar datos necesarios
if (!isset($bank_details['monto']) || !isset($bank_details['order_id']) || 
    !isset($bank_details['banco']) || !isset($bank_details['cuenta']) || 
    !isset($bank_details['titular'])) {
    $_SESSION['error'] = 'Error: Datos de pago incompletos';
    header('Location: ../cart.php');
    exit;
}

// Validar monto
if (!is_numeric($bank_details['monto']) || $bank_details['monto'] <= 0) {
    $_SESSION['error'] = 'Error: Monto inválido';
    header('Location: ../cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transferencia Bancaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .copy-button {
            cursor: pointer;
            transition: all 0.3s;
        }
        .copy-button:hover {
            transform: scale(1.05);
        }
        .bank-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .bank-info p {
            margin-bottom: 10px;
            font-size: 16px;
        }
        .bank-info strong {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container payment-container">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h3 class="mb-0"><i class="fas fa-university me-2"></i>Transferencia Bancaria</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-university fa-3x text-primary mb-3"></i>
                    <h4>Monto a transferir: S/ <?php echo number_format($bank_details['monto'], 2); ?></h4>
                    <p class="text-muted">Orden #<?php echo $bank_details['order_id']; ?></p>
                </div>

                <div class="alert alert-info">
                    <h5 class="alert-heading">Instrucciones:</h5>
                    <ol class="mb-0">
                        <li>Realiza la transferencia por el monto exacto indicado</li>
                        <li>Usa los datos bancarios proporcionados abajo</li>
                        <li>Guarda el comprobante de transferencia</li>
                        <li>Envía el comprobante a través del formulario</li>
                    </ol>
                </div>

                <div class="bank-info">
                    <h5 class="mb-3">Datos Bancarios:</h5>
                    <p><strong>Banco:</strong> <?php echo htmlspecialchars($bank_details['banco']); ?></p>
                    <p><strong>Número de Cuenta:</strong> 
                        <span class="d-inline-block"><?php echo htmlspecialchars($bank_details['cuenta']); ?></span>
                        <button class="btn btn-sm btn-outline-primary ms-2 copy-button" 
                                onclick="copyToClipboard(this)" 
                                data-copy="<?php echo htmlspecialchars($bank_details['cuenta']); ?>">
                            <i class="fas fa-copy"></i>
                        </button>
                    </p>
                    <p><strong>Titular:</strong> <?php echo htmlspecialchars($bank_details['titular']); ?></p>
                    <p><strong>Monto:</strong> S/ <?php echo number_format($bank_details['monto'], 2); ?></p>
                </div>

                <form action="../confirmacion-pago.php" method="post" enctype="multipart/form-data" class="mb-4">
                    <input type="hidden" name="order_id" value="<?php echo $bank_details['order_id']; ?>">
                    <input type="hidden" name="payment_method" value="Transferencia Bancaria">
                    
                    <div class="mb-3">
                        <label for="comprobante" class="form-label">Comprobante de transferencia:</label>
                        <input type="file" class="form-control" id="comprobante" name="comprobante" required 
                               accept="image/jpeg,image/png,image/jpg,application/pdf">
                        <div class="form-text">Formatos permitidos: JPG, PNG, PDF. Tamaño máximo: 5MB</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check-circle me-2"></i>Confirmar Transferencia
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <a href="../cart.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver al carrito
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function copyToClipboard(button) {
        const textToCopy = button.getAttribute('data-copy');
        navigator.clipboard.writeText(textToCopy).then(() => {
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                button.innerHTML = originalHTML;
            }, 2000);
        });
    }
    </script>
</body>
</html>