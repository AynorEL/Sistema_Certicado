<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['idcurso'])) {
        throw new Exception('ID de curso no proporcionado');
    }
    
    $idcurso = (int)$_GET['idcurso'];
    
    // Obtener la configuración del certificado
    $stmt = $pdo->prepare("SELECT config_certificado FROM curso WHERE idcurso = ?");
    $stmt->execute([$idcurso]);
    $row = $stmt->fetch();
    
    if (!$row) {
        throw new Exception('Curso no encontrado');
    }
    
    $configJson = $row['config_certificado'];
    
    if (empty($configJson) || $configJson === 'null' || $configJson === '""') {
        echo json_encode([
            'success' => true,
            'config' => null,
            'message' => 'No hay configuración guardada'
        ]);
        exit;
    }
    
    $config = json_decode($configJson, true);
    
    if ($config === null) {
        throw new Exception('Error al decodificar la configuración');
    }
    
    echo json_encode([
        'success' => true,
        'config' => $config
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
