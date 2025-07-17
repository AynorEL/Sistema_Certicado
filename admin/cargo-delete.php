<?php
require_once('header.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Validar si se puede eliminar
    $validacion = validarEliminacionCargo($pdo, $id);
    
    if (!$validacion['puede_eliminar']) {
        $_SESSION['error'] = $validacion['mensaje'];
        header('location: cargo.php');
        exit;
    }
    
    // Si pasa la validación, proceder con la eliminación
    try {
        $stmt = $pdo->prepare("DELETE FROM cargo WHERE idcargo = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = "✅ Cargo eliminado exitosamente.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Error al eliminar el cargo: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "❌ ID de cargo no proporcionado.";
}

header('location: cargo.php');
exit;
?> 