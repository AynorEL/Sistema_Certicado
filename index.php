<?php

require_once('header.php');

// Obtener enlace de WhatsApp desde el header
global $whatsapp_link;

// Obtener sliders
$statement = $pdo->prepare("SELECT * FROM sliders ORDER BY posicion ASC");
$statement->execute();
$sliders = $statement->fetchAll(PDO::FETCH_ASSOC);

// Obtener configuraciones
$statement = $pdo->prepare("SELECT * FROM configuraciones WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

$cta_title = '';
$cta_content = '';
$cta_read_more_text = '';
$cta_read_more_url = '';
$cta_photo = '';
$home_service_on_off = 0;
$home_welcome_on_off = 0;
$bienvenida_activa = 0;
$certificados_activos = 0;
$servicios_activos = 0;
$boletin_activo = 0;
$total_certificados_recientes = 6;
$total_certificados_populares = 6;
$faq_activo = 0;

foreach ($result as $row) {
    $cta_title = $row['cta_title'] ?? '';
    $cta_content = $row['cta_content'] ?? '';
    $cta_read_more_text = $row['cta_read_more_text'] ?? '';
    $cta_read_more_url = $row['cta_read_more_url'] ?? '';
    $cta_photo = $row['cta_photo'] ?? '';
    $home_service_on_off = $row['home_service_on_off'] ?? 0;
    $home_welcome_on_off = $row['home_welcome_on_off'] ?? 0;
    $bienvenida_activa = $row['bienvenida_activa'] ?? 0;
    $certificados_activos = $row['certificados_activos'] ?? 0;
    $servicios_activos = $row['servicios_activos'] ?? 0;
    $boletin_activo = $row['boletin_activo'] ?? 0;
    $total_certificados_recientes = $row['total_certificados_recientes'] ?? 6;
    $total_certificados_populares = $row['total_certificados_populares'] ?? 6;
    $faq_activo = $row['faq_activo'] ?? 0;
}

// Obtener cursos recientes con información completa
$statement = $pdo->prepare(
    "SELECT c.*, cat.nombre_categoria, 
            i.nombre as instructor_nombre, i.apellido as instructor_apellido, i.especialidad as instructor_especialidad,
            e.nombre as especialista_nombre, e.apellido as especialista_apellido, e.especialidad as especialista_especialidad
    FROM curso c
    LEFT JOIN categoria cat ON c.idcategoria = cat.idcategoria
    LEFT JOIN instructor i ON c.idinstructor = i.idinstructor
    LEFT JOIN especialista e ON c.idespecialista = e.idespecialista
    WHERE c.estado = 'Activo'
    ORDER BY c.idcurso DESC LIMIT :limit"
);
$statement->bindValue(':limit', $total_certificados_recientes, PDO::PARAM_INT);
$statement->execute();
$cursos_recientes = $statement->fetchAll(PDO::FETCH_ASSOC);

// Obtener cursos populares con información completa
$statement = $pdo->prepare(
    "SELECT c.*, cat.nombre_categoria, COUNT(i.idinscripcion) as total_inscripciones,
            ins.nombre as instructor_nombre, ins.apellido as instructor_apellido, ins.especialidad as instructor_especialidad,
            e.nombre as especialista_nombre, e.apellido as especialista_apellido, e.especialidad as especialista_especialidad
    FROM curso c
    LEFT JOIN categoria cat ON c.idcategoria = cat.idcategoria
    LEFT JOIN inscripcion i ON c.idcurso = i.idcurso
    LEFT JOIN instructor ins ON c.idinstructor = ins.idinstructor
    LEFT JOIN especialista e ON c.idespecialista = e.idespecialista
    WHERE c.estado = 'Activo'
    GROUP BY c.idcurso
    ORDER BY total_inscripciones DESC, c.idcurso DESC
    LIMIT :limit"
);
$statement->bindValue(':limit', $total_certificados_populares, PDO::PARAM_INT);
$statement->execute();
$cursos_populares = $statement->fetchAll(PDO::FETCH_ASSOC);

// Obtener servicios
$statement = $pdo->prepare("SELECT * FROM servicios");
$statement->execute();
$servicios = $statement->fetchAll(PDO::FETCH_ASSOC);

// Obtener preguntas frecuentes
$statement = $pdo->prepare("SELECT * FROM preguntas_frecuentes ORDER BY orden_pregunta ASC");
$statement->execute();
$preguntas_frecuentes = $statement->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Sistema de Búsqueda Dropdown -->
<div class="search-dropdown-container" id="searchDropdown" style="display: none;">
    <div class="search-dropdown">
        <div class="search-input-container">
            <input type="text" id="indexSearchInput" class="form-control" placeholder="Buscar cursos, servicios, preguntas... (escribe para buscar)">
            <button type="button" class="search-btn" onclick="performIndexSearch()">
                <i class="fas fa-search"></i>
                </button>
        </div>
        <div id="indexSearchResults" class="search-results"></div>
    </div>
</div>

<!-- Toast para notificaciones -->
<div class="toast-container" style="position: fixed; top: 0; right: 0; z-index: 9999; padding: 15px;">
    <div id="searchToast" class="alert alert-info" role="alert" style="display: none;">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <div id="toastBody"></div>
    </div>
</div>

<style>
/* Estilos para el sistema de búsqueda dropdown */
.search-dropdown-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 50px;
}

