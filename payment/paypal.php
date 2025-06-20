<?php
session_start();
require_once('../admin/inc/config.php');

// Verificar si hay datos de pago en sesión
if (!isset($_SESSION['paypal_details'])) {
    $_SESSION['error'] = 'Error: Datos de pago no encontrados';
    header('Location: ../cart.php');
    exit;
}

$paypal_details = $_SESSION['paypal_details'];
$order_id = $paypal_details['order_id'];
$amount = $paypal_details['monto'];
$paypal_email = $paypal_details['email'];
$paypal_sandbox = $paypal_details['sandbox'];

// Configurar URL de PayPal según el modo
$paypal_url = $paypal_sandbox ? 
    'https://www.sandbox.paypal.com/cgi-bin/webscr' : 
    'https://www.paypal.com/cgi-bin/webscr';

// Validar datos
if (empty($paypal_email) || !is_numeric($amount) || $amount <= 0) {
    $_SESSION['error'] = 'Error: Datos de PayPal inválidos';
    header('Location: ../cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago con PayPal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .paypal-button {
            background-color: #0070ba;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .paypal-button:hover {
            background-color: #005ea6;
        }
    </style>
</head>
<body>
    <div class="container payment-container">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h3 class="mb-0"><i class="fab fa-paypal me-2"></i>Pago con PayPal</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg" alt="PayPal" class="mb-3">
                    <h4>Monto a pagar: S/ <?php echo number_format($amount, 2); ?></h4>
                    <p class="text-muted">Orden #<?php echo $order_id; ?></p>
                </div>

                <form action="<?php echo htmlspecialchars($paypal_url); ?>" method="post" id="paypal-form">
                    <input type="hidden" name="business" value="<?php echo htmlspecialchars($paypal_email); ?>">
                    <input type="hidden" name="cmd" value="_xclick">
                    <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
                    <input type="hidden" name="currency_code" value="PEN">
                    <input type="hidden" name="item_name" value="Pago de Certificado">
                    <input type="hidden" name="item_number" value="<?php echo htmlspecialchars($order_id); ?>">
                    <input type="hidden" name="custom" value="<?php echo htmlspecialchars($order_id); ?>">
                    <input type="hidden" name="no_shipping" value="1">
                    <input type="hidden" name="no_note" value="1">
                    <input type="hidden" name="rm" value="2">
                    <input type="hidden" name="notify_url" value="<?php echo htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/paypal-ipn.php'); ?>">
                    <input type="hidden" name="return" value="<?php echo htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI'])) . '/payment-success.php'); ?>">
                    <input type="hidden" name="cancel_return" value="<?php echo htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI'])) . '/cart.php'); ?>">
                    
                    <div class="text-center">
                        <button type="submit" class="paypal-button">
                            <i class="fab fa-paypal me-2"></i>Pagar con PayPal
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <a href="../cart.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Volver al carrito
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>