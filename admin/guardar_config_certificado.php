<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

header('Content-Type: application/json');

try {
    $rawInput = file_get_contents('php://input');
    
    if (empty($rawInput)) {
        throw new Exception('No se recibieron datos');
    }
    
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
    }
    
    if (!$input) {
        throw new Exception('Datos JSON inválidos');
    }
    
    if (!isset($input['idcurso'])) {
        throw new Exception('ID de curso no proporcionado');
    }
    
    if (!isset($input['configuracion'])) {
        throw new Exception('Configuración no proporcionada');
    }
    
    $idcurso = (int)$input['idcurso'];
    $configuracion = $input['configuracion'];
    
    // Validar que el curso existe
    $stmt = $pdo->prepare("SELECT idcurso FROM curso WHERE idcurso = ?");
    $stmt->execute([$idcurso]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Curso no encontrado');
    }
    
    // Convertir la configuración a JSON
    $configJson = json_encode($configuracion, JSON_UNESCAPED_UNICODE);
    
    if ($configJson === false) {
        throw new Exception('Error al codificar la configuración');
    }
    
    // Actualizar la configuración del certificado
    $stmt = $pdo->prepare("UPDATE curso SET config_certificado = ? WHERE idcurso = ?");
    
    if ($stmt->execute([$configJson, $idcurso])) {
        echo json_encode([
            'success' => true,
            'message' => 'Configuración guardada exitosamente',
            'idcurso' => $idcurso
        ]);
    } else {
        throw new Exception('Error al actualizar la base de datos');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