.search-dropdown {
    background: white;
    border-radius: 15px;
    padding: 25px;
    width: 90%;
    max-width: 700px;
    max-height: 70vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.search-input-container {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

#indexSearchInput {
    flex: 1;
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1.1rem;
    transition: border-color 0.3s ease;
}

#indexSearchInput:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.search-btn {
    padding: 15px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-btn:hover {
    background: #0056b3;
    transform: translateY(-2px);
}

.search-results {
    max-height: 400px;
    overflow-y: auto;
}

.search-result-item {
    padding: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.search-result-item:hover {
    background: #e9ecef;
    border-color: #007bff;
    transform: translateX(5px);
}

.search-result-title {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.search-result-type {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 5px;
}

.search-result-description {
    font-size: 0.9rem;
    color: #555;
}

/* Estilos para secciones con ID */
.section-highlight {
    animation: highlightSection 2s ease-in-out;
}

@keyframes highlightSection {
    0% { background-color: transparent; }
    50% { background-color: rgba(0, 123, 255, 0.1); }
    100% { background-color: transparent; }
}

/* Toast personalizado */
.toast {
    background: white;
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.toast-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    border-radius: 10px 10px 0 0;
}

.toast-body {
    padding: 15px;
    font-size: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .search-container {
        width: 95%;
        padding: 20px;
    }
    
    .search-header h3 {
        font-size: 1.2rem;
    }
    
    #indexSearchInput {
        font-size: 1rem;
        padding: 12px;
    }
}

html, body {
    max-width: 100vw;
    overflow-x: hidden !important;
}
</style>

<script>
// Datos del contenido del index para búsqueda local
const indexContent = {
    cursos: [
        <?php foreach ($cursos_recientes as $curso): ?>
        {
            id: <?php echo $curso['idcurso']; ?>,
            titulo: "<?php echo addslashes($curso['nombre_curso']); ?>",
            descripcion: "<?php echo addslashes($curso['descripcion']); ?>",
            categoria: "<?php echo addslashes($curso['nombre_categoria']); ?>",
            duracion: "<?php echo $curso['duracion']; ?>",
            tipo: 'curso',
            seccion: 'cursos-recientes',
            url: 'curso.php?id=<?php echo $curso['idcurso']; ?>'
        },
            <?php endforeach; ?>
        <?php foreach ($cursos_populares as $curso): ?>
        {
            id: <?php echo $curso['idcurso']; ?>,
            titulo: "<?php echo addslashes($curso['nombre_curso']); ?>",
            descripcion: "<?php echo addslashes($curso['descripcion']); ?>",
            categoria: "<?php echo addslashes($curso['nombre_categoria']); ?>",
            duracion: "<?php echo $curso['duracion']; ?>",
            tipo: 'curso',
            seccion: 'cursos-populares',
            url: 'curso.php?id=<?php echo $curso['idcurso']; ?>'
        },
        <?php endforeach; ?>
    ],
    servicios: [
        <?php foreach ($servicios as $servicio): ?>
        {
            id: <?php echo $servicio['id']; ?>,
            titulo: "<?php echo addslashes($servicio['titulo']); ?>",
            descripcion: "<?php echo addslashes($servicio['contenido']); ?>",
            tipo: 'servicio',
            seccion: 'servicios'
        },
        <?php endforeach; ?>
    ],
    faq: [
        <?php foreach ($preguntas_frecuentes as $faq): ?>
        {
            id: <?php echo $faq['id']; ?>,
            titulo: "<?php echo addslashes($faq['titulo_pregunta']); ?>",
            descripcion: "<?php echo addslashes($faq['contenido_pregunta']); ?>",
            tipo: 'faq',
            seccion: 'faq',
            url: 'faq.php#faq-<?php echo $faq['id']; ?>'
        },
        <?php endforeach; ?>
    ]
};

// Función para abrir búsqueda
function openSearch() {
    document.getElementById('searchDropdown').style.display = 'flex';
    document.getElementById('indexSearchInput').focus();
}

// Función para cerrar búsqueda
function closeSearch() {
    document.getElementById('searchDropdown').style.display = 'none';
    document.getElementById('indexSearchInput').value = '';
    document.getElementById('indexSearchResults').innerHTML = '';
}

// Función para realizar búsqueda predictiva en tiempo real
function performIndexSearch() {
    const query = document.getElementById('indexSearchInput').value.trim();
    
    if (query.length < 1) {
        document.getElementById('indexSearchResults').innerHTML = '';
        return;
    }
    
    // Mostrar indicador de carga
    document.getElementById('indexSearchResults').innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Buscando...</span>
        </div>
            <p style="margin-top: 10px; color: #6c757d;">Buscando "${query}"...</p>
        </div>
    `;
    
    // Realizar búsqueda AJAX
    fetch(`search-predictive.php?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showToast(data.error, 'error');
                return;
            }
            displayPredictiveResults(data.results, query, data.suggestions);
        })
        .catch(error => {
            console.error('Error en búsqueda:', error);
            showToast('Error al realizar la búsqueda', 'error');
        });
}

