<?php
/**
 * Configuración del sistema de búsqueda predictiva
 */

// Configuración general de búsqueda
$SEARCH_CONFIG = [
    'cursos' => [
        'enabled' => true,
        'max_results' => 5,
        'search_fields' => ['nombre_curso', 'descripcion', 'objetivos', 'requisitos']
    ],
    'preguntas_frecuentes' => [
        'enabled' => true,
        'max_results' => 3,
        'search_fields' => ['titulo_pregunta', 'contenido_pregunta']
    ],
    'categorias' => [
        'enabled' => true,
        'max_results' => 3,
        'search_fields' => ['nombre_categoria', 'descripcion']
    ],
    'servicios' => [
        'enabled' => true,
        'max_results' => 3,
        'search_fields' => ['nombre_servicio', 'descripcion']
    ]
];

// Palabras prohibidas para búsqueda (evitar spam)
$FORBIDDEN_WORDS = [
    'admin', 'administrator', 'root', 'system', 'config', 'setup', 'install',
    'password', 'passwd', 'secret', 'private', 'internal', 'test', 'debug',
    'error', 'warning', 'notice', 'undefined', 'null', 'void', 'empty',
    'delete', 'drop', 'truncate', 'alter', 'create', 'insert', 'update',
    'select', 'union', 'join', 'where', 'from', 'into', 'values',
    'script', 'javascript', 'vbscript', 'onload', 'onclick', 'onmouseover',
    'iframe', 'frame', 'object', 'embed', 'applet', 'meta', 'link',
    'style', 'css', 'xml', 'html', 'head', 'body', 'title', 'div',
    'span', 'p', 'br', 'hr', 'table', 'tr', 'td', 'th', 'ul', 'ol', 'li',
    'a', 'img', 'form', 'input', 'button', 'textarea', 'select', 'option',
    'label', 'fieldset', 'legend', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
    'b', 'i', 'u', 'strong', 'em', 'code', 'pre', 'blockquote', 'cite',
    'abbr', 'acronym', 'address', 'big', 'small', 'sub', 'sup', 'tt',
    'kbd', 'samp', 'var', 'dfn', 'del', 'ins', 'q', 's', 'strike',
    'font', 'center', 'marquee', 'blink', 'nobr', 'wbr', 'bdo', 'bdi',
    'ruby', 'rt', 'rp', 'mark', 'time', 'meter', 'progress', 'details',
    'summary', 'menu', 'menuitem', 'command', 'keygen', 'output', 'canvas',
    'svg', 'math', 'text', 'tspan', 'path', 'circle', 'rect', 'line',
    'polyline', 'polygon', 'ellipse', 'defs', 'g', 'use', 'symbol',
    'pattern', 'linearGradient', 'radialGradient', 'stop', 'animate',
    'animateTransform', 'animateMotion', 'set', 'mpath', 'foreignObject',
    'switch', 'textPath', 'tref', 'altGlyph', 'altGlyphDef', 'altGlyphItem',
    'glyphRef', 'feBlend', 'feColorMatrix', 'feComponentTransfer',
    'feComposite', 'feConvolveMatrix', 'feDiffuseLighting', 'feDisplacementMap',
    'feDistantLight', 'feDropShadow', 'feFlood', 'feFuncA', 'feFuncB',
    'feFuncG', 'feFuncR', 'feGaussianBlur', 'feImage', 'feMerge',
    'feMergeNode', 'feMorphology', 'feOffset', 'fePointLight',
    'feSpecularLighting', 'feSpotLight', 'feTile', 'feTurbulence',
    'filter', 'mask', 'clipPath', 'defs', 'metadata', 'title', 'desc'
];

// Configuración de validación
$VALIDATION_CONFIG = [
    'min_length' => 1,
    'max_length' => 100,
    'allowed_chars' => '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_.,!?()]+$/',
    'max_words' => 10
];

// Configuración de seguridad
$SECURITY_CONFIG = [
    'rate_limit' => [
        'enabled' => true,
        'max_requests' => 10,
        'time_window' => 60 // segundos
    ],
    'sql_injection_protection' => true,
    'xss_protection' => true
];

// Configuración de resultados
$RESULTS_CONFIG = [
    'max_total_results' => 20,
    'highlight_query' => true,
    'include_suggestions' => true,
    'suggestions_limit' => 3
];

// Incluir configuración centralizada
require_once(__DIR__ . '/config.php');

/**
 * Validar consulta de búsqueda
 */
function validateSearchQuery($query) {
    global $VALIDATION_CONFIG, $FORBIDDEN_WORDS;
    
    // Verificar longitud mínima y máxima
    if (strlen($query) < $VALIDATION_CONFIG['min_length'] || 
        strlen($query) > $VALIDATION_CONFIG['max_length']) {
        return false;
    }
    
    // Verificar caracteres permitidos
    if (!preg_match($VALIDATION_CONFIG['allowed_chars'], $query)) {
        return false;
    }
    
    // Verificar número máximo de palabras
    $words = explode(' ', trim($query));
    if (count($words) > $VALIDATION_CONFIG['max_words']) {
        return false;
    }
    
    // Verificar palabras prohibidas
    $query_lower = strtolower($query);
    foreach ($FORBIDDEN_WORDS as $forbidden) {
        if (strpos($query_lower, strtolower($forbidden)) !== false) {
            return false;
        }
    }
    
    return true;
}

/**
 * Sanitizar consulta de búsqueda
 */
function sanitizeSearchQuery($query) {
    // Eliminar espacios extra
    $query = preg_replace('/\s+/', ' ', trim($query));
    
    // Escapar caracteres especiales para SQL
    $query = str_replace(['%', '_'], ['\\%', '\\_'], $query);
    
    return $query;
}

/**
 * Generar URL para resultado
 */
function generateResultUrl($tipo, $id) {
    global $query;
    $searchParam = '';
    if (!empty($query)) {
        $searchParam = '?search=' . urlencode($query);
    }
    switch ($tipo) {
        case 'curso':
            return BASE_URL . "curso.php?id=" . intval($id) . $searchParam . "#curso-" . intval($id);
        case 'servicio':
            return BASE_URL . "servicios.php?id=" . intval($id) . $searchParam;
        case 'faq':
            return BASE_URL . "faq.php" . $searchParam . "#faq-" . intval($id);
        case 'categoria':
            return BASE_URL . "categoria.php?id=" . intval($id) . $searchParam;
        default:
            return "#";
    }
}

/**
 * Obtener icono para tipo de resultado
 */
function getTypeIcon($tipo) {
    $icons = [
        'curso' => 'fas fa-graduation-cap',
        'servicio' => 'fas fa-cogs',
        'faq' => 'fas fa-question-circle',
        'categoria' => 'fas fa-folder'
    ];
    return $icons[$tipo] ?? 'fas fa-file';
}

/**
 * Obtener color para tipo de resultado
 */
function getTypeColor($tipo) {
    $colors = [
        'curso' => '#007bff',
        'servicio' => '#28a745',
        'faq' => '#ffc107',
        'categoria' => '#6c757d'
    ];
    return $colors[$tipo] ?? '#6c757d';
}

/**
 * Generar sugerencias de búsqueda
 */
function generateSuggestions($query, $results) {
    $suggestions = [];
    
    // Si no hay resultados, sugerir términos similares
    if (empty($results)) {
        $words = explode(' ', $query);
        foreach ($words as $word) {
            if (strlen($word) > 2) {
                $suggestions[] = $word;
            }
        }
    }
    
    return array_slice($suggestions, 0, 3);
} 