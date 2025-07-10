<?php
require_once('admin/inc/config.php');

// Configurar headers para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar que se recibió una consulta
if (!isset($_GET['query']) || empty($_GET['query'])) {
    echo json_encode(['results' => []]);
    exit;
}

$query = trim($_GET['query']);

// Validación simple
if (strlen($query) < 1 || strlen($query) > 100) {
    echo json_encode([
        'error' => 'Consulta de búsqueda no válida',
        'results' => []
    ]);
    exit;
}

// Sanitizar consulta
$query = preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_.,!?()]/', '', $query);
$search_query = '%' . $query . '%';

try {
    $results = [];
    
    // Buscar en CURSOS
    $statement = $pdo->prepare("
        SELECT 
            c.idcurso as id,
            c.nombre_curso as titulo,
            c.descripcion,
            c.precio,
            c.duracion,
            c.diseño as imagen,
            cat.nombre_categoria as categoria,
            'curso' as tipo
        FROM curso c
        LEFT JOIN categoria cat ON c.idcategoria = cat.idcategoria
        WHERE c.estado = 'Activo' 
        AND (
            LOWER(c.nombre_curso) LIKE LOWER(?) OR 
            LOWER(c.descripcion) LIKE LOWER(?) OR 
            LOWER(cat.nombre_categoria) LIKE LOWER(?)
        )
        ORDER BY 
            CASE 
                WHEN LOWER(c.nombre_curso) LIKE LOWER(?) THEN 1
                ELSE 2
            END,
            c.idcurso DESC
        LIMIT 5
    ");
    
    $exact_match = $query . '%';
    $statement->execute([
        $search_query, 
        $search_query, 
        $search_query,
        $exact_match
    ]);
    $cursos = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($cursos as $curso) {
        $results[] = [
            'id' => $curso['id'],
            'titulo' => htmlspecialchars($curso['titulo']),
            'descripcion' => htmlspecialchars(substr($curso['descripcion'], 0, 150)) . '...',
            'categoria' => htmlspecialchars($curso['categoria']),
            'precio' => number_format($curso['precio'], 2),
            'duracion' => $curso['duracion'] . ' horas',
            'imagen' => $curso['imagen'] ? 'assets/uploads/cursos/' . $curso['imagen'] : 'assets/img/default-course.jpg',
            'tipo' => $curso['tipo'],
            'tipo_nombre' => 'Curso',
            'url' => BASE_URL . 'curso.php?id=' . $curso['id'],
            'icon' => 'fas fa-graduation-cap',
            'color' => '#007bff'
        ];
    }
    
    // Buscar en PREGUNTAS FRECUENTES
    $statement = $pdo->prepare("
        SELECT 
            id,
            titulo_pregunta as titulo,
            contenido_pregunta as descripcion,
            orden_pregunta,
            'faq' as tipo
        FROM preguntas_frecuentes 
        WHERE LOWER(titulo_pregunta) LIKE LOWER(?) OR LOWER(contenido_pregunta) LIKE LOWER(?)
        ORDER BY 
            CASE 
                WHEN LOWER(titulo_pregunta) LIKE LOWER(?) THEN 1
                ELSE 2
            END,
            orden_pregunta ASC
        LIMIT 3
    ");
    
    $exact_match = $query . '%';
    $statement->execute([
        $search_query, 
        $search_query,
        $exact_match
    ]);
    $faqs = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($faqs as $faq) {
        $results[] = [
            'id' => $faq['id'],
            'titulo' => htmlspecialchars($faq['titulo']),
            'descripcion' => htmlspecialchars(substr($faq['descripcion'], 0, 150)) . '...',
            'categoria' => 'Preguntas Frecuentes',
            'orden' => $faq['orden_pregunta'],
            'tipo' => $faq['tipo'],
            'tipo_nombre' => 'Pregunta Frecuente',
            'url' => BASE_URL . 'faq.php#faq-' . $faq['id'],
            'icon' => 'fas fa-question-circle',
            'color' => '#ffc107'
        ];
    }
    
    // Limitar resultados totales
    $results = array_slice($results, 0, 20);
    
    // Devolver respuesta JSON
    echo json_encode([
        'results' => $results,
        'query' => $query,
        'total' => count($results),
        'suggestions' => []
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error en la búsqueda: ' . $e->getMessage(),
        'results' => []
    ]);
} 