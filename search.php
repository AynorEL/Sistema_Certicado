<?php
require_once('admin/inc/config.php');
require_once('admin/inc/functions.php');

header('Content-Type: application/json');

if (!isset($_GET['query']) || empty($_GET['query'])) {
    echo json_encode(['results' => []]);
    exit;
}

$query = $_GET['query'];
$query = '%' . $query . '%';

try {
    // Buscar en certificados
    $statement = $pdo->prepare("
        SELECT id, titulo, categoria 
        FROM certificados 
        WHERE titulo LIKE ? OR descripcion LIKE ? 
        LIMIT 5
    ");
    $statement->execute([$query, $query]);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Formatear resultados
    $formatted_results = array_map(function($item) {
        return [
            'id' => $item['id'],
            'titulo' => htmlspecialchars($item['titulo']),
            'categoria' => htmlspecialchars($item['categoria'])
        ];
    }, $results);

    echo json_encode(['results' => $formatted_results]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la búsqueda']);
} 