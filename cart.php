<?php
session_start();
require_once 'admin/inc/config.php';
require_once 'admin/inc/functions.php';
require_once 'admin/inc/CSRF_Protect.php';
$csrf = new CSRF_Protect();

// Verificar si el cliente está logueado
$clienteId = $_SESSION['customer']['idcliente'] ?? null;
if (!$clienteId) {
    header('Location: login.php');
    exit;
}

// Eliminar por GET (opcional)
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    if (isset($_SESSION['carritos'][$clienteId][$id])) {
        unset($_SESSION['carritos'][$clienteId][$id]);
    }
    header('Location: cart.php');
    exit;
}

// Preparar carrito
if (!isset($_SESSION['carritos'][$clienteId])) {
    $_SESSION['carritos'][$clienteId] = [];
}
$_SESSION['cart_certificados'] = $_SESSION['carritos'][$clienteId];
$cart = $_SESSION['cart_certificados'];

// Totales
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += (float)($item['precio'] ?? 0);
}
$igv = $subtotal * 0.18;
$total = $subtotal + $igv;

// Incluir header (opcionalmente con buffer)
ob_start();
include 'header.php';
echo trim(ob_get_clean());
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Carrito de Compras</title>
    <style>
        .cart-item:hover { background: #f8f9fa; }
        .remove-btn:hover { transform: scale(1.1); }
        .cart-empty { text-align: center; padding: 3rem; }
        .cart-empty i { font-size: 4rem; color: #6c757d; margin-bottom: 1rem; }
        .summary-card { position: sticky; top: 20px; }
    </style>
</head>
<body>

<div class="container my-5">
    <div class="row">
        <!-- Lista -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3><i class="fas fa-shopping-cart me-2"></i>Carrito de Compras</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($cart)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Curso</th>
                                        <th>Precio</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart as $item): ?>
                                    <tr class="cart-item" data-id="<?= htmlspecialchars($item['idcurso']) ?>">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-graduation-cap text-primary me-3"></i>
                                                <?= htmlspecialchars($item['nombre']) ?>
                                            </div>
                                        </td>
                                        <td>S/ <?= number_format($item['precio'], 2) ?></td>
                                        <td>
                                            <button class="btn btn-outline-danger btn-sm remove-from-cart" data-id="<?= (int)$item['idcurso'] ?>">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="cart-empty">
                            <i class="fas fa-shopping-cart"></i>
                            <h4>Tu carrito está vacío</h4>
                            <p class="text-muted">Agrega cursos a tu carrito para comenzar tu aprendizaje</p>
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-book me-2"></i>Explorar Cursos
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Resumen -->
        <div class="col-md-4">
            <div class="card shadow-sm summary-card">
                <div class="card-header bg-white">
                    <h3><i class="fas fa-receipt me-2"></i>Resumen de Compra</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>S/ <?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>IGV (18%):</span>
                        <span>S/ <?= number_format($igv, 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <strong>Total:</strong>
                        <strong class="text-primary">S/ <?= number_format($total, 2) ?></strong>
                    </div>
                    <?php if (!empty($cart)): ?>
                    <a href="checkout.php" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-credit-card me-2"></i> Proceder al Pago
                    </a>
                    <div class="text-center mt-3">
                        <a href="index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i> Continuar Comprando
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para eliminar -->
<script>
$(document).ready(function() {
    $('.remove-from-cart').click(function(e) {
        e.preventDefault();
        const idcurso = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar curso?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'cart-remove.php',
                    type: 'POST',
                    data: { idcurso: idcurso },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Eliminado', response.message, 'success')
                            .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>
