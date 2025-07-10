<?php
require_once('admin/inc/config.php');
require_once('admin/inc/functions.php');
require_once('search-config.php');

header('Content-Type: application/json');

if (!isset($_GET['query']) || empty($_GET['query'])) {
    echo json_encode(['results' => []]);
    exit;
}

$query = trim($_GET['query']);

// Validar consulta
if (!validateSearchQuery($query)) {
    echo json_encode([
        'error' => 'Consulta de búsqueda no válida',
        'results' => []
    ]);
    exit;
}

// Sanitizar consulta
$query = sanitizeSearchQuery($query);
$search_query = '%' . $query . '%';

try {
    $results = [];
    
    // Buscar en CURSOS
    if ($SEARCH_CONFIG['cursos']['enabled']) {
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
            AND (c.nombre_curso LIKE ? OR c.descripcion LIKE ? OR c.objetivos LIKE ? OR c.requisitos LIKE ?)
            ORDER BY c.idcurso DESC
            LIMIT ?
        ");
        $statement->execute([
            $search_query, 
            $search_query, 
            $search_query, 
            $search_query,
            $SEARCH_CONFIG['cursos']['max_results']
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
                'url' => generateResultUrl('curso', $curso['id']),
                'icon' => getTypeIcon('curso'),
                'color' => getTypeColor('curso')
            ];
        }
    }
    
    // Buscar en PREGUNTAS FRECUENTES
    if ($SEARCH_CONFIG['preguntas_frecuentes']['enabled']) {
        $statement = $pdo->prepare("
            SELECT 
                id,
                titulo_pregunta as titulo,
                contenido_pregunta as descripcion,
                'faq' as tipo
            FROM preguntas_frecuentes 
            WHERE titulo_pregunta LIKE ? OR contenido_pregunta LIKE ?
            ORDER BY orden_pregunta ASC
            LIMIT ?
        ");
        $statement->execute([
            $search_query, 
            $search_query,
            $SEARCH_CONFIG['preguntas_frecuentes']['max_results']
        ]);
        $faqs = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($faqs as $faq) {
            $results[] = [
                'id' => $faq['id'],
                'titulo' => htmlspecialchars($faq['titulo']),
                'descripcion' => htmlspecialchars(substr($faq['descripcion'], 0, 150)) . '...',
                'categoria' => 'Preguntas Frecuentes',
                'tipo' => $faq['tipo'],
                'url' => generateResultUrl('faq', $faq['id']),
                'icon' => getTypeIcon('faq'),
                'color' => getTypeColor('faq')
            ];
        }
    }
    
    // Buscar en CATEGORÍAS
    if ($SEARCH_CONFIG['categorias']['enabled']) {
        $statement = $pdo->prepare("
            SELECT 
                idcategoria as id,
                nombre_categoria as titulo,
                descripcion,
                'categoria' as tipo
            FROM categoria 
            WHERE nombre_categoria LIKE ? OR descripcion LIKE ?
            LIMIT ?
        ");
        $statement->execute([
            $search_query, 
            $search_query,
            $SEARCH_CONFIG['categorias']['max_results']
        ]);
        $categorias = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($categorias as $categoria) {
            $results[] = [
                'id' => $categoria['id'],
                'titulo' => htmlspecialchars($categoria['titulo']),
                'descripcion' => htmlspecialchars(substr($categoria['descripcion'], 0, 150)) . '...',
                'categoria' => 'Categoría',
                'tipo' => $categoria['tipo'],
                'url' => generateResultUrl('categoria', $categoria['id']),
                'icon' => getTypeIcon('categoria'),
                'color' => getTypeColor('categoria')
            ];
        }
    }
    
    // Buscar en SERVICIOS
    if ($SEARCH_CONFIG['servicios']['enabled']) {
        $statement = $pdo->prepare("
            SELECT 
                id,
                titulo,
                contenido as descripcion,
                foto as imagen,
                'servicio' as tipo
            FROM servicios 
            WHERE titulo LIKE ? OR contenido LIKE ?
            LIMIT ?
        ");
        $statement->execute([
            $search_query, 
            $search_query,
            $SEARCH_CONFIG['servicios']['max_results']
        ]);
        $servicios = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($servicios as $servicio) {
            $results[] = [
                'id' => $servicio['id'],
                'titulo' => htmlspecialchars($servicio['titulo']),
                'descripcion' => htmlspecialchars(substr($servicio['descripcion'], 0, 150)) . '...',
                'categoria' => 'Servicio',
                'imagen' => $servicio['imagen'] ? 'assets/uploads/' . $servicio['imagen'] : 'assets/img/default-service.jpg',
                'tipo' => $servicio['tipo'],
                'url' => generateResultUrl('servicio', $servicio['id']),
                'icon' => getTypeIcon('servicio'),
                'color' => getTypeColor('servicio')
            ];
        }
    }
    
    // Limitar resultados totales
    $results = array_slice($results, 0, SEARCH_MAX_RESULTS);
    
    // Loggear la búsqueda
    logSearch($query, count($results));
    
    echo json_encode([
        'results' => $results,
        'total' => count($results),
        'query' => htmlspecialchars($query),
        'message' => count($results) > 0 ? 'Resultados encontrados' : 'No se encontraron resultados'
    ]);
    
} catch (PDOException $e) {
    error_log("Error en búsqueda: " . $e->getMessage());
    echo json_encode([
        'error' => 'Error en la búsqueda',
        'results' => []
    ]);
} 