<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['idcurso']) || !isset($input['configuracion'])) {
        throw new Exception('Datos incompletos');
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
        'message' => $e->getMessage()
    ]);
}
?>
