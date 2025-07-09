<?php
session_start();
require_once('../admin/inc/config.php');

if (!isset($_SESSION['yape_details'])) {
    $_SESSION['error'] = 'Error: Datos de pago no encontrados';
    header('Location: ../cart.php');
    exit;
}

$yape_details = $_SESSION['yape_details'];

if (!isset($yape_details['monto']) || !isset($yape_details['order_id']) || !isset($yape_details['numero'])) {
    $_SESSION['error'] = 'Error: Datos de pago incompletos';
    header('Location: ../cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pago con Yape</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-header">
            <h3 class="text-center">Pago con Yape</h3>
        </div>
        <div class="card-body">
            <p>Monto a pagar: <strong>S/ <?php echo number_format($yape_details['monto'], 2); ?></strong></p>
            <p>Orden N°: <?php echo $yape_details['order_id']; ?></p>
            <p>Escanea este QR o usa el número:</p>
            <div class="text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($yape_details['numero']); ?>" alt="QR Yape">
            </div>
            <p class="text-center mt-3">Número Yape: <strong><?php echo $yape_details['numero']; ?></strong></p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger mt-3">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="yape-verify.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="order_id" value="<?php echo $yape_details['order_id']; ?>">
                <div class="mb-3">
                    <label>Subir comprobante:</label>
                    <input type="file" name="comprobante" class="form-control" accept="image/*,application/pdf" required>
                    <small class="form-text text-muted">JPG, PNG o PDF. Máx: 5MB</small>
                </div>
                <div class="text-center">
                    <button class="btn btn-primary" type="submit">Confirmar pago</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