// Función para mostrar resultados predictivos detallados
function displayPredictiveResults(results, query, suggestions) {
    const resultsContainer = document.getElementById('indexSearchResults');
    
    if (results.length === 0) {
        let html = `
            <div class="text-center py-4">
                <i class="fas fa-search fa-3x" style="color: #6c757d; margin-bottom: 15px;"></i>
                <h5>No se encontraron resultados</h5>
                <p class="text-muted">No encontramos resultados para: "${query}"</p>
        `;
        
        // Mostrar sugerencias si las hay
        if (suggestions && suggestions.length > 0) {
            html += `
                <div style="margin-top: 15px;">
                    <h6>¿Quizás quisiste buscar?</h6>
                    <div class="suggestions-container">
            `;
            suggestions.forEach(suggestion => {
                html += `
                    <button class="btn btn-default btn-sm" style="margin-right: 8px; margin-bottom: 8px;" 
                            onclick="document.getElementById('indexSearchInput').value='${suggestion}'; performIndexSearch();">
                        ${suggestion}
                    </button>
                `;
            });
            html += `</div></div>`;
        }
        
        html += `</div>`;
        resultsContainer.innerHTML = html;
        return;
    }
    
    let html = `
                    <div class="search-results-header" style="margin-bottom: 15px;">
                <h6 style="margin-bottom: 10px;">
                <i class="fas fa-search text-primary"></i> 
                Se encontraron ${results.length} resultados para "${query}"
            </h6>
            <div class="search-stats">
                <small class="text-muted">
                    ${results.filter(r => r.tipo === 'curso').length} cursos • 
                    ${results.filter(r => r.tipo === 'faq').length} preguntas • 
                    ${results.filter(r => r.tipo === 'servicio').length} servicios
                </small>
            </div>
        </div>
    `;
    
    results.forEach(result => {
        const icon = result.icon || getTypeIcon(result.tipo);
        const typeName = result.tipo_nombre || getTypeName(result.tipo);
        const typeColor = result.color || getTypeColor(result.tipo);
        
        html += `
            <div class="search-result-item-detailed" onclick="navigateToResult('${result.seccion || ''}', ${result.id}, '${result.url || ''}')">
                <div class="result-header">
                    <div class="result-title-section">
                        <div class="result-icon" style="color: ${typeColor};">
                            <i class="${icon}"></i>
                        </div>
                        <div class="result-title">
                            <h6 style="margin-bottom: 5px;">${result.titulo}</h6>
                            <div class="result-meta">
                                <span class="badge" style="background-color: ${typeColor}; color: white;">${typeName}</span>
                                ${result.categoria && result.categoria !== typeName ? `<span class="badge" style="background-color: #6c757d; margin-left: 8px;">${result.categoria}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="result-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); showResultDetails(${result.id}, '${result.tipo}');">
                            <i class="fas fa-info-circle"></i> Detalles
                        </button>
                    </div>
                </div>
                
                <div class="result-description">
                    ${result.descripcion}
                </div>
                
                ${result.detalles ? `
                <div class="result-details">
                    ${Object.entries(result.detalles).slice(0, 3).map(([key, value]) => `
                        <span class="detail-item">
                            <strong>${key}:</strong> ${value}
                        </span>
                    `).join('')}
                </div>
                ` : ''}
            </div>
        `;
    });
    
    resultsContainer.innerHTML = html;
}

// Función para navegar al resultado
function navigateToResult(seccion, id, url) {
    closeSearch();
    
    // Si hay URL específica, ir directamente
    if (url) {
        window.location.href = url;
        return;
    }
    
    // Buscar la sección en el DOM
    const section = document.getElementById(seccion);
    if (section) {
        // Scroll suave a la sección
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Resaltar la sección
        section.classList.add('section-highlight');
        setTimeout(() => {
            section.classList.remove('section-highlight');
        }, 2000);
        
        showToast('Navegando a la sección encontrada', 'success');
    } else {
        showToast('Sección no encontrada', 'error');
    }
}

// Función para mostrar toast
function showToast(message, type = 'info') {
    const toast = document.getElementById('searchToast');
    const toastBody = document.getElementById('toastBody');
    
    toastBody.textContent = message;
    
    // Cambiar color según tipo
    toast.className = `toast toast-${type}`;
    
    // Mostrar toast
    $('#searchToast').fadeIn().delay(3000).fadeOut();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Búsqueda en tiempo real con debounce
    document.getElementById('indexSearchInput').addEventListener('input', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => {
            performIndexSearch();
        }, 300);
    });
    
    // Buscar con Enter
    document.getElementById('indexSearchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            clearTimeout(window.searchTimeout);
            performIndexSearch();
        }
    });
    
    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSearch();
        }
    });
    
    // Cerrar al hacer clic fuera
    document.getElementById('searchDropdown').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSearch();
        }
    });
});

// Funciones auxiliares
function getTypeIcon(type) {
    switch (type) {
        case 'curso': return 'fas fa-graduation-cap';
        case 'faq': return 'fas fa-question-circle';
        case 'servicio': return 'fas fa-cogs';
        default: return 'fas fa-search';
    }
}

function getTypeName(type) {
    switch (type) {
        case 'curso': return 'Curso';
        case 'faq': return 'Pregunta Frecuente';
        case 'servicio': return 'Servicio';
        default: return 'Resultado';
    }
}
</script>



<section class="slider-section">
    <div id="mainCarousel" class="carousel slide" data-ride="carousel" data-interval="4000">
        <!-- Indicadores -->
        <ol class="carousel-indicators">
            <?php foreach ($sliders as $key => $slider): ?>
                <li data-target="#mainCarousel" data-slide-to="<?php echo $key; ?>" <?php echo $key == 0 ? 'class="active"' : ''; ?>></li>
            <?php endforeach; ?>
        </ol>

        <!-- Slides -->
        <div class="carousel-inner">
            <?php foreach ($sliders as $key => $slider): ?>
                <div class="item <?php echo $key == 0 ? 'active' : ''; ?>">
                    <?php if (file_exists('assets/uploads/' . $slider['foto'])): ?>
                        <img src="assets/uploads/<?php echo $slider['foto']; ?>" alt="<?php echo $slider['titulo']; ?>" class="carousel-image">
                    <?php endif; ?>
                    <div class="carousel-caption">
                        <h2><?php echo $slider['titulo']; ?></h2>
                        <p><?php echo $slider['contenido']; ?></p>
                        <?php if ($slider['texto_boton']): ?>
                            <a href="<?php echo $slider['url_boton']; ?>" class="btn btn-primary btn-lg">
                                <?php echo $slider['texto_boton']; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Controles -->
        <a class="left carousel-control" href="#mainCarousel" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left"></span>
        </a>
        <a class="right carousel-control" href="#mainCarousel" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right"></span>
        </a>
    </div>
</section>

<script>
$(document).ready(function() {
    // Verificar que Bootstrap esté cargado
    if (typeof $.fn.carousel !== 'undefined') {
        // Inicializar carousel con autoplay
        $('#mainCarousel').carousel({
            interval: 4000,
            pause: "hover",
            wrap: true
        });
        
        // Asegurar que el carousel funcione automáticamente
        $('#mainCarousel').carousel('cycle');
        
        // Debug: verificar que solo un slide esté activo
        console.log('Carousel inicializado correctamente');
        console.log('Slides activos:', $('.carousel-inner .item.active').length);
        
        // Forzar que solo el primer slide esté activo al inicio
        $('.carousel-inner .item').removeClass('active');
        $('.carousel-inner .item:first').addClass('active');
        
        $('.carousel-indicators li').removeClass('active');
        $('.carousel-indicators li:first').addClass('active');
        
    } else {
        console.error('Bootstrap carousel no está disponible');
    }
});
</script>

<style>
.slider-section {
    margin-top: -20px;
    position: relative;
    width: 100%;
}

.carousel {
    margin-bottom: 0;
    border-radius: 0;
    overflow: hidden;
    box-shadow: none;
    position: relative;
    width: 100%;
}



.carousel .item {
    height: 80vh;
    min-height: 500px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    display: none;
}

.carousel .item.active {
    display: block;
}

.carousel-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

.carousel-caption {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    width: 90%;
    max-width: 900px;
    z-index: 3;
}

.carousel-caption h2 {
    font-size: 3.5rem;
    font-weight: 800;
    color: white;
    margin-bottom: 1.5rem;
    letter-spacing: -1px;
    line-height: 1.2;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.carousel-caption p {
    font-size: 1.3rem;
    color: white;
    margin-bottom: 2.5rem;
    line-height: 1.6;
    font-weight: 500;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
}

.carousel-caption .btn {
    padding: 1.2rem 3rem;
    font-size: 1.1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    border: 2px solid white;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.carousel-caption .btn:hover {
    background: white;
    color: black;
    transform: translateY(-2px);
}

.carousel-control {
    width: 40px;
    height: 40px;
    opacity: 0;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    margin: 0 15px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    position: absolute;
    z-index: 10;
}

.carousel-control.left {
    left: 10px;
}

.carousel-control.right {
    right: 10px;
}

.carousel:hover .carousel-control {
    opacity: 0.7;
}

.carousel-control:hover {
    background: rgba(255, 255, 255, 0.95);
    transform: translateY(-50%) scale(1.05);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    opacity: 1;
}

.carousel-control .glyphicon {
    font-size: 18px;
    color: #3b82f6;
    text-shadow: none;
    line-height: 40px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.carousel-indicators {
    bottom: 30px;
    z-index: 3;
}

.carousel-indicators li {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    margin: 0 8px;
    background-color: rgba(255, 255, 255, 0.6);
    border: 3px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    cursor: pointer;
}

.carousel-indicators li.active {
    background-color: #667eea;
    transform: scale(1.3);
    border-color: #667eea;
    box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
}

/* Estilos adicionales para el carousel */
.carousel-control {
    cursor: pointer;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.carousel-caption {
    z-index: 15;
}

.carousel-indicators {
    z-index: 15;
}

/* Responsive para el carousel */
@media (max-width: 768px) {
    .carousel .item {
        height: 60vh;
        min-height: 400px;
    }

    .carousel-caption {
        width: 95%;
        max-width: 600px;
    }

    .carousel-caption h2 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .carousel-caption p {
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
    }

    .carousel-caption .btn {
        padding: 1rem 2.5rem;
        font-size: 1rem;
    }
    
    .carousel-control {
        width: 35px;
        height: 35px;
        margin: 0 10px;
    }
    
    .carousel-control.left {
        left: 5px;
    }
    
    .carousel-control.right {
        right: 5px;
    }
    
    .carousel-control .glyphicon {
        font-size: 16px;
        line-height: 35px;
    }
    
    .carousel-indicators li {
        width: 14px;
        height: 14px;
        margin: 0 6px;
    }
}

@media (max-width: 480px) {
    .carousel .item {
        height: 50vh;
        min-height: 350px;
    }

    .carousel-caption {
        width: 98%;
        max-width: 400px;
    }

    .carousel-caption h2 {
        font-size: 2rem;
        margin-bottom: 0.8rem;
    }

    .carousel-caption p {
        font-size: 1rem;
        margin-bottom: 1.2rem;
    }

    .carousel-caption .btn {
        padding: 0.8rem 2rem;
        font-size: 0.9rem;
    }
    
    .carousel-control {
        width: 30px;
        height: 30px;
        margin: 0 8px;
    }
    
    .carousel-control.left {
        left: 3px;
    }
    
    .carousel-control.right {
        right: 3px;
    }
    
    .carousel-control .glyphicon {
        font-size: 14px;
        line-height: 30px;
    }
    
    .carousel-indicators {
        bottom: 20px;
    }
    
    .carousel-indicators li {
        width: 12px;
        height: 12px;
        margin: 0 4px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Inicializar carousel con auto-play
    $('#mainCarousel').carousel({
        interval: 3000,
        pause: 'hover',
        wrap: true
    });

    // Asegurar que el carousel funcione correctamente
    $('#mainCarousel').carousel('cycle');
    
    // Forzar el inicio del ciclo
    setTimeout(function() {
        $('#mainCarousel').carousel('cycle');
    }, 100);
});
</script>

<?php if ($bienvenida_activa): ?>
<section class="welcome-section" style="padding: 50px 0;">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <h2>Bienvenido a nuestro Sistema de Certificados</h2>
                <p>Ofrecemos cursos de alta calidad con certificación oficial</p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="verification-section">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <div class="verification-content">
                <h2 class="verification-title">
                    <i class="fa fa-shield"></i> Verificación de Certificados
                </h2>
                <p class="verification-subtitle">
                    Todos nuestros certificados incluyen códigos QR únicos para verificar su autenticidad en tiempo real.
                    Escanea el código QR de tu certificado y verifica su validez instantáneamente.
                </p>
                <div class="verification-features">
                    <div class="feature-item">
                        <i class="fa fa-check-circle"></i>
                        <span>Verificación instantánea</span>
                    </div>
                    <div class="feature-item">
                        <i class="fa fa-mobile"></i>
                        <span>Acceso desde cualquier dispositivo</span>
                    </div>
                    <div class="feature-item">
                        <i class="fa fa-lock"></i>
                        <span>Certificados seguros y auténticos</span>
                    </div>
                </div>
                <div class="verification-actions">
                        <a href="verificar-qr.php" class="btn btn-primary btn-lg">
                        <i class="fa fa-qrcode"></i> Verificar Certificado
                    </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.verification-section {
    background: linear-gradient(135deg, #3b82f6 0%, #9333ea 50%, #ec4899 100%);
    padding: 80px 0;
    position: relative;
    overflow: hidden;
    color: white;
}

.verification-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.verification-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    margin: 0 auto;
}

.verification-title {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 2rem;
    position: relative;
    z-index: 1;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.verification-title i {
    margin-right: 15px;
    color: #28a745;
}

.verification-subtitle {
    font-size: 1.3rem;
    margin-bottom: 3rem;
    opacity: 0.95;
    line-height: 1.7;
    position: relative;
    z-index: 1;
    font-weight: 300;
}

.verification-features {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 30px;
    margin-bottom: 3rem;
}

.feature-item {
    display: flex;
    align-items: center;
    font-size: 1.1rem;
    background: rgba(255, 255, 255, 0.1);
    padding: 15px 25px;
    border-radius: 50px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.feature-item:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.feature-item i {
    margin-right: 12px;
    font-size: 1.3rem;
    color: #28a745;
}

.verification-actions {
    position: relative;
    z-index: 1;
}

.verification-actions .btn {
    padding: 15px 40px;
    font-size: 1.2rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: rgba(255, 255, 255, 0.98);
    color: #3b82f6;
    border: none;
    border-radius: 50px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.verification-actions .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transition: left 0.5s;
}

.verification-actions .btn:hover::before {
    left: 100%;
}

.verification-actions .btn:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    background: #fff;
}

/* Responsive para la sección de verificación */
@media (max-width: 768px) {
    .verification-section {
        padding: 60px 0;
    }
    
    .verification-title {
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
    }
    
    .verification-subtitle {
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }
    
    .verification-features {
        gap: 20px;
        margin-bottom: 2rem;
    }
    
    .feature-item {
        padding: 12px 20px;
        font-size: 1rem;
    }
    
    .verification-actions .btn {
        padding: 12px 30px;
        font-size: 1.1rem;
    }
}

@media (max-width: 480px) {
    .verification-section {
        padding: 40px 0;
    }
    
    .verification-title {
        font-size: 2rem;
        margin-bottom: 1rem;
    }
    
    .verification-subtitle {
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .verification-features {
        flex-direction: column;
        gap: 15px;
        margin-bottom: 1.5rem;
    }
    
    .feature-item {
        padding: 10px 15px;
        font-size: 0.9rem;
        justify-content: center;
    }
    
    .verification-actions .btn {
        padding: 10px 25px;
        font-size: 1rem;
    }
}

.verification-actions {
    position: relative;
    z-index: 1;
}

.verification-actions .btn {
    transition: all 0.3s ease;
}

.verification-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

@media (max-width: 768px) {
    .verification-title {
        font-size: 2rem;
    }

    .verification-subtitle {
        font-size: 1rem;
    }

    .verification-actions {
        margin-top: 2rem;
    }
}
</style>

<?php if ($certificados_activos == 1): ?>
    <div id="cursos-recientes" class="courses-section pt_70 pb_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="headline">
                        <h2>Cursos Recientes</h2>
                        <h3>Descubre nuestros últimos cursos disponibles</h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php $i = 0; foreach ($cursos_recientes as $curso): ?>
                    <div class="col-md-3" style="margin-bottom: 30px;">
                        <div class="course-card-modern">
                            <!-- Imagen del curso -->
                            <div class="course-image">
                                <?php if (!empty($curso['diseño']) && file_exists('assets/uploads/cursos/' . $curso['diseño'])): ?>
                                    <img src="assets/uploads/cursos/<?php echo $curso['diseño']; ?>" alt="<?php echo htmlspecialchars($curso['nombre_curso']); ?>" class="img-responsive">
                                <?php else: ?>
                                    <div class="course-image-placeholder">
                                        <i class="fas fa-graduation-cap"></i>
                                </div>
                                <?php endif; ?>
                                <div class="course-overlay">
                                    <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-primary">Ver Curso</a>
                                </div>
                            </div>
                            
                            <!-- Contenido del curso -->
                            <div class="course-content">
                                <div class="course-category">
                                    <span class="badge badge-category"><?php echo htmlspecialchars($curso['nombre_categoria']); ?></span>
                                </div>
                                
                                <h4 class="course-title">
                                    <a href="curso.php?id=<?php echo $curso['idcurso']; ?>"><?php echo htmlspecialchars($curso['nombre_curso']); ?></a>
                                </h4>
                                
                                <p class="course-description"><?php echo substr(htmlspecialchars($curso['descripcion']), 0, 120) . '...'; ?></p>
                                
                                <!-- Información del instructor -->
                                <?php if (!empty($curso['instructor_nombre'])): ?>
                                    <div class="course-instructor">
                                        <i class="fas fa-user-tie"></i>
                                        <span><?php echo htmlspecialchars($curso['instructor_nombre'] . ' ' . $curso['instructor_apellido']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Detalles del curso -->
                                <div class="course-details">
                                    <div class="detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo htmlspecialchars($curso['duracion']); ?> horas</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo htmlspecialchars($curso['dias_semana']); ?></span>
                                    </div>
                                    <?php if (!empty($curso['precio']) && $curso['precio'] > 0): ?>
                                        <div class="detail-item price">
                                            <i class="fas fa-tag"></i>
                                            <span class="price-value">S/ <?php echo number_format($curso['precio'], 2); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Botón de acción -->
                                <div class="course-action">
                                    <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ((++$i) % 4 == 0): ?>
                        <div class="clearfix visible-md-block visible-lg-block"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="cursos-populares" class="courses-section bg-light pt_70 pb_70">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="headline">
                        <h2>Cursos Populares</h2>
                        <h3>Los cursos más solicitados por nuestros estudiantes</h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php $i = 0; foreach ($cursos_populares as $curso): ?>
                    <div class="col-md-3" style="margin-bottom: 30px;">
                        <div class="course-card-modern popular">
                            <!-- Badge de popular -->
                            <div class="popular-badge">
                                <i class="fas fa-fire"></i> Popular
                                </div>
                            
                            <!-- Imagen del curso -->
                            <div class="course-image">
                                <?php if (!empty($curso['diseño']) && file_exists('assets/uploads/cursos/' . $curso['diseño'])): ?>
                                    <img src="assets/uploads/cursos/<?php echo $curso['diseño']; ?>" alt="<?php echo htmlspecialchars($curso['nombre_curso']); ?>" class="img-responsive">
                                <?php else: ?>
                                    <div class="course-image-placeholder">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="course-overlay">
                                    <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-primary">Ver Curso</a>
                                </div>
                            </div>
                            
                            <!-- Contenido del curso -->
                            <div class="course-content">
                                <div class="course-category">
                                    <span class="badge badge-category"><?php echo htmlspecialchars($curso['nombre_categoria']); ?></span>
                                    <span class="badge badge-popular"><?php echo $curso['total_inscripciones']; ?> estudiantes</span>
                                </div>
                                
                                <h4 class="course-title">
                                    <a href="curso.php?id=<?php echo $curso['idcurso']; ?>"><?php echo htmlspecialchars($curso['nombre_curso']); ?></a>
                                </h4>
                                
                                <p class="course-description"><?php echo substr(htmlspecialchars($curso['descripcion']), 0, 120) . '...'; ?></p>
                                
                                <!-- Información del instructor -->
                                <?php if (!empty($curso['instructor_nombre'])): ?>
                                    <div class="course-instructor">
                                        <i class="fas fa-user-tie"></i>
                                        <span><?php echo htmlspecialchars($curso['instructor_nombre'] . ' ' . $curso['instructor_apellido']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Detalles del curso -->
                                <div class="course-details">
                                    <div class="detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo htmlspecialchars($curso['duracion']); ?> horas</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo htmlspecialchars($curso['dias_semana']); ?></span>
                                    </div>
                                    <?php if (!empty($curso['precio']) && $curso['precio'] > 0): ?>
                                        <div class="detail-item price">
                                            <i class="fas fa-tag"></i>
                                            <span class="price-value">S/ <?php echo number_format($curso['precio'], 2); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Botón de acción -->
                                <div class="course-action">
                                    <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ((++$i) % 4 == 0): ?>
                        <div class="clearfix visible-md-block visible-lg-block"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <section class="featured-courses py-5">
        <div class="container">
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-12 text-center">
                    <h2 class="section-title">Cursos Destacados</h2>
                    <p class="section-subtitle">Explora nuestros cursos más populares</p>
                </div>
            </div>

            <div class="row">
                <?php foreach ($cursos_populares as $curso): ?>
                    <div class="col-md-4" style="margin-bottom: 20px;">
                        <div class="panel panel-default course-card" style="height: 100%;">
                            <div class="panel-body">
                                <h5 class="panel-title"><?php echo $curso['nombre_curso']; ?></h5>
                                <p><?php echo substr($curso['descripcion'], 0, 100) . '...'; ?></p>
                                <div class="course-details">
                                    <span><i class="fas fa-clock"></i> <?php echo $curso['duracion']; ?> horas</span>
                                    <span><i class="fas fa-calendar"></i> <?php echo $curso['dias_semana']; ?></span>
                                </div>
                            </div>
                            <div class="panel-footer" style="background: transparent; border: none;">
                                <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-primary" style="width: 100%;">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <style>
    .featured-courses {
        background-color: #f8f9fa;
    }

    .section-title {
        color: #333;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .section-subtitle {
        color: #666;
        font-size: 1.2rem;
        margin-bottom: 2rem;
    }

    .course-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .course-card .card-title {
        color: #333;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .course-card .card-text {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .course-details {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        font-size: 0.85rem;
        color: #666;
    }

    .course-details span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .course-details i {
        color: #007bff;
    }

    .card-footer .btn {
        background-color: #007bff;
        border: none;
        padding: 0.75rem;
        font-weight: 500;
        transition: background-color 0.3s ease;
    }

    .card-footer .btn:hover {
        background-color: #0056b3;
    }
    
    /* Estilos para las nuevas tarjetas de cursos modernas */
    .course-card-modern {
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        height: 100%;
    }
    
    .course-card-modern:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }
    
    .course-card-modern.popular {
        border: 2px solid #ffc107;
    }
    
    .popular-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: linear-gradient(45deg, #ff6b6b, #ffc107);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        z-index: 10;
        box-shadow: 0 2px 10px rgba(255, 193, 7, 0.3);
    }
    
    .course-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }
    
    .course-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .course-card-modern:hover .course-image img {
        transform: scale(1.1);
    }
    
    .course-image-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }
    
    .course-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .course-card-modern:hover .course-overlay {
        opacity: 1;
    }
    
    .course-content {
        padding: 20px;
    }
    
    .course-category {
        margin-bottom: 15px;
    }
    
    .badge-category {
        background: #007bff;
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 11px;
        font-weight: 500;
    }
    
    .course-title {
        margin-bottom: 10px;
        font-size: 1.2rem;
        font-weight: 600;
        line-height: 1.3;
    }
    
    .course-title a {
        color: #333;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .course-title a:hover {
        color: #007bff;
    }
    
    .course-description {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 15px;
    }
    
    .course-instructor {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        font-size: 0.85rem;
        color: #555;
    }
    
    .course-instructor i {
        color: #007bff;
        margin-right: 8px;
        font-size: 1rem;
    }
    
    .course-details {
        margin-bottom: 20px;
    }
    
    .detail-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        font-size: 0.85rem;
        color: #666;
    }
    
    .detail-item i {
        color: #007bff;
        margin-right: 8px;
        width: 16px;
        text-align: center;
    }
    
    .detail-item.price {
        font-weight: 600;
        color: #28a745;
    }
    
    .price-value {
        font-size: 1.1rem;
        font-weight: 700;
    }
    
    .course-action {
        margin-top: auto;
    }
    
    .course-action .btn {
        width: 100%;
        padding: 12px;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .course-action .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .course-card-modern {
            margin-bottom: 20px;
        }
        
        .course-image {
            height: 180px;
        }
        
        .course-title {
            font-size: 1.1rem;
        }
        
        .course-description {
            font-size: 0.85rem;
        }
    }
    </style>
<?php endif; ?>

<?php if ($servicios_activos == 1): ?>
    <section class="services-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center" style="margin-bottom: 30px;">
                    <h2 class="section-title">Nuestros Servicios</h2>
                    <p class="section-subtitle">Descubre todo lo que podemos ofrecerte</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($servicios as $servicio): ?>
                    <div class="col-md-4" style="margin-bottom: 20px;">
                        <div class="service-card">
                            <?php if (file_exists('assets/uploads/' . $servicio['foto'])): ?>
                                <div class="service-image">
                                    <img src="assets/uploads/<?php echo $servicio['foto']; ?>" alt="<?php echo $servicio['titulo']; ?>" class="img-fluid">
                                </div>
                            <?php endif; ?>
                            <div class="service-content">
                                <h3 class="service-title"><?php echo $servicio['titulo']; ?></h3>
                                <p class="service-description"><?php echo $servicio['contenido']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <style>
    .services-section {
        background-color: #f8f9fa;
        padding: 80px 0;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1rem;
        position: relative;
        padding-bottom: 15px;
    }

    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background-color: #007bff;
    }

    .section-subtitle {
        font-size: 1.2rem;
        color: #6c757d;
        margin-bottom: 3rem;
    }

    .service-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .service-image {
        height: 200px;
        overflow: hidden;
    }

    .service-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .service-card:hover .service-image img {
        transform: scale(1.1);
    }

    .service-content {
        padding: 25px;
    }

    .service-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
    }

    .service-description {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .service-card {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 2rem;
        }
    }
    </style>
<?php endif; ?>

<?php if ($faq_activo == 1): ?>
    <section class="faq-button-section py-5 text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="section-title">¿Tienes dudas?</h2>
                    <p class="section-subtitle">Consulta nuestras preguntas frecuentes</p>
                                    <a href="faq.php" class="btn btn-primary btn-lg" style="margin-top: 15px;">
                        Ver Preguntas Frecuentes
                    <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <style>
    .faq-button-section {
        background-color: #f8f9fa;
        padding: 60px 0;
    }

    .faq-button-section .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1rem;
    }

    .faq-button-section .section-subtitle {
        font-size: 1.2rem;
        color: #6c757d;
        margin-bottom: 2rem;
    }

    .faq-button-section .btn {
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 50px;
        transition: all 0.3s ease;
    }

    .faq-button-section .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    @media (max-width: 768px) {
        .faq-button-section .section-title {
            font-size: 2rem;
        }

        .faq-button-section .section-subtitle {
            font-size: 1rem;
        }

        .faq-button-section .btn {
            font-size: 1rem;
            padding: 0.8rem 1.5rem;
        }
    }
    </style>
<?php endif; ?>
<!-- Botón flotante de WhatsApp -->
<div class="whatsapp-float">
    <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="whatsapp-btn">
        <i class="fab fa-whatsapp"></i>
    </a>
</div>

<style>
.whatsapp-float {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.whatsapp-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background-color: #25d366;
    color: white;
    border-radius: 50%;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
    transition: all 0.3s ease;
}

.whatsapp-btn:hover {
    background-color: #128c7e;
    transform: scale(1.1);
    color: white;
    text-decoration: none;
}

.whatsapp-btn i {
    font-size: 30px;
}
</style>

<?php include 'newsletter-subscribe.php'; ?>

<?php require_once('footer.php'); ?>

<script>
// Función para obtener el color del tipo
function getTypeColor(tipo) {
    const colors = {
        'curso': '#007bff',
        'servicio': '#28a745',
        'faq': '#ffc107',
        'categoria': '#6c757d'
    };
    return colors[tipo] || '#6c757d';
}

// Función para mostrar detalles completos del resultado
function showResultDetails(id, tipo) {
    // Crear modal con detalles completos
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'resultDetailsModal';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Resultado</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="resultDetailsContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Cargar detalles via AJAX
    fetch(`search-predictive.php?query=id:${id}&tipo=${tipo}`)
        .then(response => response.json())
        .then(data => {
            if (data.results && data.results.length > 0) {
                const result = data.results[0];
                document.getElementById('resultDetailsContent').innerHTML = `
                    <div class="result-details-full">
                        <h4>${result.titulo}</h4>
                        <div class="result-meta-full mb-3">
                            <span class="badge" style="background-color: ${result.color}; color: white;">${result.tipo_nombre}</span>
                            ${result.categoria ? `<span class="badge bg-secondary ms-2">${result.categoria}</span>` : ''}
                        </div>
                        <div class="result-description-full mb-3">
                            ${result.descripcion}
                        </div>
                        ${result.detalles ? `
                        <div class="result-details-grid">
                            ${Object.entries(result.detalles).map(([key, value]) => `
                                <div class="detail-item-full">
                                    <strong>${key}:</strong> ${value}
                                </div>
                            `).join('')}
                        </div>
                        ` : ''}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('resultDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    Error al cargar los detalles
                </div>
            `;
        });
    
    // Mostrar modal
    $(modal).modal('show');
    
    // Limpiar modal al cerrar
    $(modal).on('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

// Configurar búsqueda predictiva con debounce
document.getElementById('indexSearchInput').addEventListener('input', function() {
    clearTimeout(window.searchTimeout);
    window.searchTimeout = setTimeout(() => {
        performIndexSearch();
    }, 300); // Esperar 300ms después de que el usuario deje de escribir
});

// Configurar búsqueda al presionar Enter
document.getElementById('indexSearchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        clearTimeout(window.searchTimeout);
        performIndexSearch();
    }
});
</script>

<style>
/* Estilos para búsqueda predictiva detallada */
.search-result-item-detailed {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.search-result-item-detailed:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0,123,255,0.15);
    transform: translateY(-2px);
}

.result-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.result-title-section {
    display: flex;
    align-items: flex-start;
    flex: 1;
}

.result-icon {
    font-size: 1.5rem;
    margin-right: 0.75rem;
    margin-top: 0.25rem;
}

.result-title h6 {
    margin: 0;
    color: #333;
    font-weight: 600;
}

.result-meta {
    margin-top: 0.5rem;
}

.result-actions {
    flex-shrink: 0;
}

.result-description {
    color: #6c757d;
    line-height: 1.5;
    margin-bottom: 0.75rem;
}

.result-details {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

.detail-item {
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    color: #495057;
}

.search-results-header {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 1rem;
}

.search-stats {
    margin-top: 0.5rem;
}

.suggestions-container {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
}

/* Estilos para modal de detalles */
.result-details-full h4 {
    color: #333;
    margin-bottom: 1rem;
}

.result-meta-full {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.result-description-full {
    color: #6c757d;
    line-height: 1.6;
    font-size: 1rem;
}

.result-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.detail-item-full {
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 6px;
    border-left: 3px solid #007bff;
}

/* Responsive */
@media (max-width: 768px) {
    .result-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .result-actions {
        margin-top: 0.75rem;
        align-self: flex-end;
    }
    
    .result-details-grid {
        grid-template-columns: 1fr;
    }
}
</style>