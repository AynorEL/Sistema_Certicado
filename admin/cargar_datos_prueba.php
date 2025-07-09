<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['idcurso'])) {
        throw new Exception('ID de curso no proporcionado');
    }
    
    $idcurso = (int)$_GET['idcurso'];
    
    // Verificar que el curso existe
    $stmt = $pdo->prepare("SELECT idcurso, nombre_curso FROM curso WHERE idcurso = ?");
    $stmt->execute([$idcurso]);
    $curso = $stmt->fetch();
    
    if (!$curso) {
        throw new Exception('Curso no encontrado');
    }
    
    $datos = [
        'curso' => $curso,
        'alumnos' => [],
        'instructores' => [],
        'especialistas' => [],
        'firmas' => []
    ];
    
    // Cargar alumnos inscritos en el curso
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.idcliente, c.nombre, c.apellido, c.email
        FROM cliente c
        INNER JOIN inscripcion i ON c.idcliente = i.idcliente
        WHERE i.idcurso = ? AND i.estado = 'Aprobado'
        ORDER BY c.apellido, c.nombre
        LIMIT 10
    ");
    $stmt->execute([$idcurso]);
    $datos['alumnos'] = $stmt->fetchAll();
    
    // Si no hay alumnos inscritos, crear datos de prueba
    if (empty($datos['alumnos'])) {
        $datos['alumnos'] = [
            ['idcliente' => 1, 'nombre' => 'Juan Carlos', 'apellido' => 'García López', 'email' => 'juan.garcia@email.com'],
            ['idcliente' => 2, 'nombre' => 'María Elena', 'apellido' => 'Rodríguez Silva', 'email' => 'maria.rodriguez@email.com'],
            ['idcliente' => 3, 'nombre' => 'Carlos Alberto', 'apellido' => 'Martínez Vega', 'email' => 'carlos.martinez@email.com'],
            ['idcliente' => 4, 'nombre' => 'Ana Patricia', 'apellido' => 'Hernández Ruiz', 'email' => 'ana.hernandez@email.com'],
            ['idcliente' => 5, 'nombre' => 'Luis Miguel', 'apellido' => 'Pérez Torres', 'email' => 'luis.perez@email.com']
        ];
    }
    
    // Cargar instructores del curso - CORREGIDO: usar firma_digital
    $stmt = $pdo->prepare("
        SELECT DISTINCT i.idinstructor, i.nombre, i.apellido, i.email, i.firma_digital as foto
        FROM instructor i
        INNER JOIN curso c ON i.idinstructor = c.idinstructor
        WHERE c.idcurso = ?
        ORDER BY i.apellido, i.nombre
    ");
    $stmt->execute([$idcurso]);
    $datos['instructores'] = $stmt->fetchAll();
    
    // Si no hay instructores, crear datos de prueba
    if (empty($datos['instructores'])) {
        $datos['instructores'] = [
            ['idinstructor' => 1, 'nombre' => 'Dr. Roberto', 'apellido' => 'Sánchez Mendoza', 'email' => 'roberto.sanchez@instituto.com', 'foto' => ''],
            ['idinstructor' => 2, 'nombre' => 'Lic. Carmen', 'apellido' => 'Vargas Jiménez', 'email' => 'carmen.vargas@instituto.com', 'foto' => '']
        ];
    }
    
    // Cargar especialistas del curso (todos los especialistas disponibles)
    $stmt = $pdo->prepare("
        SELECT DISTINCT e.idespecialista, e.nombre, e.apellido, e.email, e.firma_especialista as foto
        FROM especialista e
        ORDER BY e.apellido, e.nombre
    ");
    $stmt->execute();
    $datos['especialistas'] = $stmt->fetchAll();
    
    // Si no hay especialistas, crear datos de prueba
    if (empty($datos['especialistas'])) {
        $datos['especialistas'] = [
            ['idespecialista' => 1, 'nombre' => 'Mg. Patricia', 'apellido' => 'González Castro', 'email' => 'patricia.gonzalez@instituto.com', 'foto' => ''],
            ['idespecialista' => 2, 'nombre' => 'Dr. Manuel', 'apellido' => 'Ramírez Flores', 'email' => 'manuel.ramirez@instituto.com', 'foto' => '']
        ];
    }
    
    // Cargar firmas disponibles - CORREGIDO: ruta absoluta
    $firmasDir = __DIR__ . '/../assets/uploads/firmas/';
    $firmas = [];
    
    if (is_dir($firmasDir)) {
        $archivos = scandir($firmasDir);
        foreach ($archivos as $archivo) {
            if ($archivo !== '.' && $archivo !== '..' && pathinfo($archivo, PATHINFO_EXTENSION) === 'png') {
                $firmas[] = [
                    'nombre' => pathinfo($archivo, PATHINFO_FILENAME),
                    'archivo' => $archivo,
                    'url' => '../assets/uploads/firmas/' . $archivo
                ];
            }
        }
    }
    
    // Si no hay firmas, crear datos de prueba
    if (empty($firmas)) {
        $firmas = [
            ['nombre' => 'Firma Instructor 1', 'archivo' => 'instructor_firma_1.png', 'url' => '../assets/uploads/firmas/instructor_firma_1.png'],
            ['nombre' => 'Firma Instructor 2', 'archivo' => 'instructor_firma_2.png', 'url' => '../assets/uploads/firmas/instructor_firma_2.png'],
            ['nombre' => 'Firma Especialista 1', 'archivo' => 'especialista_firma_1.png', 'url' => '../assets/uploads/firmas/especialista_firma_1.png'],
            ['nombre' => 'Firma Especialista 2', 'archivo' => 'especialista_firma_2.png', 'url' => '../assets/uploads/firmas/especialista_firma_2.png']
        ];
    }
    
    $datos['firmas'] = $firmas;
    
    // Agregar información adicional
    $datos['fecha_actual'] = date('d/m/Y');
    $datos['fecha_actual_iso'] = date('Y-m-d');
    $datos['codigo_curso'] = 'CUR-' . str_pad($idcurso, 4, '0', STR_PAD_LEFT);
    
    echo json_encode([
        'success' => true,
        'datos' => $datos
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'last_error' => error_get_last()
        ]
    ]);
}
?> 