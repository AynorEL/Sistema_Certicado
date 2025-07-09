<?php
// ---------------- plin.php ----------------
session_start();
require_once('../admin/inc/config.php');

if (!isset($_SESSION['plin_details'])) {
    $_SESSION['error'] = 'Datos de pago no encontrados.';
    header('Location: ../cart.php');
    exit;
}

$plin = $_SESSION['plin_details'];

if (!isset($plin['order_id'], $plin['monto'], $plin['numero'])) {
    $_SESSION['error'] = 'Datos incompletos.';
    header('Location: ../cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pago con Plin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f8fb; }
        .card { max-width: 600px; margin: 50px auto; border-radius: 1rem; }
        .qr-code img { width: 200px; }
        .text-muted { font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="card shadow">
        <div class="card-header text-center bg-white">
            <h3><i class="fas fa-mobile-alt me-2"></i>Pago con Plin</h3>
        </div>
        <div class="card-body">
            <div class="text-center">
                <img src="../assets/img/plin-logo.png" alt="Plin" style="max-width: 130px;">
                <h4 class="mt-3">Monto: S/ <?= number_format($plin['monto'], 2) ?></h4>
                <p class="text-muted">Orden #<?= htmlspecialchars($plin['order_id']) ?></p>
            </div>
            <div class="alert alert-info">
                <ol class="mb-0 small">
                    <li>Abre Plin en tu celular.</li>
                    <li>Escanea el código QR o ingresa el número.</li>
                    <li>Envíanos el comprobante.</li>
                </ol>
            </div>
            <div class="text-center qr-code mb-3">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($plin['numero']) ?>" alt="QR Plin">
            </div>
            <form action="plin-verify.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($plin['order_id']) ?>">
                <div class="mb-3">
                    <label for="comprobante" class="form-label">Sube tu comprobante:</label>
                    <input type="file" class="form-control" name="comprobante" accept="image/*,application/pdf" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Confirmar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>