<?php
require_once('inc/config.php');
header('Content-Type: application/json');

if (!isset($_GET['idcurso'])) {
    echo json_encode(['success' => false, 'message' => 'ID del curso requerido']);
    exit;
}

$idcurso = (int) $_GET['idcurso'];

try {
    // Obtener datos del curso (sin JOIN con especialista por ahora)
    $stmt = $pdo->prepare("
        SELECT c.*, i.nombre as nombre_instructor, i.apellido as apellido_instructor,
               i.firma_digital as firma_instructor
        FROM curso c
        LEFT JOIN instructor i ON c.idinstructor = i.idinstructor
        WHERE c.idcurso = ?
    ");
    $stmt->execute([$idcurso]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$curso) {
        echo json_encode(['success' => false, 'message' => 'Curso no encontrado']);
        exit;
    }

    // Obtener inscritos del curso (todos, no solo aprobados para pruebas)
    $stmt = $pdo->prepare("
        SELECT i.idinscripcion, i.fecha_inscripcion, i.estado, i.nota_final,
               c.idcliente, c.nombre, c.apellido, c.dni, c.email
        FROM inscripcion i
        JOIN cliente c ON i.idcliente = c.idcliente
        WHERE i.idcurso = ?
        ORDER BY c.nombre, c.apellido
    ");
    $stmt->execute([$idcurso]);
    $inscritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todos los instructores
    $stmt = $pdo->prepare("
        SELECT idinstructor, nombre, apellido, especialidad, firma_digital as firma
        FROM instructor
        ORDER BY nombre, apellido
    ");
    $stmt->execute();
    $instructores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todos los especialistas (con campo firma_especialista)
    $stmt = $pdo->prepare("
        SELECT idespecialista, nombre, apellido, especialidad, firma_especialista as firma
        FROM especialista
        ORDER BY nombre, apellido
    ");
    $stmt->execute();
    $especialistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener configuración guardada del certificado
    $config_certificado = null;
    if (!empty($curso['config_certificado'])) {
        $config_certificado = json_decode($curso['config_certificado'], true);
    }

    echo json_encode([
        'success' => true,
        'curso' => $curso,
        'inscritos' => $inscritos,
        'instructores' => $instructores,
        'especialistas' => $especialistas,
        'config_certificado' => $config_certificado
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener datos: ' . $e->getMessage()
    ]);
}
?> 